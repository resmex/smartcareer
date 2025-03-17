<?php
session_start();
include '../../includes/connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

$userId = (int)$_SESSION['user_id'];

// Fetch profile data from user_profiles table
$stmt = $con->prepare("SELECT first_name, last_name, job_title, preferred_job_type, location, event_interests, skills, profile_picture, email, phone FROM user_profiles WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $profileData = $result->fetch_assoc();
    $_SESSION['first_name'] = $profileData['first_name'] ?? $_SESSION['first_name'] ?? 'John';
    $_SESSION['last_name'] = $profileData['last_name'] ?? $_SESSION['last_name'] ?? 'Doe';
    $_SESSION['job_title'] = $profileData['job_title'] ?? $_SESSION['job_title'] ?? 'Job Seeker';
    $_SESSION['preferred_job_type'] = $profileData['preferred_job_type'] ?? $_SESSION['preferred_job_type'] ?? 'Full-time';
    $_SESSION['location'] = $profileData['location'] ?? $_SESSION['location'] ?? 'Unknown';
    $_SESSION['event_interests'] = $profileData['event_interests'] ?? $_SESSION['event_interests'] ?? 'Career Fairs';
    $_SESSION['skills'] = $profileData['skills'] ?? $_SESSION['skills'] ?? 'UI Design, UX Research, Prototyping';
    $_SESSION['email'] = $profileData['email'] ?? $_SESSION['email'] ?? 'example@email.com';
    $_SESSION['phone'] = $profileData['phone'] ?? $_SESSION['phone'] ?? '123-456-7890';
    $_SESSION['profile_picture'] = $profileData['profile_picture'] ?? $_SESSION['profile_picture'] ?? '';
} else {
    if (!isset($_SESSION['first_name'])) {
        $stmt = $con->prepare("SELECT first_name FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $_SESSION['first_name'] = $result->num_rows > 0 ? $result->fetch_assoc()['first_name'] : 'User';
    }
}
$stmt->close();

// Set profile variables with defaults
$firstName = $_SESSION['first_name'] ?? 'John';
$lastName = $_SESSION['last_name'] ?? 'Doe';
$jobTitle = $_SESSION['job_title'] ?? 'Job Seeker';
$location = $_SESSION['location'] ?? 'Unknown';
$preferredJobType = $_SESSION['preferred_job_type'] ?? 'Full-time';
$eventInterests = $_SESSION['event_interests'] ?? 'Career Fairs';
$skills = $_SESSION['skills'] ?? 'UI Design, UX Research, Prototyping';
$email = $_SESSION['email'] ?? 'example@email.com';
$phone = $_SESSION['phone'] ?? '123-456-7890';

// Set profile picture with database value or default
$defaultProfilePic = '/smartcareer/uploads/profile_default.jpeg';
$profilePicture = !empty($_SESSION['profile_picture']) ? '/smartcareer/uploads/' . htmlspecialchars($_SESSION['profile_picture']) : $defaultProfilePic;

// Fetch stats
$stmt = $con->prepare("SELECT COUNT(*) as applied FROM job_applications WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$applied_jobs = $stmt->get_result()->fetch_assoc()['applied'] ?? 0;
$stmt->close();

$stmt = $con->prepare("SELECT COUNT(*) as enrolled FROM course_enrollments WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$courses_enrolled = $stmt->get_result()->fetch_assoc()['enrolled'] ?? 0;
$stmt->close();

$stmt = $con->prepare("SELECT COUNT(*) as enrolled FROM event_enrollments WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$events_enrolled = $stmt->get_result()->fetch_assoc()['enrolled'] ?? 0;
$stmt->close();

// Fetch trending courses, events, jobs
$trendingCoursesStmt = $con->prepare("SELECT id, title, platform, enrollment_count FROM courses ORDER BY enrollment_count DESC LIMIT 2");
$trendingCoursesStmt->execute();
$trendingCourses = $trendingCoursesStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$trendingCoursesStmt->close();

$upcomingEventsStmt = $con->prepare("SELECT id, title, date FROM events WHERE date >= NOW() ORDER BY date ASC LIMIT 2");
$upcomingEventsStmt->execute();
$upcomingEvents = $upcomingEventsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$upcomingEventsStmt->close();

$recentJobsStmt = $con->prepare("SELECT id, title, company FROM jobs ORDER BY created_at DESC LIMIT 2");
$recentJobsStmt->execute();
$recentJobs = $recentJobsStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$recentJobsStmt->close();

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['profile_submit'])) {
    $fields = ['first_name', 'last_name', 'job_title', 'preferred_job_type', 'location', 'event_interests', 'email', 'phone'];
    $data = [];
    foreach ($fields as $field) {
        $data[$field] = isset($_POST[$field]) ? trim($_POST[$field]) : '';
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!preg_match('/^[0-9]{10,15}$/', $data['phone'])) {
        $error = "Invalid phone number. Please enter 10-15 digits.";
    } else {
        $skills = isset($_POST['skills']) ? trim($_POST['skills']) : '';
        $profile_picture = $_SESSION['profile_picture'] ?? '';

        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
            $file = $_FILES['profile_picture'];
            $allowed_types = ['jpg', 'jpeg', 'png'];
            $file_type = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

            if (!in_array($file_type, $allowed_types)) {
                $error = "Invalid file type. Only JPG, JPEG, PNG allowed.";
            } elseif ($file['size'] > 2 * 1024 * 1024) {
                $error = "File size exceeds 2MB.";
            } else {
                $upload_dir = '../../uploads/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
                $new_file_name = uniqid() . "." . $file_type;
                $profile_picture_path = $upload_dir . $new_file_name;

                if (move_uploaded_file($file['tmp_name'], $profile_picture_path)) {
                    $profile_picture = $new_file_name;
                } else {
                    $error = "Failed to upload profile picture.";
                }
            }
        }

        if (!isset($error)) {
            $stmt = $con->prepare("SELECT user_id FROM user_profiles WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $profile_exists = $stmt->get_result()->num_rows > 0;
            $stmt->close();

            if ($profile_exists) {
                $sql = "UPDATE user_profiles SET first_name=?, last_name=?, job_title=?, preferred_job_type=?, location=?, event_interests=?, skills=?, profile_picture=?, email=?, phone=?, updated_at=NOW() WHERE user_id=?";
                $stmt = $con->prepare($sql);
                $stmt->bind_param("ssssssssssi", $data['first_name'], $data['last_name'], $data['job_title'], $data['preferred_job_type'], $data['location'], $data['event_interests'], $skills, $profile_picture, $data['email'], $data['phone'], $userId);
            } else {
                $sql = "INSERT INTO user_profiles (user_id, first_name, last_name, job_title, preferred_job_type, location, event_interests, skills, profile_picture, email, phone) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $con->prepare($sql);
                $stmt->bind_param("issssssssss", $userId, $data['first_name'], $data['last_name'], $data['job_title'], $data['preferred_job_type'], $data['location'], $data['event_interests'], $skills, $profile_picture, $data['email'], $data['phone']);
            }

            if ($stmt->execute()) {
                $_SESSION = array_merge($_SESSION, $data);
                $_SESSION['skills'] = $skills;
                $_SESSION['profile_picture'] = $profile_picture;
                $profilePicture = !empty($profile_picture) ? '/smartcareer/uploads/' . htmlspecialchars($profile_picture) : $defaultProfilePic;
            } else {
                $error = "Error updating profile: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCareer Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/smartcareer/assets/css/dashboard.css" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="logo">
            <span class="smart">Smart</span><span class="career">Career</span>
            <div class="toggle-btn" id="toggleSidebar"><i class="fas fa-bars"></i></div>
        </div>
        <div class="profile-header">
            <img src="<?php echo $profilePicture; ?>" alt="Profile" id="sidebarProfilePic">
            <div class="info">
                <h3><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></h3>
                <p><?php echo htmlspecialchars($jobTitle); ?></p>
            </div>
        </div>
        <nav>
            <a href="../services/jobs.php" class="nav-item"><i class="fas fa-briefcase"></i><span class="nav-text">Opportunities</span></a>
            <a href="../services/earn.php" class="nav-item"><i class="fas fa-money-bill-wave"></i><span class="nav-text">Earn Online</span></a>
            <a href="../services/bot.php" class="nav-item"><i class="fas fa-robot"></i><span class="nav-text">AI Counseling</span></a>
            <a href="../services/learning.php" class="nav-item"><i class="fas fa-book"></i><span class="nav-text">Learning</span></a>
            <a href="../services/events.php" class="nav-item"><i class="fas fa-users"></i><span class="nav-text">Events</span></a>
            <a href="javascript:void(0);" class="nav-item" onclick="toggleProfileModal()"><i class="fas fa-user"></i><span class="nav-text">View Profile</span></a>
            <a href="../logout.php" class="nav-item"><i class="fas fa-sign-out-alt"></i><span class="nav-text">Logout</span></a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Hero Section -->
        <div class="hero-section mb-8 animate-slide-in">
            <div class="flex items-center space-x-6">
                <div>
                    <h1 class="text-4xl font-bold">Welcome, <?php echo htmlspecialchars($firstName); ?>!</h1>
                    <p class="text-xl mt-2">Your career journey starts here.</p>
                </div>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
            <div class="stat-card jobs animate-slide-in" style="animation-delay: 0.1s;">
                <p class="text-sm uppercase font-medium tracking-wide">Jobs Applied</p>
                <h3 class="text-3xl font-bold mt-2"><?php echo $applied_jobs; ?></h3>
                <a href="../services/enrolled_jobs.php" class="mt-3 inline-block text-white font-medium hover:underline">View Applications</a>
            </div>
            <div class="stat-card courses animate-slide-in" style="animation-delay: 0.2s;">
                <p class="text-sm uppercase font-medium tracking-wide">Courses Joined</p>
                <h3 class="text-3xl font-bold mt-2"><?php echo $courses_enrolled; ?></h3>
                <a href="../services/enrolled_courses.php" class="mt-3 inline-block text-white font-medium hover:underline">Check Courses</a>
            </div>
            <div class="stat-card events animate-slide-in" style="animation-delay: 0.3s;">
                <p class="text-sm uppercase font-medium tracking-wide">Events Joined</p>
                <h3 class="text-3xl font-bold mt-2"><?php echo $events_enrolled; ?></h3>
                <a href="../services/enrolled_events.php" class="mt-3 inline-block text-white font-medium hover:underline">See Events</a>
            </div>
        </div>

        <!-- SmartCareer Services -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Your Career Tools</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6">
                <div class="service-card animate-slide-in" style="animation-delay: 0.1s;">
                    <i class="fas fa-briefcase text-3xl text-blue-600 mb-3"></i>
                    <h3 class="text-lg font-semibold text-gray-800">Find Jobs</h3>
                    <p class="text-sm text-gray-600 mt-1">Find your dream role.</p>
                    <a href="../services/jobs.php" class="mt-3 inline-block text-blue-600 font-medium hover:underline">Start Now</a>
                </div>
                <div class="service-card animate-slide-in" style="animation-delay: 0.2s;">
                    <i class="fas fa-money-bill-wave text-3xl text-green-600 mb-3"></i>
                    <h3 class="text-lg font-semibold text-gray-800">Earn Cash</h3>
                    <p class="text-sm text-gray-600 mt-1">Make money online.</p>
                    <a href="../services/earn.php" class="mt-3 inline-block text-blue-600 font-medium hover:underline">See how</a>
                </div>
                <div class="service-card animate-slide-in" style="animation-delay: 0.3s;">
                    <i class="fas fa-robot text-3xl text-purple-600 mb-3"></i>
                    <h3 class="text-lg font-semibold text-gray-800">AI Help</h3>
                    <p class="text-sm text-gray-600 mt-1">Career tips.</p>
                    <a href="../services/bot.php" class="mt-3 inline-block text-blue-600 font-medium hover:underline">Ask Now</a>
                </div>
                <div class="service-card animate-slide-in" style="animation-delay: 0.4s;">
                    <i class="fas fa-book text-3xl mb-3" style="color: #0d9488;"></i>
                    <h3 class="text-lg font-semibold text-gray-800">Build Skills</h3>
                    <p class="text-sm text-gray-600 mt-1">Learn top skills.</p>
                    <a href="../services/learning.php" class="mt-3 inline-block text-blue-600 font-medium hover:underline">Grow Now</a>
                </div>
                <div class="service-card animate-slide-in" style="animation-delay: 0.5s;">
                    <i class="fas fa-users text-3xl mb-3" style="color: #ea580c;"></i>
                    <h3 class="text-lg font-semibold text-gray-800">Events</h3>
                    <p class="text-sm text-gray-600 mt-1">Meet, Connect and learn.</p>
                    <a href="../services/events.php" class="mt-3 inline-block text-blue-600 font-medium hover:underline">Join Now</a>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="card animate-slide-in" style="animation-delay: 0.6s;">
                <h2 class="text-md font-semibold text-gray-800 mb-3">New Jobs | Opportunities</h2>
                <?php if (empty($recentJobs)): ?>
                    <p class="text-gray-500 text-sm">No new jobs yet.</p>
                <?php else: ?>
                    <div class="space-y-2">
                        <?php foreach ($recentJobs as $job): ?>
                            <div class="list-item">
                                <p class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($job['title']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($job['company']); ?></p>
                            </div>
                        <?php endforeach; ?>
                        <a href="../services/jobs.php" class="mt-2 inline-block text-blue-600 text-sm hover:underline">Find More and Apply</a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card animate-slide-in" style="animation-delay: 0.7s;">
                <h2 class="text-md font-semibold text-gray-800 mb-3">Improve Your Skills</h2>
                <?php if (empty($trendingCourses)): ?>
                    <p class="text-gray-500 text-sm">No popular skills available.</p>
                <?php else: ?>
                    <div class="space-y-2">
                        <?php foreach ($trendingCourses as $course): ?>
                            <div class="list-item">
                                <p class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($course['title']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($course['platform']); ?></p>
                            </div>
                        <?php endforeach; ?>
                        <a href="../services/learning.php" class="mt-2 inline-block text-blue-600 text-sm hover:underline">More resources for Skills in Demand</a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card animate-slide-in" style="animation-delay: 0.8s;">
                <h2 class="text-md font-semibold text-gray-800 mb-3">Upcoming Events</h2>
                <?php if (empty($upcomingEvents)): ?>
                    <p class="text-gray-500 text-sm">No events coming up.</p>
                <?php else: ?>
                    <div class="space-y-2">
                        <?php foreach ($upcomingEvents as $event): ?>
                            <div class="list-item">
                                <p class="text-sm font-medium text-gray-700"><?php echo htmlspecialchars($event['title']); ?></p>
                                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($event['date']); ?></p>
                            </div>
                        <?php endforeach; ?>
                        <a href="../services/events.php" class="mt-2 inline-block text-blue-600 text-sm hover:underline">More events to participate</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- AI Career Preparation Section -->
        <div class="mb-8">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Boost Your Career with AI</h2>
            <div class="bot-card animate-slide-in" style="animation-delay: 0.9s;">
                <div class="flex items-center space-x-4 mb-4">
                    <i class="fas fa-robot text-4xl"></i>
                    <div>
                        <h3 class="text-xl font-bold">Smart AI Counseling</h3>
                        <p class="text-sm">Get personalized career guidance.</p>
                    </div>
                </div>
                <ul class="space-y-3">
                    <li class="flex items-center space-x-2"><i class="fas fa-check-circle text-green-300"></i><a href="../services/bot.php" class="text-sm hover:underline">Get Ready for Interview</a></li>
                    <li class="flex items-center space-x-2"><i class="fas fa-check-circle text-green-300"></i><a href="../services/bot.php" class="text-sm hover:underline">What to Know Before Interview</a></li>
                    <li class="flex items-center space-x-2"><i class="fas fa-check-circle text-green-300"></i><a href="../services/bot.php" class="text-sm hover:underline">Assess Your Skills</a></li>
                    <li class="flex items-center space-x-2"><i class="fas fa-check-circle text-green-300"></i><a href="../services/bot.php" class="text-sm hover:underline">Know Your Capabilities</a></li>
                </ul>
                <a href="../services/bot.php" class="mt-4 inline-block px-6 py-2 bg-white text-purple-600 rounded-full font-semibold hover:bg-purple-100 transition">Ask AI Now</a>
            </div>
        </div>

        <!-- Call to Action -->
        <div class="mt-8 card hero-section-cta animate-pulse">
            <h2 class="text-2xl font-bold">Ready to Take the Next Step?</h2>
            <p class="text-lg mt-2">Unlock new opportunities with SmartCareer—your future awaits!</p>
            <div class="mt-4 space-x-4">
                <a href="../services/jobs.php" class="inline-block px-6 py-2 bg-white text-blue-600 rounded-full font-semibold hover:bg-blue-100 transition">Find a Job</a>
                <a href="../services/learning.php" class="inline-block px-6 py-2 bg-white text-green-600 rounded-full font-semibold hover:bg-green-100 transition">Start Learning</a>
            </div>
        </div>
    </main>

    <!-- Profile Modal -->
    <div class="modal" id="profileModal">
        <div class="modal-content">
            <button class="modal-close" onclick="toggleProfileModal()">×</button>
            <div id="profileView" class="profile-view">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Your Profile</h2>
                <img src="<?php echo $profilePicture; ?>" alt="Profile" id="modalProfilePic">
                <h3><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></h3>
                <p><?php echo htmlspecialchars($jobTitle); ?></p>
                <p><?php echo htmlspecialchars($location); ?></p>
                <p><?php echo htmlspecialchars($preferredJobType); ?> | <?php echo htmlspecialchars($eventInterests); ?></p>
                <p class="font-semibold mt-4">Contact Info</p>
                <p><?php echo htmlspecialchars($email); ?></p>
                <p><?php echo htmlspecialchars($phone); ?></p>
                <p class="font-semibold mt-4">Skills</p>
                <p><?php echo htmlspecialchars($skills); ?></p>
                <button onclick="toggleEditMode()" class="mt-6 py-2 px-4 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition duration-300">Edit Profile</button>
            </div>
            <div id="profileEdit" class="hidden">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6 text-center">Edit Your Profile</h2>
                <?php if (isset($error)): ?>
                    <p class="text-red-500 text-sm mb-4 text-center"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>
                <form action="" method="POST" enctype="multipart/form-data" class="form-grid" id="profileForm">
                    <input type="hidden" name="profile_submit" value="1">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                        <input type="text" name="first_name" value="<?php echo htmlspecialchars($firstName); ?>" class="form-input" placeholder="First Name" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                        <input type="text" name="last_name" value="<?php echo htmlspecialchars($lastName); ?>" class="form-input" placeholder="Last Name" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" class="form-input" placeholder="Email" required>
                        <p class="form-error" id="emailError">Invalid email format.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" class="form-input" placeholder="Phone" pattern="[0-9]{10,15}" required>
                        <p class="form-error" id="phoneError">Invalid phone number (10-15 digits).</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Job Title</label>
                        <input type="text" name="job_title" value="<?php echo htmlspecialchars($jobTitle); ?>" class="form-input" placeholder="Job Title" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Preferred Job Type</label>
                        <select name="preferred_job_type" class="form-select" required>
                            <option value="">Select Job Type</option>
                            <option value="intern" <?php echo $preferredJobType == 'intern' ? 'selected' : ''; ?>>📚 Intern</option>
                            <option value="full-time" <?php echo $preferredJobType == 'full-time' ? 'selected' : ''; ?>>💼 Full-Time</option>
                            <option value="part-time" <?php echo $preferredJobType == 'part-time' ? 'selected' : ''; ?>>⌛ Part-Time</option>
                            <option value="remote" <?php echo $preferredJobType == 'remote' ? 'selected' : ''; ?>>🏠 Remote</option>
                            <option value="freelance" <?php echo $preferredJobType == 'freelance' ? 'selected' : ''; ?>>🔄 Freelance</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <input type="text" name="location" value="<?php echo htmlspecialchars($location); ?>" class="form-input" placeholder="City, Country" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Events of Interest</label>
                        <select name="event_interests" class="form-select" required>
                            <option value="">Select Event Type</option>
                            <option value="tech-conferences" <?php echo $eventInterests == 'tech-conferences' ? 'selected' : ''; ?>>🎯 Tech Conferences</option>
                            <option value="workshops" <?php echo $eventInterests == 'workshops' ? 'selected' : ''; ?>>🛠️ Workshops</option>
                            <option value="hackathons" <?php echo $eventInterests == 'hackathons' ? 'selected' : ''; ?>>💻 Hackathons</option>
                            <option value="networking" <?php echo $eventInterests == 'networking' ? 'selected' : ''; ?>>🤝 Networking Events</option>
                            <option value="webinars" <?php echo $eventInterests == 'webinars' ? 'selected' : ''; ?>>🎥 Webinars</option>
                            <option value="career-fairs" <?php echo $eventInterests == 'career-fairs' ? 'selected' : ''; ?>>🎪 Career Fairs</option>
                            <option value="meetups" <?php echo $eventInterests == 'meetups' ? 'selected' : ''; ?>>👥 Industry Meetups</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Skills</label>
                        <input type="text" name="skills" value="<?php echo htmlspecialchars($skills); ?>" class="form-input" placeholder="Skills (comma-separated)">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Profile Picture</label>
                        <input type="file" name="profile_picture" class="form-input" accept=".jpg,.jpeg,.png">
                    </div>
                    <div class="full-width flex space-x-4">
                        <button type="submit" class="flex-1 py-2 px-4 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition duration-300">Save Changes</button>
                        <button type="button" onclick="toggleEditMode()" class="flex-1 py-2 px-4 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 transition duration-300">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="/smartcareer/assets/js/dashboard.js"></script>
    <script>
        const form = document.getElementById('profileForm');
        form.addEventListener('submit', (e) => {
            const email = form.querySelector('input[name="email"]');
            const phone = form.querySelector('input[name="phone"]');
            const emailError = document.getElementById('emailError');
            const phoneError = document.getElementById('phoneError');
            let valid = true;

            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                emailError.style.display = 'block';
                valid = false;
            } else {
                emailError.style.display = 'none';
            }

            if (!/^[0-9]{10,15}$/.test(phone.value)) {
                phoneError.style.display = 'block';
                valid = false;
            } else {
                phoneError.style.display = 'none';
            }

            if (!valid) {
                e.preventDefault();
            } else {
                const fileInput = form.querySelector('input[name="profile_picture"]');
                if (fileInput.files && fileInput.files[0]) {
                    const newPicUrl = '<?php echo $profilePicture; ?>?' + new Date().getTime();
                    sidebarProfilePic.src = newPicUrl;
                    modalProfilePic.src = newPicUrl;
                }
            }
        });
    </script>
</body>
</html>