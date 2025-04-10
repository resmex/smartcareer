<?php
session_start();

// Include database connection
include '../../includes/connect.php'; // Adjust the path as needed

// Redirect if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];

    // Sanitize and validate form data
    $fields = ['first_name', 'last_name', 'job_title', 'preferred_job_type', 'location', 'event_interests', 'email', 'phone'];
    $data = [];
    foreach ($fields as $field) {
        $data[$field] = isset($_POST[$field]) ? trim($_POST[$field]) : '';
    }

    // Validate email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    // Validate phone number (10-15 digits)
    if (!preg_match('/^[0-9]{10,15}$/', $data['phone'])) {
        die("Invalid phone number. Please enter a valid number between 10 to 15 digits.");
    }

    // Handle skills
    $skills = isset($_POST['skills']) && is_array($_POST['skills']) ? implode(",", $_POST['skills']) : '';

    // Handle profile picture upload
    $profile_picture = $_SESSION['profile_picture'] ?? ''; // Keep existing picture if no new one is uploaded
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['profile_picture'];
        $allowed_types = ['jpg', 'jpeg', 'png'];
        $file_type = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Validate file type
        if (!in_array($file_type, $allowed_types)) {
            die("Invalid file type. Only JPG, JPEG, and PNG allowed.");
        }

        // Validate file size (2MB limit)
        if ($file['size'] > 2 * 1024 * 1024) {
            die("File size exceeds 2MB.");
        }

        // Create uploads directory if it doesn't exist
        $upload_dir = '../../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Generate a unique file name
        $new_file_name = uniqid() . "." . $file_type;
        $profile_picture_path = $upload_dir . $new_file_name;

        // Move the uploaded file to the uploads directory
        if (!move_uploaded_file($file['tmp_name'], $profile_picture_path)) {
            die("Failed to upload profile picture.");
        }

        // Store only the filename in the database
        $profile_picture = $new_file_name;
    }

    // Check if profile exists in the database
    $stmt = $con->prepare("SELECT user_id FROM user_profiles WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $profile_exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    // Prepare SQL statement
    if ($profile_exists) {
        $sql = "UPDATE user_profiles SET 
                first_name=?, last_name=?, job_title=?, preferred_job_type=?, location=?,
                event_interests=?, skills=?, profile_picture=?, email=?, phone=?, updated_at=NOW()
                WHERE user_id=?";
    } else {
        $sql = "INSERT INTO user_profiles 
                (user_id, first_name, last_name, job_title, preferred_job_type, location, 
                 event_interests, skills, profile_picture, email, phone) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    }

    // Bind parameters and execute
    $stmt = $con->prepare($sql);
    if ($profile_exists) {
        $stmt->bind_param("ssssssssssi", $data['first_name'], $data['last_name'], $data['job_title'], $data['preferred_job_type'],
            $data['location'], $data['event_interests'], $skills, $profile_picture, $data['email'], $data['phone'], $user_id);
    } else {
        $stmt->bind_param("issssssssss", $user_id, $data['first_name'], $data['last_name'], $data['job_title'], $data['preferred_job_type'],
            $data['location'], $data['event_interests'], $skills, $profile_picture, $data['email'], $data['phone']);
    }

    if ($stmt->execute()) {
        // Update session data
        $_SESSION = array_merge($_SESSION, $data);
        $_SESSION['skills'] = $skills;
        $_SESSION['profile_picture'] = $profile_picture;

        // Redirect to dashboard after successful update
        header("Location: ../services/dashboard.php");
        exit();
    } else {
        die("Error updating profile: " . $stmt->error);
    }

    $stmt->close();
}

// Fetch profile data for display
$firstName = $_SESSION['first_name'] ?? 'John';
$lastName = $_SESSION['last_name'] ?? 'Doe';
$jobTitle = $_SESSION['job_title'] ?? 'Job Seeker';
$location = $_SESSION['location'] ?? 'Unknown';
$preferredJobType = $_SESSION['preferred_job_type'] ?? 'Full-time';
$eventInterests = $_SESSION['event_interests'] ?? 'Career Fairs';
$skills = $_SESSION['skills'] ?? 'UI Design, UX Research, Prototyping';
$email = $_SESSION['email'] ?? 'example@email.com';
$phone = $_SESSION['phone'] ?? '123-456-7890';
$profilePicture = !empty($_SESSION['profile_picture']) 
    ? '../../uploads/' . htmlspecialchars($_SESSION['profile_picture']) 
    : '../../uploads/profile_default.jpeg'; // Default profile picture path
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartCareer | Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style>
        /* Custom CSS for grid layout */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .form-grid .full-width {
            grid-column: span 2;
        }

        .cancel-button {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            font-size: 2rem;
            color: red;
            cursor: pointer;
            transition: transform 0.2s ease, color 0.2s ease;
        }

        .cancel-button:hover {
            transform: scale(1.2);
            color: darkred;
        }
    </style>
    <script>
        function toggleEditProfile() {
            const editForm = document.getElementById("edit-profile");
            editForm.classList.toggle("hidden");
        }

        function redirectToDashboard() {
            window.location.href = "../services/dashboard.php"; // Redirect to dashboard
        }
    </script>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-white to-purple-50 min-h-screen flex justify-center items-center py-8 px-4">
    <div class="max-w-md w-full bg-white p-6 rounded-xl shadow-lg backdrop-blur-md bg-opacity-90 relative">
        <!-- Cancel Button -->
        <button class="cancel-button" onclick="redirectToDashboard()">×</button>

        <div class="flex flex-col items-center space-y-4">
            <div class="text-center">
                <h2 class="text-xl font-semibold text-gray-800">Your Profile</h2>
            </div>
            <div class="w-24 h-24 rounded-full overflow-hidden border-2 border-white shadow-lg">
                <img src="<?php echo $profilePicture; ?>" alt="Profile Picture" class="w-full h-full object-cover">
            </div>
            <div class="text-center mt-4">
                <h3 class="text-lg font-semibold text-gray-700"><?php echo htmlspecialchars($firstName . ' ' . $lastName); ?></h3>
                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($jobTitle); ?></p>
                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($location); ?></p>
                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($preferredJobType); ?> | <?php echo htmlspecialchars($eventInterests); ?></p>
            </div>
            <div class="mt-6 text-center">
                <p class="font-semibold text-gray-800">Contact Info</p>
                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($email); ?></p>
                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($phone); ?></p>
            </div>
            <div class="mt-6">
                <p class="font-semibold text-gray-800">Skills</p>
                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($skills); ?></p>
            </div>
            <div class="mt-6">
                <a href="javascript:void(0);" onclick="toggleEditProfile()" class="text-blue-500">Edit Profile</a>
            </div>
            <div id="edit-profile" class="hidden mt-6 w-full">
                <form action="" method="POST" enctype="multipart/form-data" class="space-y-4">
                    <div class="form-grid">
                        <!-- First Name -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">First Name</label>
                            <input type="text" name="first_name" value="<?php echo htmlspecialchars($firstName); ?>"
                                class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                placeholder="First Name" required>
                        </div>

                        <!-- Last Name -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Last Name</label>
                            <input type="text" name="last_name" value="<?php echo htmlspecialchars($lastName); ?>"
                                class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                placeholder="Last Name" required>
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>"
                                class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                placeholder="Email" required>
                            <p class="text-red-500 text-xs mt-1 hidden" id="emailError">Invalid email format.</p>
                        </div>

                        <!-- Phone -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Phone</label>
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>"
                                class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                placeholder="Phone" pattern="[0-9]{10,15}" required>
                            <p class="text-red-500 text-xs mt-1 hidden" id="phoneError">Invalid phone number. Please enter a valid number between 10 to 15 digits.</p>
                        </div>

                        <!-- Job Title -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Job Title</label>
                            <input type="text" name="job_title" value="<?php echo htmlspecialchars($jobTitle); ?>"
                                class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                placeholder="Job Title" required>
                        </div>

                        <!-- Preferred Job Type -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Preferred Job Type</label>
                            <select name="preferred_job_type" required
                                class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Job Type</option>
                                <option value="intern" <?php echo ($preferredJobType == 'intern') ? 'selected' : ''; ?>>📚 Intern</option>
                                <option value="full-time" <?php echo ($preferredJobType == 'full-time') ? 'selected' : ''; ?>>💼 Full-Time</option>
                                <option value="part-time" <?php echo ($preferredJobType == 'part-time') ? 'selected' : ''; ?>>⌛ Part-Time</option>
                                <option value="remote" <?php echo ($preferredJobType == 'remote') ? 'selected' : ''; ?>>🏠 Remote</option>
                                <option value="freelance" <?php echo ($preferredJobType == 'freelance') ? 'selected' : ''; ?>>🔄 Freelance</option>
                            </select>
                        </div>

                        <!-- Location -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Location</label>
                            <input type="text" name="location" value="<?php echo htmlspecialchars($location); ?>"
                                class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                placeholder="City, Country" required>
                        </div>

                        <!-- Event Interests -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Events of Interest</label>
                            <select name="event_interests" required
                                class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Event Type</option>
                                <option value="tech-conferences" <?php echo ($eventInterests == 'tech-conferences') ? 'selected' : ''; ?>>🎯 Tech Conferences</option>
                                <option value="workshops" <?php echo ($eventInterests == 'workshops') ? 'selected' : ''; ?>>🛠️ Workshops</option>
                                <option value="hackathons" <?php echo ($eventInterests == 'hackathons') ? 'selected' : ''; ?>>💻 Hackathons</option>
                                <option value="networking" <?php echo ($eventInterests == 'networking') ? 'selected' : ''; ?>>🤝 Networking Events</option>
                                <option value="webinars" <?php echo ($eventInterests == 'webinars') ? 'selected' : ''; ?>>🎥 Webinars</option>
                                <option value="career-fairs" <?php echo ($eventInterests == 'career-fairs') ? 'selected' : ''; ?>>🎪 Career Fairs</option>
                                <option value="meetups" <?php echo ($eventInterests == 'meetups') ? 'selected' : ''; ?>>👥 Industry Meetups</option>
                            </select>
                        </div>

                        <!-- Skills -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Skills</label>
                            <input type="text" name="skills" value="<?php echo htmlspecialchars($skills); ?>"
                                class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                                placeholder="Skills (comma-separated)">
                        </div>

                        <!-- Profile Picture -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700">Profile Picture</label>
                            <input type="file" name="profile_picture" class="w-full p-2 border border-gray-300 rounded-md">
                        </div>

                        <!-- Save Button -->
                        <div class="full-width">
                            <button type="submit"
                                class="w-full py-2 px-4 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition duration-300">Save
                                Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>