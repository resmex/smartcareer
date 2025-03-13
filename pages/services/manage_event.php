<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$dataDir = __DIR__ . '/data';
$eventsFile = $dataDir . '/events.json';
$events = file_exists($eventsFile) ? json_decode(file_get_contents($eventsFile), true) : [];
$userEvents = array_filter($events, fn($event) => isset($event['posted_by']) && $event['posted_by'] === $userId);

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $eventId = filter_input(INPUT_POST, 'event_id', FILTER_SANITIZE_STRING);
    $eventIndex = array_search($eventId, array_column($events, 'id'));
    if ($eventIndex !== false && $events[$eventIndex]['posted_by'] === $userId) {
        unset($events[$eventIndex]);
        $events = array_values($events); // Re-index array
        file_put_contents($eventsFile, json_encode($events, JSON_PRETTY_PRINT));
        echo json_encode(['success' => 'Event deleted successfully!']);
        exit();
    } else {
        echo json_encode(['error' => 'Event not found or unauthorized']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Your Events | SmartCareer</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .event-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .event-card:hover { transform: translateY(-3px); box-shadow: 0 10px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <?php include '../../includes/header.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Manage Your Events</h1>
        <a href="events.php" class="mb-6 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Back to Events</a>

        <?php if (empty($userEvents)): ?>
            <p class="text-gray-600">You haven't posted any events yet.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($userEvents as $event): ?>
                    <div class="event-card bg-white rounded-xl shadow-md p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($event['type']); ?></p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                            <div><i class="fas fa-calendar-alt mr-2"></i> <?php echo htmlspecialchars($event['date']); ?></div>
                            <div><i class="fas fa-clock mr-2"></i> <?php echo htmlspecialchars($event['time']); ?></div>
                            <div><i class="fas fa-map-marker-alt mr-2"></i> <?php echo htmlspecialchars($event['location']); ?></div>
                        </div>
                        <div class="mt-4">
                            <p class="text-sm text-gray-500 truncate"><?php echo htmlspecialchars($event['description']); ?></p>
                        </div>
                        <div class="mt-4 flex space-x-2">
                            <button onclick="deleteEvent('<?php echo $event['id']; ?>')" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
                            <button onclick="editEvent('<?php echo $event['id']; ?>')" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">Edit</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        async function deleteEvent(eventId) {
            if (confirm('Are you sure you want to delete this event?')) {
                try {
                    const response = await fetch('manage_event.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'delete', event_id: eventId })
                    });
                    if (!response.ok) throw new Error('Failed to delete event');
                    const result = await response.json();
                    if (result.error) throw new Error(result.error);
                    alert('Event deleted successfully!');
                    location.reload();
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            }
        }

        function editEvent(eventId) {
            window.location.href = `edit_event.php?event_id=${eventId}`;
        }
    </script>
</body>
</html>