<?php
session_start();
include '../../includes/connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../pages/login.php");
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobId = filter_input(INPUT_POST, 'job_id', FILTER_SANITIZE_STRING);
    $fullName = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    $userId = (int)$_SESSION['user_id'];
    $appliedAt = date("Y-m-d H:i:s");

    if (!$jobId || !$fullName || !$email || !$phone) {
        echo json_encode(['error' => 'All fields are required']);
        exit();
    }

    // Handle resume upload
    $resumePath = null;
    $uploadDir = __DIR__ . '/uploads/resumes';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $fileType = $_FILES['resume']['type'];
        $fileSize = $_FILES['resume']['size'];
        if ($fileType === 'application/pdf' && $fileSize <= 5 * 1024 * 1024) {
            $newFileName = uniqid() . '_' . basename($_FILES['resume']['name']);
            $destPath = $uploadDir . '/' . $newFileName;
            if (move_uploaded_file($_FILES['resume']['tmp_name'], $destPath)) {
                $resumePath = '/smartcareer/pages/services/uploads/resumes/' . $newFileName;
            } else {
                echo json_encode(['error' => 'Failed to upload resume']);
                exit();
            }
        } else {
            echo json_encode(['error' => 'Invalid resume format or size (PDF, max 5MB)']);
            exit();
        }
    }

    $stmt = $con->prepare("INSERT INTO job_applications (job_id, user_id, full_name, email, phone, resume_path, applied_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sisssss", $jobId, $userId, $fullName, $email, $phone, $resumePath, $appliedAt);

    if ($stmt->execute()) {
        echo json_encode(['success' => 'Application submitted successfully']);
    } else {
        echo json_encode(['error' => 'Failed to save application: ' . $stmt->error]);
    }
    $stmt->close();
    exit();
}
?>