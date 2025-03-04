<?php
session_start();
include '../includes/connect.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    
    // Check if email exists
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Generate reset token
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Store reset token in database
        $sql = "UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE email = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param('sss', $token, $expiry, $email);
        
        if ($stmt->execute()) {
            // In a real application, you would send this link via email
            // For demonstration, we'll just show the reset link
            $reset_link = "reset-password.php?token=" . $token;
            $message = "Password reset link has been sent to your email address.";
        } else {
            $message = "An error occurred. Please try again.";
        }
    } else {
        $message = "If this email exists in our system, you will receive a password reset link.";
    }
    
    $stmt->close();
    $con->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | SmartCareer</title>
    <link rel="stylesheet" href="../assets/css/tailwind.min.css">
    <style>
        body {
            background-color: #f0f4f8;
        }
    </style>
</head>
<body class="font-sans">
    <div class="container mx-auto mt-10 px-4">
        <div class="flex justify-center">
            <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-md">
                <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">Reset Password</h2>
                
                <?php if ($message): ?>
                    <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <p class="text-gray-600 mb-6 text-center">
                    Enter your email address and we'll send you a link to reset your password.
                </p>

                <form action="password_forgot.php" method="post">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" id="email" name="email" required
                            placeholder="Enter your email address"
                            class="mt-2 block w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <button type="submit"
                        class="mt-6 w-full px-4 py-2 bg-blue-500 text-white font-bold rounded-lg shadow-md hover:bg-blue-600">
                        Send Reset Link
                    </button>
                </form>

                <div class="text-center mt-6">
                    <a href="login.php" class="text-blue-500 hover:underline">
                        Back to Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
