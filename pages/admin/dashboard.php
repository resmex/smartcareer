<?php
session_start();
include '../../includes/connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../pages/login.php?error=unauthorized");
    exit();
}

$user_id = $_SESSION['user_id'];
$firstName = $_SESSION['first_name'];

$profilePicture = '/smartcareer/uploads/profile_default.jpeg';
$stmt = $con->prepare("SELECT profile_picture FROM user_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user_profile = $result->fetch_assoc();
    $profilePicture = !empty($user_profile['profile_picture']) ? '/smartcareer/uploads/' . htmlspecialchars($user_profile['profile_picture']) : $profilePicture;
}
$stmt->close();

// Search and Filter Logic
$user_search = isset($_GET['user_search']) ? "%" . $_GET['user_search'] . "%" : "%";
$user_filter = isset($_GET['user_filter']) ? $_GET['user_filter'] : 'all';
$job_search = isset($_GET['job_search']) ? "%" . $_GET['job_search'] . "%" : "%";
$job_filter_date = isset($_GET['job_filter_date']) ? $_GET['job_filter_date'] : 'all';
$job_filter_type = isset($_GET['job_filter_type']) ? $_GET['job_filter_type'] : 'all';
$job_filter_company = isset($_GET['job_filter_company']) ? $_GET['job_filter_company'] : 'all';
$event_search = isset($_GET['event_search']) ? "%" . $_GET['event_search'] . "%" : "%";
$event_filter_date = isset($_GET['event_filter_date']) ? $_GET['event_filter_date'] : 'all';
$event_filter_type = isset($_GET['event_filter_type']) ? $_GET['event_filter_type'] : 'all';
$course_search = isset($_GET['course_search']) ? "%" . $_GET['course_search'] . "%" : "%";
$course_filter_date = isset($_GET['course_filter_date']) ? $_GET['course_filter_date'] : 'all';
$course_filter_category = isset($_GET['course_filter_category']) ? $_GET['course_filter_category'] : 'all';
$contact_search = isset($_GET['contact_search']) ? "%" . $_GET['contact_search'] . "%" : "%";

// Fetch stats
$total_users = $con->query("SELECT COUNT(*) as count FROM users WHERE role != 'admin'")->fetch_assoc()['count'] ?? 0;
$deleted_users = $con->query("SELECT COUNT(*) as count FROM deleted_users")->fetch_assoc()['count'] ?? 0;
$total_users_all = $total_users + $deleted_users;
$active_users = $con->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1 AND role != 'admin'")->fetch_assoc()['count'] ?? 0;
$restricted_users = $con->query("SELECT COUNT(*) as count FROM users WHERE is_active = 0 AND role != 'admin'")->fetch_assoc()['count'] ?? 0;
$total_jobs = $con->query("SELECT COUNT(*) as count FROM jobs")->fetch_assoc()['count'] ?? 0;
$total_events = $con->query("SELECT COUNT(*) as count FROM events")->fetch_assoc()['count'] ?? 0;
$total_courses = $con->query("SELECT COUNT(*) as count FROM courses")->fetch_assoc()['count'] ?? 0;

// Fetch data with search and filters (no LIMIT)
$user_query = "SELECT id, first_name, last_name, email, phone, is_active FROM users WHERE id != ? AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
if ($user_filter !== 'all') $user_query .= " AND is_active = ?";
$user_query .= " ORDER BY id DESC";
$users_stmt = $con->prepare($user_query);
if ($user_filter === 'all') {
    $users_stmt->bind_param("isss", $user_id, $user_search, $user_search, $user_search);
} else {
    $is_active = $user_filter === 'active' ? 1 : 0;
    $users_stmt->bind_param("isssi", $user_id, $user_search, $user_search, $user_search, $is_active);
}
$users_stmt->execute();
$users = $users_stmt->get_result();

$job_query = "SELECT id, title, company, date_posted, type FROM jobs WHERE (title LIKE ? OR company LIKE ?)";
if ($job_filter_date === 'recent') $job_query .= " AND date_posted >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
if ($job_filter_type !== 'all') $job_query .= " AND type = ?";
if ($job_filter_company !== 'all') $job_query .= " AND company = ?";
$job_query .= " ORDER BY date_posted DESC"; // Removed LIMIT 3
$jobs_stmt = $con->prepare($job_query);
if ($job_filter_date === 'all' && $job_filter_type === 'all' && $job_filter_company === 'all') {
    $jobs_stmt->bind_param("ss", $job_search, $job_search);
} elseif ($job_filter_type === 'all' && $job_filter_company === 'all') {
    $jobs_stmt->bind_param("ss", $job_search, $job_search);
} elseif ($job_filter_company === 'all') {
    $jobs_stmt->bind_param("sss", $job_search, $job_search, $job_filter_type);
} elseif ($job_filter_type === 'all') {
    $jobs_stmt->bind_param("sss", $job_search, $job_search, $job_filter_company);
} else {
    $jobs_stmt->bind_param("ssss", $job_search, $job_search, $job_filter_type, $job_filter_company);
}
$jobs_stmt->execute();
$jobs = $jobs_stmt->get_result();

$event_query = "SELECT id, title, date, location, type FROM events WHERE title LIKE ?";
if ($event_filter_date === 'recent') $event_query .= " AND date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
if ($event_filter_type !== 'all') $event_query .= " AND type = ?";
$event_query .= " ORDER BY date DESC"; // Removed LIMIT 3
$events_stmt = $con->prepare($event_query);
if ($event_filter_type === 'all') {
    $events_stmt->bind_param("s", $event_search);
} else {
    $events_stmt->bind_param("ss", $event_search, $event_filter_type);
}
$events_stmt->execute();
$events = $events_stmt->get_result();

$course_query = "SELECT id, title, category, duration, created_at FROM courses WHERE (title LIKE ? OR category LIKE ?)";
if ($course_filter_date === 'recent') $course_query .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
if ($course_filter_category !== 'all') $course_query .= " AND category = ?";
$course_query .= " ORDER BY created_at DESC"; // Removed LIMIT 3
$courses_stmt = $con->prepare($course_query);
if ($course_filter_category === 'all') {
    $courses_stmt->bind_param("ss", $course_search, $course_search);
} else {
    $courses_stmt->bind_param("sss", $course_search, $course_search, $course_filter_category);
}
$courses_stmt->execute();
$courses = $courses_stmt->get_result();

$contact_stmt = $con->prepare("SELECT id, name, email, message, attachment, created_at FROM contact_messages WHERE (name LIKE ? OR email LIKE ?) ORDER BY created_at DESC");
$contact_stmt->bind_param("ss", $contact_search, $contact_search);
$contact_stmt->execute();
$contact_messages = $contact_stmt->get_result();

// Fetch unique filter options
$job_types = $con->query("SELECT DISTINCT type FROM jobs WHERE type IS NOT NULL")->fetch_all(MYSQLI_ASSOC);
$job_companies = $con->query("SELECT DISTINCT company FROM jobs WHERE company IS NOT NULL")->fetch_all(MYSQLI_ASSOC);
$event_types = $con->query("SELECT DISTINCT type FROM events WHERE type IS NOT NULL")->fetch_all(MYSQLI_ASSOC);
$course_categories = $con->query("SELECT DISTINCT category FROM courses WHERE category IS NOT NULL")->fetch_all(MYSQLI_ASSOC);

// Admin actions (unchanged)
if (isset($_GET['remove_user']) && is_numeric($_GET['remove_user'])) {
    $remove_id = $_GET['remove_user'];
    $con->query("INSERT INTO deleted_users (id) VALUES ($remove_id)");
    $delete_stmt = $con->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    $delete_stmt->bind_param("i", $remove_id);
    $delete_stmt->execute();
    header("Location: dashboard.php");
    exit();
}

if (isset($_GET['restrict']) && is_numeric($_GET['restrict'])) {
    $restrict_id = $_GET['restrict'];
    $update_stmt = $con->prepare("UPDATE users SET is_active = 0 WHERE id = ? AND role != 'admin'");
    $update_stmt->bind_param("i", $restrict_id);
    $update_stmt->execute();
    header("Location: dashboard.php");
    exit();
}

if (isset($_GET['activate']) && is_numeric($_GET['activate'])) {
    $activate_id = $_GET['activate'];
    $update_stmt = $con->prepare("UPDATE users SET is_active = 1 WHERE id = ? AND role != 'admin'");
    $update_stmt->bind_param("i", $activate_id);
    $update_stmt->execute();
    header("Location: dashboard.php");
    exit();
}

if (isset($_GET['delete_job']) && is_numeric($_GET['delete_job'])) {
    $delete_id = $_GET['delete_job'];
    $delete_stmt = $con->prepare("DELETE FROM jobs WHERE id = ?");
    $delete_stmt->bind_param("i", $delete_id);
    $delete_stmt->execute();
    header("Location: dashboard.php");
    exit();
}

if (isset($_GET['delete_event']) && is_numeric($_GET['delete_event'])) {
    $delete_id = $_GET['delete_event'];
    $delete_stmt = $con->prepare("DELETE FROM events WHERE id = ?");
    $delete_stmt->bind_param("i", $delete_id);
    $delete_stmt->execute();
    header("Location: dashboard.php");
    exit();
}

if (isset($_GET['delete_course']) && is_numeric($_GET['delete_course'])) {
    $delete_id = $_GET['delete_course'];
    $delete_stmt = $con->prepare("DELETE FROM courses WHERE id = ?");
    $delete_stmt->bind_param("i", $delete_id);
    $delete_stmt->execute();
    header("Location: dashboard.php");
    exit();
}

if (isset($_GET['delete_attachment']) && is_numeric($_GET['delete_attachment'])) {
    $id = $_GET['delete_attachment'];
    $stmt = $con->prepare("SELECT attachment FROM contact_messages WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result && $result['attachment']) {
        $file_path = 'uploads/contact/' . $result['attachment'];
        if (file_exists($file_path)) unlink($file_path);
    }
    $stmt = $con->prepare("UPDATE contact_messages SET attachment = NULL WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | SmartCareer</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/admin_dashboard.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <aside class="sidebar" id="sidebar">
        <div class="logo">
            <a href="../pages/home.php" class="flex items-center space-x-2">
                <span class="text-blue-600 font-bold text-xl">Smart</span><span class="text-gray-900 font-bold text-xl">Career</span>
            </a>
            <div class="toggle-btn" id="toggleSidebar"><i class="fas fa-bars text-white"></i></div>
        </div>
        <nav>
            <a href="#" class="nav-item active"><i class="fas fa-home mr-3"></i><span class="nav-text">Admin Dashboard</span></a>
            <a href="../services/dashboard.php" class="nav-item"><i class="fas fa-user mr-3"></i><span class="nav-text">User Dashboard</span></a>
            <a href="../services/jobs/jobs.php" class="nav-item"><i class="fas fa-briefcase mr-3"></i><span class="nav-text">Manage Jobs</span></a>
            <a href="../services/learning.php" class="nav-item"><i class="fas fa-book mr-3"></i><span class="nav-text">Manage Courses</span></a>
            <a href="../services/events.php" class="nav-item"><i class="fas fa-calendar mr-3"></i><span class="nav-text">Manage Events</span></a>
            <a href="../services/bot.php" class="nav-item"><i class="fas fa-robot mr-3"></i><span class="nav-text">AI Logs</span></a>
            <a href="../logout.php" class="nav-item"><i class="fas fa-sign-out-alt mr-3"></i><span class="nav-text">Logout</span></a>
        </nav>
    </aside>

    <main class="main-content" id="mainContent">
        <div class="mb-8 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <img src="<?php echo $profilePicture; ?>" alt="Profile" class="w-12 h-12 rounded-full object-cover border-2 border-blue-500">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Welcome, <?php echo htmlspecialchars($firstName); ?>!</h1>
                    <p class="text-sm text-gray-500">Admin Control Panel</p>
                </div>
            </div>
            <div class="text-gray-500"><?php echo date('M d, Y'); ?></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="stats-card bg-gradient-to-r from-blue-600 to-blue-800 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium">Total Users (All Time)</p>
                        <h3 class="text-2xl font-bold"><?php echo $total_users_all; ?></h3>
                    </div>
                    <i class="fas fa-users text-3xl opacity-50"></i>
                </div>
            </div>
            <div class="stats-card bg-gradient-to-r from-blue-600 to-blue-800 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium">Active Users</p>
                        <h3 class="text-2xl font-bold"><?php echo $active_users; ?></h3>
                    </div>
                    <i class="fas fa-user-check text-3xl opacity-50"></i>
                </div>
            </div>
            <div class="stats-card bg-gradient-to-r from-blue-600 to-blue-800 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium">Restricted Users</p>
                        <h3 class="text-2xl font-bold"><?php echo $restricted_users; ?></h3>
                    </div>
                    <i class="fas fa-user-slash text-3xl opacity-50"></i>
                </div>
            </div>
            <div class="stats-card bg-gradient-to-r from-blue-600 to-blue-800 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium">Total Jobs Available</p>
                        <h3 class="text-2xl font-bold"><?php echo $total_jobs; ?></h3>
                    </div>
                    <i class="fas fa-briefcase text-3xl opacity-50"></i>
                </div>
            </div>
            <div class="stats-card bg-gradient-to-r from-blue-600 to-blue-800 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium">Total Events</p>
                        <h3 class="text-2xl font-bold"><?php echo $total_events; ?></h3>
                    </div>
                    <i class="fas fa-calendar text-3xl opacity-50"></i>
                </div>
            </div>
            <div class="stats-card bg-gradient-to-r from-blue-600 to-blue-800 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium">Total Courses</p>
                        <h3 class="text-2xl font-bold"><?php echo $total_courses; ?></h3>
                    </div>
                    <i class="fas fa-book text-3xl opacity-50"></i>
                </div>
            </div>
        </div>

        <div class="card mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Registered Users</h2>
            <form method="GET" class="mb-4 flex space-x-4 filter-form" data-section="users">
                <input type="text" name="user_search" placeholder="Search by name or email" value="<?php echo isset($_GET['user_search']) ? htmlspecialchars($_GET['user_search']) : ''; ?>" class="w-full px-4 py-2 border rounded-md">
                <select name="user_filter" class="px-4 py-2 border rounded-md">
                    <option value="all" <?php echo $user_filter === 'all' ? 'selected' : ''; ?>>All</option>
                    <option value="active" <?php echo $user_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="restricted" <?php echo $user_filter === 'restricted' ? 'selected' : ''; ?>>Restricted</option>
                </select>
                <button type="submit" class="btn bg-blue-600 text-white hidden">Search</button>
            </form>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['id']); ?></td>
                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                <td>
                                    <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo $user['is_active'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                        <?php echo $user['is_active'] ? 'Active' : 'Restricted'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['is_active']): ?>
                                        <a href="?restrict=<?php echo $user['id']; ?>" class="btn btn-warning">Restrict</a>
                                    <?php else: ?>
                                        <a href="?activate=<?php echo $user['id']; ?>" class="btn btn-success">Activate</a>
                                    <?php endif; ?>
                                    <a href="?remove_user=<?php echo $user['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to remove this user?');">Remove</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Jobs</h2>
            <form method="GET" class="mb-4 flex flex-wrap gap-4 filter-form" data-section="jobs">
                <input type="text" name="job_search" placeholder="Search by title or company" value="<?php echo isset($_GET['job_search']) ? htmlspecialchars($_GET['job_search']) : ''; ?>" class="w-full md:w-1/4 px-4 py-2 border rounded-md">
                <select name="job_filter_date" class="px-4 py-2 border rounded-md">
                    <option value="all" <?php echo $job_filter_date === 'all' ? 'selected' : ''; ?>>All Dates</option>
                    <option value="recent" <?php echo $job_filter_date === 'recent' ? 'selected' : ''; ?>>Recent (7 days)</option>
                </select>
                <select name="job_filter_type" class="px-4 py-2 border rounded-md">
                    <option value="all" <?php echo $job_filter_type === 'all' ? 'selected' : ''; ?>>All Types</option>
                    <?php foreach ($job_types as $type): ?>
                        <option value="<?php echo htmlspecialchars($type['type']); ?>" <?php echo $job_filter_type === $type['type'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($type['type']); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="job_filter_company" class="px-4 py-2 border rounded-md">
                    <option value="all" <?php echo $job_filter_company === 'all' ? 'selected' : ''; ?>>All Companies</option>
                    <?php foreach ($job_companies as $company): ?>
                        <option value="<?php echo htmlspecialchars($company['company']); ?>" <?php echo $job_filter_company === $company['company'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($company['company']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn bg-blue-600 text-white hidden">Search</button>
            </form>
            <div class="table-container limited-height">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Company</th>
                            <th>Type</th>
                            <th>Date Posted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($job = $jobs->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($job['id']); ?></td>
                                <td><?php echo htmlspecialchars($job['title']); ?></td>
                                <td><?php echo htmlspecialchars($job['company']); ?></td>
                                <td><?php echo htmlspecialchars($job['type']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($job['date_posted'])); ?></td>
                                <td>
                                    <a href="?delete_job=<?php echo $job['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this job?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Events</h2>
            <form method="GET" class="mb-4 flex flex-wrap gap-4 filter-form" data-section="events">
                <input type="text" name="event_search" placeholder="Search by title" value="<?php echo isset($_GET['event_search']) ? htmlspecialchars($_GET['event_search']) : ''; ?>" class="w-full md:w-1/3 px-4 py-2 border rounded-md">
                <select name="event_filter_date" class="px-4 py-2 border rounded-md">
                    <option value="all" <?php echo $event_filter_date === 'all' ? 'selected' : ''; ?>>All Dates</option>
                    <option value="recent" <?php echo $event_filter_date === 'recent' ? 'selected' : ''; ?>>Recent (7 days)</option>
                </select>
                <select name="event_filter_type" class="px-4 py-2 border rounded-md">
                    <option value="all" <?php echo $event_filter_type === 'all' ? 'selected' : ''; ?>>All Types</option>
                    <?php foreach ($event_types as $type): ?>
                        <option value="<?php echo htmlspecialchars($type['type']); ?>" <?php echo $event_filter_type === $type['type'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($type['type']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn bg-blue-600 text-white hidden">Search</button>
            </form>
            <div class="table-container limited-height">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Location</th>
                            <th>Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($event = $events->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($event['id']); ?></td>
                                <td><?php echo htmlspecialchars($event['title']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($event['date'])); ?></td>
                                <td><?php echo htmlspecialchars($event['location']); ?></td>
                                <td><?php echo htmlspecialchars($event['type']); ?></td>
                                <td>
                                    <a href="?delete_event=<?php echo $event['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this event?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Courses</h2>
            <form method="GET" class="mb-4 flex flex-wrap gap-4 filter-form" data-section="courses">
                <input type="text" name="course_search" placeholder="Search by title or category" value="<?php echo isset($_GET['course_search']) ? htmlspecialchars($_GET['course_search']) : ''; ?>" class="w-full md:w-1/3 px-4 py-2 border rounded-md">
                <select name="course_filter_date" class="px-4 py-2 border rounded-md">
                    <option value="all" <?php echo $course_filter_date === 'all' ? 'selected' : ''; ?>>All Dates</option>
                    <option value="recent" <?php echo $course_filter_date === 'recent' ? 'selected' : ''; ?>>Recent (7 days)</option>
                </select>
                <select name="course_filter_category" class="px-4 py-2 border rounded-md">
                    <option value="all" <?php echo $course_filter_category === 'all' ? 'selected' : ''; ?>>All Categories</option>
                    <?php foreach ($course_categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['category']); ?>" <?php echo $course_filter_category === $category['category'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['category']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn bg-blue-600 text-white hidden">Search</button>
            </form>
            <div class="table-container limited-height">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Duration</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($course = $courses->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($course['id']); ?></td>
                                <td><?php echo htmlspecialchars($course['title']); ?></td>
                                <td><?php echo htmlspecialchars($course['category']); ?></td>
                                <td><?php echo htmlspecialchars($course['duration']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($course['created_at'])); ?></td>
                                <td>
                                    <a href="?delete_course=<?php echo $course['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this course?');">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-8">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Contact Messages</h2>
            <form method="GET" class="mb-4 flex space-x-4 filter-form" data-section="contact">
                <input type="text" name="contact_search" placeholder="Search by name or email" value="<?php echo isset($_GET['contact_search']) ? htmlspecialchars($_GET['contact_search']) : ''; ?>" class="w-full px-4 py-2 border rounded-md">
                <button type="submit" class="btn bg-blue-600 text-white hidden">Search</button>
            </form>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Message</th>
                            <th>Attachment</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($msg = $contact_messages->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($msg['id']); ?></td>
                                <td><?php echo htmlspecialchars($msg['name']); ?></td>
                                <td><?php echo htmlspecialchars($msg['email']); ?></td>
                                <td><?php echo htmlspecialchars(substr($msg['message'], 0, 50)) . (strlen($msg['message']) > 50 ? '...' : ''); ?></td>
                                <td>
                                    <?php if ($msg['attachment']): ?>
                                        <a href="/smartcareer/uploads/contact/<?php echo htmlspecialchars($msg['attachment']); ?>" download class="text-blue-600 hover:underline">Download</a>
                                    <?php else: ?>
                                        None
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($msg['created_at'])); ?></td>
                                <td>
                                    <?php if ($msg['attachment']): ?>
                                        <a href="?delete_attachment=<?php echo $msg['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this attachment?');">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="../../assets/js/admin_dashboard.js"></script>
</body>
</html>
<?php 
$users_stmt->close();
$jobs_stmt->close();
$events_stmt->close();
$courses_stmt->close();
$contact_stmt->close();
$con->close(); 
?>