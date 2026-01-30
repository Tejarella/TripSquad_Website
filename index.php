<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TripSquad - Plan Trips. Split Expenses. Travel Together.</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        * {
            font-family: 'Inter', sans-serif;
        }
        
        html {
            scroll-behavior: smooth;
        }
        
        body {
            transition: background-color 0.4s ease, color 0.4s ease;
        }
        
        body.light {
            background-color: #F3EFEF;
            color: #2E2E2E;
        }
        
        body.dark {
            background-color: #1E1E1E;
            color: #EDEDED;
        }
        
        .hero-carousel {
            animation: fadeCarousel 20s infinite;
            background-size: cover;
            background-position: center;
        }
        
        @keyframes fadeCarousel {
            0%, 33% { background-image: url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=1920'); }
            34%, 66% { background-image: url('https://images.unsplash.com/photo-1530789253388-582c481c54b0?w=1920'); }
            67%, 100% { background-image: url('https://images.unsplash.com/photo-1488646953014-85cb44e25828?w=1920'); }
        }
        
        .card-hover {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(230, 179, 161, 0.3);
        }
        
        .btn-primary {
            background: #E6B3A1;
            color: #2E2E2E;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: #D99C8A;
            transform: scale(1.05);
        }
        
        .btn-secondary {
            border: 2px solid #E6B3A1;
            color: #E6B3A1;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: #E6B3A1;
            color: #2E2E2E;
            transform: scale(1.05);
        }
        
        body.dark .btn-primary {
            background: #E6B3A1;
            color: #1E1E1E;
        }
        
        body.dark .btn-secondary {
            border-color: #E6B3A1;
            color: #E6B3A1;
        }
        
        body.dark .btn-secondary:hover {
            background: #E6B3A1;
            color: #1E1E1E;
        }
        
        .navbar-blur {
            backdrop-filter: blur(10px);
            background: rgba(243, 239, 239, 0.85);
            transition: background 0.4s ease;
        }
        
        body.dark .navbar-blur {
            background: rgba(30, 30, 30, 0.85);
        }
        
        .section-light {
            background: #D8ECEB;
            transition: background 0.4s ease;
        }
        
        body.dark .section-light {
            background: #2A2A2A;
        }
        
        .section-secondary {
            background: #BFD8D7;
            transition: background 0.4s ease;
        }
        
        body.dark .section-secondary {
            background: #2A2A2A;
        }
        
        .card-bg {
            background: #D8ECEB;
            transition: background 0.4s ease;
        }
        
        body.dark .card-bg {
            background: #2A2A2A;
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
        
        .border-custom {
            border-color: #E0D6D2;
            transition: border-color 0.4s ease;
        }
        
        body.dark .border-custom {
            border-color: #3A3A3A;
        }
        
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: #E6B3A1;
            border-radius: 50%;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .back-to-top:hover {
            background: #D99C8A;
            transform: scale(1.1);
        }
        
        .back-to-top.show {
            display: flex;
        }
        
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease;
        }
        
        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .accordion-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease;
        }
        
        .accordion-content.active {
            max-height: 500px;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: #E6B3A1;
        }
    </style>
</head>
<body class="light">

    <nav class="navbar-blur fixed w-full top-0 z-50 shadow-md">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="text-3xl">🌴</span>
                <span class="text-2xl font-bold text-primary">TripSquad</span>
            </div>
            
            <div class="hidden md:flex items-center space-x-8">
                <a href="#home" class="text-primary hover:text-[#E6B3A1] transition">Home</a>
                <a href="#how-it-works" class="text-primary hover:text-[#E6B3A1] transition">How It Works</a>
                <a href="#features" class="text-primary hover:text-[#E6B3A1] transition">Features</a>
                <a href="#testimonials" class="text-primary hover:text-[#E6B3A1] transition">Testimonials</a>
                <a href="#faqs" class="text-primary hover:text-[#E6B3A1] transition">FAQs</a>
            </div>
            
            <div class="flex items-center space-x-4">
                <button onclick="toggleTheme()" class="text-2xl transition transform hover:scale-110">
                    <span id="theme-icon">🌙</span>
                </button>
                <a href="auth/login.php" class="btn-secondary px-6 py-2 rounded-full font-semibold">Login</a>
                <a href="auth/register.php" class="btn-primary px-6 py-2 rounded-full font-semibold">Register</a>
            </div>
        </div>
    </nav>

    <section id="home" class="hero-carousel min-h-screen flex items-center justify-center relative mt-16">
        <div class="absolute inset-0 bg-gradient-to-b from-black/60 to-black/40"></div>
        <div class="relative z-10 text-center text-white px-6 max-w-5xl">
            <p class="text-[#E6B3A1] text-sm font-semibold mb-4 tracking-wider">✨ Trusted by 10,000+ travelers</p>
            <h1 class="text-6xl md:text-7xl font-bold mb-6 leading-tight">
                Plan Trips. <span class="text-[#E6B3A1]">Split Expenses.</span><br>Travel Together.
            </h1>
            <p class="text-xl md:text-2xl text-gray-200 mb-10 max-w-3xl mx-auto">
                Stress-free group travel planning and expense sharing for friends, families, and adventure seekers.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center space-y-4 sm:space-y-0 sm:space-x-6">
                <a href="auth/register.php" class="btn-primary px-10 py-4 rounded-full font-bold text-lg shadow-lg">
                    Get Started Free →
                </a>
                <a href="#how-it-works" class="btn-secondary px-10 py-4 rounded-full font-bold text-lg">
                    ▶ Watch Demo
                </a>
            </div>
            <div class="mt-16 grid grid-cols-3 gap-8 max-w-3xl mx-auto">
                <div>
                    <div class="stat-number">10K+</div>
                    <p class="text-gray-300">Active Users</p>
                </div>
                <div>
                    <div class="stat-number">25K+</div>
                    <p class="text-gray-300">Trips Planned</p>
                </div>
                <div>
                    <div class="stat-number">$2M+</div>
                    <p class="text-gray-300">Expenses Tracked</p>
                </div>
            </div>
        </div>
    </section>

    <section id="about" class="py-24 px-6 section-light">
        <div class="max-w-7xl mx-auto grid md:grid-cols-2 gap-12 items-center fade-in">
            <div>
                <img src="https://images.unsplash.com/photo-1469854523086-cc02fe5d8800?w=800" 
                     alt="Travel destination" 
                     class="rounded-3xl shadow-2xl w-full h-[500px] object-cover">
            </div>
            <div>
                <p class="text-[#E6B3A1] text-sm font-bold mb-4 tracking-wider">WHO WE ARE</p>
                <h2 class="text-5xl font-bold text-primary mb-6">About TripSquad</h2>
                <p class="text-secondary text-lg mb-6 leading-relaxed">
                    TripSquad was born from a simple idea: group travel should be fun, not stressful. 
                    We eliminate the headache of tracking expenses and splitting costs, so you can focus 
                    on creating unforgettable memories with the people you love.
                </p>
                <p class="text-secondary text-lg mb-8 leading-relaxed">
                    Whether you're planning a weekend getaway with friends, a family vacation, or an epic 
                    adventure across continents, TripSquad makes it simple to plan, track, and settle expenses.
                </p>
                <div class="space-y-4">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-[#E6B3A1] rounded-full flex items-center justify-center text-2xl">✓</div>
                        <p class="text-primary font-semibold">Simple & Intuitive Interface</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-[#E6B3A1] rounded-full flex items-center justify-center text-2xl">✓</div>
                        <p class="text-primary font-semibold">Real-time Expense Tracking</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-[#E6B3A1] rounded-full flex items-center justify-center text-2xl">✓</div>
                        <p class="text-primary font-semibold">Smart Settlement System</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="how-it-works" class="py-24 px-6">
        <div class="max-w-7xl mx-auto fade-in">
            <p class="text-[#E6B3A1] text-sm font-bold text-center mb-4 tracking-wider">SIMPLE PROCESS</p>
            <h2 class="text-5xl font-bold text-center text-primary mb-4">How It Works</h2>
            <p class="text-center text-secondary text-lg mb-16 max-w-2xl mx-auto">
                Get started in minutes. No complicated setup or learning curve.
            </p>
            
            <div class="grid md:grid-cols-4 gap-8">
                <div class="card-bg rounded-3xl p-8 card-hover relative">
                    <div class="absolute -top-4 -left-4 w-12 h-12 bg-[#E6B3A1] rounded-full flex items-center justify-center text-white font-bold text-xl">1</div>
                    <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-4xl mb-6 shadow-md">✈️</div>
                    <h3 class="text-2xl font-bold text-primary mb-4">Create a Trip</h3>
                    <p class="text-secondary leading-relaxed">
                        Start by creating a new trip with a name, dates, and destination. Add all the details that matter.
                    </p>
                </div>
                
                <div class="card-bg rounded-3xl p-8 card-hover relative">
                    <div class="absolute -top-4 -left-4 w-12 h-12 bg-[#E6B3A1] rounded-full flex items-center justify-center text-white font-bold text-xl">2</div>
                    <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-4xl mb-6 shadow-md">👥</div>
                    <h3 class="text-2xl font-bold text-primary mb-4">Add Friends</h3>
                    <p class="text-secondary leading-relaxed">
                        Invite your travel buddies via email or share a link. Everyone can join and contribute.
                    </p>
                </div>
                
                <div class="card-bg rounded-3xl p-8 card-hover relative">
                    <div class="absolute -top-4 -left-4 w-12 h-12 bg-[#E6B3A1] rounded-full flex items-center justify-center text-white font-bold text-xl">3</div>
                    <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-4xl mb-6 shadow-md">💰</div>
                    <h3 class="text-2xl font-bold text-primary mb-4">Track Expenses</h3>
                    <p class="text-secondary leading-relaxed">
                        Log expenses as you go. Choose who paid and select which members should split the cost.
                    </p>
                </div>
                
                <div class="card-bg rounded-3xl p-8 card-hover relative">
                    <div class="absolute -top-4 -left-4 w-12 h-12 bg-[#E6B3A1] rounded-full flex items-center justify-center text-white font-bold text-xl">4</div>
                    <div class="w-16 h-16 bg-white rounded-2xl flex items-center justify-center text-4xl mb-6 shadow-md">💳</div>
                    <h3 class="text-2xl font-bold text-primary mb-4">Settle Easily</h3>
                    <p class="text-secondary leading-relaxed">
                        When the trip ends, see exactly who owes whom. Settle up with just a few taps.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section id="features" class="py-24 px-6 section-light">
        <div class="max-w-7xl mx-auto fade-in">
            <p class="text-[#E6B3A1] text-sm font-bold text-center mb-4 tracking-wider">POWERFUL FEATURES</p>
            <h2 class="text-5xl font-bold text-center text-primary mb-4">Why Choose TripSquad</h2>
            <p class="text-center text-secondary text-lg mb-16 max-w-2xl mx-auto">
                Everything you need to make group travel planning effortless and enjoyable.
            </p>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="card-bg rounded-3xl p-8 card-hover">
                    <div class="text-5xl mb-4">🎯</div>
                    <h3 class="text-xl font-bold text-primary mb-3">Selective Expense Splitting</h3>
                    <p class="text-secondary">Choose exactly who participates in each expense. No more complicated math.</p>
                </div>
                
                <div class="card-bg rounded-3xl p-8 card-hover">
                    <div class="text-5xl mb-4">✨</div>
                    <h3 class="text-xl font-bold text-primary mb-3">No Confusion in Settlements</h3>
                    <p class="text-secondary">Our smart algorithm calculates the optimal way to settle balances.</p>
                </div>
                
                <div class="card-bg rounded-3xl p-8 card-hover">
                    <div class="text-5xl mb-4">👨‍👩‍👧‍👦</div>
                    <h3 class="text-xl font-bold text-primary mb-3">Works for Any Group Size</h3>
                    <p class="text-secondary">From couples to large groups, TripSquad scales perfectly.</p>
                </div>
                
                <div class="card-bg rounded-3xl p-8 card-hover">
                    <div class="text-5xl mb-4">💡</div>
                    <h3 class="text-xl font-bold text-primary mb-3">Simple & Intuitive UI</h3>
                    <p class="text-secondary">Clean design that anyone can use. No training required.</p>
                </div>
                
                <div class="card-bg rounded-3xl p-8 card-hover">
                    <div class="text-5xl mb-4">📱</div>
                    <h3 class="text-xl font-bold text-primary mb-3">Mobile-Friendly</h3>
                    <p class="text-secondary">Track expenses on the go from any device, anywhere.</p>
                </div>
                
                <div class="card-bg rounded-3xl p-8 card-hover">
                    <div class="text-5xl mb-4">🔒</div>
                    <h3 class="text-xl font-bold text-primary mb-3">Secure & Private</h3>
                    <p class="text-secondary">Your data is encrypted and protected with industry-leading security.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="testimonials" class="py-24 px-6">
        <div class="max-w-7xl mx-auto fade-in">
            <p class="text-[#E6B3A1] text-sm font-bold text-center mb-4 tracking-wider">WHAT TRAVELERS SAY</p>
            <h2 class="text-5xl font-bold text-center text-primary mb-16">Testimonials</h2>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="card-bg rounded-3xl p-8 card-hover">
                    <div class="flex items-center mb-4">
                        <div class="w-14 h-14 bg-[#E6B3A1] rounded-full flex items-center justify-center text-white text-xl font-bold">SJ</div>
                        <div class="ml-4">
                            <h4 class="font-bold text-primary">Sarah Johnson</h4>
                            <p class="text-sm text-secondary">Travel Blogger</p>
                        </div>
                    </div>
                    <div class="text-[#E6B3A1] text-2xl mb-3">★★★★★</div>
                    <p class="text-secondary italic">"TripSquad made our Europe backpacking trip stress-free and fun! No more awkward money conversations."</p>
                </div>
                
                <div class="card-bg rounded-3xl p-8 card-hover">
                    <div class="flex items-center mb-4">
                        <div class="w-14 h-14 bg-[#E6B3A1] rounded-full flex items-center justify-center text-white text-xl font-bold">MC</div>
                        <div class="ml-4">
                            <h4 class="font-bold text-primary">Michael Chen</h4>
                            <p class="text-sm text-secondary">Software Engineer</p>
                        </div>
                    </div>
                    <div class="text-[#E6B3A1] text-2xl mb-3">★★★★★</div>
                    <p class="text-secondary italic">"Finally, an app that actually makes splitting expenses easy. The settlement feature is genius!"</p>
                </div>
                
                <div class="card-bg rounded-3xl p-8 card-hover">
                    <div class="flex items-center mb-4">
                        <div class="w-14 h-14 bg-[#E6B3A1] rounded-full flex items-center justify-center text-white text-xl font-bold">EP</div>
                        <div class="ml-4">
                            <h4 class="font-bold text-primary">Emma Patel</h4>
                            <p class="text-sm text-secondary">Teacher</p>
                        </div>
                    </div>
                    <div class="text-[#E6B3A1] text-2xl mb-3">★★★★★</div>
                    <p class="text-secondary italic">"Used it for our family reunion. Everyone loved how simple and transparent everything was!"</p>
                </div>
            </div>
        </div>
    </section>

    <section id="faqs" class="py-24 px-6 section-light">
        <div class="max-w-4xl mx-auto fade-in">
            <p class="text-[#E6B3A1] text-sm font-bold text-center mb-4 tracking-wider">GOT QUESTIONS?</p>
            <h2 class="text-5xl font-bold text-center text-primary mb-16">Frequently Asked Questions</h2>
            
            <div class="space-y-4">
                <div class="card-bg rounded-2xl overflow-hidden">
                    <button onclick="toggleAccordion(0)" class="w-full text-left p-6 flex justify-between items-center">
                        <h3 class="text-xl font-bold text-primary">Is TripSquad free to use?</h3>
                        <span class="text-2xl text-[#E6B3A1] accordion-icon">+</span>
                    </button>
                    <div class="accordion-content px-6 pb-6">
                        <p class="text-secondary">Yes! TripSquad offers a free tier with all essential features. Premium plans are available for larger groups and advanced features.</p>
                    </div>
                </div>
                
                <div class="card-bg rounded-2xl overflow-hidden">
                    <button onclick="toggleAccordion(1)" class="w-full text-left p-6 flex justify-between items-center">
                        <h3 class="text-xl font-bold text-primary">Can expenses be split unevenly?</h3>
                        <span class="text-2xl text-[#E6B3A1] accordion-icon">+</span>
                    </button>
                    <div class="accordion-content px-6 pb-6">
                        <p class="text-secondary">Absolutely! You can split expenses equally, by percentage, or enter custom amounts for each participant.</p>
                    </div>
                </div>
                
                <div class="card-bg rounded-2xl overflow-hidden">
                    <button onclick="toggleAccordion(2)" class="w-full text-left p-6 flex justify-between items-center">
                        <h3 class="text-xl font-bold text-primary">Who can edit trips and expenses?</h3>
                        <span class="text-2xl text-[#E6B3A1] accordion-icon">+</span>
                    </button>
                    <div class="accordion-content px-6 pb-6">
                        <p class="text-secondary">The trip creator has admin rights. They can grant edit permissions to other members or keep it restricted.</p>
                    </div>
                </div>
                
                <div class="card-bg rounded-2xl overflow-hidden">
                    <button onclick="toggleAccordion(3)" class="w-full text-left p-6 flex justify-between items-center">
                        <h3 class="text-xl font-bold text-primary">Is TripSquad mobile-friendly?</h3>
                        <span class="text-2xl text-[#E6B3A1] accordion-icon">+</span>
                    </button>
                    <div class="accordion-content px-6 pb-6">
                        <p class="text-secondary">Yes! TripSquad works seamlessly on all devices - desktop, tablet, and mobile phones.</p>
                    </div>
                </div>
                
                <div class="card-bg rounded-2xl overflow-hidden">
                    <button onclick="toggleAccordion(4)" class="w-full text-left p-6 flex justify-between items-center">
                        <h3 class="text-xl font-bold text-primary">Is my financial data secure?</h3>
                        <span class="text-2xl text-[#E6B3A1] accordion-icon">+</span>
                    </button>
                    <div class="accordion-content px-6 pb-6">
                        <p class="text-secondary">Security is our top priority. We use bank-level encryption and never store sensitive payment information.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-24 px-6 section-secondary">
        <div class="max-w-4xl mx-auto text-center fade-in">
            <p class="text-[#E6B3A1] text-sm font-bold mb-4 tracking-wider">🌴 Start your adventure today</p>
            <h2 class="text-5xl md:text-6xl font-bold text-primary mb-6">Ready for Your Next Adventure?</h2>
            <p class="text-secondary text-xl mb-10 max-w-2xl mx-auto">
                Join thousands of travelers who plan trips smarter. Create your first trip in under a minute.
            </p>
            <a href="auth/register.php" class="btn-primary px-12 py-5 rounded-full font-bold text-xl shadow-2xl inline-block">
                Start Planning Now →
            </a>
            <p class="text-secondary text-sm mt-6">Free forever for basic features • No credit card required</p>
        </div>
    </section>

    <footer class="section-light py-16 px-6 border-t-2 border-custom">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-5 gap-12 mb-12">
                <div class="md:col-span-2">
                    <div class="flex items-center space-x-2 mb-6">
                        <span class="text-3xl">🌴</span>
                        <span class="text-2xl font-bold text-primary">TripSquad</span>
                    </div>
                    <p class="text-secondary mb-6 max-w-md leading-relaxed">
                        Making group travel planning and expense splitting effortless. Join thousands of happy travelers worldwide.
                    </p>
                    
                    <div class="mb-6">
                        <h4 class="text-primary font-bold mb-4">Subscribe to our newsletter</h4>
                        <p class="text-secondary text-sm mb-4">Get travel tips, product updates, and exclusive offers.</p>
                        <div class="flex">
                            <input type="email" placeholder="Enter your email" class="flex-1 px-4 py-3 rounded-l-full border-2 border-custom bg-white text-primary">
                            <button class="btn-primary px-6 py-3 rounded-r-full font-semibold">→</button>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex items-center space-x-3 text-secondary">
                            <span class="text-[#E6B3A1]">📧</span>
                            <span>hello@tripsquad.app</span>
                        </div>
                        <div class="flex items-center space-x-3 text-secondary">
                            <span class="text-[#E6B3A1]">📞</span>
                            <span>+1 (555) 123-4567</span>
                        </div>
                        <div class="flex items-center space-x-3 text-secondary">
                            <span class="text-[#E6B3A1]">📍</span>
                            <span>San Francisco, CA</span>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-primary font-bold mb-6">Product</h4>
                    <ul class="space-y-3">
                        <li><a href="#features" class="text-secondary hover:text-[#E6B3A1] transition">Features</a></li>
                        <li><a href="#how-it-works" class="text-secondary hover:text-[#E6B3A1] transition">How It Works</a></li>
                        <li><a href="#" class="text-secondary hover:text-[#E6B3A1] transition">Pricing</a></li>
                        <li><a href="#" class="text-secondary hover:text-[#E6B3A1] transition">Roadmap</a></li>
                        <li><a href="#" class="text-secondary hover:text-[#E6B3A1] transition">Changelog</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-primary font-bold mb-6">Company</h4>
                    <ul class="space-y-3">
                        <li><a href="#about" class="text-secondary hover:text-[#E6B3A1] transition">About Us</a></li>
                        <li><a href="#" class="text-secondary hover:text-[#E6B3A1] transition">Careers</a></li>
                        <li><a href="#" class="text-secondary hover:text-[#E6B3A1] transition">Press Kit</a></li>
                        <li><a href="#" class="text-secondary hover:text-[#E6B3A1] transition">Contact</a></li>
                        <li><a href="#" class="text-secondary hover:text-[#E6B3A1] transition">Partners</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="text-primary font-bold mb-6">Resources</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-secondary hover:text-[#E6B3A1] transition">Blog</a></li>
                        <li><a href="#" class="text-secondary hover:text-[#E6B3A1] transition">Help Center</a></li>
                        <li><a href="#" class="text-secondary hover:text-[#E6B3A1] transition">Community</a></li>
                        <li><a href="#" class="text-secondary hover:text-[#E6B3A1] transition">Guides</a></li>
                        <li><a href="#" class="text-secondary hover:text-[#E6B3A1] transition">API Docs</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t-2 border-custom pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                    <div class="flex items-center space-x-6">
                        <a href="#" class="text-secondary hover:text-[#E6B3A1] transition">Privacy Policy</a>
                        <a href="#" class="text-secondary hover:text-[#E6B3A1] transition">Terms of Service</a>
                        <a href="#" class="text-secondary hover:text-[#E6B3A1] transition">Cookie Policy</a>
                        <a href="#" class="text-secondary hover:text-[#E6B3A1] transition">GDPR</a>
                        <a href="#" class="text-secondary hover:text-[#E6B3A1] transition">Security</a>
                    </div>
                    
                    <div class="flex items-center space-x-6">
                        <h4 class="text-primary font-semibold">Follow Us</h4>
                        <a href="#" class="text-2xl hover:scale-110 transition transform">𝕏</a>
                        <a href="#" class="text-2xl hover:scale-110 transition transform">📷</a>
                        <a href="#" class="text-2xl hover:scale-110 transition transform">📘</a>
                        <a href="#" class="text-2xl hover:scale-110 transition transform">🔗</a>
                        <a href="#" class="text-2xl hover:scale-110 transition transform">📺</a>
                        <a href="#" class="text-2xl hover:scale-110 transition transform">💻</a>
                    </div>
                </div>
                
                <div class="mt-8 text-center">
                    <p class="text-secondary mb-4">© 2026 TripSquad. All rights reserved.</p>
                    <p class="text-secondary text-sm">Made with ❤️ for travelers everywhere</p>
                    
                    <div class="mt-6 flex justify-center items-center space-x-4">
                        <span class="text-secondary text-sm">Coming Soon:</span>
                        <div class="flex items-center space-x-2 bg-white dark:bg-[#2A2A2A] px-4 py-2 rounded-full">
                            <span>📱</span>
                            <span class="text-primary font-semibold">iOS App</span>
                        </div>
                        <div class="flex items-center space-x-2 bg-white dark:bg-[#2A2A2A] px-4 py-2 rounded-full">
                            <span>🤖</span>
                            <span class="text-primary font-semibold">Android App</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <div class="back-to-top" onclick="scrollToTop()">
        <span class="text-white text-2xl">↑</span>
    </div>

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
        
        const backToTop = document.querySelector('.back-to-top');
        
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
            
            const sections = document.querySelectorAll('.fade-in');
            sections.forEach(section => {
                const rect = section.getBoundingClientRect();
                if (rect.top < window.innerHeight * 0.8) {
                    section.classList.add('visible');
                }
            });
        });
        
        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        function toggleAccordion(index) {
            const accordions = document.querySelectorAll('.accordion-content');
            const icons = document.querySelectorAll('.accordion-icon');
            
            accordions.forEach((accordion, i) => {
                if (i === index) {
                    accordion.classList.toggle('active');
                    icons[i].textContent = accordion.classList.contains('active') ? '−' : '+';
                } else {
                    accordion.classList.remove('active');
                    icons[i].textContent = '+';
                }
            });
        }
    </script>

</body>
</html>