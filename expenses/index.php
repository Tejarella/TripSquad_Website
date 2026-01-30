<?php
session_start();
include "../config/db.php";

/* AUTH CHECK */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['trip_id'])) {
    header("Location: ../trips/list.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$trip_id = $_GET['trip_id'];

/* AUTHORIZATION CHECK */
$authCheck = mysqli_query($conn,
    "SELECT trips.id
     FROM trips
     LEFT JOIN trip_members ON trips.id = trip_members.trip_id
     WHERE trips.id = $trip_id
       AND (trips.created_by = $user_id
            OR trip_members.user_id = $user_id)"
);
if (mysqli_num_rows($authCheck) == 0) {
    die("Unauthorized access");
}

/* FETCH TRIP INFO */
$tripInfo = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT title, destination FROM trips WHERE id = $trip_id"
));
$tripLabel = $tripInfo['title']." (".$tripInfo['destination'].")";

/* ADD EXPENSE */
if (isset($_POST['add_expense'])) {

    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $paid_by = $_POST['paid_by'];
    $participants = $_POST['participants'] ?? [];

    if (count($participants) == 0) {
        die("At least one participant is required");
    }

    /* Insert expense */
    mysqli_query($conn,
        "INSERT INTO expenses (trip_id, paid_by, amount, description)
         VALUES ($trip_id, $paid_by, $amount, '$description')"
    );

    $expense_id = mysqli_insert_id($conn);

    /* Insert participants */
    foreach ($participants as $pid) {
        mysqli_query($conn,
            "INSERT INTO expense_participants (expense_id, user_id)
             VALUES ($expense_id, $pid)"
        );
    }

    /* Activity log */
    mysqli_query($conn,
        "INSERT INTO activity_logs (user_id, action)
         VALUES ($user_id, 'Added expense to: $tripLabel')"
    );

    header("Location: index.php?trip_id=$trip_id");
    exit;
}

/* SETTLEMENT */
if (isset($_POST['settle'])) {
    $from = $_POST['from'];
    $to = $_POST['to'];
    $amount = $_POST['amount'];

    mysqli_query($conn,
        "INSERT INTO settlements (trip_id, paid_by, paid_to, amount)
         VALUES ($trip_id, $from, $to, $amount)"
    );

    mysqli_query($conn,
        "INSERT INTO activity_logs (user_id, action)
         VALUES ($user_id, 'Settled an expense in: $tripLabel')"
    );

    header("Location: index.php?trip_id=$trip_id");
    exit;
}

/* FETCH MEMBERS */
$members = [];
$membersQuery = mysqli_query($conn,
    "SELECT users.id, users.name
     FROM users
     JOIN trip_members ON users.id = trip_members.user_id
     WHERE trip_members.trip_id = $trip_id
     UNION
     SELECT id, name FROM users WHERE id =
       (SELECT created_by FROM trips WHERE id = $trip_id)"
);

while ($m = mysqli_fetch_assoc($membersQuery)) {
    $members[$m['id']] = ['name'=>$m['name'], 'paid'=>0, 'balance'=>0];
}

/* FETCH EXPENSES */
$expenses = [];
$totalExpense = 0;

$expenseQuery = mysqli_query($conn,
    "SELECT expenses.*, users.name
     FROM expenses
     JOIN users ON users.id = expenses.paid_by
     WHERE expenses.trip_id = $trip_id"
);

while ($e = mysqli_fetch_assoc($expenseQuery)) {
    $members[$e['paid_by']]['paid'] += $e['amount'];
    $totalExpense += $e['amount'];
    $expenses[] = $e;
}

/* FETCH SETTLEMENTS */
$settleQuery = mysqli_query($conn,
    "SELECT * FROM settlements WHERE trip_id = $trip_id"
);
while ($s = mysqli_fetch_assoc($settleQuery)) {
    $members[$s['paid_by']]['paid'] += $s['amount'];
    $members[$s['paid_to']]['paid'] -= $s['amount'];
}

/* RESET BALANCES */
foreach ($members as $id => $m) {
    $members[$id]['balance'] = 0;
}

/* PROCESS EACH EXPENSE WITH PARTICIPANTS */
$expenseQuery = mysqli_query($conn,
    "SELECT * FROM expenses WHERE trip_id = $trip_id"
);

while ($e = mysqli_fetch_assoc($expenseQuery)) {

    $expense_id = $e['id'];
    $amount = $e['amount'];
    $paid_by = $e['paid_by'];

    /* Get participants for this expense */
    $partQuery = mysqli_query($conn,
        "SELECT user_id FROM expense_participants
         WHERE expense_id = $expense_id"
    );

    $participants = [];
    while ($p = mysqli_fetch_assoc($partQuery)) {
        $participants[] = $p['user_id'];
    }

    if (count($participants) == 0) continue;

    $split = $amount / count($participants);

    /* Each participant owes split */
    foreach ($participants as $uid) {
        $members[$uid]['balance'] -= $split;
    }

    /* Payer gets full credit */
    $members[$paid_by]['balance'] += $amount;
}

/* APPLY SETTLEMENTS */
$settleQuery = mysqli_query($conn,
    "SELECT * FROM settlements WHERE trip_id = $trip_id"
);

while ($s = mysqli_fetch_assoc($settleQuery)) {
    $members[$s['paid_by']]['balance'] += $s['amount'];
    $members[$s['paid_to']]['balance'] -= $s['amount'];
}

/* PREPARE PAYERS & RECEIVERS */
$payers = $receivers = [];
foreach ($members as $id => $m) {
    if ($m['balance'] < 0) $payers[$id] = $m;
    elseif ($m['balance'] > 0) $receivers[$id] = $m;
}

/* SUMMARY DISPLAY VALUES (UI ONLY) */
$memberCount = count($members);
$averageShare = $memberCount > 0 ? $totalExpense / $memberCount : 0;

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Expenses | TripSquad</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    @keyframes slideIn {
      from { opacity: 0; transform: translateX(-20px); }
      to { opacity: 1; transform: translateX(0); }
    }
    @keyframes pulse {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.05); }
    }
    .fade-in { animation: fadeIn 0.6s ease-out; }
    .slide-in { animation: slideIn 0.5s ease-out; }
    .card-hover { transition: all 0.3s ease; }
    .card-hover:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.15); }
    * { transition: background-color 0.3s ease, color 0.3s ease; }
    .stat-card {
      position: relative;
      overflow: hidden;
    }
    .stat-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(230, 179, 161, 0.1), transparent);
      transition: left 0.5s;
    }
    .stat-card:hover::before {
      left: 100%;
    }
    .debt-card {
      border-left: 4px solid #E6B3A1;
      transition: all 0.3s ease;
    }
    .debt-card:hover {
      transform: translateX(8px);
      box-shadow: 0 6px 16px rgba(230, 179, 161, 0.2);
    }
    .expense-row {
      transition: all 0.2s ease;
    }
    .expense-row:hover {
      background-color: rgba(230, 179, 161, 0.1);
      transform: scale(1.01);
    }
  </style>
</head>

<body class="min-h-screen transition-colors duration-300">

<nav class="border-b sticky top-0 z-50 backdrop-blur-sm bg-opacity-90">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center h-16">
      <div class="flex items-center gap-8">
        <a href="../dashboard/index.php" class="flex items-center gap-2 text-xl font-bold hover:scale-105 transition-transform">
          <span class="text-2xl">🌴</span>
          <span>TripSquad</span>
        </a>
        <a href="../dashboard/index.php" class="px-4 py-2 rounded-lg font-medium hover:scale-105 transition-all">
          Dashboard
        </a>
      </div>
      <div class="flex items-center gap-4">
        <button id="themeToggle" class="p-2 rounded-lg hover:scale-110 transition-all text-2xl">
          🌙
        </button>
        <a href="../auth/logout.php" class="px-4 py-2 rounded-lg font-medium hover:scale-105 transition-all">
          Logout
        </a>
      </div>
    </div>
  </div>
</nav>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

  <div class="fade-in mb-12 text-center">
    <h1 class="text-4xl sm:text-5xl font-bold mb-3">💰 Trip Expenses</h1>
    <p class="text-lg opacity-75">Track spending and settle up with your group</p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
    <div class="stat-card card-hover rounded-2xl p-8 fade-in shadow-lg" style="animation-delay: 0.1s">
      <div class="flex items-center gap-3 mb-3">
        <div class="w-12 h-12 rounded-full flex items-center justify-center text-2xl" style="background: linear-gradient(135deg, #E6B3A1, #D99C8A);">
          💵
        </div>
        <p class="text-sm font-medium opacity-75">Total Expense</p>
      </div>
      <p class="text-3xl font-bold" style="color: #E6B3A1;">
        ₹<?= number_format($totalExpense, 2) ?>
      </p>
    </div>

    <div class="stat-card card-hover rounded-2xl p-8 fade-in shadow-lg" style="animation-delay: 0.2s">
      <div class="flex items-center gap-3 mb-3">
        <div class="w-12 h-12 rounded-full flex items-center justify-center text-2xl" style="background: linear-gradient(135deg, #BFD8D7, #8FA6A1);">
          📊
        </div>
        <p class="text-sm font-medium opacity-75">Average Per Person</p>
      </div>
      <p class="text-3xl font-bold">
        ₹<?= number_format($averageShare, 2) ?>
      </p>
      <p class="text-xs opacity-60 mt-2">(Varies by expense)</p>
    </div>

    <div class="stat-card card-hover rounded-2xl p-8 fade-in shadow-lg" style="animation-delay: 0.3s">
      <div class="flex items-center gap-3 mb-3">
        <div class="w-12 h-12 rounded-full flex items-center justify-center text-2xl" style="background: linear-gradient(135deg, #D8ECEB, #BFD8D7);">
          👥
        </div>
        <p class="text-sm font-medium opacity-75">Members</p>
      </div>
      <p class="text-3xl font-bold"><?= $memberCount ?></p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
    
    <div class="card-hover rounded-2xl p-8 fade-in shadow-lg" style="animation-delay: 0.4s">
      <div class="flex items-center gap-3 mb-6">
        <span class="text-3xl">➕</span>
        <h2 class="text-2xl font-semibold">Add Expense</h2>
      </div>

      <form method="POST" class="space-y-5">
        <div>
          <label class="block text-sm font-medium mb-2 opacity-75">Amount (₹)</label>
          <input 
            type="number" 
            step="0.01" 
            name="amount" 
            required
            placeholder="0.00"
            class="w-full px-4 py-3 rounded-lg border-2 focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all text-lg font-semibold"
          >
        </div>

        <div>
          <label class="block text-sm font-medium mb-2 opacity-75">Description</label>
          <input 
            type="text" 
            name="description" 
            required
            placeholder="Hotel, Food, Taxi, Activities..."
            class="w-full px-4 py-3 rounded-lg border-2 focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all"
          >
        </div>

        <div>
          <label class="block text-sm font-medium mb-2 opacity-75">Paid By</label>
          <select 
            name="paid_by" 
            required
            class="w-full px-4 py-3 rounded-lg border-2 focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all"
          >
            <?php foreach ($members as $id => $m) { ?>
              <option value="<?= $id ?>"><?= htmlspecialchars($m['name']) ?></option>
            <?php } ?>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium mb-2 opacity-75">Split Between</label>
          <div class="rounded-lg border-2 p-4 space-y-3">
            <?php foreach ($members as $id => $m) { ?>
              <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-opacity-50 cursor-pointer transition-all">
                <input 
                  type="checkbox" 
                  name="participants[]" 
                  value="<?= $id ?>"
                  checked
                  class="w-5 h-5 rounded cursor-pointer"
                >
                <span class="font-medium"><?= htmlspecialchars($m['name']) ?></span>
              </label>
            <?php } ?>
          </div>
        </div>

        <button 
          name="add_expense"
          class="w-full px-6 py-3 rounded-lg font-semibold hover:scale-105 active:scale-95 transition-all shadow-md"
        >
          Add Expense
        </button>
      </form>
    </div>

    <div class="card-hover rounded-2xl p-8 fade-in shadow-lg" style="animation-delay: 0.5s">
      <div class="flex items-center gap-3 mb-6">
        <span class="text-3xl">🤝</span>
        <h2 class="text-2xl font-semibold">Settlements</h2>
      </div>

      <?php 
      $hasDebts = false;
      foreach ($payers as $pid => $p) {
        foreach ($receivers as $rid => $r) {
          $amt = min(abs($p['balance']), $r['balance']);
          if ($amt <= 0) continue;
          $hasDebts = true;
      ?>
        <div class="debt-card rounded-xl p-5 mb-4 slide-in">
          <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-bold" style="background: linear-gradient(135deg, #E6B3A1, #D99C8A);">
                  <?= strtoupper(substr($p['name'], 0, 1)) ?>
                </div>
                <span class="font-semibold"><?= htmlspecialchars($p['name']) ?></span>
              </div>
              <p class="text-sm opacity-75 mb-1">owes</p>
              <div class="flex items-center gap-2 mb-3">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-bold" style="background: linear-gradient(135deg, #BFD8D7, #8FA6A1);">
                  <?= strtoupper(substr($r['name'], 0, 1)) ?>
                </div>
                <span class="font-semibold"><?= htmlspecialchars($r['name']) ?></span>
              </div>
              <p class="text-2xl font-bold" style="color: #E6B3A1;">
                ₹<?= number_format($amt, 2) ?>
              </p>
            </div>

            <?php if ($pid == $user_id) { ?>
              <form method="POST" class="flex flex-col gap-2">
                <input type="hidden" name="from" value="<?= $pid ?>">
                <input type="hidden" name="to" value="<?= $rid ?>">
                
                <input 
                  type="number"
                  name="amount"
                  step="0.01"
                  value="<?= number_format($amt, 2, '.', '') ?>"
                  class="w-28 px-3 py-2 rounded-lg border-2 text-center font-semibold focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all"
                >
                
                <button 
                  name="settle"
                  class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-semibold hover:scale-105 active:scale-95 transition-all shadow-md"
                >
                  Pay Now
                </button>
              </form>
            <?php } ?>
          </div>
        </div>
      <?php }} 
      
      if (!$hasDebts) { ?>
        <div class="text-center py-12">
          <div class="text-6xl mb-4">✅</div>
          <p class="text-xl font-medium mb-2">All settled up!</p>
          <p class="opacity-60">No pending payments</p>
        </div>
      <?php } ?>
    </div>

  </div>

  <div class="card-hover rounded-2xl p-8 fade-in shadow-lg" style="animation-delay: 0.6s">
    <div class="flex items-center gap-3 mb-6">
      <span class="text-3xl">🧾</span>
      <h2 class="text-2xl font-semibold">Expense History</h2>
    </div>

    <?php if (count($expenses) > 0) { ?>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b-2">
              <th class="text-left py-4 px-4 font-semibold opacity-75">Person</th>
              <th class="text-left py-4 px-4 font-semibold opacity-75">Description</th>
              <th class="text-right py-4 px-4 font-semibold opacity-75">Amount</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($expenses as $e) { ?>
              <tr class="expense-row border-b">
                <td class="py-4 px-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold" style="background: linear-gradient(135deg, #E6B3A1, #D99C8A);">
                      <?= strtoupper(substr($e['name'], 0, 1)) ?>
                    </div>
                    <span class="font-medium"><?= htmlspecialchars($e['name']) ?></span>
                  </div>
                </td>
                <td class="py-4 px-4">
                  <span class="opacity-90"><?= htmlspecialchars($e['description']) ?></span>
                </td>
                <td class="py-4 px-4 text-right">
                  <span class="text-lg font-bold" style="color: #E6B3A1;">
                    ₹<?= number_format($e['amount'], 2) ?>
                  </span>
                </td>
              </tr>
            <?php } ?>
          </tbody>
          <tfoot>
            <tr class="border-t-2">
              <td colspan="2" class="py-4 px-4 text-right font-semibold text-lg">Total</td>
              <td class="py-4 px-4 text-right text-2xl font-bold" style="color: #E6B3A1;">
                ₹<?= number_format($totalExpense, 2) ?>
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    <?php } else { ?>
      <div class="text-center py-12">
        <div class="text-6xl mb-4">💸</div>
        <p class="text-xl font-medium mb-2">No expenses yet</p>
        <p class="opacity-60">Start tracking your trip expenses above</p>
      </div>
    <?php } ?>
  </div>

</main>

<button id="backToTop" class="fixed bottom-8 right-8 w-12 h-12 rounded-full shadow-lg flex items-center justify-center text-white font-bold text-xl hover:scale-110 active:scale-95 transition-all opacity-0 pointer-events-none" style="background-color: #E6B3A1;">
  ↑
</button>

<footer class="text-center py-8 mt-16 opacity-60 text-sm border-t">
  © 2026 TripSquad • Travel together, smarter
</footer>

<script>
const html = document.documentElement;
const themeToggle = document.getElementById('themeToggle');
const backToTop = document.getElementById('backToTop');

const lightTheme = {
  bg: '#F3EFEF',
  card: '#D8ECEB',
  cardHover: '#BFD8D7',
  accent: '#E6B3A1',
  accentHover: '#D99C8A',
  text: '#2E2E2E',
  textSecondary: '#6B6B6B',
  border: '#E0D6D2',
  nav: '#D8ECEB',
  input: '#FFFFFF',
  inputBorder: '#E0D6D2',
  debt: '#FFFFFF',
  row: '#FFFFFF'
};

const darkTheme = {
  bg: '#1E1E1E',
  card: '#2A2A2A',
  cardHover: '#333333',
  accent: '#E6B3A1',
  accentHover: '#D99C8A',
  text: '#EDEDED',
  textSecondary: '#A0A0A0',
  border: '#404040',
  nav: '#2A2A2A',
  input: '#1E1E1E',
  inputBorder: '#404040',
  debt: '#333333',
  row: '#2A2A2A'
};

function applyTheme(theme) {
  const colors = theme === 'dark' ? darkTheme : lightTheme;
  
  document.body.style.backgroundColor = colors.bg;
  document.body.style.color = colors.text;
  
  document.querySelectorAll('nav').forEach(el => {
    el.style.backgroundColor = colors.nav;
    el.style.borderColor = colors.border;
  });
  
  document.querySelectorAll('nav a, nav button').forEach(el => {
    el.style.color = colors.text;
  });
  
  document.querySelectorAll('.card-hover').forEach(el => {
    el.style.backgroundColor = colors.card;
    el.style.color = colors.text;
  });
  
  document.querySelectorAll('input, select, textarea').forEach(el => {
    el.style.backgroundColor = colors.input;
    el.style.borderColor = colors.inputBorder;
    el.style.color = colors.text;
  });
  
  document.querySelectorAll('button[name="add_expense"]').forEach(el => {
    el.style.backgroundColor = colors.accent;
    el.style.color = theme === 'dark' ? colors.text : '#2E2E2E';
  });
  
  document.querySelectorAll('nav a:not(:first-child)').forEach(el => {
    el.addEventListener('mouseenter', function() {
      this.style.backgroundColor = colors.cardHover;
    });
    el.addEventListener('mouseleave', function() {
      this.style.backgroundColor = 'transparent';
    });
  });
  
  document.querySelectorAll('.debt-card').forEach(el => {
    el.style.backgroundColor = colors.debt;
  });
  
  document.querySelectorAll('label:not(.flex)').forEach(el => {
    el.style.color = colors.textSecondary;
  });
  
  document.querySelectorAll('th').forEach(el => {
    el.style.borderColor = colors.border;
  });
  
  document.querySelectorAll('tr').forEach(el => {
    el.style.borderColor = colors.border;
  });
  
  document.querySelectorAll('tfoot tr').forEach(el => {
    el.style.borderColor = colors.border;
  });
  
  document.querySelectorAll('footer').forEach(el => {
    el.style.borderColor = colors.border;
    el.style.color = colors.textSecondary;
  });
  
  themeToggle.textContent = theme === 'dark' ? '☀️' : '🌙';
}

const savedTheme = localStorage.getItem('theme') || 'light';
applyTheme(savedTheme);

themeToggle.addEventListener('click', () => {
  const currentTheme = localStorage.getItem('theme') || 'light';
  const newTheme = currentTheme === 'light' ? 'dark' : 'light';
  localStorage.setItem('theme', newTheme);
  applyTheme(newTheme);
});

window.addEventListener('scroll', () => {
  if (window.scrollY > 300) {
    backToTop.style.opacity = '1';
    backToTop.style.pointerEvents = 'auto';
  } else {
    backToTop.style.opacity = '0';
    backToTop.style.pointerEvents = 'none';
  }
});

backToTop.addEventListener('click', () => {
  window.scrollTo({ top: 0, behavior: 'smooth' });
});
</script>

</body>
</html>