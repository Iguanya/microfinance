<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();
$page_title = 'Loan Search';
?>
<?php include 'includes/bootstrap_header.php'; ?>

                <h2 class="mb-4"><i class="fa fa-credit-card"></i> Loan Search</h2>

                <!-- Tab Navigation -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" href="loans_search.php">Search</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="loans_act.php">Active Loans</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="loans_pend.php">Pending Loans</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="loans_securities.php">Loan Securities</a>
                    </li>
                </ul>

                <div class="row">
                    <!-- Search by Loan Number -->
                    <div class="col-md-6">
                        <div class="form-section">
                            <h5>Search by Loan Number</h5>
                            <form action="loans_result.php" method="post">
                                <div class="mb-3">
                                    <input type="text" name="loan_no" class="form-control" placeholder="Enter Loan Number" required />
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
                            </form>
                        </div>
                    </div>

                    <!-- Search by Status -->
                    <div class="col-md-6">
                        <div class="form-section">
                            <h5>Search by Loan Status</h5>
                            <form action="loans_result.php" method="post">
                                <div class="mb-3">
                                    <select name="loan_status" class="form-select" required>
                                        <option value="">-- Select Status --</option>
                                        <option value="1">Pending</option>
                                        <option value="2">Approved</option>
                                        <option value="3">Refused</option>
                                        <option value="4">Abandoned</option>
                                        <option value="5">Cleared</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
                            </form>
                        </div>
                    </div>
                </div>

<?php include 'includes/bootstrap_footer.php'; ?>
