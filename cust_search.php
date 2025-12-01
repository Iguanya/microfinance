<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();
$page_title = 'Customer Search';
?>
<?php include 'includes/bootstrap_header.php'; ?>

                <h2 class="mb-4"><i class="fa fa-users"></i> Customer Search</h2>

                <!-- Tab Navigation -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" href="cust_search.php">Search</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cust_new.php">New Customer</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cust_act.php">Active Customers</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cust_inact.php">Inactive Customers</a>
                    </li>
                </ul>

                <div class="row">
                    <!-- Quick Search by ID -->
                    <?PHP
                    if (isset($_SESSION['set_csi']) && $_SESSION['set_csi'] == 1) {
                        echo '
                    <div class="col-md-6">
                        <div class="form-section">
                            <h5>Quick Search by ID</h5>
                            <form action="customer.php" method="get">
                                <div class="mb-3">
                                    <input type="text" name="cust" class="form-control" placeholder="Enter Customer ID" required />
                                </div>
                                <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
                            </form>
                        </div>
                    </div>';
                    }
                    ?>

                    <!-- Detailed Search -->
                    <div class="<?PHP echo (isset($_SESSION['set_csi']) && $_SESSION['set_csi'] == 1) ? 'col-md-6' : 'col-md-12'; ?>">
                        <div class="form-section">
                            <h5>Detailed Customer Search</h5>
                            <form action="cust_result.php" method="post">
                                <div class="mb-3">
                                    <label class="form-label">Customer ID/Number</label>
                                    <input type="text" name="cust_search_no" class="form-control" placeholder="Customer ID or number part" />
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="cust_search_name" class="form-control" placeholder="Customer name or part" />
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Occupation</label>
                                    <input type="text" name="cust_search_occup" class="form-control" placeholder="Occupation or part" />
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Address</label>
                                    <input type="text" name="cust_search_addr" class="form-control" placeholder="Address or address part" />
                                </div>
                                <button type="submit" name="cust_search" class="btn btn-primary"><i class="fa fa-search"></i> Search</button>
                            </form>
                        </div>
                    </div>
                </div>

<?php include 'includes/bootstrap_footer.php'; ?>
