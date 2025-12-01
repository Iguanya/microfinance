<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();

$rep_year = date("Y",time());
$rep_month = date("m",time());

//Make array for exporting data
$_SESSION['rep_export'] = array();
$_SESSION['rep_exp_title'] = $rep_year.'-'.$rep_month.'_loans-active';

//Select Active Loans from LOANS
$sql_loans = "SELECT * FROM loans LEFT JOIN loanstatus ON loans.loanstatus_id = loanstatus.loanstatus_id LEFT JOIN customer ON loans.cust_id = customer.cust_id WHERE loans.loanstatus_id = 2 ORDER BY loan_dateout, loans.cust_id";
$query_loans = db_query($db_link, $sql_loans);
checkSQL($db_link, $query_loans);
$page_title = 'Active Loans';
?>
<?php include 'includes/bootstrap_header.php'; ?>

                <h2 class="mb-4"><i class="fa fa-credit-card"></i> Active Loans</h2>

                <!-- Tab Navigation -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link" href="loans_search.php">Search</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="loans_act.php">Active Loans</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="loans_pend.php">Pending Loans</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="loans_securities.php">Loan Securities</a>
                    </li>
                </ul>

                <!-- Export Button -->
                <div class="mb-3">
                    <form action="rep_export.php" method="post" class="d-inline">
                        <button type="submit" name="export_rep" class="btn btn-success"><i class="fa fa-download"></i> Export</button>
                    </form>
                </div>

                <!-- Loans Table -->
                <div class="table-section">
                    <table class="table table-hover table-striped">
                        <thead class="table-orange">
                            <tr>
                                <th>Loan No.</th>
                                <th>Customer</th>
                                <th>Loan Period</th>
                                <th>Principal</th>
                                <th>Interest</th>
                                <th>Remaining</th>
                                <th>Issued on</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?PHP
                            $count = 0;
                            while ($row_loans = db_fetch_assoc($query_loans)){
                                $loan_balances = getLoanBalance($db_link, $row_loans['loan_id']);
                                echo '<tr>
                                    <td><a href="loan.php?lid='.$row_loans['loan_id'].'" class="text-decoration-none">'.$row_loans['loan_no'].'</a></td>
                                    <td>'.$row_loans['cust_name'].' <small class="text-muted">(<a href="customer.php?cust='.$row_loans['cust_id'].'">'.$row_loans['cust_no'].')</a></small></td>
                                    <td>'.$row_loans['loan_period'].'</td>
                                    <td>'.number_format($loan_balances['pdue']).' '.$_SESSION['set_cur'].'</td>
                                    <td>'.number_format($loan_balances['idue']).' '.$_SESSION['set_cur'].'</td>
                                    <td>'.number_format($loan_balances['balance']).' '.$_SESSION['set_cur'].'</td>
                                    <td>'.date("d.m.Y", $row_loans['loan_dateout']).'</td>
                                </tr>';

                                // Export Array
                                array_push($_SESSION['rep_export'], array("Loan No." => $row_loans['loan_no'], "Customer" => $row_loans['cust_name'].' ('.$row_loans['cust_no'].')', "Status" => $row_loans['loanstatus_status'],"Loan Period" => $row_loans['loan_period'], "Principal" => $loan_balances['pdue'], "Interest" => $loan_balances['idue'], "Remaining" => $loan_balances['balance'], "Issued on" => date("d.m.Y", $row_loans['loan_dateout'])));

                                $count++;
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="7">
                                    <strong><?PHP echo $count.' active loan'; if ($count != 1) echo 's'; ?></strong>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

<?php include 'includes/bootstrap_footer.php'; ?>
