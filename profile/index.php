<?php
session_start();
include "../config/db.php";
/* LOGIN CHECK */
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$message = "";
$message_type = "";
/* FETCH USER */
$user = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT name, email, password FROM users WHERE id = $user_id"
));
/* ---------------- UPDATE PROFILE ---------------- */
if (isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    mysqli_query($conn,
        "UPDATE users SET name = '$name' WHERE id = $user_id"
    );
    $_SESSION['user_name'] = $name;
    $message = "Profile updated successfully";
    $message_type = "success";
}
/* ---------------- CHANGE PASSWORD ---------------- */
if (isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    if (!password_verify($current, $user['password'])) {
        $message = "Current password is incorrect";
        $message_type = "error";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        mysqli_query($conn,
            "UPDATE users SET password = '$hashed' WHERE id = $user_id"
        );
        $message = "Password changed successfully";
        $message_type = "success";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | TripSquad</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            transition: background-color 0.4s ease, color 0.4s ease;
        }
        
        body.light {
            background: linear-gradient(135deg, #F3EFEF 0%, #E6D7D5 100%);
            color: #2E2E2E;
        }
        
        body.dark {
            background: linear-gradient(135deg, #1E1E1E 0%, #2A2A2A 100%);
            color: #EDEDED;
        }
        
        .glass-card {
            background: rgba(216, 236, 235, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(230, 179, 161, 0.2);
            transition: all 0.4s ease;
        }
        
        body.dark .glass-card {
            background: rgba(42, 42, 42, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        }
        
        .input-field {
            background: rgba(255, 255, 255, 0.5);
            border: 2px solid rgba(224, 214, 210, 0.3);
            transition: all 0.3s ease;
        }
        
        .input-field:focus {
            outline: none;
            border-color: #E6B3A1;
            box-shadow: 0 0 20px rgba(230, 179, 161, 0.3);
            background: rgba(255, 255, 255, 0.8);
        }
        
        .input-field:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        body.dark .input-field {
            background: rgba(30, 30, 30, 0.6);
            border-color: rgba(255, 255, 255, 0.1);
            color: #EDEDED;
        }
        
        body.dark .input-field:focus {
            background: rgba(42, 42, 42, 0.8);
            border-color: #E6B3A1;
        }
        
        .input-field::placeholder {
            color: #6B6B6B;
        }
        
        body.dark .input-field::placeholder {
            color: #8A8A8A;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #E6B3A1 0%, #D99C8A 100%);
            color: #2E2E2E;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(230, 179, 161, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(230, 179, 161, 0.5);
        }
        
        .btn-secondary {
            background: rgba(191, 216, 215, 0.5);
            color: #2E2E2E;
            border: 2px solid #E6B3A1;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: rgba(230, 179, 161, 0.3);
            transform: translateY(-2px);
        }
        
        body.dark .btn-secondary {
            background: rgba(42, 42, 42, 0.5);
            color: #EDEDED;
        }
        
        body.dark .btn-secondary:hover {
            background: rgba(230, 179, 161, 0.2);
        }
        
        .navbar-blur {
            backdrop-filter: blur(10px);
            background: rgba(243, 239, 239, 0.85);
            transition: background 0.4s ease;
        }
        
        body.dark .navbar-blur {
            background: rgba(30, 30, 30, 0.85);
        }
        
        .text-primary {
            color: #2E2E2E;
            transition: color 0.4s ease;
        }
        
        body.dark .text-primary {
            color: #EDEDED;
        }
        
        .text-secondary {
            color: #6B6B6B;
            transition: color 0.4s ease;
        }
        
        body.dark .text-secondary {
            color: #B0B0B0;
        }
        
        .message-success {
            background: rgba(34, 197, 94, 0.1);
            border-left: 4px solid #22C55E;
            color: #16A34A;
        }
        
        body.dark .message-success {
            background: rgba(34, 197, 94, 0.2);
            color: #86EFAC;
        }
        
        .message-error {
            background: rgba(239, 68, 68, 0.1);
            border-left: 4px solid #EF4444;
            color: #DC2626;
        }
        
        body.dark .message-error {
            background: rgba(239, 68, 68, 0.2);
            color: #FCA5A5;
        }
        
        .link-back {
            color: #E6B3A1;
            transition: all 0.3s ease;
        }
        
        .link-back:hover {
            color: #D99C8A;
            transform: translateX(-4px);
        }
        
        .section-divider {
            border-color: rgba(224, 214, 210, 0.3);
            transition: border-color 0.4s ease;
        }
        
        body.dark .section-divider {
            border-color: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body class="light min-h-screen">

    <nav class="navbar-blur fixed w-full top-0 z-50 shadow-md">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="text-3xl">🌴</span>
                <span class="text-2xl font-bold text-primary">TripSquad</span>
            </div>
            
            <button onclick="toggleTheme()" class="text-2xl transition transform hover:scale-110">
                <span id="theme-icon">🌙</span>
            </button>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-6 py-24 mt-8">
        
        <a href="../dashboard/index.php" class="link-back inline-flex items-center space-x-2 mb-8 font-semibold transition">
            <span>←</span>
            <span>Back to Dashboard</span>
        </a>

        <div class="glass-card rounded-3xl p-10">
            
            <div class="flex items-center space-x-4 mb-8">
                <div class="w-16 h-16 bg-gradient-to-br from-[#E6B3A1] to-[#D99C8A] rounded-full flex items-center justify-center text-3xl">
                    👤
                </div>
                <div>
                    <h1 class="text-4xl font-bold text-primary">My Profile</h1>
                    <p class="text-secondary">Manage your account settings</p>
                </div>
            </div>

            <?php if ($message) { ?>
                <div class="message-<?= $message_type ?> p-4 rounded-xl mb-8 flex items-center space-x-3">
                    <span class="text-2xl"><?= $message_type == 'success' ? '✓' : '⚠' ?></span>
                    <p class="font-semibold"><?= $message ?></p>
                </div>
            <?php } ?>

            <form method="POST" class="mb-8">
                <div class="flex items-center space-x-3 mb-6">
                    <span class="text-2xl">✏️</span>
                    <h2 class="text-2xl font-bold text-primary">Edit Profile</h2>
                </div>
                
                <div class="space-y-5">
                    <div>
                        <label class="block text-secondary font-semibold mb-2 ml-1">Full Name</label>
                        <input type="text" name="name" required
                            value="<?= htmlspecialchars($user['name']) ?>"
                            class="input-field w-full px-4 py-4 rounded-xl font-medium">
                    </div>
                    
                    <div>
                        <label class="block text-secondary font-semibold mb-2 ml-1">Email Address</label>
                        <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled
                            class="input-field w-full px-4 py-4 rounded-xl font-medium">
                        <p class="text-secondary text-sm mt-2 ml-1">Email cannot be changed</p>
                    </div>
                </div>

                <button type="submit" name="update_profile"
                    class="btn-primary px-8 py-3 rounded-xl font-bold mt-6">
                    Update Profile
                </button>
            </form>

            <div class="border-t-2 section-divider my-8"></div>

            <form method="POST">
                <div class="flex items-center space-x-3 mb-6">
                    <span class="text-2xl">🔒</span>
                    <h2 class="text-2xl font-bold text-primary">Change Password</h2>
                </div>
                
                <div class="space-y-5">
                    <div>
                        <label class="block text-secondary font-semibold mb-2 ml-1">Current Password</label>
                        <input type="password" name="current_password" required
                            placeholder="Enter current password"
                            class="input-field w-full px-4 py-4 rounded-xl font-medium">
                    </div>
                    
                    <div>
                        <label class="block text-secondary font-semibold mb-2 ml-1">New Password</label>
                        <input type="password" name="new_password" required
                            placeholder="Enter new password"
                            class="input-field w-full px-4 py-4 rounded-xl font-medium">
                    </div>
                </div>

                <button type="submit" name="change_password"
                    class="btn-secondary px-8 py-3 rounded-xl font-bold mt-6">
                    Change Password
                </button>
            </form>

        </div>
    </div>

    <footer class="py-6 text-center mt-12">
        <p class="text-secondary">© TripSquad • Plan Smart. Travel Together.</p>
    </footer>

    <script>
        function toggleTheme() {
            const body = document.body;
            const icon = document.getElementById('theme-icon');
            
            if (body.classList.contains('light')) {
                body.classList.remove('light');
                body.classList.add('dark');
                icon.textContent = '☀️';
            } else {
                body.classList.remove('dark');
                body.classList.add('light');
                icon.textContent = '🌙';
            }
        }
    </script>

</body>
</html>