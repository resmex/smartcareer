<?php
session_start();

// Redirect to dashboard if logged in
if (isset($_SESSION['user_id'])) {
    header("Location: pages/services/dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCareer Tanzania | Unleash Your Future</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3b82f6; /* Vibrant blue */
            --secondary-color: #1e3a8a; /* Deep blue */
            --accent-color: #f59e0b; /* Bright amber */
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f9fafb;
            color: #1f2937;
            overflow-x: hidden;
        }

        .nav-sticky {
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 50;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            backdrop-filter: blur(5px);
        }

        .nav-link {
            position: relative;
            padding-bottom: 4px;
            transition: color 0.3s ease;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--accent-color);
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .hero-gradient {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            position: relative;
            overflow: hidden;
        }

        .hero-gradient::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1), transparent);
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .card {
            background: white;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .btn {
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }

        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeIn 0.8s ease-out forwards;
        }

        @keyframes fadeIn {
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .mobile-menu {
                position: fixed;
                top: 60px;
                left: 0;
                width: 100%;
                background: white;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="nav-sticky">
        <div class="container mx-auto px-6 py-4 flex items-center justify-between">
            <a href="/" class="text-3xl font-bold">
                <span class="text-blue-600">Smart</span><span class="text-gray-900">Career</span>
            </a>
            <div class="hidden md:flex space-x-10">
                <a href="#" class="nav-link text-gray-700 hover:text-blue-600 font-semibold">Home</a>
                <a href="#services" class="nav-link text-gray-700 hover:text-blue-600 font-semibold">Services</a>
                <a href="#opportunities" class="nav-link text-gray-700 hover:text-blue-600 font-semibold">Opportunities</a>
                <a href="#ai-counseling" class="nav-link text-gray-700 hover:text-blue-600 font-semibold">AI Counseling</a>
                <a href="#contact" class="nav-link text-gray-700 hover:text-blue-600 font-semibold">Contact</a>
            </div>
            <div class="flex items-center space-x-4">
                <a href="login.php" class="text-gray-700 hover:text-blue-600 font-semibold">Login</a>
                <a href="register.php" class="btn px-5 py-2 bg-blue-600 text-white rounded-full hover:bg-blue-700 font-semibold">Join Now</a>
                <button id="mobile-menu-button" class="md:hidden text-gray-700 hover:text-blue-600">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
        <div id="mobile-menu" class="hidden mobile-menu md:hidden py-6 px-6">
            <ul class="space-y-6 text-center">
                <li><a href="#" class="text-gray-700 hover:text-blue-600 font-semibold">Home</a></li>
                <li><a href="#services" class="text-gray-700 hover:text-blue-600 font-semibold">Services</a></li>
                <li><a href="#opportunities" class="text-gray-700 hover:text-blue-600 font-semibold">Opportunities</a></li>
                <li><a href="#ai-counseling" class="text-gray-700 hover:text-blue-600 font-semibold">AI Counseling</a></li>
                <li><a href="#contact" class="text-gray-700 hover:text-blue-600 font-semibold">Contact</a></li>
                <li><a href="login.php" class="text-gray-700 hover:text-blue-600 font-semibold">Login</a></li>
                <li><a href="register.php" class="text-blue-600 hover:text-blue-700 font-semibold">Join Now</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero-gradient pt-28 pb-20 text-white relative">
        <div class="container mx-auto px-6 text-center">
            <h1 class="text-5xl md:text-6xl font-bold mb-6 fade-in">Achieve your goals!</h1>
            <p class="text-xl md:text-2xl text-blue-100 mb-8 fade-in" style="animation-delay: 0.2s;"> AI tools and chances to help students, jobseekers, and professionals in Tanzania grow their careers.</p>
            <div class="flex flex-col md:flex-row justify-center gap-6 fade-in" style="animation-delay: 0.4s;">
                <a href="register.php" class="btn px-8 py-4 bg-white text-blue-600 rounded-full font-bold hover:bg-gray-100">Start Your Journey</a>
                <a href="#services" class="btn px-8 py-4 bg-blue-500 text-white rounded-full font-bold hover:bg-blue-600">Discover Services</a>
            </div>
        </div>
    </header>

    <!-- Statistics Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="card p-6 text-center fade-in">
                    <i class="fas fa-users text-blue-600 text-3xl mb-4"></i>
                    <div class="text-3xl font-bold text-gray-800">10K+</div>
                    <div class="text-gray-600">Students Inspired</div>
                </div>
                <div class="card p-6 text-center fade-in" style="animation-delay: 0.2s;">
                    <i class="fas fa-building text-blue-600 text-3xl mb-4"></i>
                    <div class="text-3xl font-bold text-gray-800">500+</div>
                    <div class="text-gray-600">Partner Companies</div>
                </div>
                <div class="card p-6 text-center fade-in" style="animation-delay: 0.4s;">
                    <i class="fas fa-briefcase text-blue-600 text-3xl mb-4"></i>
                    <div class="text-3xl font-bold text-gray-800">1K+</div>
                    <div class="text-gray-600">Careers Launched</div>
                </div>
                <div class="card p-6 text-center fade-in" style="animation-delay: 0.6s;">
                    <i class="fas fa-star text-blue-600 text-3xl mb-4"></i>
                    <div class="text-3xl font-bold text-gray-800">95%</div>
                    <div class="text-gray-600">Success Rate</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="py-20 bg-gray-50">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold text-center text-gray-800 mb-12 fade-in">What We Offer</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="card p-8 fade-in">
                    <i class="fas fa-briefcase text-blue-600 text-4xl mb-6"></i>
                    <h3 class="text-xl font-semibold mb-3">Job Opportunities</h3>
                    <p class="text-gray-600 mb-6">Connect with top jobs and internships in Tanzania.</p>
                    <a href="login.php?redirect=services/jobs.php" class="text-blue-600 hover:text-blue-800 font-semibold">Explore Jobs</a>
                </div>
                <div class="card p-8 fade-in" style="animation-delay: 0.2s;">
                    <i class="fas fa-dollar-sign text-green-600 text-4xl mb-6"></i>
                    <h3 class="text-xl font-semibold mb-3">Earn Online</h3>
                    <p class="text-gray-600 mb-6">Find gigs to make money while you learn.</p>
                    <a href="login.php?redirect=services/earn.php" class="text-blue-600 hover:text-blue-800 font-semibold">Get Started</a>
                </div>
                <div class="card p-8 fade-in" style="animation-delay: 0.4s;">
                    <i class="fas fa-book text-teal-600 text-4xl mb-6"></i>
                    <h3 class="text-xl font-semibold mb-3">Skill Building</h3>
                    <p class="text-gray-600 mb-6">Learn in-demand skills with tailored courses.</p>
                    <a href="login.php?redirect=services/learning.php" class="text-blue-600 hover:text-blue-800 font-semibold">Start Learning</a>
                </div>
                <div class="card p-8 fade-in" style="animation-delay: 0.6s;">
                    <i class="fas fa-robot text-yellow-600 text-4xl mb-6"></i>
                    <h3 class="text-xl font-semibold mb-3">AI Guidance</h3>
                    <p class="text-gray-600 mb-6">Personalized career advice powered by AI.</p>
                    <a href="login.php?redirect=services/bot.php" class="text-blue-600 hover:text-blue-800 font-semibold">Try AI Now</a>
                </div>
                <div class="card p-8 fade-in" style="animation-delay: 0.8s;">
                    <i class="fas fa-calendar text-red-600 text-4xl mb-6"></i>
                    <h3 class="text-xl font-semibold mb-3">Career Events</h3>
                    <p class="text-gray-600 mb-6">Join events to network and grow.</p>
                    <a href="login.php?redirect=services/events.php" class="text-blue-600 hover:text-blue-800 font-semibold">See Events</a>
                </div>
                <div class="card p-8 fade-in" style="animation-delay: 1s;">
                    <i class="fas fa-users text-indigo-600 text-4xl mb-6"></i>
                    <h3 class="text-xl font-semibold mb-3">Community</h3>
                    <p class="text-gray-600 mb-6">Connect with peers and mentors.</p>
                    <a href="#" class="text-blue-600 hover:text-blue-800 font-semibold">Join Now</a>
                </div>
            </div>
        </div>
    </section>

    <!-- AI Counseling Section -->
    <section id="ai-counseling" class="py-20 bg-gradient-to-r from-blue-600 to-blue-800 text-white">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div class="fade-in">
                    <h2 class="text-3xl font-bold mb-6">Next-Level AI Career Support</h2>
                    <p class="text-lg mb-8">Unlock personalized guidance with our AI tools.</p>
                    <ul class="space-y-4">
                        <li class="flex items-center"><i class="fas fa-check-circle mr-3"></i> Career Planning</li>
                        <li class="flex items-center"><i class="fas fa-check-circle mr-3"></i> Resume Boost</li>
                        <li class="flex items-center"><i class="fas fa-check-circle mr-3"></i> Interview Prep</li>
                    </ul>
                </div>
                <div class="text-center fade-in" style="animation-delay: 0.2s;">
                    <a href="login.php?redirect=services/bot.php" class="btn inline-block px-8 py-4 bg-white text-blue-600 rounded-full font-bold hover:bg-gray-100">Experience AI Now</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-20 bg-gray-50">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold text-center text-gray-800 mb-12 fade-in">Let’s Connect</h2>
            <div class="max-w-lg mx-auto card p-8 fade-in" style="animation-delay: 0.2s;">
                <form class="space-y-6">
                    <input type="text" placeholder="Your Name" class="w-full px-4 py-3 rounded-md border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    <input type="email" placeholder="Your Email" class="w-full px-4 py-3 rounded-md border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    <textarea rows="4" placeholder="Your Message" class="w-full px-4 py-3 rounded-md border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"></textarea>
                    <button type="submit" class="w-full btn px-6 py-3 bg-blue-600 text-white rounded-full hover:bg-blue-700 font-bold">Send Message</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-12">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <div>
                    <h4 class="text-xl font-bold text-white mb-4">SmartCareer</h4>
                    <p class="text-sm">Empowering Tanzanian students for a brighter future.</p>
                </div>
                <div>
                    <h4 class="text-xl font-bold text-white mb-4">Explore</h4>
                    <ul class="space-y-3 text-sm">
                        <li><a href="#" class="hover:text-white">About Us</a></li>
                        <li><a href="#services" class="hover:text-white">Services</a></li>
                        <li><a href="#contact" class="hover:text-white">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xl font-bold text-white mb-4">Legal</h4>
                    <ul class="space-y-3 text-sm">
                        <li><a href="#" class="hover:text-white">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-white">Terms of Service</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xl font-bold text-white mb-4">Follow Us</h4>
                    <div class="flex space-x-6">
                        <a href="#" class="hover:text-white text-xl"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="hover:text-white text-xl"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="hover:text-white text-xl"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <div class="mt-12 pt-8 border-t border-gray-800 text-center text-sm">
                <p>© <?php echo date('Y'); ?> SmartCareer Tanzania. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Trigger fade-in animations on scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
    </script>
</body>
</html>