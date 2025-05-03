<?php
/**
 * Admin Dashboard
 * Holistic Prosperity Ministry Payment System
 */

// Initialize session
session_start();

// Include configuration and functions
require_once '../includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header("Location: login.php");
    exit;
}

// Get dashboard statistics
try {
    // Total donations
    $stmt = $pdo->query("
        SELECT COUNT(*) as total_count, SUM(amount) as total_amount 
        FROM transactions
    ");
    $totalStats = $stmt->fetch();
    
    // Donations by status
    $stmt = $pdo->query("
        SELECT payment_status, COUNT(*) as count, SUM(amount) as amount 
        FROM transactions 
        GROUP BY payment_status
    ");
    $statusStats = $stmt->fetchAll();
    
    // Donations by payment method
    $stmt = $pdo->query("
        SELECT payment_method, COUNT(*) as count, SUM(amount) as amount 
        FROM transactions 
        GROUP BY payment_method
    ");
    $methodStats = $stmt->fetchAll();
    
    // Donations by category
    $stmt = $pdo->query("
        SELECT c.category_name, COUNT(t.transaction_id) as count, SUM(t.amount) as amount 
        FROM transactions t
        JOIN donation_categories c ON t.category_id = c.category_id
        GROUP BY t.category_id
    ");
    $categoryStats = $stmt->fetchAll();
    
    // Recent transactions
    $stmt = $pdo->query("
        SELECT t.*, d.first_name, d.last_name, d.email, c.category_name
        FROM transactions t
        JOIN donors d ON t.donor_id = d.donor_id
        JOIN donation_categories c ON t.category_id = c.category_id
        ORDER BY t.transaction_date DESC
        LIMIT 10
    ");
    $recentTransactions = $stmt->fetchAll();
    
    // Pending mobile money transactions
    $stmt = $pdo->query("
        SELECT t.*, d.first_name, d.last_name, d.email, m.phone_number, m.provider
        FROM transactions t
        JOIN donors d ON t.donor_id = d.donor_id
        JOIN mobile_money_payments m ON t.transaction_id = m.transaction_id
        WHERE t.payment_status = 'pending'
        ORDER BY t.transaction_date DESC
    ");
    $pendingMoMo = $stmt->fetchAll();
    
} catch (PDOException $e) {
    // Log error
    error_log("Admin Dashboard Error: " . $e->getMessage());
    
    // Set error message
    $_SESSION['error_message'] = "An error occurred while loading dashboard data.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/dataTables.bootstrap5.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="admin-brand mb-4 text-center">
                        <h5 class="text-white">HPM Admin</h5>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="transactions.php">
                                <i class="fas fa-money-bill-wave me-2"></i>
                                Transactions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="donors.php">
                                <i class="fas fa-users me-2"></i>
                                Donors
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="categories.php">
                                <i class="fas fa-tags me-2"></i>
                                Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reports.php">
                                <i class="fas fa-chart-bar me-2"></i>
                                Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="fas fa-cog me-2"></i>
                                Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                                <i class="fas fa-print"></i> Print Report
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="export-csv">
                                <i class="fas fa-file-csv"></i> Export CSV
                            </button>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
                            <i class="fas fa-calendar"></i> This Month
                        </button>
                    </div>
                </div>
                
                <!-- Alert messages -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-white bg-primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Total Donations</h6>
                                        <h3 class="card-text"><?php echo formatCurrency($totalStats['total_amount'] ?? 0); ?></h3>
                                    </div>
                                    <i class="fas fa-donate fa-2x opacity-50"></i>
                                </div>
                                <p class="card-text mt-2 mb-0">
                                    <small><?php echo number_format($totalStats['total_count'] ?? 0); ?> transactions</small>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card text-white bg-success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Completed Payments</h6>
                                        <?php
                                        $completedAmount = 0;
                                        $completedCount = 0;
                                        foreach ($statusStats as $stat) {
                                            if ($stat['payment_status'] === 'completed') {
                                                $completedAmount = $stat['amount'];
                                                $completedCount = $stat['count'];
                                                break;
                                            }
                                        }
                                        ?>
                                        <h3 class="card-text"><?php echo formatCurrency($completedAmount); ?></h3>
                                    </div>
                                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                                </div>
                                <p class="card-text mt-2 mb-0">
                                    <small><?php echo number_format($completedCount); ?> transactions</small>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card text-white bg-warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Pending Payments</h6>
                                        <?php
                                        $pendingAmount = 0;
                                        $pendingCount = 0;
                                        foreach ($statusStats as $stat) {
                                            if ($stat['payment_status'] === 'pending') {
                                                $pendingAmount = $stat['amount'];
                                                $pendingCount = $stat['count'];
                                                break;
                                            }
                                        }
                                        ?>
                                        <h3 class="card-text"><?php echo formatCurrency($pendingAmount); ?></h3>
                                    </div>
                                    <i class="fas fa-clock fa-2x opacity-50"></i>
                                </div>
                                <p class="card-text mt-2 mb-0">
                                    <small><?php echo number_format($pendingCount); ?> transactions</small>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card text-white bg-info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="card-title">Mobile Money</h6>
                                        <?php
                                        $momoAmount = 0;
                                        $momoCount = 0;
                                        foreach ($methodStats as $stat) {
                                            if ($stat['payment_method'] === 'mobile_money') {
                                                $momoAmount = $stat['amount'];
                                                $momoCount = $stat['count'];
                                                break;
                                            }
                                        }
                                        ?>
                                        <h3 class="card-text"><?php echo formatCurrency($momoAmount); ?></h3>
                                    </div>
                                    <i class="fas fa-mobile-alt fa-2x opacity-50"></i>
                                </div>
                                <p class="card-text mt-2 mb-0">
                                    <small><?php echo number_format($momoCount); ?> transactions</small>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Donations by Payment Method</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="paymentMethodChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Donations by Category</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="categoryChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Pending Mobile Money Verifications -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">Pending Mobile Money Verifications</h5>
                    </div>
                    <div class="card-body">
                        <?php if (count($pendingMoMo) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Reference</th>
                                            <th>Date</th>
                                            <th>Donor</th>
                                            <th>Phone Number</th>
                                            <th>Provider</th>
                                            <th>Amount</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pendingMoMo as $transaction): ?>
                                            <tr>
                                                <td><?php echo $transaction['reference_code']; ?></td>
                                                <td><?php echo date('M d, Y', strtotime($transaction['transaction_date'])); ?></td>
                                                <td>
                                                    <?php echo $transaction['first_name'] . ' ' . $transaction['last_name']; ?>
                                                    <small class="d-block text-muted"><?php echo $transaction['email']; ?></small>
                                                </td>
                                                <td><?php echo $transaction['phone_number']; ?></td>
                                                <td><?php echo $transaction['provider']; ?></td>
                                                <td><?php echo formatCurrency($transaction['amount']); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="verify-payment.php?id=<?php echo $transaction['transaction_id']; ?>&action=verify" class="btn btn-success" title="Verify Payment">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                        <a href="verify-payment.php?id=<?php echo $transaction['transaction_id']; ?>&action=reject" class="btn btn-danger" title="Reject Payment">
                                                            <i class="fas fa-times"></i>
                                                        </a>
                                                        <a href="transaction-details.php?id=<?php echo $transaction['transaction_id']; ?>" class="btn btn-info" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-center">No pending mobile money transactions to verify.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Recent Transactions -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Recent Transactions</h5>
                        <a href="transactions.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="recent-transactions">
                                <thead>
                                    <tr>
                                        <th>Reference</th>
                                        <th>Date</th>
                                        <th>Donor</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentTransactions as $transaction): ?>
                                        <tr>
                                            <td><?php echo $transaction['reference_code']; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($transaction['transaction_date'])); ?></td>
                                            <td>
                                                <?php if ($transaction['is_anonymous']): ?>
                                                    <span class="text-muted">Anonymous</span>
                                                <?php else: ?>
                                                    <?php echo $transaction['first_name'] . ' ' . $transaction['last_name']; ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo formatCurrency($transaction['amount']); ?></td>
                                            <td><?php echo getPaymentMethodName($transaction['payment_method']); ?></td>
                                            <td><?php echo $transaction['category_name']; ?></td>
                                            <td>
                                                <?php if ($transaction['payment_status'] === 'completed'): ?>
                                                    <span class="badge bg-success">Completed</span>
                                                <?php elseif ($transaction['payment_status'] === 'pending'): ?>
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                <?php elseif ($transaction['payment_status'] === 'failed'): ?>
                                                    <span class="badge bg-danger">Failed</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Unknown</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="transaction-details.php?id=<?php echo $transaction['transaction_id']; ?>" class="btn btn-info" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="generate-receipt.php?id=<?php echo $transaction['transaction_id']; ?>" class="btn btn-secondary" title="Generate Receipt">
                                                        <i class="fas fa-file-invoice"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Admin JS -->
    <script src="js/admin.js"></script>
    
    <script>
        // Initialize DataTable
        $(document).ready(function() {
            $('#recent-transactions').DataTable({
                pageLength: 5,
                lengthMenu: [5, 10, 25, 50],
                order: [[1, 'desc']]
            });
        });
        
        // Payment Method Chart
        const methodCtx = document.getElementById('paymentMethodChart').getContext('2d');
        const methodChart = new Chart(methodCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    <?php 
                    foreach ($methodStats as $stat) {
                        echo "'" . getPaymentMethodName($stat['payment_method']) . "', ";
                    }
                    ?>
                ],
                datasets: [{
                    data: [
                        <?php 
                        foreach ($methodStats as $stat) {
                            echo $stat['amount'] . ", ";
                        }
                        ?>
                    ],
                    backgroundColor: [
                        '#4e73df',
                        '#1cc88a',
                        '#36b9cc',
                        '#f6c23e',
                        '#e74a3b',
                        '#858796'
                    ],
                    hoverBackgroundColor: [
                        '#2e59d9',
                        '#17a673',
                        '#2c9faf',
                        '#dda20a',
                        '#be2617',
                        '#60616f'
                    ],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }]
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            const dataset = data.datasets[tooltipItem.datasetIndex];
                            const currentValue = dataset.data[tooltipItem.index];
                            const total = dataset.data.reduce((acc, val) => acc + val, 0);
                            const percentage = Math.round((currentValue / total) * 100);
                            return `${data.labels[tooltipItem.index]}: $${currentValue.toFixed(2)} (${percentage}%)`;
                        }
                    }
                },
                legend: {
                    position: 'right'
                },
                cutoutPercentage: 70
            }
        });
        
        // Category Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        const categoryChart = new Chart(categoryCtx, {
            type: 'bar',
            data: {
                labels: [
                    <?php 
                    foreach ($categoryStats as $stat) {
                        echo "'" . $stat['category_name'] . "', ";
                    }
                    ?>
                ],
                datasets: [{
                    label: 'Donation Amount',
                    data: [
                        <?php 
                        foreach ($categoryStats as $stat) {
                            echo $stat['amount'] . ", ";
                        }
                        ?>
                    ],
                    backgroundColor: '#4e73df',
                    hoverBackgroundColor: '#2e59d9',
                    borderColor: '#4e73df',
                    borderWidth: 1
                }]
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }]
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, data) {
                            return '$' + tooltipItem.yLabel.toFixed(2);
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
