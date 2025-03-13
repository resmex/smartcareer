<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dataDir = __DIR__ . '/data';
    $eventsFile = $dataDir . '/events.json';

    // Sanitize inputs
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
    $time = filter_input(INPUT_POST, 'time', FILTER_SANITIZE_STRING);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
    $image = filter_input(INPUT_POST, 'image', FILTER_VALIDATE_URL) ?: '';
    $link = filter_input(INPUT_POST, 'link', FILTER_VALIDATE_URL) ?: '#';
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

    // Validate required fields
    if (!$title || !$type || !$date || !$time || !$location || !$description) {
        header("Location: post_event.php?error=All required fields must be filled.");
        exit();
    }

    // Read existing events
    $existingEvents = [];
    if (file_exists($eventsFile)) {
        $existingEvents = json_decode(file_get_contents($eventsFile), true);
        if (!is_array($existingEvents)) {
            $existingEvents = [];
        }
    }

    // Create new event
    $newEvent = [
        'id' => uniqid(),
        'title' => $title,
        'type' => $type,
        'date' => $date,
        'time' => $time,
        'location' => $location,
        'image' => $image,
        'link' => $link,
        'description' => $description,
        'source' => 'User Posted',
        'posted_by' => $_SESSION['user_id'],
        'created_at' => date("Y-m-d H:i:s")
    ];

    $existingEvents[] = $newEvent;

    // Ensure data directory exists
    if (!is_dir($dataDir)) {
        if (!mkdir($dataDir, 0777, true)) {
            header("Location: post_event.php?error=Failed to create data directory.");
            exit();
        }
    }

    // Save events to file
    if (file_put_contents($eventsFile, json_encode($existingEvents, JSON_PRETTY_PRINT)) === false) {
        header("Location: post_event.php?error=Failed to save event.");
        exit();
    }

    header("Location: events.php?success=Event posted successfully!");
    exit();
}
?>