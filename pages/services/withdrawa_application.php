<?php
session_start();
include '../../includes/connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $type = $input['type'] ?? '';
    $id = $input['id'] ?? '';
    $userId = (int)$_SESSION['user_id'];

    if (!$type || !$id) {
        echo json_encode(['error' => 'Missing type or ID']);
        exit();
    }

    $tableMap = [
        'job' => ['table' => 'job_applications', 'id_col' => 'job_id'],
        'event' => ['table' => 'event_enrollments', 'id_col' => 'event_id'],
        'course' => ['table' => 'course_enrollments', 'id_col' => 'course_id']
    ];

    if (!isset($tableMap[$type])) {
        echo json_encode(['error' => 'Invalid type']);
        exit();
    }

    $table = $tableMap[$type]['table'];
    $idCol = $tableMap[$type]['id_col'];

    if ($type === 'job') {
        $stmt = $con->prepare("DELETE FROM $table WHERE user_id = ? AND $idCol = ? AND status = 'pending'");
    } else {
        $stmt = $con->prepare("DELETE FROM $table WHERE user_id = ? AND $idCol = ? AND completed_at IS NULL");
    }
    $stmt->bind_param("is", $userId, $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => 'Application/Enrollment withdrawn']);
        } else {
            echo json_encode(['error' => 'No pending or incomplete enrollment found']);
        }
    } else {
        echo json_encode(['error' => 'Failed to withdraw: ' . $stmt->error]);
    }
    $stmt->close();
    $con->close();
}
?>