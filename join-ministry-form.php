<?php
/**
 * Join Ministry Form
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

// Process form submission
$formSubmitted = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form inputs
    if (empty($_POST['name'])) {
        $errors['name'] = 'Please enter your name';
    }
    
    if (empty($_POST['email'])) {
        $errors['email'] = 'Please enter your email address';
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }
    
    if (empty($_POST['phone'])) {
        $errors['phone'] = 'Please enter your phone number';
    }
    
    // If no errors, process the form
    if (empty($errors)) {
        // In a real application, you would save this data to a database
        // For now, we'll just redirect to the confirmation page
        header('Location: join-ministry-confirmation.php?ministry=' . urlencode($ministry));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join <?php echo htmlspecialchars($ministryName); ?> - Holistic Prosperity Ministry</title>
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
        .form-header {
            background-color: #4B0082;
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .join-form-card {
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
        .form-control:focus {
            border-color: #4B0082;
            box-shadow: 0 0 0 0.25rem rgba(75, 0, 130, 0.25);
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

    <!-- Form Header -->
    <header class="form-header mt-5 pt-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h1>Join <?php echo htmlspecialchars($ministryName); ?></h1>
                    <p class="lead">Fill out the form below to become part of our ministry community</p>
                </div>
            </div>
        </div>
    </header>

    <!-- Join Ministry Form -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card join-form-card">
                        <div class="card-body p-4 p-md-5">
                            <form method="post" action="">
                                <input type="hidden" name="ministry" value="<?php echo htmlspecialchars($ministry); ?>">
                                
                                <div class="mb-4">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                                    <?php if (isset($errors['name'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                                    <?php if (isset($errors['email'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['email']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                    <?php if (isset($errors['phone'])): ?>
                                        <div class="invalid-feedback"><?php echo $errors['phone']; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="address" class="form-label">Address (Optional)</label>
                                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="experience" class="form-label">Relevant Experience (Optional)</label>
                                    <textarea class="form-control" id="experience" name="experience" rows="3"><?php echo isset($_POST['experience']) ? htmlspecialchars($_POST['experience']) : ''; ?></textarea>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="motivation" class="form-label">Why do you want to join this ministry?</label>
                                    <textarea class="form-control" id="motivation" name="motivation" rows="4"><?php echo isset($_POST['motivation']) ? htmlspecialchars($_POST['motivation']) : ''; ?></textarea>
                                </div>
                                
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" id="agree_terms" name="agree_terms" required>
                                    <label class="form-check-label" for="agree_terms">
                                        I agree to receive communications from Holistic Prosperity Ministry
                                    </label>
                                </div>
                                
                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg">Submit Application</button>
                                </div>
                            </form>
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