<?php
session_start();
include '../../includes/connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

$userId = (int)$_SESSION['user_id'];
$jobId = filter_input(INPUT_GET, 'job_id', FILTER_SANITIZE_STRING);

if (!$jobId) {
    die("Invalid job ID.");
}

// Fetch job
$stmt = $con->prepare("SELECT * FROM jobs WHERE id = ? AND posted_by = ?");
$stmt->bind_param("si", $jobId, $userId);
$stmt->execute();
$job = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$job) {
    die("Job not found or not owned by you.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $company = filter_input(INPUT_POST, 'company', FILTER_SANITIZE_STRING);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
    $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
    $salary = filter_input(INPUT_POST, 'salary', FILTER_SANITIZE_STRING) ?: 'Not specified';
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $link = filter_input(INPUT_POST, 'link', FILTER_VALIDATE_URL) ?: '#';
    $categories = isset($_POST['categories']) ? json_encode(array_filter((array)$_POST['categories'])) : $job['categories'];
    $isRemote = stripos($location, 'remote') !== false ? 1 : 0;
    $updatedAt = date("Y-m-d H:i:s");

    if ($title && $company && $location && $type && $description) {
        $stmt = $con->prepare("UPDATE jobs SET title = ?, company = ?, location = ?, type = ?, salary = ?, description = ?, full_description = ?, link = ?, categories = ?, is_remote = ?, updated_at = ? WHERE id = ? AND posted_by = ?");
        $shortDescription = substr($description, 0, 150) . "...";
        $stmt->bind_param("ssssssssssissi", $title, $company, $location, $type, $salary, $shortDescription, $description, $link, $categories, $isRemote, $updatedAt, $jobId, $userId);

        if ($stmt->execute()) {
            header("Location: manage_jobs.php");
        } else {
            echo "Error updating job: " . $stmt->error;
        }
        $stmt->close();
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job | SmartCareer</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style>
        .logo { font-size: 2.5rem; font-weight: bold; text-align: center; margin-bottom: 1rem; }
        .logo .smart { color: #2563eb; }
        .logo .career { color: #000000; }
        .centered-heading { text-align: center; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="max-w-3xl mx-auto px-4 py-10">
        <h1 class="text-3xl font-bold mb-6 centered-heading">Edit Job</h1>
        <form method="POST" class="bg-white p-6 rounded-xl shadow-md">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Job Title</label>
                <input type="text" name="title" value="<?php echo htmlspecialchars($job['title']); ?>" required class="mt-1 w-full px-4 py-2 border rounded-lg">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Company</label>
                <input type="text" name="company" value="<?php echo htmlspecialchars($job['company']); ?>" required class="mt-1 w-full px-4 py-2 border rounded-lg">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Location</label>
                <input type="text" name="location" value="<?php echo htmlspecialchars($job['location']); ?>" required class="mt-1 w-full px-4 py-2 border rounded-lg">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Job Type</label>
                <select name="type" required class="mt-1 w-full px-4 py-2 border rounded-lg">
                    <option value="Full Time" <?php echo $job['type'] === 'Full Time' ? 'selected' : ''; ?>>Full Time</option>
                    <option value="Part Time" <?php echo $job['type'] === 'Part Time' ? 'selected' : ''; ?>>Part Time</option>
                    <option value="Contract" <?php echo $job['type'] === 'Contract' ? 'selected' : ''; ?>>Contract</option>
                    <option value="Internship" <?php echo $job['type'] === 'Internship' ? 'selected' : ''; ?>>Internship</option>
                    <option value="Freelance" <?php echo $job['type'] === 'Freelance' ? 'selected' : ''; ?>>Freelance</option>
                    <option value="Remote" <?php echo $job['type'] === 'Remote' ? 'selected' : ''; ?>>Remote</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Salary (Optional)</label>
                <input type="text" name="salary" value="<?php echo htmlspecialchars($job['salary']); ?>" class="mt-1 w-full px-4 py-2 border rounded-lg">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" required rows="4" class="mt-1 w-full px-4 py-2 border rounded-lg"><?php echo htmlspecialchars($job['full_description']); ?></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Application Link (Optional)</label>
                <input type="url" name="link" value="<?php echo htmlspecialchars($job['link']); ?>" class="mt-1 w-full px-4 py-2 border rounded-lg">
            </div>
            <div class="flex space-x-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Changes</button>
                <a href="manage_jobs.php" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>