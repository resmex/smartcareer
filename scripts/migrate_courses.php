<?php
include '../includes/connect.php';

$jsonFile = 'C:\xampp\htdocs\smartcareer\pages\services\data\courses.json';

if (!file_exists($jsonFile)) {
    die("Error: JSON file not found at $jsonFile");
}

$jsonData = file_get_contents($jsonFile);
$courses = json_decode($jsonData, true);

if ($courses === null) {
    die("Error: Failed to decode JSON data. JSON error: " . json_last_error_msg());
}

function convertEnrolledToInt($enrolled) {
    $enrolled = strtoupper(trim($enrolled));
    if (strpos($enrolled, 'M') !== false) {
        return (int)(floatval(str_replace('M', '', $enrolled)) * 1000000);
    } elseif (strpos($enrolled, 'K') !== false) {
        return (int)(floatval(str_replace('K', '', $enrolled)) * 1000);
    }
    return (int)$enrolled;
}

$stmt = $con->prepare("
    INSERT INTO courses (
        id, title, description, category, platform, instructor, duration, level, 
        image, link, price, file_path, video_path, rating, enrollment_count, 
        posted_by, created_at, updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    die("Error preparing statement: " . $con->error);
}

$inserted = 0;
$skipped = 0;

foreach ($courses as $course) {
    // Check if course already exists
    $checkStmt = $con->prepare("SELECT id FROM courses WHERE id = ?");
    $checkStmt->bind_param("s", $course['id']);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    $checkStmt->close();

    if ($result->num_rows > 0) {
        echo "Skipping course ID {$course['id']} - already exists in database.\n";
        $skipped++;
        continue;
    }

    // Check if posted_by exists in users
    $posted_by = (int)$course['posted_by'];
    $userCheckStmt = $con->prepare("SELECT id FROM users WHERE id = ?");
    $userCheckStmt->bind_param("i", $posted_by);
    $userCheckStmt->execute();
    $userResult = $userCheckStmt->get_result();
    $userCheckStmt->close();

    if ($userResult->num_rows == 0) {
        $posted_by = 1; // Default to admin user ID 1
        echo "Warning: posted_by {$course['posted_by']} not found, defaulting to admin (ID 1) for course ID {$course['id']}.\n";
    }

    $id = $course['id'];
    $title = $course['title'];
    $description = null;
    $category = $course['category'];
    $platform = $course['platform'];
    $instructor = $course['instructor'];
    $duration = $course['duration'];
    $level = $course['level'];
    $image = $course['image'];
    $link = $course['link'];
    $price = $course['price'];
    $file_path = null;
    $video_path = null;
    $rating = (float)$course['rating'];
    $enrollment_count = convertEnrolledToInt($course['enrolled']);
    $created_at = $course['created_at'];
    $updated_at = $created_at;

    $stmt->bind_param(
        "ssssssssssssssdiss",
        $id, $title, $description, $category, $platform, $instructor, $duration, $level,
        $image, $link, $price, $file_path, $video_path, $rating, $enrollment_count,
        $posted_by, $created_at, $updated_at
    );

    if ($stmt->execute()) {
        echo "Successfully migrated course: {$title} (ID: {$id})\n";
        $inserted++;
    } else {
        echo "Error migrating course ID {$id}: " . $stmt->error . "\n";
    }
}

$stmt->close();
$con->close();

echo "\nMigration complete!\n";
echo "Inserted: $inserted courses\n";
echo "Skipped: $skipped courses\n";
?>