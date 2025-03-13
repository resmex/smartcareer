<?php
session_start();
include '../../includes/connect.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

// Fetch chat history from bot.php (assuming it's stored in the session)
$_SESSION['chat_history'] = isset($_SESSION['chat_history']) ? $_SESSION['chat_history'] : [];

// $username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// Fetch first_name if not set
if (!isset($_SESSION['first_name'])) {
    $stmt = $con->prepare("SELECT first_name FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $_SESSION['first_name'] = $result->num_rows > 0 ? $result->fetch_assoc()['first_name'] : 'User';
}
$firstName = $_SESSION['first_name'];

// Fetch profile picture with updated path
$profilePicture = '/smartcareer/uploads/profile_default.jpeg'; // Relative path for web access
$stmt = $con->prepare("SELECT profile_picture FROM user_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user_profile = $result->fetch_assoc();
    $profilePicture = !empty($user_profile['profile_picture']) ? '/smartcareer/uploads/' . htmlspecialchars($user_profile['profile_picture']) : $profilePicture;
}
$stmt->close();

// Placeholder stats (replace with real queries)
$applied_jobs = 12;
$courses_completed = 4;
$ai_sessions = count($_SESSION['chat_history']); // Number of AI sessions
$events_attended = 3;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCareer Dashboard | Career Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3b82f6; /* Softer blue */
            --secondary-color: #1e3a8a; /* Darker blue */
            --accent-color: #f59e0b; /* Warm amber */
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 80px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .header {
            background: white;
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.05);
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            z-index: 1000;
            height: 60px;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: #ffffff;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
            transition: width 0.3s ease;
            z-index: 999;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar .logo {
            padding: 1.5rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar.collapsed .logo-text {
            display: none;
        }

        .sidebar.collapsed .nav-text {
            display: none;
        }

        .sidebar .nav-item {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: #4b5563;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .sidebar .nav-item:hover, .sidebar .nav-item.active {
            background: var(--primary-color);
            color: white;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            margin-top: 60px;
            min-height: calc(100vh - 60px);
            transition: margin-left 0.3s ease;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .progress-bar {
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.5s ease;
        }

        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .modal.show {
            display: block;
            opacity: 1;
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: 999;
        }

        .overlay.show {
            display: block;
        }

        .toggle-btn {
            background: var(--primary-color);
            color: white;
            padding: 0.5rem;
            border-radius: 50%;
            cursor: pointer;
            position: absolute;
            top: 1.5rem;
            right: 1rem;
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar.collapsed .toggle-btn {
            right: 0.75rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: var(--sidebar-collapsed-width);
            }

            .main-content {
                margin-left: var(--sidebar-collapsed-width);
            }

            .sidebar .logo-text, .sidebar .nav-text {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <?php include '../../includes/header.php'; ?>
    </header>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="logo">
            <span class="logo-text">SmartCareer</span>
            <div class="toggle-btn" id="toggleSidebar"><i class="fas fa-bars"></i></div>
        </div>
        <nav>
            <a href="#" class="nav-item active"><i class="fas fa-home mr-3"></i><span class="nav-text">Dashboard</span></a>
            <a href="../services/jobs.php" class="nav-item"><i class="fas fa-briefcase mr-3"></i><span class="nav-text">Opportunities</span></a>
            <a href="../services/earn.php" class="nav-item"><i class="fas fa-money-bill-wave mr-3"></i><span class="nav-text">Earn Online</span></a>
            <a href="../services/bot.php" class="nav-item"><i class="fas fa-robot mr-3"></i><span class="nav-text">AI Counseling</span></a>
            <a href="../services/learning.php" class="nav-item"><i class="fas fa-book mr-3"></i><span class="nav-text">Learning</span></a>
            <a href="../services/events.php" class="nav-item"><i class="fas fa-calendar mr-3"></i><span class="nav-text">Events</span></a>
            <button id="settingsButtonSidebar" class="nav-item w-full text-left"><i class="fas fa-cog mr-3"></i><span class="nav-text">Settings</span></button>
            <a href="../logout.php" class="nav-item"><i class="fas fa-sign-out-alt mr-3"></i><span class="nav-text">Logout</span></a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Welcome Section -->
        <div class="mb-8 card">
            <div class="flex items-center space-x-4">
                <!-- <img src="<?php echo $profilePicture; ?>" alt="Profile" class="w-12 h-12 rounded-full object-cover border-2 border-gray-200"> -->
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Welcome back, <?php echo htmlspecialchars($firstName); ?>!</h1>
                    <p class="text-sm text-gray-500">Your career journey continues here.</p>
                </div>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="card">
                <p class="text-gray-500 text-xs uppercase font-medium">Applied Jobs</p>
                <h3 class="text-xl font-semibold text-gray-800"><?php echo $applied_jobs; ?></h3>
                <div class="progress-bar mt-2"><div class="progress-fill bg-blue-500" style="width: <?php echo min($applied_jobs * 10, 100); ?>%;"></div></div>
            </div>
            <div class="card">
                <p class="text-gray-500 text-xs uppercase font-medium">Courses Completed</p>
                <h3 class="text-xl font-semibold text-gray-800"><?php echo $courses_completed; ?></h3>
                <div class="progress-bar mt-2"><div class="progress-fill bg-green-500" style="width: <?php echo min($courses_completed * 20, 100); ?>%;"></div></div>
            </div>
            <div class="card">
                <p class="text-gray-500 text-xs uppercase font-medium">AI Sessions</p>
                <h3 class="text-xl font-semibold text-gray-800"><?php echo $ai_sessions; ?></h3>
                <div class="progress-bar mt-2"><div class="progress-fill bg-yellow-500" style="width: <?php echo min($ai_sessions * 12.5, 100); ?>%;"></div></div>
                <a href="../services/bot.php" class="text-blue-600 hover:underline text-xs font-medium mt-2 inline-block">View All Sessions</a>
            </div>
            <div class="card">
                <p class="text-gray-500 text-xs uppercase font-medium">Events Attended</p>
                <h3 class="text-xl font-semibold text-gray-800"><?php echo $events_attended; ?></h3>
                <div class="progress-bar mt-2"><div class="progress-fill bg-purple-500" style="width: <?php echo min($events_attended * 33, 100); ?>%;"></div></div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Career Progress -->
            <div class="card lg:col-span-2">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Career Progress</h2>
                <div class="space-y-6">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-sm text-gray-600 font-medium">Profile Completion</p>
                            <span class="text-blue-600 text-sm font-semibold">75%</span>
                        </div>
                        <div class="progress-bar"><div class="progress-fill bg-blue-600" style="width: 75%;"></div></div>
                        <p class="text-xs text-gray-500 mt-1">Add skills and experience</p>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-sm text-gray-600 font-medium">Job Readiness</p>
                            <span class="text-green-600 text-sm font-semibold">60%</span>
                        </div>
                        <div class="progress-bar"><div class="progress-fill bg-green-600" style="width: 60%;"></div></div>
                        <p class="text-xs text-gray-500 mt-1">Based on applications and skills</p>
                    </div>
                    <a href="../profile/profile.php" class="text-blue-600 hover:underline text-sm font-medium">Update Profile</a>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h2>
                <div class="space-y-3">
                    <a href="../services/jobs.php" class="block p-2 bg-blue-50 text-blue-700 rounded-md hover:bg-blue-100 text-sm font-medium"><i class="fas fa-search mr-2"></i>Find Jobs</a>
                    <a href="../services/bot.php" class="block p-2 bg-yellow-50 text-yellow-700 rounded-md hover:bg-yellow-100 text-sm font-medium"><i class="fas fa-robot mr-2"></i>Ask CareerBot</a>
                    <a href="../services/learning.php" class="block p-2 bg-green-50 text-green-700 rounded-md hover:bg-green-100 text-sm font-medium"><i class="fas fa-book mr-2"></i>Start Learning</a>
                </div>
            </div>

            <!-- Personalized Recommendations -->
            <div class="card">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Recommendations</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Learn Python</p>
                        <p class="text-xs text-gray-500">Boost your tech skills</p>
                        <a href="../services/learning.php" class="text-blue-600 hover:underline text-sm">Start Now</a>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700">Update Resume</p>
                        <p class="text-xs text-gray-500">Increase job matches</p>
                        <a href="../pages/profile/profile_edit.php" class="text-blue-600 hover:underline text-sm">Edit Now</a>
                    </div>
                </div>
            </div>

            <!-- Upcoming Events & Opportunities -->
            <div class="card">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">What’s Next</h2>
                <div class="space-y-4">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-calendar text-blue-600 text-base"></i>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Tech Career Fair</p>
                            <p class="text-xs text-gray-500">Mar 15, 9:00 AM</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-briefcase text-blue-600 text-base"></i>
                        <div>
                            <p class="text-sm font-medium text-gray-700">Software Developer</p>
                            <p class="text-xs text-gray-500">TechCorp Tanzania</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Settings Modal -->
    <div id="settingsCard" class="modal bg-white rounded-xl shadow-lg w-full max-w-md p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Settings</h2>
        <div class="space-y-3">
            <a href="../profile/profile.php" class="block p-2 bg-green-50 text-green-700 rounded-md hover:bg-green-100 text-sm font-medium"><i class="fas fa-eye mr-2"></i>View Profile</a>
            <a href="../logout.php" class="block p-2 bg-red-50 text-red-700 rounded-md hover:bg-red-100 text-sm font-medium"><i class="fas fa-sign-out-alt mr-2"></i>Logout</a>
        </div>
    </div>

    <!-- Overlay -->
    <div id="overlay" class="overlay"></div>
    <!-- <?php include '../../includes/footer.php'; ?> -->

    <script>
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const toggleSidebar = document.getElementById('toggleSidebar');
        const settingsButtonSidebar = document.getElementById('settingsButtonSidebar');
        const settingsCard = document.getElementById('settingsCard');
        const overlay = document.getElementById('overlay');

        // Sidebar toggle
        toggleSidebar.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });

        // Settings modal toggle
        const toggleSettings = () => {
            settingsCard.classList.toggle('show');
            overlay.classList.toggle('show');
        };

        settingsButtonSidebar.addEventListener('click', toggleSettings);
        overlay.addEventListener('click', toggleSettings);
    </script>
</body>
</html>