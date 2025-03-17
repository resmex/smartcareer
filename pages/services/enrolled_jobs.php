<?php
session_start();
include '../../includes/connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

$userId = (int)$_SESSION['user_id'];
$stmt = $con->prepare("SELECT j.id, j.title, j.company, ja.status, ja.applied_at FROM jobs j INNER JOIN job_applications ja ON j.id = ja.job_id WHERE ja.user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$jobs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Job Applications | SmartCareer</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f3f4f6; }
        .card { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); padding: 1.5rem; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <div class="max-w-7xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">My Job Applications</h1>
        <div class="card">
            <?php if (empty($jobs)): ?>
                <p class="text-gray-500">You haven’t applied to any jobs yet.</p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($jobs as $job): ?>
                        <div class="p-4 bg-gray-50 rounded-md">
                            <p class="font-medium text-gray-700"><?php echo htmlspecialchars($job['title']); ?></p>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($job['company']); ?> | Applied: <?php echo htmlspecialchars($job['applied_at']); ?></p>
                            <p class="text-sm text-gray-500">Status: <span class="font-medium"><?php echo ucfirst(htmlspecialchars($job['status'])); ?></span></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <a href="dashboard.php" class="mt-4 inline-block text-blue-600 hover:underline">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>