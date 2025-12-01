<?php
/**
 * Bootstrap Header Template for mangoO
 * Include this in all pages for consistent Bootstrap styling
 */
?><!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>mangoO | <?php echo isset($page_title) ? $page_title : 'Microfinance'; ?></title>
    <link rel="shortcut icon" href="ico/favicon.ico" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="ico/font-awesome/css/font-awesome.min.css">
    <!-- Custom Dashboard CSS -->
    <link rel="stylesheet" href="css/bootstrap-dashboard.css" />
    <style>
        body { background-color: #f8f9fa; }
        .content-wrapper { margin-top: 20px; }
        .page-card { background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 20px; margin-bottom: 20px; }
        .breadcrumb-nav { background-color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .form-section { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .form-section h5 { color: #FF8C00; margin-bottom: 20px; border-bottom: 2px solid #FF8C00; padding-bottom: 10px; }
        .table-section { background: white; padding: 20px; border-radius: 8px; }
        .btn-primary { background-color: #FF8C00; border-color: #FF8C00; }
        .btn-primary:hover { background-color: #e67e00; border-color: #e67e00; }
    </style>
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
                    <li class="nav-item"><a class="nav-link" href="start.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="cust_search.php"><i class="fa fa-users"></i> Customers</a></li>
                    <li class="nav-item"><a class="nav-link" href="loans_search.php"><i class="fa fa-credit-card"></i> Loans</a></li>
                    <li class="nav-item"><a class="nav-link" href="books_expense.php"><i class="fa fa-calculator"></i> Accounting</a></li>
                    <?PHP if (isset($_SESSION['log_report']) && $_SESSION['log_report'] == 1): ?>
                    <li class="nav-item"><a class="nav-link" href="rep_incomes.php"><i class="fa fa-line-chart"></i> Reports</a></li>
                    <?PHP endif; ?>
                    <?PHP if (isset($_SESSION['log_admin']) && $_SESSION['log_admin'] == 1): ?>
                    <li class="nav-item"><a class="nav-link" href="set_basic.php"><i class="fa fa-cog"></i> Settings</a></li>
                    <?PHP endif; ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fa fa-user"></i> <?PHP echo isset($_SESSION['log_user']) ? $_SESSION['log_user'] : 'User'; ?>
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
            <nav class="col-md-2 d-md-block bg-light sidebar" style="min-height: 80vh;">
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
                            <a class="nav-link" href="acc_sav_list.php">
                                <i class="fa fa-piggy-bank"></i> Savings
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main role="main" class="col-md-10 ms-sm-auto px-md-4 content-wrapper">
