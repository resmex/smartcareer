<?php
session_start();
include '../../includes/connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

$eventId = filter_input(INPUT_GET, 'event_id', FILTER_SANITIZE_STRING);
if (!$eventId) {
    header("Location: manage_event.php?error=Invalid event ID");
    exit();
}

$userId = (int)$_SESSION['user_id'];

// Fetch event
$stmt = $con->prepare("SELECT * FROM events WHERE id = ? AND posted_by = ?");
$stmt->bind_param("si", $eventId, $userId);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$event) {
    header("Location: manage_event.php?error=Event not found or unauthorized");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANIT施filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
    $date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
    $time = filter_input(INPUT_POST, 'time', FILTER_SANITIZE_STRING);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $link = filter_input(INPUT_POST, 'link', FILTER_VALIDATE_URL) ?: '#';
    $updatedAt = date("Y-m-d H:i:s");

    if (!$title || !$type || !$date || !$time || !$location || !$description) {
        header("Location: edit_event.php?event_id=$eventId&error=All required fields must be filled.");
        exit();
    }

    $imagePath = $event['image'];
    $uploadDir = __DIR__ . '/uploads';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileType = $_FILES['image']['type'];
        $fileSize = $_FILES['image']['size'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($fileType, $allowedTypes) && $fileSize <= 5 * 1024 * 1024) {
            $newFileName = uniqid() . '_' . basename($_FILES['image']['name']);
            $destPath = $uploadDir . '/' . $newFileName;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $destPath)) {
                $imagePath = '/smartcareer/pages/services/uploads/' . $newFileName;
            }
        }
    }

    $stmt = $con->prepare("UPDATE events SET title = ?, type = ?, date = ?, time = ?, location = ?, description = ?, link = ?, image = ?, updated_at = ? WHERE id = ? AND posted_by = ?");
    $stmt->bind_param("ssssssssssi", $title, $type, $date, $time, $location, $description, $link, $imagePath, $updatedAt, $eventId, $userId);

    if ($stmt->execute()) {
        header("Location: manage_event.php?success=Event updated successfully!");
    } else {
        header("Location: edit_event.php?event_id=$eventId&error=Failed to save changes");
    }
    $stmt->close();
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
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-md p-6">
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
                    <label for="image" class="block text-sm font-medium text-gray-700">Event Image (Optional)</label>
                    <input type="file" id="image" name="image" accept="image/*" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <?php if (!empty($event['image'])): ?>
                        <p class="text-sm text-gray-500 mt-1">Current: <a href="<?php echo htmlspecialchars($event['image']); ?>" target="_blank">View Image</a></p>
                    <?php endif; ?>
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