<?php
// C:\xampp\htdocs\smartcareer\pages\services\jobs_fetch.php
header('Content-Type: application/json');

function fetchJobs() {
    $dataDir = __DIR__ . '/data';
    $jobsFile = $dataDir . '/jobs.json';

    // Ensure the data directory exists
    if (!is_dir($dataDir)) {
        if (!mkdir($dataDir, 0777, true)) {
            return json_encode(['error' => 'Failed to create data directory.']);
        }
    }

    // Check if the jobs file exists
    if (!file_exists($jobsFile)) {
        return json_encode([]); // Return an empty array if the file doesn't exist
    }

    // Read and decode the jobs file
    $existingJobs = json_decode(file_get_contents($jobsFile), true);
    if (!is_array($existingJobs)) {
        return json_encode([]); // Return an empty array if the file is invalid
    }

    // Process jobs to ensure they have required fields
    foreach ($existingJobs as $index => &$job) {
        // Ensure each job has an ID
        if (!isset($job['id'])) {
            $job['id'] = $index + 1;
        }

        // Ensure each job has a categoryClass
        if (!isset($job['categoryClass'])) {
            $job['categoryClass'] = array_map(function($cat) {
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
            }, $job['categories'] ?? []);
        }
    }
    unset($job); // Break the reference

    return $existingJobs;
}

// Fetch and output jobs
$jobs = fetchJobs();
echo json_encode($jobs, JSON_PRETTY_PRINT);
?>