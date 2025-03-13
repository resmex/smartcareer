<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$dataDir = __DIR__ . '/data';
$jobsFile = $dataDir . '/jobs.json';
$jobs = file_exists($jobsFile) ? json_decode(file_get_contents($jobsFile), true) : [];
$userJobs = array_filter($jobs, fn($job) => isset($job['posted_by']) && $job['posted_by'] === $userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Your Jobs | SmartCareer</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .job-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .job-card:hover { transform: translateY(-3px); box-shadow: 0 10px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <?php include '../../includes/header.php'; ?>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">Manage Your Jobs</h1>
        <a href="jobs.php" class="mb-6 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Back to Jobs</a>

        <?php if (empty($userJobs)): ?>
            <p class="text-gray-600">You haven't posted any jobs yet.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($userJobs as $job): ?>
                    <div class="job-card bg-white rounded-xl shadow-md p-6">
                        <!-- Job Title and Company -->
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($job['title']); ?></h3>
                                <p class="text-gray-600 text-sm"><?php echo htmlspecialchars($job['company']); ?></p>
                            </div>
                            <?php if ($job['isRemote'] ?? false): ?>
                                <span class="remote-badge text-xs px-2 py-1 rounded-full">Remote</span>
                            <?php endif; ?>
                        </div>

                        <!-- Job Categories -->
                        <div class="flex flex-wrap gap-2 mb-4">
                            <?php foreach ($job['categories'] ?? [] as $cat): ?>
                                <span class="text-xs px-2 py-1 rounded-full <?php echo getCategoryClass($cat); ?>"><?php echo htmlspecialchars($cat); ?></span>
                            <?php endforeach; ?>
                        </div>

                        <!-- Job Details -->
                        <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                            <div><i class="fas fa-map-marker-alt mr-2"></i> <?php echo htmlspecialchars($job['location']); ?></div>
                            <div><i class="fas fa-clock mr-2"></i> <?php echo htmlspecialchars($job['type']); ?></div>
                            <div><i class="fas fa-money-bill-wave mr-2"></i> <?php echo htmlspecialchars($job['salary'] ?? 'Not Specified'); ?></div>
                            <div><i class="fas fa-calendar-alt mr-2"></i> <?php echo htmlspecialchars($job['date_posted'] ?? 'No Date'); ?></div>
                        </div>

                        <!-- Job Description -->
                        <div class="mt-4">
                            <p class="text-sm text-gray-500 truncate"><?php echo htmlspecialchars($job['description']); ?></p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-4 flex space-x-2">
                            <button onclick="deleteJob(<?php echo $job['id']; ?>)" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Delete</button>
                            <button onclick="editJob(<?php echo $job['id']; ?>)" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">Edit</button>
                            <button onclick="viewApplications(<?php echo $job['id']; ?>)" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Applications</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        async function deleteJob(jobId) {
            if (confirm('Are you sure you want to delete this job?')) {
                try {
                    const response = await fetch('manage_jobs.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ action: 'delete', job_id: jobId })
                    });
                    if (!response.ok) throw new Error('Failed to delete job');
                    const result = await response.json();
                    if (result.error) throw new Error(result.error);
                    alert('Job deleted successfully!');
                    location.reload();
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            }
        }

        function editJob(jobId) {
            window.location.href = `edit_job.php?job_id=${jobId}`;
        }

        function viewApplications(jobId) {
            alert(`Viewing applications for job ID ${jobId}. (Feature under development)`);
            // Future: Redirect to an applications management page
        }
    </script>
</body>
</html>

<?php
function getCategoryClass($category) {
    $classes = [
        'government' => 'government-badge',
        'ngo' => 'bg-purple-100 text-purple-800',
        'banking' => 'bg-blue-100 text-blue-800',
        'teaching' => 'bg-yellow-100 text-yellow-800',
        'tech' => 'tech-badge',
        'marketing' => 'bg-pink-100 text-pink-800',
        'design' => 'bg-orange-100 text-orange-800',
        'devops' => 'bg-indigo-100 text-indigo-800'
    ];
    return $classes[$category] ?? 'bg-gray-100 text-gray-800';
}
?>