<?php
// jobs.php - Fetch and display job postings

// Fetch jobs from the API
$api_url = "http://localhost:5000/api/jobs"; // Replace with your Flask API URL
$json_data = file_get_contents($api_url);
$jobs = json_decode($json_data, true);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Opportunities</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .job-card {
            transition: transform 0.2s ease-in-out;
        }
        .job-card:hover {
            transform: translateY(-4px);
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
    <!-- Jobs Grid -->
    <div class="max-w-7xl mx-auto px-4 py-8">
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
        // Pass PHP data to JavaScript
        const jobsData = <?php echo json_encode($jobs); ?>;

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

        // Function to render jobs
        function renderJobs() {
            const container = document.getElementById('jobsContainer');
            const loadingState = document.getElementById('loadingState');
            
            if (jobsData.length > 0) {
                container.innerHTML = jobsData.map(job => createJobCard(job)).join('');
                loadingState.style.display = 'none';
            } else {
                container.innerHTML = '<p class="text-red-500">No jobs found.</p>';
                loadingState.style.display = 'none';
            }
        }

        // Initialize page
        document.addEventListener('DOMContentLoaded', renderJobs);
    </script>
</body>
</html>