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

// Verify the course belongs to the user
$userId = $_SESSION['user_id'];
$stmt = $con->prepare("SELECT title FROM courses WHERE id = ? AND posted_by = ?");
$stmt->bind_param("si", $courseId, $userId);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$course) {
    header("Location: manage_course.php?error=Course not found or unauthorized");
    exit();
}

// Fetch enrolled users
$stmt = $con->prepare("
    SELECT u.id, u.first_name, ce.enrolled_at, ce.completed_at 
    FROM course_enrollments ce 
    INNER JOIN users u ON ce.user_id = u.id 
    WHERE ce.course_id = ?
");
$stmt->bind_param("s", $courseId);
$stmt->execute();
$enrolledUsers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrolled Users for <?php echo htmlspecialchars($course['title']); ?> | SmartCareer</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .table-container { overflow-x: auto; }
        .status-badge { padding: 4px 8px; border-radius: 9999px; font-size: 12px; }
        .status-completed { background-color: #d1fae5; color: #10b981; }
        .status-enrolled { background-color: #bfdbfe; color: #1d4ed8; }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <?php include '../../includes/header.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Enrolled Users for "<?php echo htmlspecialchars($course['title']); ?>"</h1>
        <a href="manage_course.php" class="mb-6 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Back to Manage Courses</a>

        <?php if (empty($enrolledUsers)): ?>
            <p class="text-gray-600">No users have enrolled in this course yet.</p>
        <?php else: ?>
            <div class="table-container">
                <table class="min-w-full bg-white rounded-xl shadow-md">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User ID</th>
                            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrolled At</th>
                            <th class="py-3 px-6 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($enrolledUsers as $user): ?>
                            <tr>
                                <td class="py-4 px-6 text-sm text-gray-900"><?php echo htmlspecialchars($user['id']); ?></td>
                                <td class="py-4 px-6 text-sm text-gray-900"><?php echo htmlspecialchars($user['first_name']); ?></td>
                                <td class="py-4 px-6 text-sm text-gray-500"><?php echo htmlspecialchars($user['enrolled_at']); ?></td>
                                <td class="py-4 px-6 text-sm">
                                    <span class="status-badge <?php echo $user['completed_at'] ? 'status-completed' : 'status-enrolled'; ?>">
                                        <?php echo $user['completed_at'] ? 'Completed' : 'Enrolled'; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>