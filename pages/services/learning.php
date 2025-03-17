<?php
session_start();
include '../../includes/connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

$userId = $_SESSION['user_id'];

// Fetch enrolled courses for progress
$stmt = $con->prepare("SELECT COUNT(*) as in_progress FROM course_enrollments WHERE user_id = ? AND completed_at IS NULL");
$stmt->bind_param("i", $userId);
$stmt->execute();
$inProgress = $stmt->get_result()->fetch_assoc()['in_progress'];
$stmt->close();

$stmt = $con->prepare("SELECT COUNT(*) as completed FROM course_enrollments WHERE user_id = ? AND completed_at IS NOT NULL");
$stmt->bind_param("i", $userId);
$stmt->execute();
$completed = $stmt->get_result()->fetch_assoc()['completed'];
$stmt->close();

// Fetch all courses
$stmt = $con->prepare("SELECT * FROM courses");
$stmt->execute();
$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCareer | Learning Resources</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .course-card { transition: transform 0.2s ease-in-out; }
        .course-card:hover { transform: translateY(-4px); }
        .progress-bar { transition: width 0.3s ease; }
        .category-pill.active { background-color: #2563eb; color: white; }
    </style>
</head>
<body class="bg-gray-50">
    <?php include '../../includes/header.php'; ?>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Learning Progress Overview -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Your Learning Progress</h2>
                <a href="dashboard.php" class="text-blue-600 hover:text-blue-800">View Dashboard</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-blue-50 rounded-lg p-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-600">Courses in Progress</span>
                        <span class="text-lg font-bold text-blue-600"><?php echo $inProgress; ?></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 rounded-full h-2 progress-bar" style="width: <?php echo min($inProgress * 20, 100); ?>%"></div>
                    </div>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-600">Completed Courses</span>
                        <span class="text-lg font-bold text-green-600"><?php echo $completed; ?></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-600 rounded-full h-2" style="width: <?php echo min($completed * 20, 100); ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories, Search, and Navigation -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex flex-wrap gap-2" id="categories">
                    <button class="category-pill px-4 py-2 rounded-full bg-gray-200 hover:bg-blue-600 hover:text-white active" data-category="All">All</button>
                    <?php foreach (['Technology', 'Business', 'Design', 'Marketing', 'Personal Development', 'Writing', 'Freelancing', 'Languages', 'Health'] as $cat): ?>
                        <button class="category-pill px-4 py-2 rounded-full bg-gray-200 hover:bg-blue-600 hover:text-white" data-category="<?php echo $cat; ?>"><?php echo $cat; ?></button>
                    <?php endforeach; ?>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Search courses..." class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                    <a href="post_course.php" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center">
                        <i class="fas fa-plus mr-2"></i> Post a Course
                    </a>
                    <a href="manage_course.php" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 flex items-center">
                        <i class="fas fa-cog mr-2"></i> Manage Courses
                    </a>
                </div>
            </div>
        </div>

        <!-- Courses Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6" id="coursesContainer">
            <?php foreach ($courses as $course): ?>
                <div class="course-card bg-white rounded-lg shadow-sm overflow-hidden" data-category="<?php echo htmlspecialchars($course['category']); ?>">
                    <img src="<?php echo htmlspecialchars($course['image']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="w-full h-32 object-cover">
                    <div class="p-4">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-md font-semibold"><?php echo htmlspecialchars($course['title']); ?></h3>
                            <button class="text-gray-400 hover:text-blue-600 bookmark-btn">
                                <i class="fas fa-bookmark"></i>
                            </button>
                        </div>
                        <p class="text-gray-600 text-sm mb-2"><?php echo htmlspecialchars($course['instructor']); ?></p>
                        <div class="flex items-center mb-2">
                            <div class="flex items-center mr-4">
                                <i class="fas fa-star text-yellow-400 mr-1"></i>
                                <span class="text-sm"><?php echo htmlspecialchars($course['rating']); ?></span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-users text-gray-400 mr-1"></i>
                                <span class="text-sm"><?php echo htmlspecialchars($course['enrollment_count']); ?> students</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-600"><?php echo htmlspecialchars($course['duration']); ?></span>
                            <span class="text-sm bg-blue-100 text-blue-800 px-2 py-1 rounded"><?php echo htmlspecialchars($course['level']); ?></span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="font-bold"><?php echo htmlspecialchars($course['price']); ?></span>
                            <a href="<?php echo htmlspecialchars($course['link']); ?>" target="_blank" class="px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">Start</a>
                        </div>
                        <?php if ($course['file_path'] || $course['video_path']): ?>
                            <div class="mt-2">
                                <?php if ($course['file_path']): ?>
                                    <a href="<?php echo htmlspecialchars($course['file_path']); ?>" download class="text-blue-600 hover:underline text-sm mr-2"><i class="fas fa-file-pdf mr-1"></i>Download File</a>
                                <?php endif; ?>
                                <?php if ($course['video_path']): ?>
                                    <a href="<?php echo htmlspecialchars($course['video_path']); ?>" target="_blank" class="text-blue-600 hover:underline text-sm"><i class="fas fa-video mr-1"></i>View Video</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const categoryPills = document.querySelectorAll('.category-pill');
            const coursesContainer = document.getElementById('coursesContainer');
            const courses = document.querySelectorAll('.course-card');
            const searchInput = document.getElementById('searchInput');

            categoryPills.forEach(pill => {
                pill.addEventListener('click', () => {
                    categoryPills.forEach(p => p.classList.remove('active'));
                    pill.classList.add('active');
                    const selectedCategory = pill.getAttribute('data-category');
                    courses.forEach(course => {
                        const courseCategory = course.getAttribute('data-category');
                        course.style.display = (selectedCategory === 'All' || courseCategory === selectedCategory) ? 'block' : 'none';
                    });
                });
            });

            searchInput.addEventListener('input', () => {
                const searchTerm = searchInput.value.toLowerCase();
                courses.forEach(course => {
                    const courseTitle = course.querySelector('h3').textContent.toLowerCase();
                    course.style.display = courseTitle.includes(searchTerm) ? 'block' : 'none';
                });
            });
        });
    </script>
</body>
</html>