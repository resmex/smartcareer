<?php
// dashboard.php
session_start();
include '../../includes/connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

$username = $_SESSION['username'];

// If first_name is not set in the session, fetch it from the database
if (!isset($_SESSION['first_name'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $con->prepare("SELECT first_name FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['first_name'] = $user['first_name']; // Store first_name in session
    } else {
        $_SESSION['first_name'] = 'User'; // Default to 'User' if not found
    }
}
$firstName = $_SESSION['first_name']; // Retrieve first_name from session

// Fetch profile picture from the database
$profilePicture = '../../uploads.profile.png'; 
if (!empty($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $con->prepare("SELECT profile_picture FROM user_profiles WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user_profile = $result->fetch_assoc();
        $profilePicture = !empty($user_profile['profile_picture']) 
            ? '../../uploads/' . htmlspecialchars($user_profile['profile_picture']) 
            : $profilePicture; 
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCareer Dashboard | Student Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .sidebar-link {
            transition: all 0.3s ease;
        }
        .sidebar-link:hover {
            background-color: #2563eb;
            color: white;
        }
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .settings-card {
            display: none; /* Hidden by default */
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        .settings-card.show {
            display: block;
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        .overlay.show {
            display: block;
        }
        .sticky-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 50;
        }
        .sidebar {
            position: fixed;
            top: 64px; /* Height of the header */
            left: 0;
            height: calc(100vh - 64px); /* Full height minus header height */
            width: 16rem; /* Width of the sidebar */
            overflow-y: auto; /* Enable scrolling if content overflows */
        }
        .main-content {
            margin-left: 16rem; /* Width of the sidebar */
            margin-top: 64px; /* Height of the header */
            padding: 1rem;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Sticky Container for Header -->
    <div class="sticky-container">
        <!-- Header -->
        <?php include '../../includes/header.php'; ?>
    </div>

    <!-- Sidebar -->
    <div class="sidebar bg-white shadow-lg">
        <div class="p-4">
            <!-- Sidebar Links -->
            <ul class="space-y-2">
                <li>
                    <a href="#" class="sidebar-link flex items-center space-x-3 p-3 rounded-lg text-gray-700">
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="../services/jobs.php" class="sidebar-link flex items-center space-x-3 p-3 rounded-lg text-gray-700">
                        <i class="fas fa-briefcase"></i>
                        <span>Opportunities</span>
                    </a>
                </li>
                <li>
                    <a href="../services/earn.php" class="sidebar-link flex items-center space-x-3 p-3 rounded-lg text-gray-700">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Earn online</span>
                    </a>
                </li>
                <li>
                    <a href="../services/bot.php" class="sidebar-link flex items-center space-x-3 p-3 rounded-lg text-gray-700">
                        <i class="fas fa-robot"></i>
                        <span>AI Counseling</span>
                    </a>
                </li>
                <li>
                    <a href="../services/learning.php" class="sidebar-link flex items-center space-x-3 p-3 rounded-lg text-gray-700">
                        <i class="fas fa-book"></i>
                        <span>Learning</span>
                    </a>
                </li>
                <li>
                    <a href="../services/events.php" class="sidebar-link flex items-center space-x-3 p-3 rounded-lg text-gray-700">
                        <i class="fas fa-calendar"></i>
                        <span>Events</span>
                    </a>
                </li>
                <!-- Settings Link -->
                <li>
                    <button id="settingsButtonSidebar" class="sidebar-link flex items-center space-x-3 p-3 rounded-lg text-gray-700 w-full">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </button>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Welcome Section -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl p-8 text-white mb-8">
            <h1 class="text-3xl font-bold mb-2">Welcome back, <?php echo htmlspecialchars($firstName); ?>!</h1>
            <p>Continue your journey towards career success.</p>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500">Applied Jobs</p>
                        <h3 class="text-2xl font-bold">12</h3>
                    </div>
                    <div class="text-blue-500 text-2xl">
                        <i class="fas fa-briefcase"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500">Courses</p>
                        <h3 class="text-2xl font-bold">4</h3>
                    </div>
                    <div class="text-green-500 text-2xl">
                        <i class="fas fa-book"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500">AI Sessions</p>
                        <h3 class="text-2xl font-bold">8</h3>
                    </div>
                    <div class="text-yellow-500 text-2xl">
                        <i class="fas fa-robot"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm card-hover">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500">Events</p>
                        <h3 class="text-2xl font-bold">3</h3>
                    </div>
                    <div class="text-purple-500 text-2xl">
                        <i class="fas fa-calendar"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Recent Opportunities -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold mb-4">Recent Opportunities</h2>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div>
                            <h3 class="font-semibold">Software Developer</h3>
                            <p class="text-sm text-gray-500">TechCorp Tanzania</p>
                        </div>
                        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Apply</button>
                    </div>
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div>
                            <h3 class="font-semibold">Marketing Intern</h3>
                            <p class="text-sm text-gray-500">Global Marketing Ltd</p>
                        </div>
                        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Apply</button>
                    </div>
                </div>
            </div>

            <!-- Upcoming Events -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-bold mb-4">Upcoming Events</h2>
                <div class="space-y-4">
                    <div class="flex items-center space-x-4 p-4 border rounded-lg">
                        <div class="text-center">
                            <div class="text-xl font-bold text-blue-600">15</div>
                            <div class="text-sm text-gray-500">MAR</div>
                        </div>
                        <div>
                            <h3 class="font-semibold">Tech Career Fair</h3>
                            <p class="text-sm text-gray-500">9:00 AM - 5:00 PM</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4 p-4 border rounded-lg">
                        <div class="text-center">
                            <div class="text-xl font-bold text-blue-600">22</div>
                            <div class="text-sm text-gray-500">MAR</div>
                        </div>
                        <div>
                            <h3 class="font-semibold">Resume Workshop</h3>
                            <p class="text-sm text-gray-500">2:00 PM - 4:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Card (Modal) -->
    <div id="settingsCard" class="settings-card bg-white rounded-lg shadow-lg w-full max-w-md p-6">
        <h2 class="text-2xl font-bold text-gray-900 mb-6">Settings</h2>
        <div class="space-y-4">
            <!-- View Profile -->
            <a href="../pages/profile/profile.php" class="block px-4 py-3 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition duration-300">
                <i class="fas fa-eye mr-2"></i>View Profile
            </a>
            <!-- Logout -->
            <a href="../pages/logout.php" class="block px-4 py-3 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition duration-300">
                <i class="fas fa-sign-out-alt mr-2"></i>Logout
            </a>
        </div>
    </div>

    <!-- Overlay -->
    <div id="overlay" class="overlay"></div>

    <script>
        // Toggle settings card
        const settingsButton = document.getElementById('settingsButton');
        const settingsButtonSidebar = document.getElementById('settingsButtonSidebar');
        const settingsCard = document.getElementById('settingsCard');
        const overlay = document.getElementById('overlay');

        const toggleSettingsCard = () => {
            settingsCard.classList.toggle('show');
            overlay.classList.toggle('show');
        };

        settingsButton.addEventListener('click', toggleSettingsCard);
        settingsButtonSidebar.addEventListener('click', toggleSettingsCard);

        // Close settings card when clicking outside
        overlay.addEventListener('click', () => {
            settingsCard.classList.remove('show');
            overlay.classList.remove('show');
        });
    </script>
</body>
</html>