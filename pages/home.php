<?php
session_start();
include '../includes/connect.php';

// Redirect to dashboard if logged in
if (isset($_SESSION['user_id'])) {
    header("Location: services/dashboard.php");
    exit();
}

// Fetch real data with error handling
$users_count = 0;
$companies_count = 0;
$jobs_count = 0;
$success_rate = 0;

try {
    // Count total users
    $stmt = $con->prepare("SELECT COUNT(*) as users FROM users");
    if ($stmt) {
        $stmt->execute();
        $users_count = $stmt->get_result()->fetch_assoc()['users'] ?? 0;
        $stmt->close();
    }

    // Count total companies (assuming 'company' in jobs is a name, not an ID; adjust if linked to companies table)
    $stmt = $con->prepare("SELECT COUNT(DISTINCT company) as companies FROM jobs");
    if ($stmt) {
        $stmt->execute();
        $companies_count = $stmt->get_result()->fetch_assoc()['companies'] ?? 0;
        $stmt->close();
    }

    // Count total jobs (no status column, so count all)
    $stmt = $con->prepare("SELECT COUNT(*) as jobs FROM jobs");
    if ($stmt) {
        $stmt->execute();
        $jobs_count = $stmt->get_result()->fetch_assoc()['jobs'] ?? 0;
        $stmt->close();
    }

    // Count successful applications (status = 'hired')
    $stmt = $con->prepare("SELECT COUNT(*) as success FROM job_applications WHERE status = 'hired'");
    if ($stmt) {
        $stmt->execute();
        $success_count = $stmt->get_result()->fetch_assoc()['success'] ?? 0;
        $success_rate = $jobs_count > 0 ? round(($success_count / $jobs_count) * 100) : 0;
        $stmt->close();
    }
} catch (mysqli_sql_exception $e) {
    error_log("Database error: " . $e->getMessage());
    // Continue with default values (0) if an error occurs
}

$con->close();
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
    <link href="../assets/css/home.css" rel="stylesheet">
</head>
<body>
    <div class="bg-animated"></div>
    <div class="bg-particles"></div>

    <nav class="nav-sticky">
        <div class="container mx-auto px-6 py-4 flex items-center justify-between">
            <a href="../pages/home.php" class="text-3xl font-bold tracking-tight">
                <span class="text-blue-600">Smart</span><span class="text-gray-900">Career</span>
            </a>
            <div class="hidden md:flex space-x-12">
                <a href="#" class="nav-link text-gray-700 hover:text-blue-600">Home</a>
                <a href="#services" class="nav-link text-gray-700 hover:text-blue-600">Services</a>
                <a href="#opportunities" class="nav-link text-gray-700 hover:text-blue-600">Opportunities</a>
                <a href="#ai-counseling" class="nav-link text-gray-700 hover:text-blue-600">AI Counseling</a>
                <a href="#contact" class="nav-link text-gray-700 hover:text-blue-600">Contact</a>
            </div>
            <div class="flex items-center space-x-6">
                <a href="login.php" class="text-gray-700 hover:text-blue-600 font-semibold">Login</a>
                <a href="register.php" class="btn px-6 py-2.5 bg-blue-600 text-white rounded-full hover:bg-blue-700">Join Now</a>
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

    <header class="hero-gradient text-white relative">
        <div class="container mx-auto px-6 text-center">
            <h1 class="text-5xl md:text-6xl font-bold mb-6 fade-in tracking-tight">Unleash Your Future!</h1>
            <p class="text-xl md:text-2xl text-blue-100 mb-8 fade-in" style="animation-delay: 0.2s;">Empowering students, jobseekers, and professionals in Tanzania with AI tools and career opportunities.</p>
            <div class="flex flex-col md:flex-row justify-center gap-6 fade-in" style="animation-delay: 0.4s;">
                <a href="register.php" class="btn px-8 py-4 bg-white text-blue-600 rounded-full font-bold hover:bg-gray-100">Start Your Journey</a>
                <a href="#services" class="btn px-8 py-4 bg-blue-500 text-white rounded-full font-bold hover:bg-blue-600">Discover Services</a>
            </div>
        </div>
    </header>

    <section class="py-16 bg-white relative z-10">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="card p-6 text-center fade-in">
                    <i class="fas fa-users text-blue-600 text-3xl mb-4"></i>
                    <div class="text-3xl font-bold text-gray-800"><?php echo number_format($users_count); ?>+</div>
                    <div class="text-gray-600">Users Inspired</div>
                </div>
                <div class="card p-6 text-center fade-in" style="animation-delay: 0.2s;">
                    <i class="fas fa-building text-blue-600 text-3xl mb-4"></i>
                    <div class="text-3xl font-bold text-gray-800"><?php echo number_format($companies_count); ?>+</div>
                    <div class="text-gray-600">Partner Companies</div>
                </div>
                <div class="card p-6 text-center fade-in" style="animation-delay: 0.4s;">
                    <i class="fas fa-briefcase text-blue-600 text-3xl mb-4"></i>
                    <div class="text-3xl font-bold text-gray-800"><?php echo number_format($jobs_count); ?>+</div>
                    <div class="text-gray-600">Jobs Available</div>
                </div>
                <div class="card p-6 text-center fade-in" style="animation-delay: 0.6s;">
                    <i class="fas fa-star text-blue-600 text-3xl mb-4"></i>
                    <div class="text-3xl font-bold text-gray-800"><?php echo $success_rate; ?>%</div>
                    <div class="text-gray-600">Success Rate</div>
                </div>
            </div>
        </div>
    </section>

    <section id="services" class="py-20 bg-gray-50 relative z-10">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold text-center text-gray-800 mb-12 fade-in">What We Offer</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="card p-8 fade-in">
                    <i class="fas fa-briefcase text-blue-600 text-4xl mb-6"></i>
                    <h3 class="text-xl font-semibold mb-3">Job Opportunities</h3>
                    <p class="text-gray-600 mb-6">Access top jobs and internships across Tanzania.</p>
                    <a href="login.php?redirect=services/jobs.php" class="text-blue-600 hover:text-blue-800 font-semibold">Explore Jobs</a>
                </div>
                <div class="card p-8 fade-in" style="animation-delay: 0.2s;">
                    <i class="fas fa-dollar-sign text-green-600 text-4xl mb-6"></i>
                    <h3 class="text-xl font-semibold mb-3">Earn Online</h3>
                    <p class="text-gray-600 mb-6">Discover gigs to earn money while learning.</p>
                    <a href="login.php?redirect=services/earn.php" class="text-blue-600 hover:text-blue-800 font-semibold">Get Started</a>
                </div>
                <div class="card p-8 fade-in" style="animation-delay: 0.4s;">
                    <i class="fas fa-book text-teal-600 text-4xl mb-6"></i>
                    <h3 class="text-xl font-semibold mb-3">Skill Building</h3>
                    <p class="text-gray-600 mb-6">Master in-demand skills with curated courses.</p>
                    <a href="login.php?redirect=services/learning.php" class="text-blue-600 hover:text-blue-800 font-semibold">Start Learning</a>
                </div>
                <div class="card p-8 fade-in" style="animation-delay: 0.6s;">
                    <i class="fas fa-robot text-yellow-600 text-4xl mb-6"></i>
                    <h3 class="text-xl font-semibold mb-3">AI Guidance</h3>
                    <p class="text-gray-600 mb-6">Get tailored career advice from AI tools.</p>
                    <a href="login.php?redirect=services/bot.php" class="text-blue-600 hover:text-blue-800 font-semibold">Try AI Now</a>
                </div>
                <div class="card p-8 fade-in" style="animation-delay: 0.8s;">
                    <i class="fas fa-calendar text-red-600 text-4xl mb-6"></i>
                    <h3 class="text-xl font-semibold mb-3">Career Events</h3>
                    <p class="text-gray-600 mb-6">Network and grow at exclusive events.</p>
                    <a href="login.php?redirect=services/events.php" class="text-blue-600 hover:text-blue-800 font-semibold">See Events</a>
                </div>
                <div class="card p-8 fade-in" style="animation-delay: 1s;">
                    <i class="fas fa-users text-indigo-600 text-4xl mb-6"></i>
                    <h3 class="text-xl font-semibold mb-3">Community</h3>
                    <p class="text-gray-600 mb-6">Join a network of peers and mentors.</p>
                    <a href="register.php" class="text-blue-600 hover:text-blue-800 font-semibold">Join Now</a>
                </div>
            </div>
        </div>
    </section>

    <section id="ai-counseling" class="py-20 bg-gradient-to-r from-blue-600 to-blue-800 text-white relative z-10">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div class="fade-in">
                    <h2 class="text-3xl font-bold mb-6 tracking-tight">Next-Level AI Career Support</h2>
                    <p class="text-lg mb-8">Unlock personalized guidance with cutting-edge AI tools.</p>
                    <ul class="space-y-4">
                        <li class="flex items-center"><i class="fas fa-check-circle mr-3 text-green-300"></i> Career Planning</li>
                        <li class="flex items-center"><i class="fas fa-check-circle mr-3 text-green-300"></i> Resume Boost</li>
                        <li class="flex items-center"><i class="fas fa-check-circle mr-3 text-green-300"></i> Interview Prep</li>
                    </ul>
                </div>
                <div class="text-center fade-in" style="animation-delay: 0.2s;">
                    <a href="login.php?redirect=services/bot.php" class="btn inline-block px-8 py-4 bg-white text-blue-600 rounded-full font-bold hover:bg-gray-100">Experience AI Now</a>
                </div>
            </div>
        </div>
    </section>

    <section id="contact" class="py-20 bg-gray-50 relative z-10">
        <div class="container mx-auto px-6">
            <h2 class="text-4xl font-bold text-center text-gray-800 mb-12 fade-in">Let’s Connect</h2>
            <div class="max-w-lg mx-auto card p-8 fade-in" style="animation-delay: 0.2s;">
                <?php if (isset($_GET['success'])): ?>
                    <p class="text-green-500 mb-4"><?php echo htmlspecialchars($_GET['success']); ?></p>
                <?php elseif (isset($_GET['error'])): ?>
                    <p class="text-red-500 mb-4"><?php echo htmlspecialchars($_GET['error']); ?></p>
                <?php endif; ?>
                <form action="../submit_contact.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <input type="text" name="name" placeholder="Your Name" class="w-full px-4 py-3 rounded-md border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200" required>
                    <input type="email" name="email" placeholder="Your Email" class="w-full px-4 py-3 rounded-md border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200" required>
                    <textarea rows="4" name="message" placeholder="Your Message" class="w-full px-4 py-3 rounded-md border border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-200" required></textarea>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Upload File (Text, Image, etc.)</label>
                        <input type="file" name="attachment" class="w-full px-4 py-3 rounded-md border border-gray-300" accept=".txt,.pdf,.doc,.docx,.jpg,.jpeg,.png">
                    </div>
                    <button type="submit" class="w-full btn px-6 py-3 bg-blue-600 text-white rounded-full hover:bg-blue-700 font-bold">Send Message</button>
                </form>
            </div>
        </div>
    </section>

    <footer class="bg-gray-900 text-gray-300 py-12 relative z-10">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
                <div>
                    <h4 class="text-xl font-bold text-white mb-4">SmartCareer</h4>
                    <p class="text-sm">Empowering Tanzanian careers with innovation.</p>
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

    <script src="../assets/js/home.js"></script>
</body>
</html>