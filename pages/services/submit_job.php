<?php
session_start();
include '../../includes/connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = 'Invalid CSRF token.';
        header("Location: ../../pages/services/post_job.php");
        exit();
    }

    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $company = filter_input(INPUT_POST, 'company', FILTER_SANITIZE_STRING);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
    $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
    $salary = filter_input(INPUT_POST, 'salary', FILTER_SANITIZE_STRING) ?: 'Not specified';
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $link = filter_input(INPUT_POST, 'link', FILTER_VALIDATE_URL) ?: '#';
    $categories = isset($_POST['categories']) ? json_encode(array_filter((array)$_POST['categories'])) : '[]';
    $isRemote = stripos($location, 'remote') !== false ? 1 : 0;
    $region = determineRegion($location);
    $userId = (int)$_SESSION['user_id'];
    $jobId = uniqid();
    $datePosted = date("Y-m-d");

    if (!$title || !$company || !$location || !$type || !$description) {
        $_SESSION['error'] = 'All required fields must be filled.';
        header("Location: ../../pages/services/post_job.php");
        exit();
    }

    $stmt = $con->prepare("INSERT INTO jobs (id, title, company, location, type, salary, description, full_description, link, categories, is_remote, region, posted_by, date_posted) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $shortDescription = substr($description, 0, 150) . "...";
    $stmt->bind_param("ssssssssssissi", $jobId, $title, $company, $location, $type, $salary, $shortDescription, $description, $link, $categories, $isRemote, $region, $userId, $datePosted);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Job posted successfully!';
        header("Location: ../../pages/services/jobs.php");
    } else {
        $_SESSION['error'] = 'Failed to save job: ' . $stmt->error;
        header("Location: ../../pages/services/post_job.php");
    }
    $stmt->close();
    exit();
}

function determineRegion($location) {
    $location = strtolower($location);
    $regions = [
        'remote' => ['remote'],
        'usa' => ['usa', 'united states', 'us'],
        'europe' => ['europe', 'eu'],
        'asia' => ['asia'],
        'africa' => ['africa'],
    ];

    foreach ($regions as $region => $keywords) {
        foreach ($keywords as $keyword) {
            if (strpos($location, $keyword) !== false) {
                return $region;
            }
        }
    }
    return 'other';
}
?>