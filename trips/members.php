<?php
session_start();
include "../config/db.php";

/* LOGIN CHECK */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* TRIP ID CHECK */
if (!isset($_GET['trip_id'])) {
    header("Location: list.php");
    exit;
}

$trip_id = $_GET['trip_id'];

/* FETCH TRIP + CREATOR */
$tripQuery = mysqli_query($conn,
    "SELECT id, created_by FROM trips WHERE id = $trip_id"
);

if (mysqli_num_rows($tripQuery) == 0) {
    die("Trip not found");
}

$trip = mysqli_fetch_assoc($tripQuery);
$creator_id = $trip['created_by'];

/* AUTHORIZATION CHECK (creator OR member) */
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

$isCreator = ($user_id == $creator_id);
$message = "";

/* ---------------- ADD MEMBER (CREATOR ONLY) ---------------- */
if (isset($_POST['add_member']) && $isCreator) {
    $email = $_POST['email'];

    $userQuery = mysqli_query($conn,
        "SELECT id FROM users WHERE email = '$email'"
    );

    if (mysqli_num_rows($userQuery) == 1) {
        $member = mysqli_fetch_assoc($userQuery);
        $member_id = $member['id'];

        if ($member_id == $creator_id) {
            $message = "Creator is already part of the trip";
        } else {
            $exists = mysqli_query($conn,
                "SELECT id FROM trip_members
                 WHERE trip_id = $trip_id AND user_id = $member_id"
            );

            if (mysqli_num_rows($exists) == 0) {
                mysqli_query($conn,
                    "INSERT INTO trip_members (trip_id, user_id)
                     VALUES ($trip_id, $member_id)"
                );
                $message = "Member added successfully";
            } else {
                $message = "Member already exists";
            }
        }
    } else {
        $message = "User not found";
    }
}
$tripInfo = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT title, destination FROM trips WHERE id = $trip_id"
));

$tripLabel = $tripInfo['title'] . " (" . $tripInfo['destination'] . ")";

mysqli_query($conn,
    "INSERT INTO activity_logs (user_id, action)
     VALUES ($user_id, 'Added a member to: $tripLabel')"
);

/* ---------------- REMOVE MEMBER (CREATOR ONLY) ---------------- */
if (isset($_GET['remove']) && $isCreator) {
    $remove_id = $_GET['remove'];

    // Prevent creator from removing themselves
    if ($remove_id != $creator_id) {
        mysqli_query($conn,
            "DELETE FROM trip_members
             WHERE trip_id = $trip_id AND user_id = $remove_id"
        );
        header("Location: members.php?trip_id=$trip_id");
        exit;
    }
}
$tripInfo = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT title, destination FROM trips WHERE id = $trip_id"
));

$tripLabel = $tripInfo['title'] . " (" . $tripInfo['destination'] . ")";

mysqli_query($conn,
    "INSERT INTO activity_logs (user_id, action)
     VALUES ($user_id, 'Removed a member from: $tripLabel')"
);

/* FETCH MEMBERS */
$members = mysqli_query($conn,
    "SELECT users.id, users.name, users.email
     FROM trip_members
     JOIN users ON users.id = trip_members.user_id
     WHERE trip_members.trip_id = $trip_id"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trip Members | TripSquad</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .fade-in { animation: fadeIn 0.6s ease-out; }
    .card-hover { transition: all 0.3s ease; }
    .card-hover:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.15); }
    * { transition: background-color 0.3s ease, color 0.3s ease; }
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

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

  <div class="fade-in mb-12 text-center">
    <h1 class="text-4xl sm:text-5xl font-bold mb-3">👥 Trip Members</h1>
    <p class="text-lg opacity-75">Manage who's part of this trip</p>
  </div>

  <?php if ($isCreator) { ?>
  <div class="card-hover rounded-2xl p-8 mb-8 fade-in shadow-lg" style="animation-delay: 0.1s">
    <div class="flex items-center gap-3 mb-6">
      <span class="text-3xl">➕</span>
      <h2 class="text-2xl font-semibold">Add Member</h2>
    </div>

    <?php if ($message) { 
      $isSuccess = strpos($message, 'successfully') !== false;
      $alertClass = $isSuccess ? 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 border-green-300 dark:border-green-700' : 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 border-red-300 dark:border-red-700';
    ?>
      <div class="mb-6 p-4 rounded-lg border <?= $alertClass ?>">
        <?= $message ?>
      </div>
    <?php } ?>

    <form method="POST" class="flex flex-col sm:flex-row gap-3">
      <input 
        type="email" 
        name="email" 
        required
        placeholder="friend@email.com"
        class="flex-1 px-4 py-3 rounded-lg border-2 focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all"
      >
      <button 
        name="add_member"
        class="px-6 py-3 rounded-lg font-semibold hover:scale-105 active:scale-95 transition-all shadow-md"
      >
        Add Member
      </button>
    </form>
  </div>
  <?php } ?>

  <div class="card-hover rounded-2xl p-8 fade-in shadow-lg" style="animation-delay: 0.2s">
    <h2 class="text-2xl font-semibold mb-6">Current Members</h2>

    <?php 
    $memberCount = mysqli_num_rows($members);
    mysqli_data_seek($members, 0);
    
    if ($memberCount == 0 && !$isCreator) { 
    ?>
      <div class="text-center py-12">
        <div class="text-6xl mb-4">👥</div>
        <p class="text-xl font-medium mb-2">No members yet</p>
        <p class="opacity-60">Add friends to collaborate on this trip</p>
      </div>
    <?php } else { ?>
      <div class="space-y-3">
        
        <div class="flex items-center justify-between p-4 rounded-xl">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg" style="background: linear-gradient(135deg, #E6B3A1, #D99C8A);">
              <?= strtoupper(substr($_SESSION['user_name'] ?? 'C', 0, 1)) ?>
            </div>
            <div>
              <div class="font-semibold"><?= $_SESSION['user_name'] ?? 'You' ?></div>
              <div class="text-sm opacity-60"><?= $_SESSION['user_email'] ?? '' ?></div>
            </div>
          </div>
          <span class="px-4 py-1.5 rounded-full text-sm font-semibold" style="background-color: #E6B3A1; color: #2E2E2E;">
            Creator
          </span>
        </div>

        <?php while ($m = mysqli_fetch_assoc($members)) { ?>
          <div class="flex items-center justify-between p-4 rounded-xl hover:scale-[1.01] transition-all">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-lg" style="background: linear-gradient(135deg, #BFD8D7, #8FA6A1);">
                <?= strtoupper(substr($m['name'], 0, 1)) ?>
              </div>
              <div>
                <div class="font-semibold"><?= htmlspecialchars($m['name']) ?></div>
                <div class="text-sm opacity-60"><?= htmlspecialchars($m['email']) ?></div>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <span class="px-4 py-1.5 rounded-full text-sm font-medium opacity-70">
                Member
              </span>
              <?php if ($isCreator) { ?>
                <a 
                  href="members.php?trip_id=<?= $trip_id ?>&remove=<?= $m['id'] ?>"
                  onclick="return confirm('Remove <?= htmlspecialchars($m['name']) ?> from this trip?')"
                  class="px-4 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm font-medium hover:scale-105 active:scale-95 transition-all"
                >
                  Remove
                </a>
              <?php } ?>
            </div>
          </div>
        <?php } ?>

        <?php if ($memberCount == 0 && $isCreator) { ?>
          <div class="text-center py-8 opacity-60">
            <div class="text-5xl mb-3">👥</div>
            <p class="text-lg mb-1">No members yet</p>
            <p class="text-sm">Add friends to collaborate on this trip</p>
          </div>
        <?php } ?>

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
  member: '#FFFFFF'
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
  member: '#333333'
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
  
  document.querySelectorAll('input[type="email"]').forEach(el => {
    el.style.backgroundColor = colors.input;
    el.style.borderColor = colors.inputBorder;
    el.style.color = colors.text;
  });
  
  document.querySelectorAll('button[name="add_member"]').forEach(el => {
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
  
  document.querySelectorAll('.space-y-3 > div:not(:first-child)').forEach(el => {
    el.style.backgroundColor = colors.member;
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