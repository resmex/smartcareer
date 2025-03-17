<?php
session_start();
include '../../includes/connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

$userId = $_SESSION['user_id'];

$stmt = $con->prepare("SELECT * FROM courses WHERE posted_by = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userCourses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $courseId = filter_input(INPUT_POST, 'course_id', FILTER_SANITIZE_STRING);
    $stmt = $con->prepare("SELECT image, file_path, video_path FROM courses WHERE id = ? AND posted_by = ?");
    $stmt->bind_param("si", $courseId, $userId);
    $stmt->execute();
    $course = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($course) {
        if ($course['image'] && strpos($course['image'], 'via.placeholder.com') === false && file_exists($course['image'])) {
            unlink($course['image']);
        }
        if ($course['file_path'] && file_exists($course['file_path'])) {
            unlink($course['file_path']);
        }
        if ($course['video_path'] && file_exists($course['video_path'])) {
            unlink($course['video_path']);
        }

        $stmt = $con->prepare("DELETE FROM courses WHERE id = ? AND posted_by = ?");
        $stmt->bind_param("si", $courseId, $userId);
        if ($stmt->execute()) {
            echo json_encode(['success' => 'Course deleted successfully!']);
        } else {
            echo json_encode(['error' => 'Failed to delete course']);
        }
        $stmt->close();
    } else {
        echo json_encode(['error' => 'Course not found or unauthorized']);
    }
    $con->close();
    exit();
}

$con->close();
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
                            <a href="edit_course.php?course_id=<?php echo $course['id']; ?>" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">Edit</a>
                            <a href="course_enrollments.php?course_id=<?php echo $course['id']; ?>" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">View Enrolled</a>
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
    </script>
</body>
</html>