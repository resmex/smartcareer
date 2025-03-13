<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = 'Invalid CSRF token.';
        header("Location: ../../pages/services/post_job.php");
        exit();
    }

    $dataDir = __DIR__ . '/data';
    $jobsFile = $dataDir . '/jobs.json';

    // Sanitize inputs
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $company = filter_input(INPUT_POST, 'company', FILTER_SANITIZE_STRING);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
    $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
    $salary = filter_input(INPUT_POST, 'salary', FILTER_SANITIZE_STRING) ?: 'Not specified';
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $link = filter_input(INPUT_POST, 'link', FILTER_VALIDATE_URL) ?: '#';

    // Validate required fields
    if (!$title || !$company || !$location || !$type || !$description) {
        $_SESSION['error'] = 'All required fields must be filled.';
        header("Location: ../../pages/services/post_job.php");
        exit();
    }

    // Validate categories
    $allowedCategories = ['government', 'ngo', 'tech', 'marketing', 'design', 'devops', 'banking', 'teaching'];
    $categories = isset($_POST['categories']) ? array_filter($_POST['categories'], function($cat) use ($allowedCategories) {
        return in_array($cat, $allowedCategories);
    }) : [];

    // Read existing jobs
    $existingJobs = [];
    if (file_exists($jobsFile)) {
        $existingJobs = json_decode(file_get_contents($jobsFile), true);
        if (!is_array($existingJobs)) {
            $existingJobs = [];
        }
    }

    // Create new job
    $newJob = [
        'id' => uniqid(),
        'title' => $title,
        'company' => $company,
        'location' => $location,
        'type' => $type,
        'salary' => $salary,
        'categories' => $categories,
        'description' => substr($description, 0, 150) . "...",
        'full_description' => $description,
        'source' => 'User Posted',
        'link' => $link,
        'date_posted' => date("Y-m-d"),
        'isRemote' => stripos($location, 'remote') !== false,
        'region' => determineRegion($location),
        'posted_by' => $_SESSION['user_id'],
        'categoryClass' => array_map(function($cat) {
            return match($cat) {
                'government' => 'government-badge',
                'tech' => 'tech-badge',
                'ngo' => 'bg-purple-100 text-purple-800',
                'banking' => 'bg-blue-100 text-blue-800',
                'teaching' => 'bg-yellow-100 text-yellow-800',
                'marketing' => 'bg-pink-100 text-pink-800',
                'design' => 'bg-orange-100 text-orange-800',
                'devops' => 'bg-indigo-100 text-indigo-800',
                default => 'bg-gray-100 text-gray-800'
            };
        }, $categories)
    ];

    $existingJobs[] = $newJob;

    // Save jobs to file
    if (!is_dir($dataDir)) {
        if (!mkdir($dataDir, 0777, true)) {
            $_SESSION['error'] = 'Failed to create data directory.';
            header("Location: ../../pages/services/post_job.php");
            exit();
        }
    }

    if (file_put_contents($jobsFile, json_encode($existingJobs, JSON_PRETTY_PRINT)) === false) {
        $_SESSION['error'] = 'Failed to save job.';
        header("Location: ../../pages/services/post_job.php");
        exit();
    }

    $_SESSION['success'] = 'Job posted successfully!';
    header("Location: ../../pages/services/jobs.php");
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