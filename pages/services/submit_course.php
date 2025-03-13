<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dataDir = __DIR__ . '/data';
    $coursesFile = $dataDir . '/courses.json';
    $imageDir = __DIR__ . '/images';

    // Sanitize inputs
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);
    $platform = filter_input(INPUT_POST, 'platform', FILTER_SANITIZE_STRING);
    $instructor = filter_input(INPUT_POST, 'instructor', FILTER_SANITIZE_STRING);
    $duration = filter_input(INPUT_POST, 'duration', FILTER_SANITIZE_STRING);
    $level = filter_input(INPUT_POST, 'level', FILTER_SANITIZE_STRING);
    $link = filter_input(INPUT_POST, 'link', FILTER_VALIDATE_URL) ?: '#';
    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_STRING);

    // Validate required fields
    if (!$title || !$category || !$platform || !$instructor || !$duration || !$level || !$price) {
        header("Location: post_course.php?error=All required fields must be filled.");
        exit();
    }

    // Handle image upload
    $imageUrl = "https://via.placeholder.com/480x270?text=" . urlencode($title); // Default placeholder
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageName = uniqid() . '-' . basename($_FILES['image']['name']);
        $imagePath = $imageDir . '/' . $imageName;

        // Ensure image directory exists
        if (!is_dir($imageDir)) {
            if (!mkdir($imageDir, 0777, true)) {
                header("Location: post_course.php?error=Failed to create image directory.");
                exit();
            }
        }

        // Move uploaded file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            $imageUrl = "http://localhost/smartcareer/pages/services/images/" . $imageName;
        } else {
            header("Location: post_course.php?error=Failed to upload image.");
            exit();
        }
    }

    // Read existing courses
    $existingCourses = [];
    if (file_exists($coursesFile)) {
        $existingCourses = json_decode(file_get_contents($coursesFile), true);
        if (!is_array($existingCourses)) {
            $existingCourses = [];
        }
    }

    // Create new course
    $newCourse = [
        'id' => uniqid(),
        'title' => $title,
        'category' => $category,
        'platform' => $platform,
        'instructor' => $instructor,
        'duration' => $duration,
        'level' => $level,
        'image' => $imageUrl,
        'link' => $link,
        'price' => $price,
        'posted_by' => $_SESSION['user_id'],
        'created_at' => date("Y-m-d H:i:s"),
        'rating' => 0, // Default rating
        'enrolled' => "0" // Default enrollment
    ];

    $existingCourses[] = $newCourse;

    // Ensure data directory exists
    if (!is_dir($dataDir)) {
        if (!mkdir($dataDir, 0777, true)) {
            header("Location: post_course.php?error=Failed to create data directory.");
            exit();
        }
    }

    // Save courses to file
    if (file_put_contents($coursesFile, json_encode($existingCourses, JSON_PRETTY_PRINT)) === false) {
        header("Location: post_course.php?error=Failed to save course.");
        exit();
    }

    header("Location: learning.php?success=Course posted successfully!");
    exit();
}
?>