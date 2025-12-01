<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();

//Make array for exporting data
$rep_year = date("Y",time());
$rep_month = date("m",time());
$_SESSION['rep_export'] = array();
$_SESSION['rep_exp_title'] = $rep_year.'-'.$rep_month.'_cust-active';

//Select active customers from CUSTOMER
$query_custact = getCustAct($db_link);
$page_title = 'Active Customers';
?>
<?php include 'includes/bootstrap_header.php'; ?>

                <h2 class="mb-4"><i class="fa fa-users"></i> Active Customers</h2>

                <!-- Tab Navigation -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item"><a class="nav-link" href="cust_search.php">Search</a></li>
                    <li class="nav-item"><a class="nav-link" href="cust_new.php">New Customer</a></li>
                    <li class="nav-item"><a class="nav-link active" href="cust_act.php">Active Customers</a></li>
                    <li class="nav-item"><a class="nav-link" href="cust_inact.php">Inactive Customers</a></li>
                </ul>

                <div class="mb-3">
                    <form action="rep_export.php" method="post" class="d-inline">
                        <button type="submit" name="export_rep" class="btn btn-success"><i class="fa fa-download"></i> Export</button>
                    </form>
                </div>

                <div class="table-section">
                    <table class="table table-hover table-striped">
                        <thead class="table-orange">
                            <tr>
                                <th>Cust. No.</th>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>DoB</th>
                                <th>Occupation</th>
                                <th>Address</th>
                                <th>Phone No.</th>
                                <th>Memb. since</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?PHP
                            $count = 0;
                            while ($row_custact = db_fetch_assoc($query_custact)){
                                echo '<tr>
                                    <td><a href="customer.php?cust='.$row_custact['cust_id'].'" class="text-decoration-none">'.$row_custact['cust_no'].'</a></td>
                                    <td>'.$row_custact['cust_name'].'</td>
                                    <td>'.$row_custact['custsex_name'].'</td>
                                    <td>'.date("d.m.Y",$row_custact['cust_dob']).'</td>
                                    <td>'.$row_custact['cust_occup'].'</td>
                                    <td>'.$row_custact['cust_address'].'</td>
                                    <td>'.$row_custact['cust_phone'].'</td>
                                    <td>'.date("d.m.Y",$row_custact['cust_since']).'</td>
                                </tr>';
                                array_push($_SESSION['rep_export'], array("Cust. No." => $row_custact['cust_no'], "Customer Name" => $row_custact['cust_name'], "DoB" => date("d.m.Y",$row_custact['cust_dob']), "Gender" => $row_custact['custsex_name'], "Occupation" => $row_custact['cust_occup'], "Address" => $row_custact['cust_address'], "Phone No." => $row_custact['cust_phone'], "Member since" => date("d.m.Y",$row_custact['cust_since'])));
                                $count++;
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="8"><strong><?PHP echo $count.' active customer'; if ($count != 1) echo 's'; ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

<?php include 'includes/bootstrap_footer.php'; ?>
