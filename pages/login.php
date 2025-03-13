<?php
session_start();
include '../includes/connect.php';

// Capture the redirect URL from the query parameter
$redirect_url = $_GET['redirect'] ?? '../pages/services/dashboard.php'; // Default to dashboard if not provided

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = $_POST['password'] ?? '';
    $email_or_phone = $_POST['email_or_phone'] ?? '';
    $login_option = $_POST['login_option'] ?? 'email'; // email or phone

    $sql = "SELECT * FROM users WHERE ";
    $bind_param_type = "s";

    if ($login_option === 'email') {
        $sql .= "email = ?";
    } else {
        $sql .= "phone = ?";
    }

    // Validate phone number if the login option is phone
    if ($login_option === 'phone' && !preg_match('/^[0-9]{10}$/', $email_or_phone)) {
        $error_message = "Invalid phone number. Please enter a 10-digit number.";
    } else {
        $stmt = $con->prepare($sql);
        $stmt->bind_param($bind_param_type, $email_or_phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['username'] = $user['first_name'];
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['phone'] = $user['phone'];
                $_SESSION['role'] = $user['role']; // Add role to session

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: ../pages/admin/dashboard.php"); // Admin dashboard
                } else {
                    header("Location: $redirect_url"); // Regular user dashboard
                }
                exit();
            } else {
                $error_message = "Invalid password";
            }
        } else {
            $error_message = "User not found";
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
    <title>Login | SmartCareer</title>
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

        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            max-width: 28rem;
            width: 100%;
            transition: transform 0.3s ease;
        }

        .login-card:hover {
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

        .input-field, .select-field {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 0.75rem 1rem;
            width: 100%;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .input-field:focus, .select-field:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
            outline: none;
        }

        .password-container {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6b7280; /* Gray-500 */
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--primary-color);
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

        .error-message {
            color: #dc2626;
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
            <div class="login-card fade-in">
                <div class="logo">
                    <span class="smart">Smart</span><span class="career">Career</span>
                </div>
                <?php if (isset($error_message)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
                <form action="" method="post">
                    <div class="mb-4">
                        <label for="login_option" class="block text-sm font-medium text-gray-700 mb-1">Login with</label>
                        <select id="login_option" name="login_option" class="select-field" onchange="toggleIdentifierField()">
                            <option value="email">Email</option>
                            <option value="phone">Phone</option>
                        </select>
                    </div>

                    <div class="mb-4" id="identifier-field">
                        <label for="email_or_phone" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="email_or_phone" name="email_or_phone" class="input-field" placeholder="Enter your email" required>
                    </div>

                    <div class="mb-6 password-container">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" id="password" name="password" class="input-field" placeholder="••••••••" required>
                        <!-- <i class="fas fa-eye password-toggle" id="togglePassword"></i> -->
                    </div>

                    <button type="submit" class="btn w-full">Login</button>
                </form>

                <div class="text-center mt-4">
                    <a href="password_reset.php" class="text-[var(--primary-color)] hover:underline text-sm">Forgot Password?</a>
                </div>

                <div class="text-center mt-6">
                    <p class="text-sm text-gray-600">
                        Don’t have an account? 
                        <a href="register.php" class="text-[var(--primary-color)] hover:underline font-semibold">Register</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleIdentifierField() {
            const loginOption = document.getElementById('login_option').value;
            const identifierField = document.getElementById('identifier-field');
            identifierField.innerHTML = ''; // Clear previous content

            const label = document.createElement('label');
            label.setAttribute('for', 'email_or_phone');
            label.classList.add('block', 'text-sm', 'font-medium', 'text-gray-700', 'mb-1');
            label.textContent = loginOption === 'email' ? 'Email' : 'Phone';

            const input = document.createElement('input');
            input.type = loginOption === 'email' ? 'email' : 'tel';
            input.id = 'email_or_phone';
            input.name = 'email_or_phone';
            input.placeholder = loginOption === 'email' ? 'Enter your email' : 'Enter your phone (e.g., 0711223344)';
            input.classList.add('input-field');
            input.required = true;

            if (loginOption === 'phone') {
                input.pattern = "[0-9]{10}";
                input.title = "Please enter a valid 10-digit phone number (e.g., 0711223344)";
                input.oninput = function() {
                    this.value = this.value.replace(/[^0-9]/g, '');
                };
            }

            identifierField.appendChild(label);
            identifierField.appendChild(input);
        }

        // Initialize the identifier field on page load
        toggleIdentifierField();

        // Password toggle functionality
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>