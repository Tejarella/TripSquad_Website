<?php
session_start();
include "../config/db.php";

/* LOGIN CHECK */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

/* FETCH TRIPS (CREATOR OR MEMBER) */
$trips = mysqli_query($conn,
    "SELECT DISTINCT trips.*
     FROM trips
     LEFT JOIN trip_members 
       ON trips.id = trip_members.trip_id
     WHERE trips.created_by = $user_id
        OR trip_members.user_id = $user_id
     ORDER BY trips.start_date DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Trips | TripSquad</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            'dark-bg': '#0B1212',
            'dark-surface': '#1C2A2D',
            'dark-hover': '#24373B',
            'primary': '#3E5F63',
            'secondary': '#8FA6A1',
            'muted': '#6B7074',
            'text-main': '#E3ECE8'
          }
        }
      }
    }
  </script>
  <style>
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .fade-in {
      animation: fadeIn 0.6s ease-out;
    }
    .glass-nav {
      backdrop-filter: blur(12px);
      background: rgba(28, 42, 45, 0.8);
    }
  </style>
</head>

<body class="bg-dark-bg dark:bg-gray-50 text-text-main dark:text-gray-900 min-h-screen transition-colors duration-300">

<nav class="glass-nav dark:bg-white/90 fixed top-0 left-0 right-0 z-50 border-b border-dark-hover dark:border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center h-16">
      <div class="flex items-center space-x-8">
        <a href="../dashboard/index.php" class="flex items-center space-x-2 text-xl font-bold text-secondary dark:text-primary hover:text-primary dark:hover:text-secondary transition">
          <span>🌴</span>
          <span>TripSquad</span>
        </a>
        <a href="../dashboard/index.php" class="hidden sm:block text-sm hover:text-primary dark:hover:text-primary transition">
          Dashboard
        </a>
      </div>
      
      <div class="flex items-center space-x-4">
        <button onclick="toggleTheme()" class="p-2 rounded-lg hover:bg-dark-hover dark:hover:bg-gray-200 transition">
          <span class="dark:hidden">☀️</span>
          <span class="hidden dark:inline">🌙</span>
        </button>
        <a href="../auth/logout.php" class="px-4 py-2 bg-primary dark:bg-primary text-white rounded-lg hover:bg-opacity-80 transition">
          Logout
        </a>
      </div>
    </div>
  </div>
</nav>

<div class="pt-24 pb-12 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
  
  <div class="fade-in mb-8">
    <h1 class="text-4xl font-bold mb-2">📋 My Trips</h1>
    <p class="text-secondary dark:text-muted">All your upcoming, ongoing & completed trips</p>
  </div>

  <?php if (mysqli_num_rows($trips) > 0) { ?>
  
  <div class="space-y-4 fade-in">
    <?php while ($trip = mysqli_fetch_assoc($trips)) {
      if ($trip['start_date'] > $today) {
          $status = "Upcoming";
          $statusClass = "bg-green-500/20 text-green-400 dark:bg-green-100 dark:text-green-700";
      } elseif ($trip['end_date'] < $today) {
          $status = "Completed";
          $statusClass = "bg-muted/20 text-muted dark:bg-gray-200 dark:text-gray-600";
      } else {
          $status = "Ongoing";
          $statusClass = "bg-secondary/20 text-secondary dark:bg-blue-100 dark:text-blue-700";
      }
    ?>
    
    <div class="bg-dark-surface dark:bg-white rounded-xl p-6 border border-dark-hover dark:border-gray-200 hover:border-primary dark:hover:border-primary hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        
        <div class="flex-1 min-w-0">
          <h3 class="text-xl font-semibold mb-2 truncate"><?= htmlspecialchars($trip['title']) ?></h3>
          <div class="flex flex-wrap items-center gap-4 text-sm text-secondary dark:text-muted">
            <span>📍 <?= htmlspecialchars($trip['destination']) ?></span>
            <span>📅 <?= date('M d, Y', strtotime($trip['start_date'])) ?> → <?= date('M d, Y', strtotime($trip['end_date'])) ?></span>
            <span class="px-3 py-1 rounded-full text-xs font-semibold <?= $statusClass ?>">
              <?= $status ?>
            </span>
          </div>
        </div>

        <div class="flex flex-wrap gap-2">
          <?php if ($trip['created_by'] == $user_id) { ?>
          <a href="edit-dates.php?trip_id=<?= $trip['id'] ?>" 
             class="px-4 py-2 bg-primary/20 dark:bg-yellow-100 text-primary dark:text-yellow-700 rounded-lg hover:bg-primary/30 dark:hover:bg-yellow-200 transition inline-flex items-center gap-2 text-sm font-medium hover:scale-105">
            <span>✏️</span>
            <span>Edit Dates</span>
          </a>
          <a href="delete.php?trip_id=<?= $trip['id'] ?>" 
             onclick="return confirm('Are you sure you want to delete this trip? This action cannot be undone.')"
             class="px-4 py-2 bg-red-500/20 dark:bg-red-100 text-red-400 dark:text-red-700 rounded-lg hover:bg-red-500/30 dark:hover:bg-red-200 transition inline-flex items-center gap-2 text-sm font-medium hover:scale-105">
            <span>🗑</span>
            <span>Delete</span>
          </a>
          <?php } ?>
          
          <a href="members.php?trip_id=<?= $trip['id'] ?>" 
             class="px-4 py-2 bg-dark-hover dark:bg-gray-100 rounded-lg hover:bg-primary/30 dark:hover:bg-primary/20 transition inline-flex items-center gap-2 text-sm font-medium hover:scale-105">
            <span>👥</span>
            <span>Members</span>
          </a>
          
          <a href="../itinerary/index.php?trip_id=<?= $trip['id'] ?>" 
             class="px-4 py-2 bg-dark-hover dark:bg-gray-100 rounded-lg hover:bg-primary/30 dark:hover:bg-primary/20 transition inline-flex items-center gap-2 text-sm font-medium hover:scale-105">
            <span>🗓</span>
            <span>Itinerary</span>
          </a>
          
          <a href="../notes/index.php?trip_id=<?= $trip['id'] ?>" 
             class="px-4 py-2 bg-dark-hover dark:bg-gray-100 rounded-lg hover:bg-primary/30 dark:hover:bg-primary/20 transition inline-flex items-center gap-2 text-sm font-medium hover:scale-105">
            <span>📝</span>
            <span>Notes</span>
          </a>
          
          <a href="../expenses/index.php?trip_id=<?= $trip['id'] ?>" 
             class="px-4 py-2 bg-dark-hover dark:bg-gray-100 rounded-lg hover:bg-primary/30 dark:hover:bg-primary/20 transition inline-flex items-center gap-2 text-sm font-medium hover:scale-105">
            <span>💰</span>
            <span>Expenses</span>
          </a>
        </div>
      </div>
    </div>
    
    <?php } ?>
  </div>
  
  <?php } else { ?>
  
  <div class="fade-in flex flex-col items-center justify-center py-20">
    <div class="text-8xl mb-6">✈️</div>
    <h2 class="text-2xl font-semibold mb-2">No trips yet</h2>
    <p class="text-secondary dark:text-muted mb-6">Start planning your next adventure!</p>
    <a href="../dashboard/index.php" 
       class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-opacity-90 transition hover:scale-105 font-medium">
      Create Your First Trip
    </a>
  </div>
  
  <?php } ?>

</div>

<button id="backToTop" 
        onclick="scrollToTop()" 
        class="fixed bottom-8 right-8 p-4 bg-primary text-white rounded-full shadow-lg hover:bg-opacity-90 transition hover:scale-110 opacity-0 pointer-events-none">
  ⬆️
</button>

<footer class="mt-20 py-6 text-center text-sm text-muted dark:text-gray-500 border-t border-dark-hover dark:border-gray-200">
  © 2026 TripSquad • Travel together, smarter
</footer>

<script>
  function toggleTheme() {
    const html = document.documentElement;
    const isDark = html.classList.contains('dark');
    
    if (isDark) {
      html.classList.remove('dark');
      localStorage.setItem('theme', 'light');
    } else {
      html.classList.add('dark');
      localStorage.setItem('theme', 'dark');
    }
  }

  if (localStorage.getItem('theme') === 'light') {
    document.documentElement.classList.add('dark');
  }

  window.addEventListener('scroll', function() {
    const backToTop = document.getElementById('backToTop');
    if (window.pageYOffset > 300) {
      backToTop.classList.remove('opacity-0', 'pointer-events-none');
      backToTop.classList.add('opacity-100');
    } else {
      backToTop.classList.add('opacity-0', 'pointer-events-none');
      backToTop.classList.remove('opacity-100');
    }
  });

  function scrollToTop() {
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  }
</script>

</body>
</html>