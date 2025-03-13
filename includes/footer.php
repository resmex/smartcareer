<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3b82f6; /* Matches user dashboard */
            --secondary-color: #1e3a8a;
            --accent-color: #f59e0b;
        }

        footer {
            background: #ffffff;
            border-top: 1px solid #e5e7eb;
            padding: 2rem 0;
            font-family: 'Inter', sans-serif;
            color: #4b5563;
            position: relative;
            bottom: 0;
            width: 100%;
            z-index: 998; /* Below sidebar and header */
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
        }

        .footer-links a {
            color: #6b7280;
            text-decoration: none;
            margin: 0 1rem;
            font-size: 0.875rem;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--primary-color);
        }

        .footer-social i {
            color: #6b7280;
            font-size: 1.25rem;
            margin: 0 0.75rem;
            transition: color 0.3s ease;
        }

        .footer-social i:hover {
            color: var(--primary-color);
        }

        .footer-text {
            font-size: 0.875rem;
            color: #6b7280;
        }

        @media (max-width: 768px) {
            .footer-container {
                flex-direction: column;
                text-align: center;
            }

            .footer-links, .footer-social {
                margin: 1rem 0;
            }
        }
    </style>
</head>
<body>
    <footer>
        <div class="footer-container">
            <div class="footer-text">
                &copy; <?php echo date('Y'); ?> SmartCareer. All rights reserved.
            </div>
            <div class="footer-links">
                <a href="/smartcareer/pages/about.php">About</a>
                <a href="/smartcareer/pages/contact.php">Contact</a>
                <a href="/smartcareer/pages/privacy.php">Privacy Policy</a>
                <a href="/smartcareer/pages/terms.php">Terms of Service</a>
            </div>
            <div class="footer-social">
                <a href="https://facebook.com" target="_blank"><i class="fab fa-facebook-f"></i></a>
                <a href="https://twitter.com" target="_blank"><i class="fab fa-twitter"></i></a>
                <a href="https://linkedin.com" target="_blank"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </div>
    </footer>
</body>
</html>