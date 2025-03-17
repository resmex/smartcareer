<?php
session_start();
include '../../includes/connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = filter_input(INPUT_POST, 'event_id', FILTER_SANITIZE_STRING);
    $fullName = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $userId = (int)$_SESSION['user_id'];
    $registeredAt = date("Y-m-d H:i:s");

    if (!$eventId || !$fullName || !$email || !$phone) {
        echo json_encode(['error' => 'All fields are required']);
        exit();
    }

    $stmt = $con->prepare("INSERT INTO event_registrations (event_id, user_id, full_name, email, phone, registered_at) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sissss", $eventId, $userId, $fullName, $email, $phone, $registeredAt);

    if ($stmt->execute()) {
        echo json_encode(['success' => 'Registered successfully']);
    } else {
        echo json_encode(['error' => 'Failed to save registration: ' . $stmt->error]);
    }
    $stmt->close();
    exit();
}
?>