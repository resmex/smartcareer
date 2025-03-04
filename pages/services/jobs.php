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
    <title>SmartCareer | Job Opportunities</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .job-card {
            transition: transform 0.2s ease-in-out;
        }
        .job-card:hover {
            transform: translateY(-4px);
        }
        .filter-badge {
            transition: all 0.2s ease;
        }
        .filter-badge:hover {
            background-color: #2563eb;
            color: white;
        }
        .skeleton {
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { opacity: 0.6; }
            50% { opacity: 1; }
            100% { opacity: 0.6; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <!-- <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <span class="text-2xl font-bold">
                        <span class="text-blue-600">Smart</span><span class="text-black">Career</span>
                    </span>
                </div>
                <div class="flex items-center space-x-4">
                    <button id="saveSearchBtn" class="text-gray-600 hover:text-blue-600">
                        <i class="fas fa-bell"></i>
                        <span class="ml-2">Save Search</span>
                    </button>
                    <div class="relative">
                        <img src="/api/placeholder/40/40" alt="Profile" class="w-10 h-10 rounded-full">
                    </div>
                </div>
            </div>
        </div>
    </nav> -->
    <?php include '../../includes/header.php'; ?>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">

        <!-- Search and Filter Section -->
        <div class="mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex flex-col md:flex-row md:items-center md:space-x-4">
                    <div class="flex-1 mb-4 md:mb-0">
                        <div class="relative">
                            <input type="text" id="searchInput" placeholder="Search jobs by title, company, or keywords" 
                                   class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <select id="locationFilter" class="border rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500">
                            <option value="">All Locations</option>
                            <option value="dar-es-salaam">Dar es Salaam</option>
                            <option value="arusha">Arusha</option>
                            <option value="mwanza">Mwanza</option>
                        </select>
                        <select id="typeFilter" class="border rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500">
                            <option value="">All Types</option>
                            <option value="full-time">Full Time</option>
                            <option value="part-time">Part Time</option>
                            <option value="contract">Contract</option>
                            <option value="internship">Internship</option>
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2" id="quickFilters">
                    <span class="filter-badge px-3 py-1 bg-gray-100 rounded-full text-sm cursor-pointer">Software Development</span>
                    <span class="filter-badge px-3 py-1 bg-gray-100 rounded-full text-sm cursor-pointer">Marketing</span>
                    <span class="filter-badge px-3 py-1 bg-gray-100 rounded-full text-sm cursor-pointer">Finance</span>
                    <span class="filter-badge px-3 py-1 bg-gray-100 rounded-full text-sm cursor-pointer">Healthcare</span>
                    <span class="filter-badge px-3 py-1 bg-gray-100 rounded-full text-sm cursor-pointer">Education</span>
                </div>
            </div>
        </div>

        <!-- Jobs Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="jobsContainer">
            <!-- Job cards will be dynamically inserted here -->
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="skeleton bg-white rounded-lg p-6">
                    <div class="h-6 bg-gray-200 rounded w-3/4 mb-4"></div>
                    <div class="h-4 bg-gray-200 rounded w-1/2 mb-2"></div>
                    <div class="h-4 bg-gray-200 rounded w-1/4 mb-4"></div>
                    <div class="h-10 bg-gray-200 rounded"></div>
                </div>
                <!-- Repeat skeleton cards -->
            </div>
        </div>
    </div>

    <script>
        // Simulated job data (replace with actual API calls)
        const jobsData = [
            {
                id: 1,
                title: "Senior Software Engineer",
                company: "TechCorp Tanzania",
                location: "Dar es Salaam",
                type: "Full Time",
                salary: "$3,000 - $5,000",
                posted: "2 days ago",
                description: "We are looking for an experienced software engineer to join our team...",
                requirements: ["5+ years experience", "React", "Node.js", "AWS"],
                source: "LinkedIn"
            },
            // Add more job listings...
        ];

        // Function to create job card HTML
        function createJobCard(job) {
            return `
                <div class="job-card bg-white rounded-lg shadow-sm p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">${job.title}</h3>
                            <p class="text-gray-600">${job.company}</p>
                        </div>
                        <span class="text-xs text-gray-500">${job.source}</span>
                    </div>
                    <div class="mb-4">
                        <div class="flex items-center text-gray-500 text-sm mb-2">
                            <i class="fas fa-map-marker-alt mr-2"></i>
                            <span>${job.location}</span>
                        </div>
                        <div class="flex items-center text-gray-500 text-sm">
                            <i class="fas fa-clock mr-2"></i>
                            <span>${job.type}</span>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 mb-4">
                        ${job.requirements.map(req => 
                            `<span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded">${req}</span>`
                        ).join('')}
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">${job.salary}</span>
                        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Apply Now
                        </button>
                    </div>
                </div>
            `;
        }

        // Function to fetch and display jobs
        async function fetchJobs() {
            const container = document.getElementById('jobsContainer');
            const loadingState = document.getElementById('loadingState');
            
            try {
                container.style.display = 'none';
                loadingState.style.display = 'block';

                // Simulate API call delay
                await new Promise(resolve => setTimeout(resolve, 1000));

                // In real implementation, fetch data from your scraping API
                const jobs = jobsData; // Replace with actual API call

                container.innerHTML = jobs.map(job => createJobCard(job)).join('');
                
                container.style.display = 'grid';
                loadingState.style.display = 'none';
            } catch (error) {
                console.error('Error fetching jobs:', error);
                container.innerHTML = '<p class="text-red-500">Error loading jobs. Please try again later.</p>';
            }
        }

        // Search and filter functionality
        function setupSearchAndFilters() {
            const searchInput = document.getElementById('searchInput');
            const locationFilter = document.getElementById('locationFilter');
            const typeFilter = document.getElementById('typeFilter');
            const quickFilters = document.getElementById('quickFilters');

            const filterJobs = () => {
                const searchTerm = searchInput.value.toLowerCase();
                const location = locationFilter.value.toLowerCase();
                const type = typeFilter.value.toLowerCase();

                const filteredJobs = jobsData.filter(job => {
                    const matchesSearch = job.title.toLowerCase().includes(searchTerm) ||
                                       job.company.toLowerCase().includes(searchTerm);
                    const matchesLocation = !location || job.location.toLowerCase().includes(location);
                    const matchesType = !type || job.type.toLowerCase().includes(type);

                    return matchesSearch && matchesLocation && matchesType;
                });

                document.getElementById('jobsContainer').innerHTML = 
                    filteredJobs.map(job => createJobCard(job)).join('');
            };

            searchInput.addEventListener('input', filterJobs);
            locationFilter.addEventListener('change', filterJobs);
            typeFilter.addEventListener('change', filterJobs);

            quickFilters.addEventListener('click', (e) => {
                if (e.target.classList.contains('filter-badge')) {
                    searchInput.value = e.target.textContent;
                    filterJobs();
                }
            });
        }

        // Save search functionality
        function setupSaveSearch() {
            const saveSearchBtn = document.getElementById('saveSearchBtn');
            
            saveSearchBtn.addEventListener('click', () => {
                // Implement save search logic
                alert('Search preferences saved! You will receive notifications for new matching jobs.');
            });
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', () => {
            fetchJobs();
            setupSearchAndFilters();
            setupSaveSearch();

            // Set up periodic refresh (every 5 minutes)
            setInterval(fetchJobs, 300000);
        });
    </script>
</body>
</html>