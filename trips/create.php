<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$prefillDestination = $_GET['destination'] ?? "";
$type = $_GET['type'] ?? "";

if (isset($_POST['create_trip'])) {

    $title = $_POST['title'];
    $destination = $_POST['destination'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    mysqli_query($conn,
        "INSERT INTO trips (title, destination, start_date, end_date, created_by)
         VALUES ('$title','$destination','$start_date','$end_date','$user_id')"
    );

    $action = "Created a new trip: " . $title;

    mysqli_query($conn,
        "INSERT INTO activity_logs (user_id, action)
         VALUES ('$user_id', '$action')"
    );

    header("Location: ../dashboard/index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Trip | TripSquad</title>
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
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .animate-fadeIn { animation: fadeIn 0.6s ease-out forwards; }
        .animate-slideDown { animation: slideDown 0.4s ease-out; }
        .animate-float { animation: float 3s ease-in-out infinite; }
        .glass-effect { backdrop-filter: blur(12px); background: rgba(255, 255, 255, 0.1); }
        body.light { background: linear-gradient(135deg, #F3EFEF 0%, #E0D6D2 100%); color: #2E2E2E; }
        body.dark { background: linear-gradient(135deg, #1E1E1E 0%, #0F0F0F 100%); color: #EDEDED; }
        .card-light { background: #D8ECEB; border: 1px solid #E0D6D2; }
        .card-dark { background: #2A2A2A; border: 1px solid #3A3A3A; }
        .accent { background: #E6B3A1; }
        .accent:hover { background: #D99C8A; }
        .input-light { background: #F3EFEF; border: 2px solid #E0D6D2; color: #2E2E2E; }
        .input-dark { background: #1E1E1E; border: 2px solid #3A3A3A; color: #EDEDED; }
        .input-light:focus { border-color: #E6B3A1; }
        .input-dark:focus { border-color: #E6B3A1; }
        * { transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease; }
        .pattern-bg {
            background-image: 
                radial-gradient(circle at 20% 50%, rgba(230, 179, 161, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(191, 216, 215, 0.1) 0%, transparent 50%);
        }
    </style>
</head>
<body class="light min-h-screen pattern-bg">

<nav class="fixed top-0 left-0 right-0 z-50 glass-effect border-b border-[#E0D6D2] dark:border-[#3A3A3A] animate-slideDown">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <a href="../index.php" class="flex items-center space-x-2 text-2xl font-bold">
                <span class="text-3xl">🌴</span>
                <span class="bg-gradient-to-r from-[#E6B3A1] to-[#D99C8A] bg-clip-text text-transparent">TripSquad</span>
            </a>
            
            <div class="flex items-center space-x-6">
                <a href="../dashboard/index.php" class="hover:text-[#E6B3A1] font-medium">Dashboard</a>
                <a href="../profile/index.php" class="hover:text-[#E6B3A1] font-medium">Profile</a>
                <button onclick="toggleTheme()" class="p-2 rounded-lg hover:bg-[#BFD8D7] dark:hover:bg-[#333333]">
                    <span class="theme-icon text-2xl">☀️</span>
                </button>
            </div>
        </div>
    </div>
</nav>

<div class="pt-24 pb-16 px-4 sm:px-6 lg:px-8 flex items-center justify-center min-h-screen">
    
    <div class="max-w-md w-full">
        
        <div class="text-center mb-8 animate-fadeIn">
            <div class="inline-block animate-float mb-4">
                <span class="text-7xl">✈️</span>
            </div>
            <h1 class="text-4xl font-bold mb-2 bg-gradient-to-r from-[#E6B3A1] to-[#D99C8A] bg-clip-text text-transparent">
                Create New Trip
            </h1>
            <p class="text-lg opacity-70">Plan your next adventure</p>
            <?php if ($type) { ?>
                <div class="mt-4 inline-block px-4 py-2 rounded-full accent text-white font-semibold text-sm">
                    <?= $type == 'domestic' ? '🇮🇳' : '🌍' ?> <?= ucfirst($type) ?> Trip
                </div>
            <?php } ?>
        </div>

        <form method="POST" class="card-light dark:card-dark p-8 rounded-2xl shadow-2xl animate-fadeIn" style="animation-delay: 0.2s;">
            
            <div class="mb-6">
                <label class="block mb-2 font-semibold text-sm uppercase tracking-wide opacity-70">
                    <span class="mr-1">📝</span> Trip Title
                </label>
                <input type="text" name="title" required
                    placeholder="e.g., Summer Beach Getaway"
                    class="input-light dark:input-dark w-full p-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#E6B3A1] transition-all">
            </div>

            <div class="mb-6">
                <label class="block mb-2 font-semibold text-sm uppercase tracking-wide opacity-70">
                    <span class="mr-1">📍</span> Destination
                </label>
                <input type="text" name="destination" required
                    value="<?= htmlspecialchars($prefillDestination) ?>"
                    placeholder="e.g., Goa, India"
                    class="input-light dark:input-dark w-full p-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#E6B3A1] transition-all">
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block mb-2 font-semibold text-sm uppercase tracking-wide opacity-70">
                        <span class="mr-1">🗓️</span> Start Date
                    </label>
                    <input type="date" name="start_date" required
                        class="input-light dark:input-dark w-full p-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#E6B3A1] transition-all">
                </div>

                <div>
                    <label class="block mb-2 font-semibold text-sm uppercase tracking-wide opacity-70">
                        <span class="mr-1">🏁</span> End Date
                    </label>
                    <input type="date" name="end_date" required
                        class="input-light dark:input-dark w-full p-4 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#E6B3A1] transition-all">
                </div>
            </div>

            <button name="create_trip" type="submit"
                class="w-full accent text-white py-4 rounded-xl font-bold text-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300 mb-4">
                ✨ Create Trip
            </button>

            <a href="../dashboard/index.php"
               class="block text-center opacity-70 hover:opacity-100 font-medium transition-opacity">
                ← Back to Dashboard
            </a>

        </form>

        <div class="mt-8 text-center opacity-60 text-sm animate-fadeIn" style="animation-delay: 0.4s;">
            <p>💡 Tip: You can add members and expenses after creating the trip</p>
        </div>

    </div>

</div>

<script>
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

const today = new Date().toISOString().split('T')[0];
document.querySelector('input[name="start_date"]').setAttribute('min', today);
document.querySelector('input[name="start_date"]').addEventListener('change', function() {
    document.querySelector('input[name="end_date"]').setAttribute('min', this.value);
});
</script>

</body>
</html>