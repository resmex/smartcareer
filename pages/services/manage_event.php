<?php
session_start();
include '../../includes/connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

$userId = (int)$_SESSION['user_id'];

// Fetch user events
$stmt = $con->prepare("SELECT * FROM events WHERE posted_by = ? ORDER BY date ASC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userEvents = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    $eventId = $input['event_id'] ?? '';

    if ($action === 'delete' && $eventId) {
        $stmt = $con->prepare("DELETE FROM events WHERE id = ? AND posted_by = ?");
        $stmt->bind_param("si", $eventId, $userId);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo json_encode(['success' => 'Event deleted successfully!']);
        } else {
            echo json_encode(['error' => 'Event not found or unauthorized']);
        }
        $stmt->close();
        exit();
    }
    echo json_encode(['error' => 'Invalid action']);
    exit();
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
        .event-card { transition: transform 0.2s ease, box-shadow 0.2s ease; border-radius: 12px; }
        .event-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.15); }
        .btn { padding: 0.5rem 1rem; border-radius: 8px; color: white; }
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
                    <div class="event-card bg-white shadow-md overflow-hidden">
                        <?php if (!empty($event['image'])): ?>
                            <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>" class="w-full h-48 object-cover">
                        <?php endif; ?>
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($event['type']); ?></p>
                            <div class="grid grid-cols-2 gap-4 text-sm text-gray-600 mt-2">
                                <div><i class="fas fa-calendar-alt mr-2"></i> <?php echo htmlspecialchars($event['date']); ?></div>
                                <div><i class="fas fa-clock mr-2"></i> <?php echo htmlspecialchars($event['time']); ?></div>
                                <div><i class="fas fa-map-marker-alt mr-2"></i> <?php echo htmlspecialchars($event['location']); ?></div>
                            </div>
                            <p class="text-sm text-gray-500 mt-4 truncate"><?php echo htmlspecialchars($event['description']); ?></p>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <button onclick="deleteEvent('<?php echo $event['id']; ?>')" class="btn bg-red-600 hover:bg-red-700">Delete</button>
                                <button onclick="editEvent('<?php echo $event['id']; ?>')" class="btn bg-yellow-600 hover:bg-yellow-700">Edit</button>
                                <a href="event_participants.php?event_id=<?php echo $event['id']; ?>" class="btn bg-green-600 hover:bg-green-700">Participants</a>
                            </div>
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
                        headers: { 'Content-Type: 'application/json' },
                        body: JSON.stringify({ action: 'delete', event_id: eventId })
                    });
                    if (!response.ok) throw new Error('Network error');
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