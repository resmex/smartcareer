<?php
session_start();
include '../../includes/connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

$courseId = filter_input(INPUT_GET, 'course_id', FILTER_SANITIZE_STRING);
if (!$courseId) {
    header("Location: manage_course.php?error=Invalid course ID");
    exit();
}

$stmt = $con->prepare("SELECT * FROM courses WHERE id = ? AND posted_by = ?");
$stmt->bind_param("si", $courseId, $_SESSION['user_id']);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$course) {
    header("Location: manage_course.php?error=Course not found or unauthorized");
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

    if (!$title || !$category || !$platform || !$instructor || !$duration || !$level || !$price) {
        header("Location: edit_course.php?course_id=$courseId&error=All required fields must be filled.");
        exit();
    }

    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $imageUrl = $course['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageName = uniqid() . '-' . basename($_FILES['image']['name']);
        $imagePath = $uploadDir . $imageName;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            $imageUrl = "http://localhost/smartcareer/pages/services/uploads/$imageName";
            if (file_exists($course['image']) && strpos($course['image'], 'via.placeholder.com') === false) {
                unlink($course['image']);
            }
        }
    }

    $filePath = $course['file_path'];
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileName = uniqid() . '-' . basename($_FILES['file']['name']);
        $filePathFull = $uploadDir . $fileName;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $filePathFull)) {
            $filePath = "http://localhost/smartcareer/pages/services/uploads/$fileName";
            if ($course['file_path'] && file_exists($course['file_path'])) {
                unlink($course['file_path']);
            }
        }
    }

    $videoPath = $course['video_path'];
    if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $videoName = uniqid() . '-' . basename($_FILES['video']['name']);
        $videoPathFull = $uploadDir . $videoName;
        if (move_uploaded_file($_FILES['video']['tmp_name'], $videoPathFull)) {
            $videoPath = "http://localhost/smartcareer/pages/services/uploads/$videoName";
            if ($course['video_path'] && file_exists($course['video_path'])) {
                unlink($course['video_path']);
            }
        }
    }

    $stmt = $con->prepare("
        UPDATE courses SET title = ?, description = ?, category = ?, platform = ?, instructor = ?, duration = ?, level = ?, image = ?, link = ?, price = ?, file_path = ?, video_path = ?
        WHERE id = ? AND posted_by = ?
    ");
    $stmt->bind_param("sssssssssssssi", $title, $description, $category, $platform, $instructor, $duration, $level, $imageUrl, $link, $price, $filePath, $videoPath, $courseId, $_SESSION['user_id']);

    if ($stmt->execute()) {
        header("Location: learning.php?success=Course updated successfully!");
    } else {
        header("Location: edit_course.php?course_id=$courseId&error=Failed to update course: " . $stmt->error);
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
    <title>Edit Course | SmartCareer</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <?php include '../../includes/header.php'; ?>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Edit Course</h1>
        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        <form action="" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-md p-6">
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Course Title *</label>
                    <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($course['title']); ?>" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" name="description" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($course['description']); ?></textarea>
                </div>
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">Category *</label>
                    <select id="category" name="category" required class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <?php foreach (['Technology', 'Business', 'Design', 'Marketing', 'Personal Development', 'Writing', 'Freelancing', 'Languages', 'Health'] as $cat): ?>
                            <option value="<?php echo $cat; ?>" <?php echo $course['category'] === $cat ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="platform" class="block text-sm font-medium text-gray-700">Platform *</label>
                    <input type="text" id="platform" name="platform" required value="<?php echo htmlspecialchars($course['platform']); ?>" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="instructor" class="block text-sm font-medium text-gray-700">Instructor *</label>
                    <input type="text" id="instructor" name="instructor" required value="<?php echo htmlspecialchars($course['instructor']); ?>" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="duration" class="block text-sm font-medium text-gray-700">Duration *</label>
                    <input type="text" id="duration" name="duration" required value="<?php echo htmlspecialchars($course['duration']); ?>" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="level" class="block text-sm font-medium text-gray-700">Level *</label>
                    <select id="level" name="level" required class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <?php foreach (['Beginner', 'Intermediate', 'Advanced'] as $lvl): ?>
                            <option value="<?php echo $lvl; ?>" <?php echo $course['level'] === $lvl ? 'selected' : ''; ?>><?php echo $lvl; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700">Course Image (Optional)</label>
                    <input type="file" id="image" name="image" accept="image/*" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <p class="text-sm text-gray-500 mt-1">Current: <a href="<?php echo htmlspecialchars($course['image']); ?>" target="_blank">View Image</a></p>
                </div>
                <div>
                    <label for="file" class="block text-sm font-medium text-gray-700">Course File (Optional, e.g., PDF)</label>
                    <input type="file" id="file" name="file" accept=".pdf,.docx" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <?php if ($course['file_path']): ?>
                        <p class="text-sm text-gray-500 mt-1">Current: <a href="<?php echo htmlspecialchars($course['file_path']); ?>" target="_blank">View File</a></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="video" class="block text-sm font-medium text-gray-700">Course Video (Optional)</label>
                    <input type="file" id="video" name="video" accept="video/*" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <?php if ($course['video_path']): ?>
                        <p class="text-sm text-gray-500 mt-1">Current: <a href="<?php echo htmlspecialchars($course['video_path']); ?>" target="_blank">View Video</a></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="link" class="block text-sm font-medium text-gray-700">Course Link (Optional)</label>
                    <input type="url" id="link" name="link" value="<?php echo htmlspecialchars($course['link']); ?>" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">Price *</label>
                    <input type="text" id="price" name="price" required value="<?php echo htmlspecialchars($course['price']); ?>" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="mt-6 flex space-x-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Changes</button>
                <a href="manage_course.php" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>