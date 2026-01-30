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
    header("Location: ../trips/list.php");
    exit;
}

$trip_id = $_GET['trip_id'];

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

$message = "";

/* ADD NOTE */
if (isset($_POST['add_note'])) {
    $note = $_POST['note'];

    mysqli_query($conn,
        "INSERT INTO notes (trip_id, user_id, note)
         VALUES ('$trip_id','$user_id','$note')"
    );

    $message = "Note added successfully";
}
$tripInfo = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT title, destination FROM trips WHERE id = $trip_id"
));

$tripLabel = $tripInfo['title'] . " (" . $tripInfo['destination'] . ")";

mysqli_query($conn,
    "INSERT INTO activity_logs (user_id, action)
     VALUES ($user_id, 'Added a note to: $tripLabel')"
);

/* FETCH NOTES */
$notes = mysqli_query($conn,
    "SELECT notes.note, notes.created_at, users.name
     FROM notes
     JOIN users ON users.id = notes.user_id
     WHERE notes.trip_id = $trip_id
     ORDER BY notes.created_at DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trip Notes | TripSquad</title>
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
    .note-card {
      transition: all 0.3s ease;
      border-left: 4px solid #E6B3A1;
    }
    .note-card:hover {
      transform: translateX(4px);
      box-shadow: 0 4px 12px rgba(230, 179, 161, 0.2);
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

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

  <div class="fade-in mb-12 text-center">
    <h1 class="text-4xl sm:text-5xl font-bold mb-3">📝 Trip Notes</h1>
    <p class="text-lg opacity-75">Share ideas, reminders, and important details</p>
  </div>

  <div class="card-hover rounded-2xl p-8 mb-8 fade-in shadow-lg" style="animation-delay: 0.1s">
    <div class="flex items-center gap-3 mb-6">
      <span class="text-3xl">✍️</span>
      <h2 class="text-2xl font-semibold">Add a Note</h2>
    </div>

    <?php if ($message) { ?>
      <div class="mb-6 p-4 rounded-lg border bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 border-green-300 dark:border-green-700">
        <?= $message ?>
      </div>
    <?php } ?>

    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-sm font-medium mb-2 opacity-75">Your Note</label>
        <textarea 
          name="note" 
          required
          rows="5"
          placeholder="Packing list, meeting point, important reminders, restaurant recommendations..."
          class="w-full px-4 py-3 rounded-lg border-2 focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all resize-none"
        ></textarea>
      </div>

      <button 
        name="add_note"
        class="w-full sm:w-auto px-6 py-3 rounded-lg font-semibold hover:scale-105 active:scale-95 transition-all shadow-md"
      >
        Add Note
      </button>
    </form>
  </div>

  <div class="card-hover rounded-2xl p-8 fade-in shadow-lg" style="animation-delay: 0.2s">
    <h2 class="text-2xl font-semibold mb-6">Shared Notes</h2>

    <?php if (mysqli_num_rows($notes) > 0) { ?>
      <div class="space-y-4">
        <?php while ($n = mysqli_fetch_assoc($notes)) { ?>
          <div class="note-card rounded-xl p-5">
            <div class="flex items-start gap-3 mb-3">
              <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0" style="background: linear-gradient(135deg, #E6B3A1, #D99C8A);">
                <?= strtoupper(substr($n['name'], 0, 1)) ?>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 text-sm opacity-75">
                  <span class="font-semibold"><?= htmlspecialchars($n['name']) ?></span>
                  <span>•</span>
                  <span><?= date("M j, Y", strtotime($n['created_at'])) ?></span>
                  <span>•</span>
                  <span><?= date("g:i A", strtotime($n['created_at'])) ?></span>
                </div>
              </div>
            </div>
            <div class="pl-13">
              <p class="leading-relaxed whitespace-pre-wrap">
                <?= htmlspecialchars($n['note']) ?>
              </p>
            </div>
          </div>
        <?php } ?>
      </div>
    <?php } else { ?>
      <div class="text-center py-12">
        <div class="text-6xl mb-4">📝</div>
        <p class="text-xl font-medium mb-2">No notes yet</p>
        <p class="opacity-60">Start sharing ideas and reminders with your travel group</p>
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
  note: '#FFFFFF'
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
  note: '#333333'
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
  
  document.querySelectorAll('textarea').forEach(el => {
    el.style.backgroundColor = colors.input;
    el.style.borderColor = colors.inputBorder;
    el.style.color = colors.text;
  });
  
  document.querySelectorAll('button[name="add_note"]').forEach(el => {
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
  
  document.querySelectorAll('.note-card').forEach(el => {
    el.style.backgroundColor = colors.note;
  });
  
  document.querySelectorAll('label').forEach(el => {
    el.style.color = colors.textSecondary;
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