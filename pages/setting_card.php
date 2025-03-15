<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: ../pages/login.php");
    exit();
}

$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | SmartCareer</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .settings-card {
            transition: all 0.3s ease;
        }
        .settings-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex justify-center">
            <!-- Settings Card -->
            <div class="settings-card bg-white rounded-lg shadow-lg w-full max-w-md p-6">
                <h2 class="text-2xl font-bold text-align-center text-gray-900 mb-6">Settings</h2>
                <div class="space-y-4">
                    <!-- Edit Profile -->
                    <!-- <a href="profile.php" class="block px-4 py-3 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition duration-300">
                        <i class="fas fa-user-edit mr-2"></i>Edit Profile
                    </a> -->
                    <!-- View Profile -->
                    <a href="profile.php" class="block px-4 py-3 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition duration-300">
                        <i class="fas fa-eye mr-2"></i>View Profile
                    </a>
                    <!-- Logout -->
                    <a href="logout.php" class="block px-4 py-3 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition duration-300">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>