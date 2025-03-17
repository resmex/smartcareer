<?php
session_start();
include '../../includes/connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
    $time = filter_input(INPUT_POST, 'time', FILTER_SANITIZE_STRING);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $link = filter_input(INPUT_POST, 'link', FILTER_VALIDATE_URL) ?: '#';
    $userId = (int)$_SESSION['user_id'];
    $eventId = uniqid();
    $datePosted = date("Y-m-d");

    if (!$title || !$type || !$date || !$time || !$location || !$description) {
        $_SESSION['error'] = 'All required fields must be filled.';
        header("Location: post_event.php");
        exit();
    }

    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileType = $_FILES['image']['type'];
        $fileSize = $_FILES['image']['size'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($fileType, $allowedTypes) && $fileSize <= 5 * 1024 * 1024) {
            $newFileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $destPath = $uploadDir . '/' . $newFileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $destPath)) {
                $imagePath = '/smartcareer/pages/services/uploads/' . $newFileName;
            } else {
                $_SESSION['error'] = 'Failed to upload image.';
                header("Location: post_event.php");
                exit();
            }
        } else {
            $_SESSION['error'] = 'Invalid image format or size (max 5MB, JPG/PNG/GIF/WEBP).';
            header("Location: post_event.php");
            exit();
        }
    }

    $stmt = $con->prepare("INSERT INTO events (id, title, type, date, time, location, description, link, image, posted_by, date_posted) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssis", $eventId, $title, $type, $date, $time, $location, $description, $link, $imagePath, $userId, $datePosted);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Event posted successfully!';
        header("Location: events.php");
    } else {
        $_SESSION['error'] = 'Failed to save event: ' . $stmt->error;
        header("Location: post_event.php");
    }
    $stmt->close();
    exit();
}
?>