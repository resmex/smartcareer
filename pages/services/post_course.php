<?php
session_start();
include '../../includes/connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_STRING);
    $platform = filter_input(INPUT_POST, 'platform', FILTER_SANITIZE_STRING);
    $instructor = filter_input(INPUT_POST, 'instructor', FILTER_SANITIZE_STRING);
    $duration = filter_input(INPUT_POST, 'duration', FILTER_SANITIZE_STRING);
    $level = filter_input(INPUT_POST, 'level', FILTER_SANITIZE_STRING);
    $link = filter_input(INPUT_POST, 'link', FILTER_VALIDATE_URL) ?: '#';
    $price = filter_input(INPUT_POST, 'price', FILTER_SANITIZE_STRING);
    $userId = $_SESSION['user_id'];

    if (!$title || !$category || !$platform || !$instructor || !$duration || !$level || !$price) {
        header("Location: post_course.php?error=All required fields must be filled.");
        exit();
    }

    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    // Handle image upload
    $imageUrl = 'https://via.placeholder.com/480x270?text=' . urlencode($title);
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageName = uniqid() . '-' . basename($_FILES['image']['name']);
        $imagePath = $uploadDir . $imageName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            $imageUrl = "http://localhost/smartcareer/pages/services/uploads/$imageName";
        }
    }

    // Handle file upload (e.g., PDF)
    $filePath = null;
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileName = uniqid() . '-' . basename($_FILES['file']['name']);
        $filePathFull = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $filePathFull)) {
            $filePath = "http://localhost/smartcareer/pages/services/uploads/$fileName";
        }
    }

    // Handle video upload
    $videoPath = null;
    if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $videoName = uniqid() . '-' . basename($_FILES['video']['name']);
        $videoPathFull = $uploadDir . $videoName;
        if (move_uploaded_file($_FILES['video']['tmp_name'], $videoPathFull)) {
            $videoPath = "http://localhost/smartcareer/pages/services/uploads/$videoName";
        }
    }

    $courseId = uniqid('course');
    $stmt = $con->prepare("
        INSERT INTO courses (id, title, description, category, platform, instructor, duration, level, image, link, price, file_path, video_path, posted_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("sssssssssssssi", $courseId, $title, $description, $category, $platform, $instructor, $duration, $level, $imageUrl, $link, $price, $filePath, $videoPath, $userId);

    if ($stmt->execute()) {
        header("Location: learning.php?success=Course posted successfully!");
    } else {
        header("Location: post_course.php?error=Failed to post course: " . $stmt->error);
    }
    $stmt->close();
    $con->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Course | SmartCareer</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <?php include '../../includes/header.php'; ?>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Post a New Course</h1>
        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        <form action="" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-md p-6">
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Course Title *</label>
                    <input type="text" id="title" name="title" required class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" name="description" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">Category *</label>
                    <select id="category" name="category" required class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="Technology">Technology</option>
                        <option value="Business">Business</option>
                        <option value="Design">Design</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Personal Development">Personal Development</option>
                        <option value="Writing">Writing</option>
                        <option value="Freelancing">Freelancing</option>
                        <option value="Languages">Languages</option>
                        <option value="Health">Health</option>
                    </select>
                </div>
                <div>
                    <label for="platform" class="block text-sm font-medium text-gray-700">Platform *</label>
                    <input type="text" id="platform" name="platform" required class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="instructor" class="block text-sm font-medium text-gray-700">Instructor *</label>
                    <input type="text" id="instructor" name="instructor" required class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="duration" class="block text-sm font-medium text-gray-700">Duration *</label>
                    <input type="text" id="duration" name="duration" required placeholder="e.g., 6 weeks" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="level" class="block text-sm font-medium text-gray-700">Level *</label>
                    <select id="level" name="level" required class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="Beginner">Beginner</option>
                        <option value="Intermediate">Intermediate</option>
                        <option value="Advanced">Advanced</option>
                    </select>
                </div>
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700">Course Image (Optional)</label>
                    <input type="file" id="image" name="image" accept="image/*" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="file" class="block text-sm font-medium text-gray-700">Course File (Optional, e.g., PDF)</label>
                    <input type="file" id="file" name="file" accept=".pdf,.docx" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="video" class="block text-sm font-medium text-gray-700">Course Video (Optional)</label>
                    <input type="file" id="video" name="video" accept="video/*" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="link" class="block text-sm font-medium text-gray-700">Course Link (Optional)</label>
                    <input type="url" id="link" name="link" placeholder="https://example.com/course" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">Price *</label>
                    <input type="text" id="price" name="price" required placeholder="e.g., Free or $49" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="mt-6 flex space-x-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Post Course</button>
                <a href="learning.php" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>