<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();

$rep_year = date("Y",time());
$rep_month = date("m",time());

//Make array for exporting data
$_SESSION['rep_export'] = array();
$_SESSION['rep_exp_title'] = $rep_year.'-'.$rep_month.'_cust-inact';

//Select inactive customers from CUSTOMER
$query_custinact = getCustInact($db_link);
$page_title = 'Inactive Customers';
?>
<?php include 'includes/bootstrap_header.php'; ?>

                <h2 class="mb-4"><i class="fa fa-users"></i> Inactive Customers</h2>

                <!-- Tab Navigation -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item"><a class="nav-link" href="cust_search.php">Search</a></li>
                    <li class="nav-item"><a class="nav-link" href="cust_new.php">New Customer</a></li>
                    <li class="nav-item"><a class="nav-link" href="cust_act.php">Active Customers</a></li>
                    <li class="nav-item"><a class="nav-link active" href="cust_inact.php">Inactive Customers</a></li>
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
                            while ($row_custinact = db_fetch_assoc($query_custinact)){
                                echo '<tr>
                                    <td><a href="customer.php?cust='.$row_custinact['cust_id'].'" class="text-decoration-none">'.$row_custinact['cust_no'].'</a></td>
                                    <td>'.$row_custinact['cust_name'].'</td>
                                    <td>'.$row_custinact['custsex_name'].'</td>
                                    <td>'.date("d.m.Y",$row_custinact['cust_dob']).'</td>
                                    <td>'.$row_custinact['cust_occup'].'</td>
                                    <td>'.$row_custinact['cust_address'].'</td>
                                    <td>'.$row_custinact['cust_phone'].'</td>
                                    <td>'.date("d.m.Y",$row_custinact['cust_since']).'</td>
                                </tr>';
                                array_push($_SESSION['rep_export'], array("Cust. No." => $row_custinact['cust_no'], "Customer Name" => $row_custinact['cust_name'], "DoB" => date("d.m.Y",$row_custinact['cust_dob']), "Gender" => $row_custinact['custsex_name'], "Occupation" => $row_custinact['cust_occup'], "Address" => $row_custinact['cust_address'], "Phone No." => $row_custinact['cust_phone'], "Member since" => date("d.m.Y",$row_custinact['cust_since'])));
                                $count++;
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="8"><strong><?PHP echo $count.' inactive customer'; if ($count != 1) echo 's'; ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

<?php include 'includes/bootstrap_footer.php'; ?>
