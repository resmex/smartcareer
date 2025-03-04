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
    if ($login_option === 'phone') {
        if (!preg_match('/^[0-9]{10}$/', $email_or_phone)) {
            $error_message = "Invalid phone number. Please enter a 10-digit number.";
        }
    }

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

            // Redirect to the target URL after successful login
            header("Location: $redirect_url");
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SmartCareer</title>
    <link rel="stylesheet" href="../assets/css/tailwind.min.css">
    <style>
        body { background-color: #f0f4f8; }
    </style>
</head>
<body class="font-sans">
<div class="container mx-auto mt-10 px-4">
    <div class="flex justify-center">
        <div class="bg-white shadow-lg rounded-lg p-8 w-full max-w-md">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-4">
                Login
            </h2>

            <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline"><?php echo $error_message; ?></span>
                </div>
            <?php endif; ?>

            <form action="login.php?redirect=<?php echo urlencode($redirect_url); ?>" method="post">
                <div class="mt-4">
                    <label for="login_option" class="block text-sm font-medium text-gray-700">Login with:</label>
                    <select id="login_option" name="login_option" class="mt-2 block w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500" onchange="toggleIdentifierField()">
                        <option value="email">Email</option>
                        <option value="phone">Phone</option>
                    </select>
                </div>

                <div class="mt-4" id="identifier-field">
                    <label for="email_or_phone" class="block text-sm font-medium text-gray-700">
                        Email or Phone
                    </label>
                    <input type="text" id="email_or_phone" name="email_or_phone" placeholder="Enter your email or phone"
                           class="mt-2 block w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                </div>

                <div class="mt-4">
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password"
                           class="mt-2 block w-full px-4 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                </div>

                <button type="submit"
                        class="mt-6 w-full px-4 py-2 bg-blue-500 text-white font-bold rounded-lg shadow-md hover:bg-blue-600">
                    Login
                </button>
            </form>

            <div class="text-center mt-4">
                <a href="password_reset.php" class="text-blue-500 hover:underline">Forgot Password?</a>
            </div>

            <div class="text-center mt-6">
                <p class="text-sm text-gray-700">
                    Don't have an account?
                    <a href="register.php" class="text-blue-500 hover:underline">
                        Register
                    </a>
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
        label.classList.add('block', 'text-sm', 'font-medium', 'text-gray-700');
        label.textContent = loginOption === 'email' ? 'Email' : 'Phone';

        const input = document.createElement('input');
        input.type = loginOption === 'email' ? 'email' : 'tel'; // Use 'tel' for phone
        input.id = 'email_or_phone';
        input.name = 'email_or_phone';  // Important: Name must match PHP
        input.placeholder = loginOption === 'email' ? 'Enter your email' : 'Enter your phone (e.g., 0711223344)';
        input.classList.add('mt-2', 'block', 'w-full', 'px-4', 'py-2', 'border', 'rounded-lg', 'focus:ring-blue-500', 'focus:border-blue-500');
        input.required = true;

        // Add input validation for phone numbers
        if (loginOption === 'phone') {
            input.pattern = "[0-9]{10}"; // Ensure exactly 10 digits
            input.title = "Please enter a valid 10-digit phone number (e.g., 0711223344)";
            input.oninput = function() {
                this.value = this.value.replace(/[^0-9]/g, ''); // Remove non-numeric characters
            };
        }

        identifierField.appendChild(label);
        identifierField.appendChild(input);
    }

    // Initialize the identifier field on page load
    toggleIdentifierField();
</script>
</body>
</html>