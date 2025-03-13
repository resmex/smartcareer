<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include '../includes/connect.php';

    // Sanitize and validate input data
    $first_name = htmlspecialchars($_POST['first_name'] ?? '');
    $last_name = htmlspecialchars($_POST['last_name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $phone = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = "Invalid phone number. Please enter a 10-digit number.";
    } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
        $error = "Password must include at least one uppercase letter, one lowercase letter, and one number.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if user already exists
        $stmt = $con->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "User already exists.";
        } else {
            // Insert new user
            $stmt = $con->prepare("INSERT INTO users (first_name, last_name, email, phone, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $first_name, $last_name, $email, $phone, $hashed_password);

            if ($stmt->execute()) {
                $_SESSION['users'] = $first_name;
                $_SESSION['user_id'] = $stmt->insert_id;
                header("Location: ../pages/services/dashboard.php");
                exit();
            } else {
                $error = "Error: " . $stmt->error;
            }
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
    <title>Register | SmartCareer</title>
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

        .register-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            max-width: 28rem;
            width: 100%;
            transition: transform 0.3s ease;
        }

        .register-card:hover {
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
            <div class="register-card fade-in">
                
                <div class="logo">
                    Join <span class="smart">Smart</span><span class="career">Career</span>
                </div>
                <?php if (isset($error)): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form action="" method="post" onsubmit="return validateForm()">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="input-field" placeholder="Victory" required>
                        </div>
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="input-field" placeholder="Mousa" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                        <input type="email" id="email" name="email" class="input-field" placeholder="victory.mousa@example.com" required>
                    </div>

                    <div class="mb-4">
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="input-field" placeholder="0711223344" pattern="[0-9]{10}" required>
                    </div>

                    <div class="mb-6 password-container">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" id="password" name="password" class="input-field" placeholder="••••••••" required>
                        <!-- <i class="fas fa-eye password-toggle" id="togglePassword"></i> -->
                        <p class="text-xs text-gray-500 mt-1">Must include uppercase, lowercase, and a number (8+ chars).</p>
                    </div>

                    <button type="submit" class="btn w-full">Register Now</button>
                </form>

                <div class="text-center mt-6">
                    <p class="text-sm text-gray-600">
                        Already have an account? 
                        <a href="../pages/login.php" class="text-[var(--primary-color)] hover:underline font-semibold">Login</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const phoneInput = document.getElementById('phone');
        phoneInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');

        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        function validateForm() {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const phone = document.getElementById('phone').value;

            if (!email.includes('@') || !email.includes('.')) {
                alert("Please enter a valid email address (e.g., example@domain.com).");
                return false;
            }

            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
            if (!passwordRegex.test(password)) {
                alert("Password must include at least one uppercase letter, one lowercase letter, and one number (8+ characters).");
                return false;
            }

            if (!/^[0-9]{10}$/.test(phone)) {
                alert("Please enter a valid 10-digit phone number (e.g., 0711223344).");
                return false;
            }

            return true;
        }
    </script>
</body>
</html>