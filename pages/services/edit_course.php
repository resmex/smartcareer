<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

$courseId = filter_input(INPUT_GET, 'course_id', FILTER_SANITIZE_STRING);
if (!$courseId) {
    header("Location: manage_course.php?error=Invalid course ID");
    exit();
}

$dataDir = __DIR__ . '/data';
$coursesFile = $dataDir . '/courses.json';
$courses = file_exists($coursesFile) ? json_decode(file_get_contents($coursesFile), true) : [];
$course = array_filter($courses, fn($c) => $c['id'] === $courseId && $c['posted_by'] === $_SESSION['user_id']);

if (empty($course)) {
    header("Location: manage_course.php?error=Course not found or unauthorized");
    exit();
}

$course = array_values($course)[0];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
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

    $imageUrl = $course['image'];
    $imageDir = __DIR__ . '/images';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageName = uniqid() . '-' . basename($_FILES['image']['name']);
        $imagePath = $imageDir . '/' . $imageName;

        if (!is_dir($imageDir)) {
            if (!mkdir($imageDir, 0777, true)) {
                header("Location: edit_course.php?course_id=$courseId&error=Failed to create image directory.");
                exit();
            }
        }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            $imageUrl = "http://localhost/smartcareer/pages/services/images/" . $imageName;
            // Optionally delete old image if it exists and is not the default
            if (file_exists($course['image']) && strpos($course['image'], 'via.placeholder.com') === false) {
                unlink($course['image']);
            }
        } else {
            header("Location: edit_course.php?course_id=$courseId&error=Failed to upload image.");
            exit();
        }
    }

    $courseIndex = array_search($courseId, array_column($courses, 'id'));
    $courses[$courseIndex] = [
        'id' => $courseId,
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
        'created_at' => $course['created_at'],
        'updated_at' => date("Y-m-d H:i:s"),
        'rating' => $course['rating'],
        'enrolled' => $course['enrolled']
    ];

    file_put_contents($coursesFile, json_encode($courses, JSON_PRETTY_PRINT));
    header("Location: learning.php?success=Course updated successfully!");
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
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p><?php echo htmlspecialchars($_GET['error']); ?></p>
            </div>
        <?php endif; ?>

        <form id="editCourseForm" action="" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-md p-6">
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Course Title *</label>
                    <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($course['title']); ?>" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">Category *</label>
                    <select id="category" name="category" required class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="Technology" <?php echo $course['category'] === 'Technology' ? 'selected' : ''; ?>>Technology</option>
                        <option value="Business" <?php echo $course['category'] === 'Business' ? 'selected' : ''; ?>>Business</option>
                        <option value="Design" <?php echo $course['category'] === 'Design' ? 'selected' : ''; ?>>Design</option>
                        <option value="Marketing" <?php echo $course['category'] === 'Marketing' ? 'selected' : ''; ?>>Marketing</option>
                        <option value="Personal Development" <?php echo $course['category'] === 'Personal Development' ? 'selected' : ''; ?>>Personal Development</option>
                        <option value="Writing" <?php echo $course['category'] === 'Writing' ? 'selected' : ''; ?>>Writing</option>
                        <option value="Freelancing" <?php echo $course['category'] === 'Freelancing' ? 'selected' : ''; ?>>Freelancing</option>
                        <option value="Languages" <?php echo $course['category'] === 'Languages' ? 'selected' : ''; ?>>Languages</option>
                        <option value="Health" <?php echo $course['category'] === 'Health' ? 'selected' : ''; ?>>Health</option>
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
                        <option value="Beginner" <?php echo $course['level'] === 'Beginner' ? 'selected' : ''; ?>>Beginner</option>
                        <option value="Intermediate" <?php echo $course['level'] === 'Intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                        <option value="Advanced" <?php echo $course['level'] === 'Advanced' ? 'selected' : ''; ?>>Advanced</option>
                    </select>
                </div>
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700">Image (Optional)</label>
                    <input type="file" id="image" name="image" accept="image/*" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <p class="text-shift-gray-500 mt-1">Current: <a href="<?php echo htmlspecialchars($course['image']); ?>" target="_blank">View Image</a></p>
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