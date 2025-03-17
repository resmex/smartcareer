<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Job | SmartCareer</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
</head>
<body class="bg-gray-100 font-sans">
    <?php include '../../includes/header.php'; ?>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Post a New Job</h1>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                <p><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
            </div>
        <?php endif; ?>
        <form id="postJobForm" action="submit_job.php" method="POST" class="bg-white rounded-xl shadow-md p-6">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="grid grid-cols-1 gap-6">
                <!-- Job Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Job Title</label>
                    <input type="text" id="title" name="title" required class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Company -->
                <div>
                    <label for="company" class="block text-sm font-medium text-gray-700">Company</label>
                    <input type="text" id="company" name="company" required class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Location -->
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                    <input type="text" id="location" name="location" required class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Job Type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Job Type</label>
                    <select id="type" name="type" required class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="Full Time">Full Time</option>
                        <option value="Part Time">Part Time</option>
                        <option value="Contract">Contract</option>
                        <option value="Internship">Internship</option>
                        <option value="Freelance">Freelance</option>
                        <option value="Remote">Remote</option>
                    </select>
                </div>

                <!-- Salary -->
                <div>
                    <label for="salary" class="block text-sm font-medium text-gray-700">Salary (Optional)</label>
                    <input type="text" id="salary" name="salary" placeholder="e.g., $50,000 - $70,000 or 2M - 3M TZS" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Categories -->
                <div>
                    <label for="categories" class="block text-sm font-medium text-gray-700">Categories (Select all that apply)</label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="categories[]" value="government" class="rounded">
                            <span>Government</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="categories[]" value="ngo" class="rounded">
                            <span>NGO</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="categories[]" value="tech" class="rounded">
                            <span>Tech</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="categories[]" value="marketing" class="rounded">
                            <span>Marketing</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="categories[]" value="design" class="rounded">
                            <span>Design</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="categories[]" value="devops" class="rounded">
                            <span>DevOps</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="categories[]" value="banking" class="rounded">
                            <span>Banking</span>
                        </label>
                        <label class="flex items-center space-x-2">
                            <input type="checkbox" name="categories[]" value="teaching" class="rounded">
                            <span>Teaching</span>
                        </label>
                    </div>
                </div>

                <!-- Requirements -->
                <div>
                    <label for="requirements" class="block text-sm font-medium text-gray-700">Requirements (One per line)</label>
                    <textarea id="requirements" name="requirements" rows="4" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="e.g., React, TypeScript, 5+ years experience"></textarea>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" name="description" required rows="4" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <!-- Application Link -->
                <div>
                    <label for="link" class="block text-sm font-medium text-gray-700">Application Link (Optional)</label>
                    <input type="url" id="link" name="link" placeholder="https://example.com/apply" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="mt-6 w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Post Job</button>
        </form>
    </div>
</body>
</html>