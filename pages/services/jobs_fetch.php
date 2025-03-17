<?php
include '../../includes/connect.php';
header('Content-Type: application/json');

$stmt = $con->prepare("SELECT * FROM jobs ORDER BY date_posted DESC");
$stmt->execute();
$jobs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode($jobs, JSON_PRETTY_PRINT);
?>