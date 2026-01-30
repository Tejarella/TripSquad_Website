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

/* FETCH TRIP */
$tripQuery = mysqli_query($conn,
    "SELECT * FROM trips WHERE id = $trip_id"
);

if (mysqli_num_rows($tripQuery) == 0) {
    die("Trip not found");
}

$trip = mysqli_fetch_assoc($tripQuery);

/* ONLY CREATOR CAN EDIT */
if ($trip['created_by'] != $user_id) {
    die("Unauthorized access");
}

$message = "";
$success = false;

/* UPDATE DATES */
if (isset($_POST['update_dates'])) {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    if ($start_date > $end_date) {
        $message = "Start date cannot be after end date";
    } else {
        mysqli_query($conn,
            "UPDATE trips
             SET start_date = '$start_date',
                 end_date = '$end_date'
             WHERE id = $trip_id"
        );

        $tripInfo = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT title, destination FROM trips WHERE id = $trip_id"
        ));

        $tripLabel = $tripInfo['title'] . " (" . $tripInfo['destination'] . ")";

        mysqli_query($conn,
            "INSERT INTO activity_logs (user_id, action)
             VALUES ($user_id, 'Edited trip dates for: $tripLabel')"
        );

        $success = true;
        $message = "Trip dates updated successfully!";
        
        // Redirect after 1.5 seconds
        header("refresh:1.5;url=list.php");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Trip Dates | TripSquad</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fadeIn { 
            animation: fadeIn 0.6s ease-out forwards; 
        }
        
        .animate-slideDown { 
            animation: slideDown 0.4s ease-out; 
        }
        
        /* Light Mode */
        body.light { 
            background: #F3EFEF; 
            color: #2E2E2E; 
        }
        
        body.light .card { 
            background: #D8ECEB; 
            border: 1px solid #E0D6D2;
        }
        
        body.light .input-field {
            background: #BFD8D7;
            border: 1px solid #E0D6D2;
            color: #2E2E2E;
        }
        
        body.light .input-field:focus {
            border-color: #E6B3A1;
            outline: none;
            ring: 2px;
            ring-color: rgba(230, 179, 161, 0.3);
        }
        
        body.light .text-secondary {
            color: #6B6B6B;
        }
        
        body.light .label {
            color: #2E2E2E;
        }
        
        /* Dark Mode */
        body.dark { 
            background: #1E1E1E; 
            color: #EDEDED; 
        }
        
        body.dark .card { 
            background: #2A2A2A; 
            border: 1px solid #3A3A3A;
        }
        
        body.dark .input-field {
            background: #1E1E1E;
            border: 1px solid #3A3A3A;
            color: #EDEDED;
        }
        
        body.dark .input-field:focus {
            border-color: #E6B3A1;
            outline: none;
            ring: 2px;
            ring-color: rgba(230, 179, 161, 0.3);
        }
        
        body.dark .text-secondary {
            color: #B0B0B0;
        }
        
        body.dark .label {
            color: #EDEDED;
        }
        
        /* Accent Colors */
        .accent { 
            background: #E6B3A1; 
        }
        
        .accent:hover { 
            background: #D99C8A; 
        }
        
        .link-accent:hover {
            color: #E6B3A1;
        }
        
        /* Smooth transitions */
        * { 
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease; 
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }
        
        body.light ::-webkit-scrollbar-track {
            background: #F3EFEF;
        }
        
        body.light ::-webkit-scrollbar-thumb {
            background: #BFD8D7;
            border-radius: 5px;
        }
        
        body.dark ::-webkit-scrollbar-track {
            background: #1E1E1E;
        }
        
        body.dark ::-webkit-scrollbar-thumb {
            background: #3A3A3A;
            border-radius: 5px;
        }
    </style>
</head>
<body class="light min-h-screen flex flex-col justify-center items-center p-4 sm:p-8">

    <!-- Theme Toggle (Top Right) -->
    <button onclick="toggleTheme()" class="fixed top-6 right-6 p-3 rounded-full card hover:scale-110 transition-transform shadow-lg z-50">
        <span class="theme-icon text-2xl">☀️</span>
    </button>

    <!-- Main Card -->
    <div class="card p-8 sm:p-10 rounded-3xl w-full max-w-lg shadow-2xl animate-fadeIn">

        <!-- Header -->
        <div class="text-center mb-8">
            <div class="text-5xl mb-4">🗓️</div>
            <h1 class="text-3xl sm:text-4xl font-bold mb-3 bg-gradient-to-r from-[#E6B3A1] to-[#D99C8A] bg-clip-text text-transparent">
                Edit Trip Dates
            </h1>
            <p class="text-secondary text-lg">
                <?= htmlspecialchars($trip['title']) ?>
            </p>
            <p class="text-secondary text-sm mt-1">
                📍 <?= htmlspecialchars($trip['destination']) ?>
            </p>
        </div>

        <!-- Message -->
        <?php if ($message) { ?>
            <div class="mb-6 p-4 rounded-xl <?= $success ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300' ?> animate-slideDown">
                <p class="text-center font-semibold flex items-center justify-center gap-2">
                    <?= $success ? '✅' : '⚠️' ?>
                    <?= htmlspecialchars($message) ?>
                </p>
            </div>
        <?php } ?>

        <!-- Form -->
        <form method="POST" class="space-y-6">
            
            <!-- Start Date -->
            <div>
                <label class="block mb-2 label font-semibold text-sm uppercase tracking-wide">
                    Start Date
                </label>
                <input type="date" 
                       name="start_date" 
                       required
                       value="<?= htmlspecialchars($trip['start_date']) ?>"
                       class="input-field w-full p-4 rounded-xl text-lg transition-all">
            </div>

            <!-- End Date -->
            <div>
                <label class="block mb-2 label font-semibold text-sm uppercase tracking-wide">
                    End Date
                </label>
                <input type="date" 
                       name="end_date" 
                       required
                       value="<?= htmlspecialchars($trip['end_date']) ?>"
                       class="input-field w-full p-4 rounded-xl text-lg transition-all">
            </div>

            <!-- Duration Display -->
            <div class="text-center text-secondary text-sm p-3 rounded-lg card">
                <span id="duration">Calculate duration by selecting dates</span>
            </div>

            <!-- Submit Button -->
            <button type="submit" 
                    name="update_dates"
                    class="w-full accent text-white py-4 rounded-xl font-bold text-lg shadow-lg hover:scale-105 transition-transform">
                💾 Update Dates
            </button>
        </form>

        <!-- Back Link -->
        <a href="list.php" 
           class="block text-center mt-6 text-secondary hover:text-[#E6B3A1] font-medium transition-colors">
            ← Back to Trips
        </a>

    </div>

    <!-- Footer -->
    <footer class="mt-8 text-center text-secondary text-sm">
        <p>© TripSquad • Plan Smart. Travel Together.</p>
    </footer>

<script>
// Theme Toggle
function toggleTheme() {
    const body = document.body;
    const icon = document.querySelector('.theme-icon');
    
    if (body.classList.contains('light')) {
        body.classList.remove('light');
        body.classList.add('dark');
        icon.textContent = '🌙';
        localStorage.setItem('theme', 'dark');
    } else {
        body.classList.remove('dark');
        body.classList.add('light');
        icon.textContent = '☀️';
        localStorage.setItem('theme', 'light');
    }
}

// Load saved theme
window.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme') || 'light';
    const body = document.body;
    const icon = document.querySelector('.theme-icon');
    
    if (savedTheme === 'dark') {
        body.classList.remove('light');
        body.classList.add('dark');
        icon.textContent = '🌙';
    }
});

// Calculate duration between dates
function calculateDuration() {
    const startDate = document.querySelector('input[name="start_date"]').value;
    const endDate = document.querySelector('input[name="end_date"]').value;
    const durationDisplay = document.getElementById('duration');
    
    if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        const diffTime = end - start;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays < 0) {
            durationDisplay.textContent = '⚠️ End date must be after start date';
            durationDisplay.style.color = '#ef4444';
        } else if (diffDays === 0) {
            durationDisplay.textContent = '📅 Same day trip';
            durationDisplay.style.color = '';
        } else {
            const nights = diffDays;
            const days = diffDays + 1;
            durationDisplay.textContent = `📅 ${days} days, ${nights} night${nights !== 1 ? 's' : ''}`;
            durationDisplay.style.color = '';
        }
    }
}

// Add event listeners for date inputs
document.addEventListener('DOMContentLoaded', () => {
    const startDateInput = document.querySelector('input[name="start_date"]');
    const endDateInput = document.querySelector('input[name="end_date"]');
    
    startDateInput.addEventListener('change', calculateDuration);
    endDateInput.addEventListener('change', calculateDuration);
    
    // Calculate on page load
    calculateDuration();
});
</script>

</body>
</html>