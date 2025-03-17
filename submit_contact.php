<?php
session_start();
include 'includes/connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $message = htmlspecialchars(trim($_POST['message']));
    $attachment = null;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: pages/home.php?error=Invalid email.");
        exit();
    }
    

    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['attachment'];
        $allowed_types = ['txt', 'pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $file_type = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_type, $allowed_types)) {
            header("Location: pages/home.php?error=Invalid file type.");
            exit();
        } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
            header("Location: pages/home.php?error=File too large.");
            exit();
        } else {
            $upload_dir = 'uploads/contact/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $attachment = uniqid() . '.' . $file_type;
            if (!move_uploaded_file($file['tmp_name'], $upload_dir . $attachment)) {
                header("Location: pages/home.php?error=Upload failed.");
                exit();
            }
        }
        
    }

    $stmt = $con->prepare("INSERT INTO contact_messages (name, email, message, attachment, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $name, $email, $message, $attachment);
    if ($stmt->execute()) {
        header("Location: pages/home.php?success=Message sent successfully!");
    } else {
        header("Location: pages/home.php?error=Failed to send message.");
    }
    $stmt->close();
    $con->close();
} else {
    header("Location: pages/home.php");
}
?>