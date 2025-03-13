<?php
$plain_password = 'smartcareer@2025';
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);
echo "Hashed password: " . $hashed_password;

// UPDATE users 
// SET email = 'admin@smartcareer.com', 
//     password = '$2y$10$f8qf10mLmBjRIF2X2E94KOJHBj.CvUlK0UNdUuYMuzfzqcrlHkowi'
// WHERE role = 'admin' AND email = 'remmysm187@gmail.com';
?>

