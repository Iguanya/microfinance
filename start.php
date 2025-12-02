<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();
getSettings($db_link);
getFees($db_link);

// Get Dashboard Statistics with error handling
try {
    $cust_count = $db_link->query("SELECT COUNT(*) as cnt FROM customer")->fetch(PDO::FETCH_ASSOC)['cnt'] ?: 0;
    $loan_count = $db_link->query("SELECT COUNT(*) as cnt FROM loans")->fetch(PDO::FETCH_ASSOC)['cnt'] ?: 0;
    $emp_count = $db_link->query("SELECT COUNT(*) as cnt FROM employee")->fetch(PDO::FETCH_ASSOC)['cnt'] ?: 0;
    $active_loans = $db_link->query("SELECT COUNT(*) as cnt FROM loans WHERE loanstatus_id = 2")->fetch(PDO::FETCH_ASSOC)['cnt'] ?: 0;
    $sav_total = $db_link->query("SELECT COALESCE(SUM(savbal_balance), 0) as total FROM savbalance")->fetch(PDO::FETCH_ASSOC)['total'] ?: 0;
    $share_total = $db_link->query("SELECT COALESCE(SUM(share_amount), 0) as total FROM shares")->fetch(PDO::FETCH_ASSOC)['total'] ?: 0;
} catch (Exception $e) {
    $cust_count = $loan_count = $emp_count = $active_loans = $sav_total = $share_total = 0;
}

// Loan Status Data
try {
    $loan_status = $db_link->query("SELECT COALESCE(loanstatus_name, 'Unknown') as loanstatus_name, COUNT(*) as cnt FROM loans LEFT JOIN loanstatus ON loans.loanstatus_id = loanstatus.loanstatus_id GROUP BY loans.loanstatus_id")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $loan_status = array();
}

// Top Customers by Loan Amount
try {
    $top_customers = $db_link->query("SELECT customer.cust_name, COUNT(loans.loan_id) as loan_count, COALESCE(SUM(loans.loan_principal), 0) as total_borrowed FROM loans LEFT JOIN customer ON loans.cust_id = customer.cust_id GROUP BY loans.cust_id ORDER BY total_borrowed DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $top_customers = array();
}

?>
<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>mangoO | Dashboard</title>
    <link rel="shortcut icon" href="ico/favicon.ico" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="ico/font-awesome/css/font-awesome.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom Dashboard CSS -->
    <link rel="stylesheet" href="css/bootstrap-dashboard.css" />
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="start.php">
                <i class="fa fa-mango"></i> mangoO
            </a>
            <span class="navbar-text text-light me-3">Microfinance Management</span>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="start.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="cust_search.php"><i class="fa fa-users"></i> Customers</a></li>
                    <li class="nav-item"><a class="nav-link" href="loans_search.php"><i class="fa fa-credit-card"></i> Loans</a></li>
                    <li class="nav-item"><a class="nav-link" href="books_expense.php"><i class="fa fa-calculator"></i> Accounting</a></li>
                    <?PHP if ($_SESSION['log_report'] == 1): ?>
                    <li class="nav-item"><a class="nav-link" href="rep_incomes.php"><i class="fa fa-line-chart"></i> Reports</a></li>
                    <?PHP endif; ?>
                    <?PHP if ($_SESSION['log_admin'] == 1): ?>
                    <li class="nav-item"><a class="nav-link" href="set_basic.php"><i class="fa fa-cog"></i> Settings</a></li>
                    <?PHP endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fa fa-user"></i> <?PHP echo $_SESSION['log_user']; ?>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="logout.php"><i class="fa fa-sign-out"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-md-block bg-light sidebar">
                <div class="position-sticky">
                    <h6 class="px-3 py-3 mb-3 border-bottom">Quick Actions</h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="cust_search.php">
                                <i class="fa fa-search"></i> Search Customer
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="cust_new.php">
                                <i class="fa fa-plus"></i> New Customer
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="loans_search.php">
                                <i class="fa fa-search"></i> Search Loan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="loan_new.php">
                                <i class="fa fa-plus-circle"></i> New Loan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="acc_sav_list.php">
                                <i class="fa fa-piggy-bank"></i> Savings
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main role="main" class="col-md-10 ms-sm-auto px-md-4 main-content">
                <div class="d-flex justify-content-between align-items-center mb-4 pt-3">
                    <h1><i class="fa fa-dashboard"></i> Dashboard</h1>
                    <small class="text-muted"><?PHP echo date('l, j F Y'); ?></small>
                </div>

                <!-- Key Statistics -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <i class="fa fa-users" style="font-size: 2rem; color: var(--info);"></i>
                            <div class="stat-number"><?PHP echo $cust_count; ?></div>
                            <div class="stat-label">Total Customers</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card success">
                            <i class="fa fa-credit-card" style="font-size: 2rem; color: var(--success);"></i>
                            <div class="stat-number"><?PHP echo $active_loans; ?></div>
                            <div class="stat-label">Active Loans</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card warning">
                            <i class="fa fa-money" style="font-size: 2rem; color: var(--warning);"></i>
                            <div class="stat-number"><?PHP echo number_format($sav_total, 0); ?></div>
                            <div class="stat-label">Total Savings</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card danger">
                            <i class="fa fa-building" style="font-size: 2rem; color: var(--danger);"></i>
                            <div class="stat-number"><?PHP echo number_format($share_total, 0); ?></div>
                            <div class="stat-label">Total Shares</div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h5>Loan Status Distribution</h5>
                            <div class="chart-wrapper">
                                <canvas id="loanStatusChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="chart-container">
                            <h5>Top Customers by Loan Amount</h5>
                            <div class="chart-wrapper">
                                <canvas id="topCustomersChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Customers Table -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="chart-container">
                            <h5>Top Borrowers</h5>
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Customer Name</th>
                                        <th>Number of Loans</th>
                                        <th>Total Borrowed</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?PHP foreach ($top_customers as $cust): ?>
                                    <tr>
                                        <td><?PHP echo htmlspecialchars($cust['cust_name'] ?: 'Unknown'); ?></td>
                                        <td><span class="badge bg-info"><?PHP echo $cust['loan_count']; ?></span></td>
                                        <td><?PHP echo number_format($cust['total_borrowed'], 2); ?></td>
                                    </tr>
                                    <?PHP endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Logout Reminder -->
                <?PHP checkLogout(); ?>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Charts -->
    <script>
        // Loan Status Chart
        const loanStatusCtx = document.getElementById('loanStatusChart').getContext('2d');
        const loanStatusData = {
            labels: [<?PHP foreach ($loan_status as $status) echo "'" . htmlspecialchars($status['loanstatus_name']) . "',"; ?>],
            datasets: [{
                label: 'Number of Loans',
                data: [<?PHP foreach ($loan_status as $status) echo $status['cnt'] . ','; ?>],
                backgroundColor: ['#ff6600', '#27ae60', '#e74c3c', '#f39c12', '#3498db'],
                borderColor: '#fff',
                borderWidth: 2
            }]
        };
        new Chart(loanStatusCtx, {
            type: 'doughnut',
            data: loanStatusData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

        // Top Customers Chart
        const topCustomersCtx = document.getElementById('topCustomersChart').getContext('2d');
        const topCustomersData = {
            labels: [<?PHP foreach ($top_customers as $cust) echo "'" . htmlspecialchars(substr($cust['cust_name'], 0, 15)) . "',"; ?>],
            datasets: [{
                label: 'Total Borrowed',
                data: [<?PHP foreach ($top_customers as $cust) echo $cust['total_borrowed'] . ','; ?>],
                backgroundColor: '#ff6600',
                borderColor: '#f38630',
                borderWidth: 2
            }]
        };
        new Chart(topCustomersCtx, {
            type: 'bar',
            data: topCustomersData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } },
                plugins: { legend: { display: false } }
            }
        });
    </script>
</body>
</html>
