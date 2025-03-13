<?php
// C:\xampp\htdocs\smartcareer\pages\services\edit_job.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

$jobId = filter_input(INPUT_GET, 'job_id', FILTER_VALIDATE_INT);
if (!$jobId) {
    header("Location: manage_jobs.php?error=Invalid job ID");
    exit();
}

$dataDir = __DIR__ . '/data';
$jobsFile = $dataDir . '/jobs.json';
$jobs = file_exists($jobsFile) ? json_decode(file_get_contents($jobsFile), true) : [];
$job = array_filter($jobs, fn($j) => $j['id'] === $jobId && $j['posted_by'] === $_SESSION['user_id']);

if (empty($job)) {
    header("Location: manage_jobs.php?error=Job not found or unauthorized");
    exit();
}

$job = array_values($job)[0];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $company = filter_input(INPUT_POST, 'company', FILTER_SANITIZE_STRING);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
    $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
    $salary = filter_input(INPUT_POST, 'salary', FILTER_SANITIZE_STRING) ?: 'Not specified';
    $categories = isset($_POST['categories']) ? array_map('trim', $_POST['categories']) : [];
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $link = filter_input(INPUT_POST, 'link', FILTER_VALIDATE_URL) ?: '#';

    if (!$title || !$company || !$location || !$type || !$description) {
        header("Location: edit_job.php?job_id=$jobId&error=All required fields must be filled.");
        exit();
    }

    $jobIndex = array_search($jobId, array_column($jobs, 'id'));
    $jobs[$jobIndex] = [
        'id' => $jobId,
        'title' => $title,
        'company' => $company,
        'location' => $location,
        'type' => $type,
        'salary' => $salary,
        'categories' => $categories,
        'description' => substr($description, 0, 150) . "...",
        'full_description' => $description,
        'source' => $job['source'],
        'link' => $link,
        'date_posted' => $job['date_posted'],
        'isRemote' => stripos($location, 'remote') !== false,
        'region' => determineRegion($location),
        'posted_by' => $_SESSION['user_id'],
        'categoryClass' => array_map(function($cat) {
            return match($cat) {
                'government' => 'government-badge',
                'tech' => 'tech-badge',
                'ngo' => 'bg-purple-100 text-purple-800',
                'banking' => 'bg-blue-100 text-blue-800',
                'teaching' => 'bg-yellow-100 text-yellow-800',
                'marketing' => 'bg-pink-100 text-pink-800',
                'design' => 'bg-orange-100 text-orange-800',
                'devops' => 'bg-indigo-100 text-indigo-800',
                default => 'bg-gray-100 text-gray-800'
            };
        }, $categories)
    ];

    file_put_contents($jobsFile, json_encode($jobs, JSON_PRETTY_PRINT));
    header("Location: jobs.php?success=Job updated successfully!");
    exit();
}

function determineRegion($location) {
    $location = strtolower($location);
    if (strpos($location, 'remote') !== false) return 'remote';
    if (strpos($location, 'usa') !== false || strpos($location, 'united states') !== false) return 'usa';
    if (strpos($location, 'europe') !== false) return 'europe';
    if (strpos($location, 'asia') !== false) return 'asia';
    if (strpos($location, 'africa') !== false) return 'africa';
    return 'other';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Job | SmartCareer</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <?php include '../../includes/header.php'; ?>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Edit Job</h1>

        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p><?php echo htmlspecialchars($_GET['error']); ?></p>
            </div>
        <?php endif; ?>

        <form id="editJobForm" action="" method="POST" class="bg-white rounded-xl shadow-md p-6">
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Job Title *</label>
                    <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($job['title']); ?>" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="company" class="block text-sm font-medium text-gray-700">Company *</label>
                    <input type="text" id="company" name="company" required value="<?php echo htmlspecialchars($job['company']); ?>" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700">Location *</label>
                    <input type="text" id="location" name="location" required value="<?php echo htmlspecialchars($job['location']); ?>" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Job Type *</label>
                    <select id="type" name="type" required class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="Full Time" <?php echo $job['type'] === 'Full Time' ? 'selected' : ''; ?>>Full Time</option>
                        <option value="Part Time" <?php echo $job['type'] === 'Part Time' ? 'selected' : ''; ?>>Part Time</option>
                        <option value="Contract" <?php echo $job['type'] === 'Contract' ? 'selected' : ''; ?>>Contract</option>
                        <option value="Internship" <?php echo $job['type'] === 'Internship' ? 'selected' : ''; ?>>Internship</option>
                    </select>
                </div>
                <div>
                    <label for="salary" class="block text-sm font-medium text-gray-700">Salary (Optional)</label>
                    <input type="text" id="salary" name="salary" placeholder="e.g., $50,000 - $70,000" value="<?php echo htmlspecialchars($job['salary']); ?>" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Categories (Select all that apply)</label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <?php
                        $existingCategories = $job['categories'] ?? [];
                        $allCategories = ['government', 'ngo', 'tech', 'marketing', 'design', 'devops', 'banking', 'teaching'];
                        foreach ($allCategories as $category):
                        ?>
                            <label>
                                <input type="checkbox" name="categories[]" value="<?php echo $category; ?>" 
                                    <?php echo in_array($category, $existingCategories) ? 'checked' : ''; ?>> 
                                <?php echo ucfirst($category); ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description *</label>
                    <textarea id="description" name="description" required rows="4" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"><?php echo htmlspecialchars($job['full_description']); ?></textarea>
                </div>
                <div>
                    <label for="link" class="block text-sm font-medium text-gray-700">Application Link (Optional)</label>
                    <input type="url" id="link" name="link" placeholder="https://example.com/apply" value="<?php echo htmlspecialchars($job['link']); ?>" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div class="mt-6 flex space-x-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Changes</button>
                <a href="manage_jobs.php" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>