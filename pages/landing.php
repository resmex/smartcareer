<?php
session_start();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCareer Tanzania | Transforming Student Careers</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Custom Styles */
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #fbbf24;
        }

        body {
            font-family: 'Poppins', sans-serif;
        }

        .gradient-hero {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .nav-link {
            position: relative;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -4px;
            left: 0;
            background-color: var(--accent-color);
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .feature-icon {
            transition: all 0.3s ease;
        }

        .service-card:hover .feature-icon {
            transform: scale(1.1);
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Fixed Navigation -->
    <nav class="fixed w-full z-50 bg-white shadow-lg">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="/">
                        <span class="text-2xl font-bold">
                            <span class="text-blue-600">Smart</span><span class="text-black">Career</span>
                        </span>
                    </a>
                </div>
                
                <div class="hidden md:flex space-x-8">
                    <a href="#" class="nav-link text-gray-700 hover:text-blue-600 font-medium">Home</a>
                    <a href="#services" class="nav-link text-gray-700 hover:text-blue-600 font-medium">Services</a>
                    <a href="#opportunities" class="nav-link text-gray-700 hover:text-blue-600 font-medium">Opportunities</a>
                    <a href="#ai-counseling" class="nav-link text-gray-700 hover:text-blue-600 font-medium">AI Counseling</a>
                    <a href="#contact" class="nav-link text-gray-700 hover:text-blue-600 font-medium">Contact</a>
                </div>

                <div class="flex items-center space-x-4">
                    <a href="login.html" class="px-6 py-2 text-gray-600 font-semibold rounded-md transition-colors duration-300 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-200">Login</a>
                    <a href="register.html" class="px-6 py-2 text-blue-600 font-semibold rounded-md border-2 border-blue-600 transition-all duration-300
                                      hover:bg-green-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-blue-200">Register</a>
                  </div>

                <button id="mobile-menu-button" class="md:hidden text-gray-700">
                    <i class="fas fa-bars text-2xl"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Dynamic Background -->
    <header class="gradient-hero pt-32 pb-20">
        <div class="container mx-auto px-6 text-center">
            <div class="glass-effect rounded-xl p-8 max-w-4xl mx-auto">
                <h1 class="text-5xl md:text-6xl font-bold text-white mb-6">Shape Your Future Career</h1>
                <p class="text-xl text-blue-100 mb-8">Empowering Tanzanian students with AI-driven career guidance, skill development, and job opportunities</p>
                <div class="flex flex-col md:flex-row justify-center gap-4">
                    <a href="register.html" class="px-8 py-4 bg-yellow-400 text-blue-900 rounded-lg font-bold hover:bg-yellow-300 transform hover:scale-105 transition-all duration-300 shadow-lg">
                        Start Your Journey
                    </a>
                    <a href="#services" class="px-8 py-4 bg-green-400 text-white rounded-lg font-bold transition-all duration-300 shadow-lg hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-300">
                        Explore Services
                    </a>
                </div>
            </div>
        </div>
        <div id="mobile-menu" class="hidden md:hidden bg-white py-4 px-6 absolute top-16 left-0 w-full shadow-md z-50">
            <ul>
                <li><a href="#about">About</a></li>
                <li><a href="#services">Services</a></li>
                <li><a href="#contact">Contact</a></li>
                <li><a href="login.php" class="hover:text-blue-500">Log In</a></li>
                <li><a href="register.php" class="hover:text-green-500">Join</a></li>
            </ul>
        </div>
    </header>

    <!-- Statistics Section -->
    <section class="py-12 bg-white">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center p-6 rounded-lg card-hover">
                    <div class="text-3xl font-bold text-blue-600 mb-2">10,000+</div>
                    <div class="text-gray-600">Active Students</div>
                </div>
                <div class="text-center p-6 rounded-lg card-hover">
                    <div class="text-3xl font-bold text-blue-600 mb-2">500+</div>
                    <div class="text-gray-600">Partner Companies</div>
                </div>
                <div class="text-center p-6 rounded-lg card-hover">
                    <div class="text-3xl font-bold text-blue-600 mb-2">1,000+</div>
                    <div class="text-gray-600">Job Placements</div>
                </div>
                <div class="text-center p-6 rounded-lg card-hover">
                    <div class="text-3xl font-bold text-blue-600 mb-2">95%</div>
                    <div class="text-gray-600">Success Rate</div>
                </div>
            </div>
        </div>
    </section>

    <section id="services" class="py-16 bg-gray-50">
        <div class="container mx-auto px-6">
            <h2 class="text-3xl font-bold text-center mb-12">Comprehensive Career Services</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
    
                <div class="service-card bg-white p-8 rounded-xl shadow-lg card-hover">
                    <div class="feature-icon text-blue-600 text-4xl mb-6">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-4">Career Opportunities</h3>
                    <p class="text-gray-600 mb-6">Access exclusive job listings and internships from top companies in Tanzania.</p>
                    <a href="login.php?redirect=services/jobs.php class="text-blue-600 hover:text-blue-800 font-medium flex items-center">
                        Explore Opportunities <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
    
                <div class="service-card bg-white p-8 rounded-xl shadow-lg card-hover">
                    <div class="feature-icon text-green-500 text-4xl mb-6">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-4">Earn Money</h3>
                    <p class="text-gray-600 mb-6">Explore side gigs and freelance opportunities to earn while learning.</p>
                   <a href="login.php?redirect=services/earn.php" class="text-green-500 hover:text-green-700 font-medium flex items-center">
                        Find Opportunities <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
    
                <div class="service-card bg-white p-8 rounded-xl shadow-lg card-hover">
                    <div class="feature-icon text-red-500 text-4xl mb-6">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-4">Career Events</h3>
                    <p class="text-gray-600 mb-6">Join career fairs, meetups, and workshops to connect with professionals and opportunities.</p>
                    <a href="login.php?redirect=services/events.php" class="text-red-500 hover:text-red-700 font-medium flex items-center">
                        View Events <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
    
                <div class="service-card bg-white p-8 rounded-xl shadow-lg card-hover">
                    <div class="feature-icon text-green-500 text-4xl mb-6">
                        <i class="fas fa-book"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-4">Learning Resources</h3>
                    <p class="text-gray-600 mb-6">Upgrade your skills with trending courses and resources designed for Tanzanian students.</p>
                    <a href="login.php?redirect=services/learning.php" class="text-green-500 hover:text-green-700 font-medium flex items-center">
                        Explore Resources <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
    
                <div class="service-card bg-white p-8 rounded-xl shadow-lg card-hover">
                    <div class="feature-icon text-yellow-500 text-4xl mb-6">
                        <i class="fas fa-robot"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-4">AI Counseling</h3>
                    <p class="text-gray-600 mb-6">Get personalized career advice, resume tips, and interview preparation powered by AI.</p>
                    <a href="login.php?redirect=services/bot.php" class="text-yellow-500 hover:text-yellow-700 font-medium flex items-center">
                        Get Counseling <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
    
                <div class="service-card bg-white p-8 rounded-xl shadow-lg card-hover">
                    <div class="feature-icon text-indigo-500 text-4xl mb-6">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-4">Community</h3>
                    <p class="text-gray-600 mb-6">Be part of a vibrant community of students, mentors, and industry professionals.</p>
                    <a href="#" class="text-indigo-500 hover:text-indigo-700 font-medium flex items-center">
                        Join the Community <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
    
            </div>
        </div>
    </section>
    
    <section id="ai-counseling" class="py-16 bg-white">
        <div class="container mx-auto px-6">
            <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-2xl p-12 text-white">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                    <div>
                        <h2 class="text-3xl font-bold mb-6">AI-Powered Career Guidance</h2>
                        <p class="text-xl mb-8">Get personalized career advice, resume reviews, and interview preparation powered by advanced AI technology.</p>
                        <ul class="space-y-4">
                            <li class="flex items-center">
                                <i class="fas fa-check-circle mr-3"></i>
                                Personalized Career Path Planning
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check-circle mr-3"></i>
                                Smart Resume Builder
                            </li>
                            <li class="flex items-center">
                                <i class="fas fa-check-circle mr-3"></i>
                                Interview Simulation
                            </li>
                        </ul>
                    </div>
                    <div class="text-center">
                        <a href="login.php?redirect=services/bot.php" class="inline-block px-8 py-4 bg-white text-blue-600 rounded-lg font-bold hover:bg-gray-100 transform hover:scale-105 transition-all duration-300 shadow-lg">
                            Start Free Consultation
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-16 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-3xl font-bold text-center mb-8">Get in Touch</h2>
                <form class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <input type="text" placeholder="Name" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                        <input type="email" placeholder="Email" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    </div>
                    <textarea rows="5" placeholder="Message" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"></textarea>
                    <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-lg font-bold hover:bg-blue-700 transition-all duration-300">
                        Send Message
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <div>
                    <h4 class="text-xl font-bold mb-4">SmartCareer</h4>
                    <p class="text-gray-400">Empowering the next generation of Tanzanian professionals.</p>
                </div>
                <div>
                    <h4 class="text-xl font-bold mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">About Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Services</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xl font-bold mb-4">Legal</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Terms of Service</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xl font-bold mb-4">Connect</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white text-xl"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white text-xl"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white text-xl"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-400">
                <p>&copy; 2025 SmartCareer Tanzania. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuButton.addEventListener('click', () => {
            const nav = document.querySelector('nav');
            nav.classList.toggle('h-screen');
            mobileMenu.classList.toggle('hidden');
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    </script>
</body>
</html>