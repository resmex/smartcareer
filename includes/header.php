<?php
// Start the session if it hasn't already been started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../../includes/connect.php'; // Adjust the path to the database connection file

// Initialize default values
$firstName = $_SESSION['first_name'] ?? '';
$lastName = $_SESSION['last_name'] ?? '';
$job_title = "N/A";
$profile_picture = '../../uploads/profile_default.jpeg'; // Default profile picture
$user_id = $_SESSION['user_id'] ?? null;

// Fetch user data if not already in session
if (!$firstName || !$lastName) {
    if ($user_id) {
        $stmt = $con->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $firstName = $_SESSION['first_name'] = $user['first_name']; // Store first_name in session
            $lastName = $_SESSION['last_name'] = $user['last_name']; // Store last_name in session
        } else {
            $firstName = $_SESSION['first_name'] = ''; // Default to empty if not found
            $lastName = $_SESSION['last_name'] = ''; // Default to empty if not found
        }
        $stmt->close();
    }
}

// Fetch profile data from the database
if ($user_id) {
    $stmt = $con->prepare("SELECT first_name, last_name, job_title, profile_picture FROM user_profiles WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user_profile = $result->fetch_assoc();
        $firstName = htmlspecialchars($user_profile['first_name']);
        $lastName = htmlspecialchars($user_profile['last_name']);
        $job_title = htmlspecialchars($user_profile['job_title']);
        
        // Use default profile picture if profile_picture is empty or NULL
        $profile_picture = (!empty($user_profile['profile_picture']) && $user_profile['profile_picture'] !== 'NULL')
            ? '../../uploads/' . htmlspecialchars($user_profile['profile_picture'])
            : '../../uploads/profile_default.jpeg'; // Default profile picture path
    }
    $stmt->close();
}

// Display the user's name as "first_name last_name"
$user_display_name = $firstName . ' ' . $lastName;

// Store profile picture in session for consistency
$_SESSION['profile_picture'] = $profile_picture;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Header</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="bg-white shadow- static w-full z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <span class="text-2xl font-bold">
                        <span class="text-blue-600">Smart</span><span class="text-black">Career</span>
                    </span>
                </div>

                <!-- Profile Section -->
                <div class="flex items-center space-x-4">
                     <a href="../services/dashboard.php" class="text-gray-700 hover:text-blue-600 transition duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </a>
                    <div class="relative">
                        <!-- Profile Trigger Button -->
                        <button id="profileTrigger" class="flex items-center space-x-3 focus:outline-none" onclick="toggleDropdown(event)">
                            <!-- Profile Picture -->
                            <img src="<?php echo $profile_picture; ?>" alt="Profile" class="w-10 h-10 rounded-full border-2 border-gray-200">
                            <!-- Profile Details (Hidden on small screens) -->
                            <div class="hidden md:block text-left">
                                <p class="text-sm font-medium text-gray-800"><?php echo $firstName . ' ' . $lastName; ?></p>
                                <p class="text-xs text-gray-500"><?php echo $job_title; ?></p>
                            </div>
                            <!-- Dropdown Icon -->
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="profileDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg hidden z-50 border border-gray-200">
                            <div class="p-3">
                                <!-- Profile Preview -->
                                <div class="flex flex-col items-center pb-3 border-b">
                                    <img src="<?php echo $profile_picture; ?>" alt="Profile Large" class="w-12 h-12 rounded-full">
                                    <div class="mt-2 text-center">
                                        <h3 class="text-sm font-semibold text-gray-800"><?php echo $firstName . ' ' . $lastName; ?></h3>
                                        <p class="text-xs text-gray-600"><?php echo $job_title; ?></p>
                                    </div>
                                </div>

                                <!-- Dropdown Links -->
                                <div class="space-y-2 mt-2">
                                    <a href="../../pages/profile/profile.php" class="block px-3 py-2 text-sm bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition duration-300">
                                        View Profile
                                    </a>
                                    <a href="../../pages/logout.php" class="block px-3 py-2 text-sm bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition duration-300">
                                        Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- JS for dropdown -->
    <script>
        function toggleDropdown(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('hidden');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('profileDropdown');
            const trigger = document.getElementById('profileTrigger');
            if (!dropdown.contains(event.target) && !trigger.contains(event.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Prevent dropdown from closing when clicking inside it
        document.getElementById('profileDropdown').addEventListener('click', function(event) {
            event.stopPropagation();
        });
    </script>
</body>
</html>
