<?php
include '../../includes/connect.php';
header('Content-Type: application/json');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Get filters from GET request
$filters = array_map('htmlspecialchars', $_GET);
$search = $filters['search'] ?? '';
$type = $filters['type'] ?? '';
$location = $filters['location'] ?? '';
$dateFilter = $filters['date'] ?? '';

// Build SQL query
$query = "SELECT * FROM events WHERE 1=1";
$params = [];
$types = "";

if ($search) {
    $query .= " AND (title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}
if ($type) {
    $query .= " AND type = ?";
    $params[] = $type;
    $types .= "s";
}
if ($location) {
    $query .= " AND location LIKE ?";
    $params[] = "%$location%";
    $types .= "s";
}
if ($dateFilter) {
    switch ($dateFilter) {
        case 'this-week':
            $query .= " AND date BETWEEN DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) AND DATE_ADD(CURDATE(), INTERVAL 6 - WEEKDAY(CURDATE()) DAY)";
            break;
        case 'this-month':
            $query .= " AND date BETWEEN DATE_SUB(CURDATE(), INTERVAL DAY(CURDATE()) - 1 DAY) AND LAST_DAY(CURDATE())";
            break;
        case 'next-month':
            $query .= " AND date BETWEEN DATE_SUB(DATE_ADD(CURDATE(), INTERVAL 1 MONTH), INTERVAL DAY(CURDATE()) - 1 DAY) AND LAST_DAY(DATE_ADD(CURDATE(), INTERVAL 1 MONTH))";
            break;
    }
}
$query .= " ORDER BY date ASC";

$stmt = $con->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$filteredEvents = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$popularEvent = !empty($filteredEvents) ? $filteredEvents[0] : null;

echo json_encode([
    'popular_event' => $popularEvent,
    'events' => array_values($filteredEvents)
]);
?>