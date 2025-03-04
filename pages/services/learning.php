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
    <title>SmartCareer | Learning Resources</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .course-card {
            transition: transform 0.2s ease-in-out;
        }
        .course-card:hover {
            transform: translateY(-4px);
        }
        .progress-bar {
            transition: width 0.3s ease;
        }
        .category-pill.active {
            background-color: #2563eb;
            color: white;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <?php include '../../includes/header.php'; ?>


    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Learning Progress Overview -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Your Learning Progress</h2>
                <button class="text-blue-600 hover:text-blue-800">View All Courses</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-blue-50 rounded-lg p-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-600">Courses in Progress</span>
                        <span class="text-lg font-bold text-blue-600">4</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-600 rounded-full h-2 progress-bar" style="width: 60%"></div>
                    </div>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-600">Completed Courses</span>
                        <span class="text-lg font-bold text-green-600">12</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-600 rounded-full h-2" style="width: 100%"></div>
                    </div>
                </div>
                <div class="bg-yellow-50 rounded-lg p-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm text-gray-600">Hours Learned</span>
                        <span class="text-lg font-bold text-yellow-600">48</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-yellow-600 rounded-full h-2" style="width: 80%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Categories and Search -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex flex-wrap gap-2" id="categories">
                    <button class="category-pill px-4 py-2 rounded-full bg-gray-200 hover:bg-blue-600 hover:text-white active">All</button>
                    <button class="category-pill px-4 py-2 rounded-full bg-gray-200 hover:bg-blue-600 hover:text-white">Technology</button>
                    <button class="category-pill px-4 py-2 rounded-full bg-gray-200 hover:bg-blue-600 hover:text-white">Business</button>
                    <button class="category-pill px-4 py-2 rounded-full bg-gray-200 hover:bg-blue-600 hover:text-white">Design</button>
                    <button class="category-pill px-4 py-2 rounded-full bg-gray-200 hover:bg-blue-600 hover:text-white">Marketing</button>
                </div>
                <div class="relative">
                    <input type="text" placeholder="Search courses..." 
                           class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>
        </div>

        <!-- Courses Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="coursesContainer">
            <!-- Course cards will be dynamically inserted here -->
        </div>
    </div>

    <script>
        // Simulated course data (replace with actual API calls)
        const coursesData = [
            {
                id: 1,
                title: "Web Development Fundamentals",
                platform: "Coursera",
                instructor: "John Smith",
                duration: "8 weeks",
                level: "Beginner",
                rating: 4.5,
                enrolled: 1200,
                progress: 60,
                image: "/api/placeholder/320/180",
                category: "Technology",
                price: "Free"
            },
            // Add more courses...
        ];

        // Function to create course card HTML
        function createCourseCard(course) {
            return `
                <div class="course-card bg-white rounded-lg shadow-sm overflow-hidden">
                    <img src="${course.image}" alt="${course.title}" class="w-full h-48 object-cover">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-lg font-semibold">${course.title}</h3>
                            <button class="text-gray-400 hover:text-blue-600 bookmark-btn" data-id="${course.id}">
                                <i class="fas fa-bookmark"></i>
                            </button>
                        </div>
                        <p class="text-gray-600 text-sm mb-4">${course.instructor}</p>
                        <div class="flex items-center mb-4">
                            <div class="flex items-center mr-4">
                                <i class="fas fa-star text-yellow-400 mr-1"></i>
                                <span class="text-sm">${course.rating}</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-users text-gray-400 mr-1"></i>
                                <span class="text-sm">${course.enrolled} students</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-sm text-gray-600">${course.duration}</span>
                            <span class="text-sm bg-blue-100 text-blue-800 px-2 py-1 rounded">${course.level}</span>
                        </div>
                        ${course.progress ? `
                            <div class="mb-4">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm text-gray-600">Progress</span>
                                    <span class="text-sm text-gray-600">${course.progress}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 rounded-full h-2" style="width: ${course.progress}%"></div>
                                </div>
                            </div>
                        ` : ''}
                        <div class="flex justify-between items-center">
                            <span class="font-bold">${course.price}</span>
                            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                ${course.progress ? 'Continue' : 'Start Learning'}
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        // Function to fetch and display courses
        async function fetchCourses(category = 'All') {
            const container = document.getElementById('coursesContainer');
            
            try {
                // Simulate API call delay
                await new Promise(resolve => setTimeout(resolve, 1000));

                // Filter courses by category
                const filteredCourses = category === 'All' 
                    ? coursesData 
                    : coursesData.filter(course => course.category === category);

                container.innerHTML = filteredCourses.map(course => createCourseCard(course)).join('');
                
                // Set up bookmark buttons
                setupBookmarkButtons();
            } catch (error) {
                console.error('Error fetching courses:', error);
                container.innerHTML = '<p class="text-red-500">Error loading courses. Please try again later.</p>';
            }
        }

        // Setup category filters
        function setupCategories() {
            const categories = document.getElementById('categories');
            
            categories.addEventListener('click', (e) => {
                if (e.target.classList.contains('category-pill')) {
                    // Remove active class from all pills
                    document.querySelectorAll('.category-pill').forEach(pill => {
                        pill.classList.remove('active');
                    });
                    
                    // Add active class to clicked pill
                    e.target.classList.add('active');
                    
                    // Fetch courses for selected category
                    fetchCourses(e.target.textContent);
                }
            });
        }

        // Setup bookmark functionality
        function setupBookmarkButtons() {
            const bookmarkBtns = document.querySelectorAll('.bookmark-btn');
            
            bookmarkBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    btn.classList.toggle('text-blue-600');
                    // Implement bookmark saving logic here
                });
            });
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', () => {
            fetchCourses();
            setupCategories();
        });
    </script>
</body>
</html>