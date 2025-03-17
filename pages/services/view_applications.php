<?php
session_start();
include '../../includes/connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

$jobId = filter_input(INPUT_GET, 'job_id', FILTER_SANITIZE_STRING);
if (!$jobId) {
    header("Location: manage_jobs.php?error=Invalid job ID");
    exit();
}

// Verify job ownership
$stmt = $con->prepare("SELECT id FROM jobs WHERE id = ? AND posted_by = ?");
$stmt->bind_param("si", $jobId, $_SESSION['user_id']);
$stmt->execute();
if (!$stmt->get_result()->fetch_assoc()) {
    header("Location: manage_jobs.php?error=Job not found or unauthorized");
    exit();
}
$stmt->close();

// Fetch applications
$stmt = $con->prepare("SELECT full_name, email, phone, resume_path, applied_at FROM job_applications WHERE job_id = ?");
$stmt->bind_param("s", $jobId);
$stmt->execute();
$applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications for Job <?php echo htmlspecialchars($jobId); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 py-10">
        <h1 class="text-3xl font-bold mb-6">Applications for Job ID: <?php echo htmlspecialchars($jobId); ?></h1>
        <?php if (empty($applications)): ?>
            <p class="text-gray-600">No applications submitted yet.</p>
        <?php else: ?>
            <table class="w-full bg-white shadow-md rounded-lg overflow-hidden">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-4 py-2 text-left">Full Name</th>
                        <th class="px-4 py-2 text-left">Email</th>
                        <th class="px-4 py-2 text-left">Phone</th>
                        <th class="px-4 py-2 text-left">Resume</th>
                        <th class="px-4 py-2 text-left">Applied At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applications as $app): ?>
                        <tr class="border-t">
                            <td class="px-4 py-2"><?php echo htmlspecialchars($app['full_name']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($app['email']); ?></td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($app['phone']); ?></td>
                            <td class="px-4 py-2">
                                <?php if ($app['resume_path']): ?>
                                    <a href="<?php echo htmlspecialchars($app['resume_path']); ?>" target="_blank" class="text-blue-600 hover:underline">View Resume</a>
                                <?php else: ?>
                                    No Resume
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-2"><?php echo htmlspecialchars($app['applied_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <a href="manage_jobs.php" class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Back</a>
    </div>
</body>
</html>