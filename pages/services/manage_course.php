<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$dataDir = __DIR__ . '/data';
$coursesFile = $dataDir . '/courses.json';
$courses = file_exists($coursesFile) ? json_decode(file_get_contents($coursesFile), true) : [];
$userCourses = array_filter($courses, fn($course) => isset($course['posted_by']) && $course['posted_by'] === $userId);

// Handle delete action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $courseId = filter_input(INPUT_POST, 'course_id', FILTER_SANITIZE_STRING);
    $courseIndex = array_search($courseId, array_column($courses, 'id'));
    if ($courseIndex !== false && $courses[$courseIndex]['posted_by'] === $userId) {
        // Delete image if it exists and is not a placeholder
        if (file_exists($courses[$courseIndex]['image']) && strpos($courses[$courseIndex]['image'], 'via.placeholder.com') === false) {
            unlink($courses[$courseIndex]['image']);
        }
        unset($courses[$courseIndex]);
        $courses = array_values($courses);
        file_put_contents($coursesFile, json_encode($courses, JSON_PRETTY_PRINT));
        echo json_encode(['success' => 'Course deleted successfully!']);
        exit();
    } else {
        echo json_encode(['error' => 'Course not found or unauthorized']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Your Courses | SmartCareer</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .course-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .course-card:hover { transform: translateY(-3px); box-shadow: 0 10px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <?php include '../../includes/header.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Manage Your Courses</h1>
        <a href="learning.php" class="mb-6 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Back to Learning Resources</a>

        <?php if (empty($userCourses)): ?>
            <p class="text-gray-600">You haven't posted any courses yet.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($userCourses as $course): ?>
                    <div class="course-card bg-white rounded-xl shadow-md p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($course['title']); ?></h3>
                                <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($course['category']); ?></p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                            <div><i class="fas fa-chalkboard-teacher mr-2"></i> <?php echo htmlspecialchars($course['instructor']); ?></div>
                            <div><i class="fas fa-clock mr-2"></i> <?php echo htmlspecialchars($course['duration']); ?></div>
                            <div><i class="fas fa-level-up-alt mr-2"></i> <?php echo htmlspecialchars($course['level']); ?></div>
                        </div>
                        <div class="mt-4 flex space-x-2">
                            <button onclick="deleteCourse('<?php echo $course['id']; ?>')" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
                            <button onclick="editCourse('<?php echo $course['id']; ?>')" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">Edit</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        async function deleteCourse(courseId) {
            if (confirm('Are you sure you want to delete this course?')) {
                try {
                    const response = await fetch('manage_course.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'delete', course_id: courseId })
                    });
                    if (!response.ok) throw new Error('Failed to delete course');
                    const result = await response.json();
                    if (result.error) throw new Error(result.error);
                    alert('Course deleted successfully!');
                    location.reload();
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            }
        }

        function editCourse(courseId) {
            window.location.href = `edit_course.php?course_id=${courseId}`;
        }
    </script>
</body>
</html>