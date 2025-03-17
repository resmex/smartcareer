<?php
include '../includes/connect.php';

$eventsFile = 'C:/xampp/htdocs/smartcareer/pages/services/data/events.json';
if (!file_exists($eventsFile)) {
    die("Error: events.json not found at $eventsFile");
}

$events = json_decode(file_get_contents($eventsFile), true);
if (!is_array($events)) {
    die("Error: Invalid JSON format in events.json");
}

echo "Starting migration of events...\n";

$stmt = $con->prepare("
   INSERT INTO events (
    id, title, company, location, type, salary, categories, description, 
    full_description, source, link, date_posted, is_remote, region, posted_by, 
    category_class, date, time, image, created_at, updated_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    die("Error preparing statement: " . $con->error);
}

$successCount = 0;
$errorCount = 0;

foreach ($events as $event) {
    $id = $event['id'] ?? uniqid();
    $title = $event['title'] ?? 'Untitled';
    $company = $event['company'] ?? null;
    $location = $event['location'] ?? 'Unknown Location';
    $type = $event['type'] ?? 'Unknown Type';
    $salary = $event['salary'] ?? 'Not specified';
    $categories = json_encode($event['categories'] ?? []);
    $description = $event['description'] ?? 'No description';
    $fullDescription = $event['full_description'] ?? null;
    $source = $event['source'] ?? null;
    $link = $event['link'] ?? '#';
    $datePosted = $event['date_posted'] ?? date("Y-m-d H:i:s"); // Fallback to current date/time
    $isRemote = isset($event['isRemote']) ? ($event['isRemote'] ? 1 : 0) : (stripos($location, 'remote') !== false ? 1 : 0);
    $region = $event['region'] ?? null;
    $organizerId = isset($event['posted_by']) ? (int)$event['posted_by'] : null;
    $categoryClass = json_encode($event['categoryClass'] ?? []);
    $date = $event['date'] ?? date("Y-m-d H:i:s");
    $time = $event['time'] ?? null;
    $image = $event['image'] ?? null;
    $createdAt = $event['created_at'] ?? date("Y-m-d H:i:s");

    $stmt->bind_param(
    "sssssssssssssisssssss",
    $id, $title, $company, $location, $type, $salary, $categories, $description,
    $fullDescription, $source, $link, $datePosted, $isRemote, $region, $organizerId,
    $categoryClass, $date, $time, $image, $createdAt, $updatedAt
);
// Handle the date column
    $date = $event['date'] ?? date("Y-m-d H:i:s");
    if ($date !== date("Y-m-d H:i:s")) { // If not the default value, parse it
        $parsedDate = DateTime::createFromFormat('F d, Y', $date);
        if ($parsedDate !== false) {
            $date = $parsedDate->format('Y-m-d H:i:s');
        } else {
            $date = date("Y-m-d H:i:s");
            echo "Warning: Invalid date format for event: $title (ID: $id). Using default date.\n";
        }
    }

    if ($stmt->execute()) {
        $successCount++;
        echo "Imported event: $title (ID: $id)\n";
    } else {
        $errorCount++;
        echo "Error importing event: $title (ID: $id) - " . $stmt->error . "\n";
    }
}

$stmt->close();
$con->close();

echo "\nMigration complete!\n";
echo "Successfully imported: $successCount events\n";
echo "Errors encountered: $errorCount\n";
?>