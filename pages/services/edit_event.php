<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

$eventId = filter_input(INPUT_GET, 'event_id', FILTER_SANITIZE_STRING);
if (!$eventId) {
    header("Location: manage_event.php?error=Invalid event ID");
    exit();
}

$dataDir = __DIR__ . '/data';
$eventsFile = $dataDir . '/events.json';
$events = file_exists($eventsFile) ? json_decode(file_get_contents($eventsFile), true) : [];
$event = array_filter($events, fn($e) => $e['id'] === $eventId && $e['posted_by'] === $_SESSION['user_id']);

if (empty($event)) {
    header("Location: manage_event.php?error=Event not found or unauthorized");
    exit();
}

$event = array_values($event)[0];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
    $time = filter_input(INPUT_POST, 'time', FILTER_SANITIZE_STRING);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
    $image = filter_input(INPUT_POST, 'image', FILTER_VALIDATE_URL) ?: '';
    $link = filter_input(INPUT_POST, 'link', FILTER_VALIDATE_URL) ?: '#';
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);

    if (!$title || !$type || !$date || !$time || !$location || !$description) {
        header("Location: edit_event.php?event_id=$eventId&error=All required fields must be filled.");
        exit();
    }

    $eventIndex = array_search($eventId, array_column($events, 'id'));
    $events[$eventIndex] = [
        'id' => $eventId,
        'title' => $title,
        'type' => $type,
        'date' => $date,
        'time' => $time,
        'location' => $location,
        'image' => $image,
        'link' => $link,
        'description' => $description,
        'source' => $event['source'],
        'posted_by' => $_SESSION['user_id'],
        'created_at' => $event['created_at'],
        'updated_at' => date("Y-m-d H:i:s")
    ];

    file_put_contents($eventsFile, json_encode($events, JSON_PRETTY_PRINT));
    header("Location: events.php?success=Event updated successfully!");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event | SmartCareer</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <?php include '../../includes/header.php'; ?>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Edit Event</h1>

        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p><?php echo htmlspecialchars($_GET['error']); ?></p>
            </div>
        <?php endif; ?>

        <form id="editEventForm" action="" method="POST" class="bg-white rounded-xl shadow-md p-6">
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Event Title *</label>
                    <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($event['title']); ?>" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Event Type *</label>
                    <select id="type" name="type" required class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="Career Fair" <?php echo $event['type'] === 'Career Fair' ? 'selected' : ''; ?>>Career Fair</option>
                        <option value="Workshop" <?php echo $event['type'] === 'Workshop' ? 'selected' : ''; ?>>Workshop</option>
                        <option value="Hackathon" <?php echo $event['type'] === 'Hackathon' ? 'selected' : ''; ?>>Hackathon</option>
                        <option value="Webinar" <?php echo $event['type'] === 'Webinar' ? 'selected' : ''; ?>>Webinar</option>
                        <option value="Networking" <?php echo $event['type'] === 'Networking' ? 'selected' : ''; ?>>Networking</option>
                    </select>
                </div>
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700">Date *</label>
                    <input type="date" id="date" name="date" required value="<?php echo htmlspecialchars($event['date']); ?>" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="time" class="block text-sm font-medium text-gray-700">Time *</label>
                    <input type="text" id="time" name="time" required value="<?php echo htmlspecialchars($event['time']); ?>" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700">Location *</label>
                    <input type="text" id="location" name="location" required value="<?php echo htmlspecialchars($event['location']); ?>" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700">Image URL (Optional)</label>
                    <input type="url" id="image" name="image" value="<?php echo htmlspecialchars($event['image']); ?>" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="link" class="block text-sm font-medium text-gray-700">Registration Link (Optional)</label>
                    <input type="url" id="link" name="link" value="<?php echo htmlspecialchars($event['link']); ?>" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description *</label>
                    <textarea id="description" name="description" required rows="4" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($event['description']); ?></textarea>
                </div>
            </div>
            <div class="mt-6 flex space-x-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Changes</button>
                <a href="manage_event.php" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>