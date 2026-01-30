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

/* ADD ITINERARY ITEM */
if (isset($_POST['add_itinerary'])) {
    $date = $_POST['trip_date'];
    $activity = $_POST['activity'];

    mysqli_query($conn,
        "INSERT INTO itinerary (trip_id, trip_date, activity)
         VALUES ('$trip_id','$date','$activity')"
    );

    $message = "Activity added successfully";
}
$tripInfo = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT title, destination FROM trips WHERE id = $trip_id"
));

$tripLabel = $tripInfo['title'] . " (" . $tripInfo['destination'] . ")";

mysqli_query($conn,
    "INSERT INTO activity_logs (user_id, action)
     VALUES ($user_id, 'Added itinerary activity to: $tripLabel')"
);

/* FETCH ITINERARY */
$items = mysqli_query($conn,
    "SELECT * FROM itinerary
     WHERE trip_id = $trip_id
     ORDER BY trip_date ASC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Itinerary | TripSquad</title>
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
    .timeline-item { position: relative; padding-left: 2rem; }
    .timeline-item::before {
      content: '';
      position: absolute;
      left: 0.375rem;
      top: 2.5rem;
      bottom: -1rem;
      width: 2px;
      background: linear-gradient(to bottom, #E6B3A1, transparent);
    }
    .timeline-item:last-child::before { display: none; }
    .timeline-dot {
      position: absolute;
      left: 0;
      top: 0.5rem;
      width: 1rem;
      height: 1rem;
      border-radius: 50%;
      background-color: #E6B3A1;
      box-shadow: 0 0 0 4px rgba(230, 179, 161, 0.2);
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
    <h1 class="text-4xl sm:text-5xl font-bold mb-3">🗓️ Trip Itinerary</h1>
    <p class="text-lg opacity-75">Plan your daily activities and adventures</p>
  </div>

  <div class="card-hover rounded-2xl p-8 mb-8 fade-in shadow-lg" style="animation-delay: 0.1s">
    <div class="flex items-center gap-3 mb-6">
      <span class="text-3xl">✨</span>
      <h2 class="text-2xl font-semibold">Add Activity</h2>
    </div>

    <?php if ($message) { ?>
      <div class="mb-6 p-4 rounded-lg border bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 border-green-300 dark:border-green-700">
        <?= $message ?>
      </div>
    <?php } ?>

    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-sm font-medium mb-2 opacity-75">Date</label>
        <input 
          type="date" 
          name="trip_date" 
          required
          class="w-full px-4 py-3 rounded-lg border-2 focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all"
        >
      </div>

      <div>
        <label class="block text-sm font-medium mb-2 opacity-75">Activity Description</label>
        <textarea 
          name="activity" 
          required
          rows="4"
          placeholder="Visit beach, sightseeing, dinner at local restaurant..."
          class="w-full px-4 py-3 rounded-lg border-2 focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all resize-none"
        ></textarea>
      </div>

      <button 
        name="add_itinerary"
        class="w-full sm:w-auto px-6 py-3 rounded-lg font-semibold hover:scale-105 active:scale-95 transition-all shadow-md"
      >
        Add Activity
      </button>
    </form>
  </div>

  <div class="card-hover rounded-2xl p-8 fade-in shadow-lg" style="animation-delay: 0.2s">
    <h2 class="text-2xl font-semibold mb-6">Your Schedule</h2>

    <?php if (mysqli_num_rows($items) > 0) { ?>
      <div class="space-y-1">
        <?php while ($row = mysqli_fetch_assoc($items)) { ?>
          <div class="timeline-item py-4">
            <div class="timeline-dot"></div>
            <div class="rounded-xl p-5 hover:scale-[1.01] transition-all">
              <div class="flex items-center gap-2 mb-3">
                <span class="text-2xl">📅</span>
                <p class="font-semibold text-lg">
                  <?= date("l, F j, Y", strtotime($row['trip_date'])) ?>
                </p>
              </div>
              <p class="leading-relaxed opacity-90">
                <?= nl2br(htmlspecialchars($row['activity'])) ?>
              </p>
            </div>
          </div>
        <?php } ?>
      </div>
    <?php } else { ?>
      <div class="text-center py-12">
        <div class="text-6xl mb-4">📅</div>
        <p class="text-xl font-medium mb-2">No activities planned yet</p>
        <p class="opacity-60">Start building your perfect itinerary above</p>
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
  timeline: '#FFFFFF'
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
  timeline: '#333333'
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
  
  document.querySelectorAll('input[type="date"], textarea').forEach(el => {
    el.style.backgroundColor = colors.input;
    el.style.borderColor = colors.inputBorder;
    el.style.color = colors.text;
  });
  
  document.querySelectorAll('button[name="add_itinerary"]').forEach(el => {
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
  
  document.querySelectorAll('.timeline-item > div').forEach(el => {
    el.style.backgroundColor = colors.timeline;
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