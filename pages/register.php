<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include '../includes/connect.php';

    // Sanitize and validate input data
    $first_name = htmlspecialchars($_POST['first_name'] ?? '');
    $last_name = htmlspecialchars($_POST['last_name'] ?? '');
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $phone = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? ''); // Remove non-numeric characters
    $password = $_POST['password'] ?? '';

   
    if (empty($first_name) || empty($last_name) || empty($email) || empty($phone) || empty($password)) {
        die("All fields are required.");
    }

   
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    if (!preg_match('/^[0-9]{10}$/', $phone)) {
        die("Invalid phone number. Please enter a 10-digit number.");
    }

    
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) {
        die("Password must include at least one uppercase letter, one lowercase letter, and one number.");
    }

    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if user already exists (by email)
    $stmt = $con->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result) {
        if ($result->num_rows > 0) {
            die("User already exists."); 
        } else {
            // Insert the new user
            $stmt = $con->prepare("INSERT INTO users (first_name, last_name, email, phone, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $first_name, $last_name, $email, $phone, $hashed_password);

            if ($stmt->execute()) {
                $_SESSION['users'] = $first_name;
                $_SESSION['user_id'] = $stmt->insert_id;
                header("Location: ../pages/services/dashboard.php");
                exit();
            } else {
                die("Error: " . $stmt->error);
            }
        }
    } else {
        die("Error: " . $con->error); 
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
    <title>Register | SmartCareer</title>
    <link rel="stylesheet" href="../assets/css/tailwind.min.css">
</head>

<body class="bg-gray-100 font-sans">
    <div class="container mx-auto mt-8 px-4">
        <div class="flex items-center justify-center">
            <div class="bg-white shadow-lg rounded-lg p-6 w-full max-w-md">
                <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">Register</h2>
                <form action="../../pages/register.php" method="post" onsubmit="return validateForm()">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                            <input type="text" id="first_name" name="first_name"
                                class="mt-1 block w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Enter your first name" required>
                        </div>
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                            <input type="text" id="last_name" name="last_name"
                                class="mt-1 block w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Enter your last name" required>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" id="email" name="email"
                            class="mt-1 block w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter your email address" required>
                    </div>

                    <div class="mt-4">
                        <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="tel" id="phone" name="phone"
                            class="mt-1 block w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter your phone number (e.g., 0711223344)"
                            pattern="[0-9]{10}" title="Please enter a valid 10-digit phone number (e.g., 0711223344)"
                            required>
                    </div>

                    <div class="mt-4">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" id="password" name="password"
                            class="mt-1 block w-full px-4 py-2 border rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Enter your password" required>
                        <p class="text-xs text-gray-500 mt-1">
                            Password must include at least one uppercase letter, one lowercase letter, and one number.
                        </p>
                    </div>

                    <button type="submit"
                        class="mt-6 w-full px-4 py-2 bg-blue-500 text-white font-bold rounded-lg shadow-md hover:bg-blue-600">
                        Register
                    </button>
                </form>

                <div class="text-center mt-6">
                    <p class="text-sm text-gray-700">
                        Already have an account?
                        <a href="login.html" class="text-blue-500 hover:underline">
                            Login
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // JavaScript to enforce numeric input for the phone field
        const phoneInput = document.getElementById('phone');
        phoneInput.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, ''); // Remove non-numeric characters
        });

        // Validate email and password before form submission
        function validateForm() {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            // Validate email format (must contain @)
            if (!email.includes('@')) {
                alert("Please enter a valid email address (e.g., example@domain.com).");
                return false; // Prevent form submission
            }

            // Validate password format (must include uppercase, lowercase, and a number)
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/;
            if (!passwordRegex.test(password)) {
                alert("Password must include at least one uppercase letter, one lowercase letter, and one number.");
                return false; // Prevent form submission
            }

            // Validate phone number (10 digits)
            const phone = phoneInput.value;
            if (!/^[0-9]{10}$/.test(phone)) {
                alert("Please enter a valid 10-digit phone number (e.g., 0711223344).");
                return false; // Prevent form submission
            }

            return true; // Allow form submission
        }
    </script>
</body>

</html>