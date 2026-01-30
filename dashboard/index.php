<?php
session_start();
include "../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

$totalTrips = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(DISTINCT trips.id) AS total
     FROM trips
     LEFT JOIN trip_members ON trips.id = trip_members.trip_id
     WHERE trips.created_by = $user_id
        OR trip_members.user_id = $user_id"
))['total'];

$upcomingTrips = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(DISTINCT trips.id) AS upcoming
     FROM trips
     LEFT JOIN trip_members ON trips.id = trip_members.trip_id
     WHERE (trips.created_by = $user_id OR trip_members.user_id = $user_id)
       AND trips.start_date > '$today'"
))['upcoming'];

$completedTrips = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(DISTINCT trips.id) AS completed
     FROM trips
     LEFT JOIN trip_members ON trips.id = trip_members.trip_id
     WHERE (trips.created_by = $user_id OR trip_members.user_id = $user_id)
       AND trips.end_date < '$today'"
))['completed'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | TripSquad</title>
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
        @keyframes countUp {
            from { opacity: 0; transform: scale(0.5); }
            to { opacity: 1; transform: scale(1); }
        }
        
        .animate-fadeIn { animation: fadeIn 0.6s ease-out forwards; }
        .animate-slideDown { animation: slideDown 0.4s ease-out; }
        .animate-countUp { animation: countUp 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards; }
        
        .glass-effect { 
            backdrop-filter: blur(12px); 
        }
        
        .card-hover { 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
        }
        
        .card-hover:hover { 
            transform: translateY(-8px) scale(1.02); 
            box-shadow: 0 20px 40px rgba(0,0,0,0.15); 
        }
        
        /* Light Mode */
        body.light { 
            background: #F3EFEF; 
            color: #2E2E2E; 
        }
        
        body.light .glass-effect {
            background: rgba(255, 255, 255, 0.7);
            border-color: #E0D6D2;
        }
        
        body.light .card-light { 
            background: #D8ECEB; 
            border: 1px solid #E0D6D2;
            color: #2E2E2E;
        }
        
        body.light .secondary-card-light { 
            background: #BFD8D7;
            color: #2E2E2E;
        }
        
        body.light .text-secondary {
            color: #6B6B6B;
        }
        
        body.light .footer-border {
            border-color: #E0D6D2;
        }
        
        /* Dark Mode */
        body.dark { 
            background: #1E1E1E; 
            color: #EDEDED; 
        }
        
        body.dark .glass-effect {
            background: rgba(42, 42, 42, 0.7);
            border-color: #3A3A3A;
        }
        
        body.dark .card-dark { 
            background: #2A2A2A; 
            border: 1px solid #3A3A3A;
            color: #EDEDED;
        }
        
        body.dark .secondary-card-dark { 
            background: #333333;
            color: #EDEDED;
        }
        
        body.dark .text-secondary {
            color: #B0B0B0;
        }
        
        body.dark .footer-border {
            border-color: #3A3A3A;
        }
        
        /* Accent Colors */
        .accent { 
            background: #E6B3A1; 
        }
        
        .accent:hover { 
            background: #D99C8A; 
        }
        
        .text-accent {
            color: #E6B3A1;
        }
        
        /* Smooth transitions */
        * { 
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease; 
        }
        
        /* Consistent card heights */
        .destination-card {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .destination-card-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .destination-card-button {
            margin-top: auto;
        }
    </style>
</head>
<body class="light min-h-screen">

<!-- Navigation -->
<nav class="fixed top-0 left-0 right-0 z-50 glass-effect border-b animate-slideDown">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <a href="../index.php" class="flex items-center space-x-2 text-2xl font-bold">
                <span class="text-3xl">🌴</span>
                <span class="bg-gradient-to-r from-[#E6B3A1] to-[#D99C8A] bg-clip-text text-transparent">TripSquad</span>
            </a>
            
            <div class="flex items-center space-x-6">
                <a href="../index.php" class="hover:text-[#E6B3A1] font-medium transition-colors">Home</a>
                <a href="../profile/index.php" class="hover:text-[#E6B3A1] font-medium transition-colors">Profile</a>
                <a href="../auth/logout.php" class="hover:text-[#E6B3A1] font-medium transition-colors">Logout</a>
                <button onclick="toggleTheme()" class="p-2 rounded-lg hover:bg-[#BFD8D7] dark:hover:bg-[#333333] transition-colors">
                    <span class="theme-icon text-2xl">☀️</span>
                </button>
            </div>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="pt-24 pb-16 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">

    <!-- Welcome Section -->
    <div class="text-center mb-16 animate-fadeIn">
        <h1 class="text-5xl font-bold mb-3 bg-gradient-to-r from-[#E6B3A1] to-[#D99C8A] bg-clip-text text-transparent">
            Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>
        </h1>
        <p class="text-xl text-secondary">Your next adventure awaits</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-20 animate-fadeIn" style="animation-delay: 0.2s;">
        <div class="card-light card-dark p-8 rounded-2xl shadow-lg card-hover">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-semibold uppercase tracking-wide text-secondary">Total Trips</p>
            </div>
            <p class="text-5xl font-bold animate-countUp"><?= $totalTrips ?></p>
        </div>

        <div class="card-light card-dark p-8 rounded-2xl shadow-lg card-hover">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-semibold uppercase tracking-wide text-secondary">Upcoming</p>
            </div>
            <p class="text-5xl font-bold text-accent animate-countUp" style="animation-delay: 0.1s;"><?= $upcomingTrips ?></p>
        </div>

        <div class="card-light card-dark p-8 rounded-2xl shadow-lg card-hover">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-semibold uppercase tracking-wide text-secondary">Completed</p>
            </div>
            <p class="text-5xl font-bold text-secondary animate-countUp" style="animation-delay: 0.2s;"><?= $completedTrips ?></p>
        </div>
    </div>

    <!-- Action Cards -->
    <section class="grid grid-cols-1 lg:grid-cols-2 gap-10 mb-20">

        <!-- Create Trip -->
        <div class="secondary-card-light secondary-card-dark p-12 rounded-3xl shadow-xl animate-fadeIn flex flex-col justify-center" style="animation-delay: 0.4s;">
            <span class="text-sm uppercase tracking-wider text-secondary mb-3">Get Started</span>
            <h2 class="text-4xl font-bold mb-4">Create Your Next Trip</h2>
            <p class="text-lg text-secondary mb-8 max-w-md">
                Start planning your perfect adventure with friends, manage expenses, itineraries, and memories — all in one place.
            </p>
            <a href="../trips/create.php" class="inline-flex items-center gap-2 accent text-white px-10 py-4 rounded-xl font-bold text-lg shadow-lg w-fit hover:scale-105 transition">
                ✨ Create Custom Trip
            </a>
        </div>

        <!-- View Trips -->
        <div class="secondary-card-light secondary-card-dark p-12 rounded-3xl shadow-xl animate-fadeIn flex flex-col justify-center" style="animation-delay: 0.6s;">
            <span class="text-sm uppercase tracking-wider text-secondary mb-3">Your Plans</span>
            <h2 class="text-4xl font-bold mb-4">View Your Trips</h2>
            <p class="text-lg text-secondary mb-8 max-w-md">
                Quickly access all your upcoming, ongoing, and completed trips with detailed insights and activity.
            </p>
            <a href="../trips/list.php" class="inline-flex items-center gap-2 accent text-white px-10 py-4 rounded-xl font-bold text-lg shadow-lg w-fit hover:scale-105 transition">
                📋 View My Trips
            </a>
        </div>

    </section>

    <!-- Domestic Trips -->
    <div class="mb-20 animate-fadeIn" style="animation-delay: 0.8s;">
        <h2 class="text-3xl font-bold mb-8 flex items-center">
            <span class="mr-3">🇮🇳</span>
            Domestic Trips (India)
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
            <?php
            $domestic = [
                ["name" => "Mumbai", "img" => "https://images.unsplash.com/photo-1567157577867-05ccb1388e66?w=600&h=400&fit=crop", "desc" => "City of dreams and endless energy", "must" => ["Gateway of India", "Marine Drive", "Elephanta Caves"]],
                ["name" => "Delhi", "img" => "https://images.unsplash.com/photo-1587474260584-136574528ed5?w=600&h=400&fit=crop", "desc" => "Historic capital with Mughal heritage", "must" => ["Red Fort", "India Gate", "Qutub Minar"]],
                ["name" => "Goa", "img" => "https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?w=600&h=400&fit=crop", "desc" => "Beach paradise and vibrant nightlife", "must" => ["Baga Beach", "Basilica of Bom Jesus", "Dudhsagar Falls"]],
                ["name" => "Jaipur", "img" => "https://images.unsplash.com/photo-1599661046289-e31897846e41?w=600&h=400&fit=crop", "desc" => "Pink city of royal palaces", "must" => ["Hawa Mahal", "Amber Fort", "City Palace"]],
                ["name" => "Bangalore", "img" => "https://images.unsplash.com/photo-1596176530529-78163a4f7af2?w=600&h=400&fit=crop", "desc" => "Silicon Valley of India", "must" => ["Lalbagh Garden", "Bangalore Palace", "Cubbon Park"]],
                ["name" => "Hyderabad", "img" => "https://images.unsplash.com/photo-1603262110889-441d324d4962?w=600&h=400&fit=crop", "desc" => "City of pearls and biryani", "must" => ["Charminar", "Golconda Fort", "Ramoji Film City"]],
                ["name" => "Chennai", "img" => "https://images.unsplash.com/photo-1582510003544-4d00b7f74220?w=600&h=400&fit=crop", "desc" => "Gateway to South India", "must" => ["Marina Beach", "Kapaleeshwarar Temple", "Fort St. George"]],
                ["name" => "Kolkata", "img" => "https://images.unsplash.com/photo-1558431382-27e303142255?w=600&h=400&fit=crop", "desc" => "Cultural capital of India", "must" => ["Victoria Memorial", "Howrah Bridge", "Indian Museum"]],
                ["name" => "Manali", "img" => "https://images.unsplash.com/photo-1626621341517-bbf3d9990a23?w=600&h=400&fit=crop", "desc" => "Himalayan paradise for adventurers", "must" => ["Rohtang Pass", "Solang Valley", "Hidimba Temple"]],
                ["name" => "Udaipur", "img" => "https://images.unsplash.com/photo-1603262110167-c89c3e12ecee?w=600&h=400&fit=crop", "desc" => "City of lakes and romance", "must" => ["City Palace", "Lake Pichola", "Jag Mandir"]]
            ];
            
            foreach ($domestic as $place) {
            ?>
                <a href="../trips/create.php?destination=<?= urlencode($place['name']) ?>&type=domestic" 
                   class="card-light card-dark rounded-2xl overflow-hidden shadow-lg card-hover destination-card group">
                    <div class="relative h-48 overflow-hidden">
                        <img src="<?= $place['img'] ?>" alt="<?= $place['name'] ?>" 
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                        <h3 class="absolute bottom-3 left-3 text-white font-bold text-xl"><?= $place['name'] ?></h3>
                    </div>
                    <div class="p-5 destination-card-content">
                        <p class="text-sm text-secondary mb-3"><?= $place['desc'] ?></p>
                        <div class="space-y-1 mb-4">
                            <p class="text-xs font-semibold text-secondary">Must Visit:</p>
                            <?php foreach ($place['must'] as $item) { ?>
                                <p class="text-xs text-secondary">• <?= $item ?></p>
                            <?php } ?>
                        </div>
                        <button class="destination-card-button w-full accent text-white py-2.5 rounded-lg font-semibold text-sm hover:scale-105 transition">
                            Plan Trip →
                        </button>
                    </div>
                </a>
            <?php } ?>
        </div>
    </div>

    <!-- International Trips -->
    <div class="mb-20 animate-fadeIn" style="animation-delay: 1s;">
        <h2 class="text-3xl font-bold mb-8 flex items-center">
            <span class="mr-3">🌍</span>
            International Trips
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
            <?php
            $international = [
                ["name" => "Paris", "img" => "https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=600&h=400&fit=crop", "desc" => "City of lights and romance", "must" => ["Eiffel Tower", "Louvre Museum", "Arc de Triomphe"]],
                ["name" => "London", "img" => "https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?w=600&h=400&fit=crop", "desc" => "Historic capital with modern charm", "must" => ["Big Ben", "Tower Bridge", "British Museum"]],
                ["name" => "Dubai", "img" => "https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=600&h=400&fit=crop", "desc" => "Futuristic oasis in the desert", "must" => ["Burj Khalifa", "Palm Jumeirah", "Dubai Mall"]],
                ["name" => "Bali", "img" => "https://images.unsplash.com/photo-1537996194471-e657df975ab4?w=600&h=400&fit=crop", "desc" => "Tropical paradise with ancient temples", "must" => ["Tanah Lot", "Ubud Rice Terraces", "Seminyak Beach"]],
                ["name" => "Singapore", "img" => "https://images.unsplash.com/photo-1525625293386-3f8f99389edd?w=600&h=400&fit=crop", "desc" => "Garden city of innovation", "must" => ["Marina Bay Sands", "Gardens by the Bay", "Sentosa Island"]],
                ["name" => "Tokyo", "img" => "https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?w=600&h=400&fit=crop", "desc" => "Blend of tradition and technology", "must" => ["Tokyo Tower", "Senso-ji Temple", "Shibuya Crossing"]],
                ["name" => "Rome", "img" => "https://images.unsplash.com/photo-1552832230-c0197dd311b5?w=600&h=400&fit=crop", "desc" => "Eternal city of ancient wonders", "must" => ["Colosseum", "Vatican City", "Trevi Fountain"]],
                ["name" => "New York", "img" => "https://images.unsplash.com/photo-1496442226666-8d4d0e62e6e9?w=600&h=400&fit=crop", "desc" => "City that never sleeps", "must" => ["Statue of Liberty", "Times Square", "Central Park"]],
                ["name" => "Bangkok", "img" => "https://images.unsplash.com/photo-1508009603885-50cf7c579365?w=600&h=400&fit=crop", "desc" => "Vibrant street life and temples", "must" => ["Grand Palace", "Wat Arun", "Floating Markets"]],
                ["name" => "Maldives", "img" => "https://images.unsplash.com/photo-1514282401047-d79a71a590e8?w=600&h=400&fit=crop", "desc" => "Luxury island paradise", "must" => ["Water Villas", "Coral Reefs", "Bioluminescent Beach"]]
            ];
            
            foreach ($international as $place) {
            ?>
                <a href="../trips/create.php?destination=<?= urlencode($place['name']) ?>&type=international" 
                   class="card-light card-dark rounded-2xl overflow-hidden shadow-lg card-hover destination-card group">
                    <div class="relative h-48 overflow-hidden">
                        <img src="<?= $place['img'] ?>" alt="<?= $place['name'] ?>" 
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                        <h3 class="absolute bottom-3 left-3 text-white font-bold text-xl"><?= $place['name'] ?></h3>
                    </div>
                    <div class="p-5 destination-card-content">
                        <p class="text-sm text-secondary mb-3"><?= $place['desc'] ?></p>
                        <div class="space-y-1 mb-4">
                            <p class="text-xs font-semibold text-secondary">Must Visit:</p>
                            <?php foreach ($place['must'] as $item) { ?>
                                <p class="text-xs text-secondary">• <?= $item ?></p>
                            <?php } ?>
                        </div>
                        <button class="destination-card-button w-full accent text-white py-2.5 rounded-lg font-semibold text-sm hover:scale-105 transition">
                            Plan Trip →
                        </button>
                    </div>
                </a>
            <?php } ?>
        </div>
    </div>

</div>

<!-- Footer -->
<footer class="text-center py-8 text-secondary border-t footer-border">
    <p>© TripSquad • Plan Smart. Travel Together.</p>
</footer>

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

document.addEventListener('DOMContentLoaded', () => {
    const counters = document.querySelectorAll('.animate-countUp');
    counters.forEach(counter => {
        const target = parseInt(counter.textContent);
        let current = 0;
        const increment = target / 30;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                counter.textContent = target;
                clearInterval(timer);
            } else {
                counter.textContent = Math.floor(current);
            }
        }, 30);
    });
});
</script>

</body>
</html>