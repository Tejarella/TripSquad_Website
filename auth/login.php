<?php
session_start();
include "../config/db.php";

$error = "";

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $result = mysqli_query($conn,
        "SELECT * FROM users WHERE email='$email'"
    );

    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header("Location: ../dashboard/index.php");
    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | TripSquad</title>
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
        
        .btn-login {
            background: linear-gradient(135deg, #E6B3A1 0%, #D99C8A 100%);
            color: #2E2E2E;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(230, 179, 161, 0.3);
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(230, 179, 161, 0.5);
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
        
        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border-left: 4px solid #EF4444;
            color: #DC2626;
        }
        
        body.dark .error-message {
            background: rgba(239, 68, 68, 0.2);
            color: #FCA5A5;
        }
        
        .link-accent {
            color: #E6B3A1;
            transition: color 0.3s ease;
        }
        
        .link-accent:hover {
            color: #D99C8A;
        }
    </style>
</head>
<body class="light min-h-screen flex flex-col">

    <nav class="navbar-blur fixed w-full top-0 z-50 shadow-md">
  <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">

    <!-- Left: Logo + Home -->
    <div class="flex items-center space-x-6">
      <a href="index.php" class="flex items-center space-x-2 hover:opacity-80 transition">
        <span class="text-3xl">🌴</span>
        <span class="text-2xl font-bold text-primary">TripSquad</span>
      </a>

      <a href="../index.php"
         class="text-base font-medium text-secondary hover:text-primary transition">
        Home
      </a>
    </div>

    <!-- Right: Theme Toggle -->
    <button onclick="toggleTheme()"
            class="text-2xl transition transform hover:scale-110">
      <span id="theme-icon">🌙</span>
    </button>

  </div>
</nav>


    <div class="flex-1 flex items-center justify-center px-6 mt-20">
        <form method="POST" class="glass-card rounded-3xl p-10 w-full max-w-md">
            
            <div class="text-center mb-8">
                <h2 class="text-4xl font-bold text-primary mb-2">Welcome Back</h2>
                <p class="text-secondary text-lg">Sign in to continue your journey</p>
            </div>

            <?php if ($error) { ?>
                <div class="error-message p-4 rounded-xl mb-6">
                    <p class="font-semibold"><?= $error ?></p>
                </div>
            <?php } ?>

            <div class="space-y-5">
                <div class="relative">
                    <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-xl">📧</span>
                    <input type="email" name="email" placeholder="Email address" required
                        class="input-field w-full pl-12 pr-4 py-4 rounded-xl font-medium">
                </div>

                <div class="relative">
                    <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-xl">🔒</span>
                    <input type="password" name="password" placeholder="Password" required
                        class="input-field w-full pl-12 pr-4 py-4 rounded-xl font-medium">
                </div>
            </div>

            <button type="submit" name="login"
                class="btn-login w-full py-4 rounded-xl font-bold text-lg mt-8">
                Sign In
            </button>

            <div class="relative my-8">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-[#E0D6D2]"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 text-secondary glass-card rounded-full py-1">or</span>
                </div>
            </div>

            <p class="text-center text-secondary">
                Don't have an account?
                <a href="register.php" class="link-accent font-semibold">Create account</a>
            </p>

        </form>
    </div>

    <footer class="py-6 text-center">
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