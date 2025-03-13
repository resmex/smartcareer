<?php
session_start();
include '../../includes/connect.php';

// Check if the user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../pages/login.php?error=unauthorized");
    exit();
}

$user_id = $_SESSION['user_id'];
$firstName = $_SESSION['first_name'];

// Fetch profile picture with updated path
$profilePicture = '/smartcareer/uploads/profile_default.jpeg'; // Relative path for web access
$stmt = $con->prepare("SELECT profile_picture FROM user_profiles WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $user_profile = $result->fetch_assoc();
    $profilePicture = !empty($user_profile['profile_picture']) ? '/smartcareer/uploads/' . htmlspecialchars($user_profile['profile_picture']) : $profilePicture;
}
$stmt->close();

// Fetch all registered users
$sql = "SELECT id, first_name, last_name, email, phone, role, is_active FROM users WHERE id != ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$users = $stmt->get_result();

// Admin actions
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    $delete_sql = "DELETE FROM users WHERE id = ? AND role != 'admin'";
    $delete_stmt = $con->prepare($delete_sql);
    $delete_stmt->bind_param("i", $remove_id);
    $delete_stmt->execute();
    header("Location: dashboard.php");
    exit();
}

if (isset($_GET['restrict']) && is_numeric($_GET['restrict'])) {
    $restrict_id = $_GET['restrict'];
    $update_sql = "UPDATE users SET is_active = 0 WHERE id = ? AND role != 'admin'";
    $update_stmt = $con->prepare($update_sql);
    $update_stmt->bind_param("i", $restrict_id);
    $update_stmt->execute();
    header("Location: dashboard.php");
    exit();
}

if (isset($_GET['activate']) && is_numeric($_GET['activate'])) {
    $activate_id = $_GET['activate'];
    $update_sql = "UPDATE users SET is_active = 1 WHERE id = ? AND role != 'admin'";
    $update_stmt = $con->prepare($update_sql);
    $update_stmt->bind_param("i", $activate_id);
    $update_stmt->execute();
    header("Location: dashboard.php");
    exit();
}

// Stats
$total_users = $users->num_rows;
$active_users = $con->query("SELECT COUNT(*) as count FROM users WHERE is_active = 1 AND role != 'admin'")->fetch_assoc()['count'];
$restricted_users = $con->query("SELECT COUNT(*) as count FROM users WHERE is_active = 0 AND role != 'admin'")->fetch_assoc()['count'];
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
    <style>
        :root {
            --primary-color: #4f46e5; /* Indigo */
            --secondary-color: #1e3a8a; /* Deep blue */
            --accent-color: #f59e0b; /* Amber */
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 80px;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: #ffffff;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
            transition: width 0.3s ease;
            z-index: 1000;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar .logo {
            padding: 1.5rem;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar.collapsed .logo-text {
            display: none;
        }

        .sidebar.collapsed .nav-text {
            display: none;
        }

        .sidebar .nav-item {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            color: #4b5563;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .sidebar .nav-item:hover, .sidebar .nav-item.active {
            background: var(--primary-color);
            color: white;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .stats-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
        }

        .table-container {
            max-height: 500px;
            overflow-y: auto;
            border-radius: 12px;
            background: white;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 1rem;
            text-align: left;
        }

        th {
            background: #f9fafb;
            font-weight: 600;
            color: #6b7280;
            position: sticky;
            top: 0;
        }

        tr:hover {
            background: #f9fafb;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-danger { background: #ef4444; color: white; }
        .btn-danger:hover { background: #dc2626; }
        .btn-warning { background: #f59e0b; color: white; }
        .btn-warning:hover { background: #d97706; }
        .btn-success { background: #10b981; color: white; }
        .btn-success:hover { background: #059669; }

        .toggle-btn {
            background: var(--primary-color);
            color: white;
            padding: 0.5rem;
            border-radius: 50%;
            cursor: pointer;
            position: absolute;
            top: 1.5rem;
            right: 1rem;
            z-index: 1001;
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar.collapsed .toggle-btn {
            right: 0.75rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: var(--sidebar-collapsed-width);
            }

            .main-content {
                margin-left: var(--sidebar-collapsed-width);
            }

            .sidebar .logo-text, .sidebar .nav-text {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="logo">
            <span class="logo-text">SmartCareer</span>
            <div class="toggle-btn" id="toggleSidebar"><i class="fas fa-bars"></i></div>
        </div>
        <nav>
            <a href="#" class="nav-item active"><i class="fas fa-home mr-3"></i><span class="nav-text">Admin Dashboard</span></a>
            <a href="../services/dashboard.php" class="nav-item"><i class="fas fa-user mr-3"></i><span class="nav-text">User Dashboard</span></a>
            <a href="../services/jobs.php" class="nav-item"><i class="fas fa-briefcase mr-3"></i><span class="nav-text">Manage Jobs</span></a>
            <a href="../services/learning.php" class="nav-item"><i class="fas fa-book mr-3"></i><span class="nav-text">Manage Courses</span></a>
            <a href="../services/events.php" class="nav-item"><i class="fas fa-calendar mr-3"></i><span class="nav-text">Manage Events</span></a>
            <a href="../services/bot.php" class="nav-item"><i class="fas fa-robot mr-3"></i><span class="nav-text">AI Logs</span></a>
            <a href="../logout.php" class="nav-item"><i class="fas fa-sign-out-alt mr-3"></i><span class="nav-text">Logout</span></a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Welcome Section -->
        <div class="mb-8 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <img src="<?php echo $profilePicture; ?>" alt="Profile" class="w-12 h-12 rounded-full object-cover border-2 border-gray-200">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Welcome, <?php echo htmlspecialchars($firstName); ?>!</h1>
                    <p class="text-sm text-gray-500">Admin Control Panel</p>
                </div>
            </div>
            <div class="text-gray-500"><?php echo date('M d, Y'); ?></div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="stats-card">
                <div>
                    <p class="text-sm font-medium">Total Users</p>
                    <h3 class="text-2xl font-bold"><?php echo $total_users; ?></h3>
                </div>
                <i class="fas fa-users text-3xl opacity-50"></i>
            </div>
            <div class="stats-card">
                <div>
                    <p class="text-sm font-medium">Active Users</p>
                    <h3 class="text-2xl font-bold"><?php echo $active_users; ?></h3>
                </div>
                <i class="fas fa-user-check text-3xl opacity-50"></i>
            </div>
            <div class="stats-card">
                <div>
                    <p class="text-sm font-medium">Restricted Users</p>
                    <h3 class="text-2xl font-bold"><?php echo $restricted_users; ?></h3>
                </div>
                <i class="fas fa-user-slash text-3xl opacity-50"></i>
            </div>
        </div>

        <!-- Registered Users -->
        <div class="card">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Registered Users</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th> <!-- Added ID column -->
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
                                <td><?php echo htmlspecialchars($user['id']); ?></td> <!-- Display user ID -->
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
                                    <a href="?remove=<?php echo $user['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to remove this user?');">Remove</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Admin Quick Actions -->
        <div class="mt-8 card">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Quick Actions</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <a href="../services/jobs.php" class="card p-4 text-center hover:bg-blue-50"><i class="fas fa-briefcase text-2xl text-blue-600 mb-2"></i><p class="text-sm font-medium">Manage Jobs</p></a>
                <a href="../services/learning.php" class="card p-4 text-center hover:bg-green-50"><i class="fas fa-book text-2xl text-green-600 mb-2"></i><p class="text-sm font-medium">Manage Courses</p></a>
                <a href="../services/events.php" class="card p-4 text-center hover:bg-purple-50"><i class="fas fa-calendar text-2xl text-purple-600 mb-2"></i><p class="text-sm font-medium">Manage Events</p></a>
                <a href="../services/bot.php" class="card p-4 text-center hover:bg-yellow-50"><i class="fas fa-robot text-2xl text-yellow-600 mb-2"></i><p class="text-sm font-medium">AI Logs</p></a>
            </div>
        </div>
    </main>

    <script>
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const toggleSidebar = document.getElementById('toggleSidebar');

        toggleSidebar.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        });
    </script>
</body>
</html>
<?php $con->close(); ?>