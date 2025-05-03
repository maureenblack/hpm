<?php
/**
 * Join Ministry Confirmation
 * Holistic Prosperity Ministry
 */

// Get the ministry from the URL parameter
$ministry = isset($_GET['ministry']) ? $_GET['ministry'] : '';

// Define ministry names for display
$ministryNames = [
    'love-in-action' => 'Love in Action Fellowship',
    'prosperity-counseling' => 'Prosperity Counseling',
    'faith-worship' => 'Faith & Worship Ministry',
    'community' => 'Community Impact Projects',
    'cryptstock' => 'CrypStock Prosperity Academy'
];

// Get the display name
$ministryName = isset($ministryNames[$ministry]) ? $ministryNames[$ministry] : 'Ministry';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Confirmed - Holistic Prosperity Ministry</title>
    <link rel="icon" href="images/hpm-favicon.svg" type="image/svg+xml">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/responsive.css">
    <style>
        .confirmation-header {
            background-color: #4B0082;
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .confirmation-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .btn-primary {
            background-color: #4B0082;
            border-color: #4B0082;
        }
        .btn-primary:hover {
            background-color: #3a006a;
            border-color: #3a006a;
        }
        .check-icon {
            font-size: 5rem;
            color: #28a745;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <img src="images/hpm-logo.svg" alt="Holistic Prosperity Ministry Logo" height="70">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.html">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.html">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="ministries.html">Ministries</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.html">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="donate-form.php">Donate</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Confirmation Header -->
    <header class="confirmation-header mt-5 pt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1>Application Confirmed</h1>
                    <p class="lead">Thank you for your interest in joining our ministry</p>
                </div>
            </div>
        </div>
    </header>

    <!-- Confirmation Content -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card confirmation-card">
                        <div class="card-body p-4 p-md-5 text-center">
                            <i class="fas fa-check-circle check-icon"></i>
                            <h2 class="mb-4">Your Application Has Been Received</h2>
                            <p class="lead mb-4">Thank you for applying to join <?php echo htmlspecialchars($ministryName); ?>.</p>
                            <p class="mb-4">Our ministry team will review your application and contact you within 3-5 business days to discuss next steps. Please check your email for a confirmation message.</p>
                            <p class="mb-4">If you have any questions, please contact us at <a href="mailto:hello@holisticprosperityministry.org">hello@holisticprosperityministry.org</a>.</p>
                            <div class="mt-4">
                                <a href="index.html" class="btn btn-primary me-2">Return to Homepage</a>
                                <a href="ministries.html" class="btn btn-outline-primary">Explore Other Ministries</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5>Holistic Prosperity Ministry</h5>
                    <p>Bridging Faith and Financial Empowerment</p>
                    <div class="social-icons mt-3">
                        <a href="#" class="me-2"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5>Contact Us</h5>
                    <address>
                        <p><i class="fas fa-map-marker-alt me-2"></i> 123 Prosperity Lane, Yaoundé</p>
                        <p><i class="fas fa-phone me-2"></i> +237 123 456 789</p>
                        <p><i class="fas fa-envelope me-2"></i> hello@holisticprosperityministry.org</p>
                    </address>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.html" class="text-white">Home</a></li>
                        <li><a href="about.html" class="text-white">About</a></li>
                        <li><a href="ministries.html" class="text-white">Ministries</a></li>
                        <li><a href="contact.html" class="text-white">Contact</a></li>
                        <li><a href="donate-form.php" class="text-white">Donate</a></li>
                    </ul>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> Holistic Prosperity Ministry. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>