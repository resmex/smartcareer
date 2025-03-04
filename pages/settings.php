<?php
include '../includes/connect.php';

// Assume user is logged in (Replace with session-based user ID)
$user_id = 1; // Replace with $_SESSION['user_id'] in real applications

// Fetch user settings
$stmt = $con->prepare("SELECT email, phone, user_type, notifications_enabled, password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Update Settings
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $user_type = trim($_POST['user_type']);
    $notifications_enabled = isset($_POST['notifications_enabled']) ? 1 : 0;

    // Update user info
    $stmt = $con->prepare("UPDATE users SET email = ?, phone = ?, user_type = ?, notifications_enabled = ? WHERE id = ?");
    $stmt->bind_param("sssii", $email, $phone, $user_type, $notifications_enabled, $user_id);
    $stmt->execute();
    $stmt->close();

    // Change Password (Without Hashing)
    if (!empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password === $confirm_password) {
            $stmt = $con->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $new_password, $user_id);
            $stmt->execute();
            $stmt->close();
            $password_message = "Password updated successfully!";
        } else {
            $password_message = "Passwords do not match!";
        }
    }

    header("Location: settings.php"); // Refresh the page after update
    exit();
}

// Deactivate Account
if (isset($_POST['deactivate_account'])) {
    $stmt = $con->prepare("UPDATE users SET account_status = 'Deactivated' WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: logout.php"); // Log out the user after deactivation
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

<div class="container mx-auto px-6 py-10">
    <div class="bg-white shadow-lg rounded-lg p-6 max-w-3xl mx-auto">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Account Settings</h2>

        <form action="settings.php" method="POST">
            <!-- Email -->
            <label class="block text-gray-600 mt-4">Email:</label>
            <input type="email" name="email" class="w-full p-3 border rounded-lg" value="<?php echo htmlspecialchars($user['email']); ?>" required>

            <!-- Phone -->
            <label class="block text-gray-600 mt-4">Phone:</label>
            <input type="text" name="phone" class="w-full p-3 border rounded-lg" value="<?php echo htmlspecialchars($user['phone']); ?>">

            <!-- User Type -->
            <label class="block text-gray-600 mt-4">Account Type:</label>
            <select name="user_type" class="w-full p-3 border rounded-lg">
                <option value="Employer" <?php echo ($user['user_type'] == 'Employer') ? 'selected' : ''; ?>>Employer</option>
                <option value="Job Seeker" <?php echo ($user['user_type'] == 'Job Seeker') ? 'selected' : ''; ?>>Job Seeker</option>
            </select>

            <!-- Notifications -->
            <label class="block text-gray-600 mt-4 flex items-center">
                <input type="checkbox" name="notifications_enabled" <?php echo ($user['notifications_enabled'] ? 'checked' : ''); ?> class="mr-2">
                Enable Notifications
            </label>

            <!-- Password Change (Without Hashing) -->
            <label class="block text-gray-600 mt-6">New Password:</label>
            <input type="password" name="new_password" class="w-full p-3 border rounded-lg">

            <label class="block text-gray-600 mt-4">Confirm New Password:</label>
            <input type="password" name="confirm_password" class="w-full p-3 border rounded-lg">
            <p class="text-sm text-green-500"><?php echo $password_message ?? ''; ?></p>

            <!-- Save Changes Button -->
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg mt-6 w-full">Save Changes</button>
        </form>

        <!-- Deactivate Account -->
        <form action="settings.php" method="POST" class="mt-6">
            <button type="submit" name="deactivate_account" class="bg-red-500 text-white px-4 py-2 rounded-lg w-full">Deactivate Account</button>
        </form>
    </div>
</div>

</body>
</html>
