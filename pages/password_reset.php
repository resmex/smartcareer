<?php
session_start();
include '../includes/connect.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address.";
    } else {
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
                // In a real application, send this link via email (e.g., using PHPMailer)
                $reset_link = "reset-password.php?token=" . $token;
                $message = "A password reset link has been sent to your email address.";
            } else {
                $message = "An error occurred. Please try again.";
            }
        } else {
            // Avoid revealing if email exists for security
            $message = "If this email exists in our system, a password reset link has been sent.";
        }
        
        $stmt->close();
        $con->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | SmartCareer</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb; /* Bright blue */
            --secondary-color: #1e3a8a; /* Deep blue */
            --accent-color: #fbbf24; /* Golden yellow */
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f9fafb, #e5e7eb);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .forgot-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            max-width: 28rem;
            width: 100%;
            transition: transform 0.3s ease;
        }

        .forgot-card:hover {
            transform: translateY(-5px);
        }

        .logo {
            font-size: 2.25rem; /* text-4xl */
            font-weight: 700;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .logo .smart {
            color: var(--primary-color);
        }

        .logo .career {
            color: #1f2937; /* Dark gray/black */
        }

        .input-field {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            width: 100%;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .input-field:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
            outline: none;
        }

        .btn {
            background: var(--primary-color);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }

        .message {
            color: #1e40af; /* Darker blue for success/info */
            font-size: 0.875rem;
            text-align: center;
            margin-bottom: 1rem;
        }

        .error-message {
            color: #dc2626; /* Red for errors */
            font-size: 0.875rem;
            text-align: center;
            margin-bottom: 1rem;
        }

        .fade-in {
            opacity: 0;
            transform: translateY(10px);
            animation: fadeIn 0.5s ease-out forwards;
        }

        @keyframes fadeIn {
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-center min-h-screen">
            <div class="forgot-card fade-in">
                <div class="logo">
                    <span class="smart">Smart</span><span class="career">Career</span>
                </div>
                <?php if ($message): ?>
                    <div class="<?php echo strpos($message, 'error') !== false ? 'error-message' : 'message'; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                <p class="text-gray-600 mb-6 text-center text-sm">
                    Enter your email address to receive a password reset link.
                </p>
                <form action="" method="post">
                    <div class="mb-6">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" id="email" name="email" class="input-field" placeholder="john.doe@example.com" required>
                    </div>
                    <button type="submit" class="btn w-full">Send Reset Link</button>
                </form>
                <div class="text-center mt-6">
                    <a href="login.php" class="text-[var(--primary-color)] hover:underline text-sm">Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>