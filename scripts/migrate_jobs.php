<?php
include '../includes/connect.php';

$jobsFile = 'C:/xampp/htdocs/smartcareer/pages/services/data/jobs.json';
if (!file_exists($jobsFile)) {
    die("Error: jobs.json not found at $jobsFile");
}

$jobs = json_decode(file_get_contents($jobsFile), true);
if (!is_array($jobs)) {
    die("Error: Invalid JSON format in jobs.json");
}

echo "Starting migration of jobs...\n";

$stmt = $con->prepare("
    INSERT IGNORE INTO jobs (
        id, title, company, location, type, description, requirements, 
        salary_range, posted_by, date_posted, updated_at, source, 
        category_class, date, time, image, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    die("Error preparing statement: " . $con->error);
}

$successCount = 0;
$errorCount = 0;

foreach ($jobs as $job) {
    $id = $job['id'] ?? uniqid();
    $title = $job['title'] ?? 'Untitled';
    $company = $job['company'] ?? null;
    $location = $job['location'] ?? 'Unknown Location';
    $type = $job['type'] ?? 'Unknown Type';
    $description = $job['description'] ?? 'No description';
    $requirements = $job['requirements'] ?? null;
    $salaryRange = $job['salary'] ?? 'Not specified';
    $postedBy = 1; // Admin ID
    $datePosted = $job['date_posted'] ?? null;
    $updatedAt = $job['updated_at'] ?? null;
    $source = $job['source'] ?? null;
    $categoryClass = json_encode($job['category_class'] ?? []);
    $date = $job['date'] ?? null;
    $time = $job['time'] ?? null;
    $image = $job['image'] ?? null;
    $createdAt = $job['created_at'] ?? null;

    $stmt->bind_param(
        "ssssssssissssssss",
        $id, $title, $company, $location, $type, $description, $requirements,
        $salaryRange, $postedBy, $datePosted, $updatedAt, $source, 
        $categoryClass, $date, $time, $image, $createdAt
    );

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $successCount++;
            echo "Imported job: $title (ID: $id)\n";
        } else {
            echo "Skipped duplicate job: $title (ID: $id)\n";
        }
    } else {
        $errorCount++;
        echo "Error importing job: $title (ID: $id) - " . $stmt->error . "\n";
    }
}

$stmt->close();
$con->close();

echo "\nMigration complete!\n";
echo "Successfully imported: $successCount jobs\n";
echo "Errors encountered: $errorCount\n";
?>