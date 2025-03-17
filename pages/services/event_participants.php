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

// Fetch participants from database
$stmt = $con->prepare("SELECT full_name, email, phone, registered_at FROM event_registrations WHERE event_id = ?");
$stmt->bind_param("s", $eventId);
$stmt->execute();
$participants = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Participants | SmartCareer</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 py-10">
        <h1 class="text-3xl font-bold mb-6">Participants for Event ID: <?php echo htmlspecialchars($eventId); ?></h1>
        <?php if (empty($participants)): ?>
            <p class="text-gray-600">No participants registered yet.</p>
        <?php else: ?>
            <table class="w-full bg-white shadow-md rounded-lg overflow-hidden">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-4 py-2 text-left">Full Name</th>
                        <th class="px-4 py-2 text-left">Email</th>
                        <th class="px-4 py-2 text-left">Phone</th>
                        <th class="px-4 py-2 text-left">Registered At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($participants as $participant): ?>
                        <tr class="border-t">
                            <td class="px-4 py-2"><?php echo htmlspecialchars($participant['full_name']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($participant['email']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($participant['phone']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($participant['registered_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <a href="manage_event.php" class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Back</a>
    </div>
</body>
</html>