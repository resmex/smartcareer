<?php
session_start();
include '../../includes/connect.php';

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
    <title>SmartCareer | Global Job Opportunities</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .job-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .job-card:hover { transform: translateY(-3px); box-shadow: 0 10px 15px rgba(0,0,0,0.1); }
        .category-badge { transition: all 0.2s ease; }
        .category-badge:hover { opacity: 0.8; }
        .category-badge.active { border: 2px solid #3b82f6; }
        .remote-badge { background-color: #3B82F6; color: white; }
        .government-badge { background-color: #10B981; color: white; }
        .tech-badge { background-color: #8B5CF6; color: white; }
        .loader { border-top-color: #3B82F6; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .alert { transition: opacity 0.5s ease; }
        #refreshButton:disabled { background-color: #a0aec0; cursor: not-allowed; }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <?php include '../../includes/header.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div id="successAlert" class="hidden bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6">
            <p><?= htmlspecialchars($_GET['success'] ?? 'Job posted successfully!') ?></p>
        </div>

        <div class="mb-8 text-center">
            <h1 class="text-4xl font-bold text-gray-900">Global Job Opportunities</h1>
            <p class="text-gray-600 mt-2 text-lg">Explore top positions from around the world</p>
        </div>

        <div class="mb-8 bg-white rounded-xl shadow-md p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="text" id="searchInput" placeholder="Search by title, company, or keyword" 
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <select id="jobTypeFilter" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500">
                    <option value="">All Job Types</option>
                    <option value="intern">📚 Intern</option>
                    <option value="full-time">💼 Full-Time</option>
                    <option value="part-time">⌛ Part-Time</option>
                    <option value="remote">🏠 Remote</option>
                    <option value="freelance">🔄 Freelance</option>
                </select>
                <select id="locationFilter" class="border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="">All Locations</option>
                    <option value="remote">Remote</option>
                    <option value="dar es salaam">Dar es Salaam</option>
                    <option value="arusha">Arusha</option>
                    <option value="mwanza">Mwanza</option>
                    <option value="zanzibar">Zanzibar</option>
                </select>
                <select id="sortFilter" class="border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                    <option value="date-desc">Newest First</option>
                    <option value="date-asc">Oldest First</option>
                    <option value="title-asc">Title A-Z</option>
                    <option value="title-desc">Title Z-A</option>
                </select>
            </div>
            <div class="mt-4 flex flex-wrap gap-2" id="categoryFilters">
                <span data-category="government" class="category-badge government-badge px-3 py-1 rounded-full text-sm cursor-pointer">Government</span>
                <span data-category="ngo" class="category-badge bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm cursor-pointer">NGO</span>
                <span data-category="banking" class="category-badge bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm cursor-pointer">Banking</span>
                <span data-category="teaching" class="category-badge bg-yellow-100 text-yellow-800 px-3 py-1 rounded-full text-sm cursor-pointer">Teaching</span>
                <span data-category="tech" class="category-badge tech-badge px-3 py-1 rounded-full text-sm cursor-pointer">Tech</span>
                <span data-category="marketing" class="category-badge bg-pink-100 text-pink-800 px-3 py-1 rounded-full text-sm cursor-pointer">Marketing</span>
                <span data-category="design" class="category-badge bg-orange-100 text-orange-800 px-3 py-1 rounded-full text-sm cursor-pointer">Design</span>
                <span data-category="devops" class="category-badge bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-sm cursor-pointer">DevOps</span>
            </div>
        </div>

        <div class="mb-6 flex flex-col sm:flex-row justify-between items-center">
            <h2 class="text-2xl font-semibold">
                <span id="totalJobs" class="text-blue-600">0</span> Opportunities
            </h2>
            <div class="mt-4 sm:mt-0 flex items-center space-x-4">
                <button id="refreshButton" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center">
                    <i class="fas fa-sync-alt mr-2"></i> Refresh
                </button>
                <a href="post_job.php" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center">
                    <i class="fas fa-plus mr-2"></i> Post a Job
                </a>
                <a href="manage_jobs.php" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 flex items-center">
                    <i class="fas fa-cog mr-2"></i> Manage Your Jobs
                </a>
            </div>
        </div>

        <div id="loading" class="hidden flex justify-center items-center py-10">
            <div class="loader w-12 h-12 border-4 border-gray-200 rounded-full"></div>
            <span class="ml-4 text-gray-600">Loading jobs...</span>
        </div>
        <div id="noResults" class="hidden bg-yellow-50 p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-yellow-800">No Jobs Found</h3>
            <p class="text-yellow-600 mt-2">Try adjusting your filters or refreshing the page.</p>
        </div>
        <div id="error" class="hidden bg-red-50 p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-red-800">Error Loading Jobs</h3>
            <p id="errorMessage" class="text-red-600 mt-2">Failed to load jobs. Please try again.</p>
            <button id="retryButton" class="mt-4 px-4 py-2 bg-red-100 text-red-800 rounded-lg hover:bg-red-200">Retry</button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="jobsContainer"></div>

        <div id="applicationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white rounded-lg p-8 max-w-md w-full">
                <h2 class="text-2xl font-bold mb-4">Apply for Job</h2>
                <form id="applicationForm" enctype="multipart/form-data">
                    <input type="hidden" id="jobId" name="job_id">
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Full Name</label>
                        <input type="text" name="full_name" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Phone</label>
                        <input type="tel" name="phone" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Resume (PDF, max 5MB)</label>
                        <input type="file" name="resume" accept=".pdf" class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:border-blue-500">
                    </div>
                    <div class="flex justify-end space-x-4">
                        <button type="button" class="px-4 py-2 text-gray-600 hover:text-gray-800" onclick="closeModal()">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Apply</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let allJobs = [];
        let filteredJobs = [];
        let isFetching = false;

        async function fetchJobs(forceRefresh = false) {
            if (isFetching) return;
            isFetching = true;
            const refreshButton = document.getElementById('refreshButton');
            refreshButton.disabled = true;

            showLoading(true);
            showError(false);
            showNoResults(false);

            try {
                const timestamp = new Date().getTime();
                const url = forceRefresh ? `jobs_fetch.php?refresh=true&t=${timestamp}` : `jobs_fetch.php?t=${timestamp}`;
                const response = await fetch(url, {
                    cache: 'no-store',
                    headers: { 'Cache-Control': 'no-cache', 'Pragma': 'no-cache' }
                });

                if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
                const data = await response.json();
                if (!Array.isArray(data)) throw new Error(data.error || 'Invalid data format');

                allJobs = data.map(job => ({
                    ...job,
                    categories: job.categories ? JSON.parse(job.categories) : [],
                    isRemote: job.is_remote,
                    region: job.region?.toLowerCase() || job.location?.toLowerCase() || '',
                    type: job.type?.toLowerCase().replace(' ', '-') || '',
                    title: job.title || 'Untitled Job',
                    company: job.company || 'Unknown Company',
                    description: job.description || 'No description available',
                    location: job.location || 'Unknown Location',
                    date_posted: job.date_posted || 'Unknown Date'
                }));
                applyFilters();
            } catch (error) {
                console.error('Fetch Error:', error);
                showError(true, `Failed to load jobs: ${error.message}`);
            } finally {
                showLoading(false);
                isFetching = false;
                refreshButton.disabled = false;
            }
        }

        function applyFilters() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const jobType = document.getElementById('jobTypeFilter').value;
            const location = document.getElementById('locationFilter').value;
            const sort = document.getElementById('sortFilter').value;
            const activeCategories = Array.from(document.querySelectorAll('.category-badge.active'))
                                        .map(b => b.dataset.category.toLowerCase());

            filteredJobs = allJobs.filter(job => {
                const matchesSearch = [
                    job.title.toLowerCase(),
                    job.company.toLowerCase(),
                    job.description.toLowerCase()
                ].some(text => text.includes(searchTerm));
                
                const matchesType = !jobType || job.type === jobType;
                const matchesLocation = !location || 
                    (location === 'remote' ? job.isRemote : job.region.includes(location));
                const matchesCategory = !activeCategories.length || 
                    activeCategories.some(cat => job.categories.includes(cat));
                
                return matchesSearch && matchesType && matchesLocation && matchesCategory;
            });

            filteredJobs.sort((a, b) => {
                if (sort === 'date-desc') return new Date(b.date_posted) - new Date(a.date_posted);
                if (sort === 'date-asc') return new Date(a.date_posted) - new Date(b.date_posted);
                if (sort === 'title-asc') return a.title.localeCompare(b.title);
                if (sort === 'title-desc') return b.title.localeCompare(a.title);
                return 0;
            });

            updateUI();
        }

        function updateUI() {
            const container = document.getElementById('jobsContainer');
            const totalJobsEl = document.getElementById('totalJobs');
            if (!container || !totalJobsEl) return;

            container.innerHTML = '';
            totalJobsEl.textContent = filteredJobs.length;

            if (filteredJobs.length === 0) {
                showNoResults(true);
                return;
            }

            filteredJobs.forEach(job => {
                const jobEl = document.createElement('div');
                jobEl.className = 'job-card bg-white rounded-xl shadow-md p-6';
                jobEl.innerHTML = `
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">${job.title}</h3>
                            <p class="text-gray-600 text-sm">${job.company}</p>
                        </div>
                        ${job.isRemote ? '<span class="remote-badge text-xs px-2 py-1 rounded-full">Remote</span>' : ''}
                    </div>
                    <div class="flex flex-wrap gap-2 mb-4">
                        ${job.categories.map(cat => `
                            <span class="text-xs px-2 py-1 rounded-full ${getCategoryClass(cat)}">${cat}</span>
                        `).join('')}
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                        <div><i class="fas fa-map-marker-alt mr-2"></i> ${job.location}</div>
                        <div><i class="fas fa-clock mr-2"></i> ${job.type}</div>
                        <div><i class="fas fa-money-bill-wave mr-2"></i> ${job.salary || 'Not Specified'}</div>
                        <div><i class="fas fa-calendar-alt mr-2"></i> ${job.date_posted}</div>
                    </div>
                    <div class="mt-4 flex justify-between items-center">
                        <p class="text-sm text-gray-500 truncate">${job.description}</p>
                        <button onclick="openApplication('${job.id}')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Apply</button>
                    </div>
                `;
                container.appendChild(jobEl);
            });
        }

        function getCategoryClass(category) {
            const classes = {
                government: 'government-badge',
                ngo: 'bg-purple-100 text-purple-800',
                banking: 'bg-blue-100 text-blue-800',
                teaching: 'bg-yellow-100 text-yellow-800',
                tech: 'tech-badge',
                marketing: 'bg-pink-100 text-pink-800',
                design: 'bg-orange-100 text-orange-800',
                devops: 'bg-indigo-100 text-indigo-800'
            };
            return classes[category.toLowerCase()] || 'bg-gray-100 text-gray-800';
        }

        function showLoading(show) {
            document.getElementById('loading')?.classList.toggle('hidden', !show);
        }

        function showError(show, message = 'Failed to load jobs. Please try again.') {
            const errorEl = document.getElementById('error');
            if (errorEl) {
                errorEl.classList.toggle('hidden', !show);
                if (show) document.getElementById('errorMessage').textContent = message;
            }
        }

        function showNoResults(show) {
            document.getElementById('noResults')?.classList.toggle('hidden', !show);
        }

        function showSuccessAlert() {
            const alert = document.getElementById('successAlert');
            if (alert) {
                alert.classList.remove('hidden');
                setTimeout(() => {
                    alert.classList.add('opacity-0');
                    setTimeout(() => alert.classList.add('hidden'), 500);
                    alert.classList.remove('opacity-0');
                }, 5000);
            }
        }

        function openApplication(jobId) {
            document.getElementById('jobId').value = jobId;
            document.getElementById('applicationModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('applicationModal').classList.add('hidden');
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.category-badge').forEach(badge => {
                badge.addEventListener('click', () => {
                    badge.classList.toggle('active');
                    applyFilters();
                });
            });

            ['searchInput', 'jobTypeFilter', 'locationFilter', 'sortFilter'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.addEventListener(id === 'searchInput' ? 'input' : 'change', applyFilters);
            });

            document.getElementById('refreshButton')?.addEventListener('click', () => fetchJobs(true));
            document.getElementById('retryButton')?.addEventListener('click', () => fetchJobs(true));

            document.getElementById('applicationModal').addEventListener('click', (e) => {
                if (e.target === e.currentTarget) closeModal();
            });

            document.getElementById('applicationForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(e.target);
                try {
                    const response = await fetch('apply_job.php', {
                        method: 'POST',
                        body: formData
                    });
                    if (!response.ok) throw new Error('Failed to apply');
                    const result = await response.json();
                    if (result.error) throw new Error(result.error);
                    alert('Application submitted successfully!');
                    closeModal();
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            });

            fetchJobs();
            <?php if (isset($_GET['success'])): ?>
                showSuccessAlert();
            <?php endif; ?>
        });
    </script>
</body>
</html>