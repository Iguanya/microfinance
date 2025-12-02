<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
                <a class="navbar-brand" href="start.php">
                        <i class="fa fa-mango"></i> mangoO
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                        <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto">
                                <li class="nav-item"><a class="nav-link" href="start.php"><i class="fa fa-dashboard"></i> Dashboard</a></li>
                                <li class="nav-item"><a class="nav-link" href="cust_search.php"><i class="fa fa-users"></i> Customers</a></li>
                                <li class="nav-item"><a class="nav-link" href="loans_search.php"><i class="fa fa-credit-card"></i> Loans</a></li>
                                <li class="nav-item"><a class="nav-link" href="loan_new.php"><i class="fa fa-plus-circle"></i> New Loan</a></li>
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
