<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Course | SmartCareer</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <?php include '../../includes/header.php'; ?>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Post a New Course</h1>
        <form id="postCourseForm" action="submit_course.php" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-md p-6">
            <div class="grid grid-cols-1 gap-6">
                <!-- Course Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Course Title *</label>
                    <input type="text" id="title" name="title" required class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Category -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700">Category *</label>
                    <select id="category" name="category" required class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="Technology">Technology</option>
                        <option value="Business">Business</option>
                        <option value="Design">Design</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Personal Development">Personal Development</option>
                        <option value="Writing">Writing</option>
                        <option value="Freelancing">Freelancing</option>
                        <option value="Languages">Languages</option>
                        <option value="Health">Health</option>
                    </select>
                </div>

                <!-- Platform -->
                <div>
                    <label for="platform" class="block text-sm font-medium text-gray-700">Platform *</label>
                    <input type="text" id="platform" name="platform" required class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Instructor -->
                <div>
                    <label for="instructor" class="block text-sm font-medium text-gray-700">Instructor *</label>
                    <input type="text" id="instructor" name="instructor" required class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Duration -->
                <div>
                    <label for="duration" class="block text-sm font-medium text-gray-700">Duration *</label>
                    <input type="text" id="duration" name="duration" required placeholder="e.g., 6 weeks" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Level -->
                <div>
                    <label for="level" class="block text-sm font-medium text-gray-700">Level *</label>
                    <select id="level" name="level" required class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="Beginner">Beginner</option>
                        <option value="Intermediate">Intermediate</option>
                        <option value="Advanced">Advanced</option>
                    </select>
                </div>

                <!-- Image Upload -->
                <div>
                    <label for="image" class="block text-sm font-medium text-gray-700">Image (Optional)</label>
                    <input type="file" id="image" name="image" accept="image/*" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                    <p class="text-sm text-gray-500 mt-1">Upload an image or leave blank for a default placeholder.</p>
                </div>

                <!-- Link -->
                <div>
                    <label for="link" class="block text-sm font-medium text-gray-700">Course Link (Optional)</label>
                    <input type="url" id="link" name="link" placeholder="https://example.com/course" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>

                <!-- Price -->
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">Price *</label>
                    <input type="text" id="price" name="price" required placeholder="e.g., Free or $49" class="mt-1 w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-6 flex space-x-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Post Course</button>
                <a href="learning.php" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>