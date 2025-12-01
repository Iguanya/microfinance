<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();

$rep_year = date("Y",time());
$rep_month = date("m",time());

//Make array for exporting data
$_SESSION['rep_export'] = array();
$_SESSION['rep_exp_title'] = $rep_year.'-'.$rep_month.'_loans-pending';

//Select Pending Loans from LOANS
$sql_loanpend = "SELECT * FROM loans LEFT JOIN loanstatus ON loans.loanstatus_id = loanstatus.loanstatus_id LEFT JOIN customer ON loans.cust_id = customer.cust_id WHERE loans.loanstatus_id = 1 ORDER BY loan_date, loan_no";
$query_loanpend = db_query($db_link, $sql_loanpend);
checkSQL($db_link, $query_loanpend);
$page_title = 'Pending Loans';
?>
<?php include 'includes/bootstrap_header.php'; ?>

                <h2 class="mb-4"><i class="fa fa-credit-card"></i> Pending Loans</h2>

                <!-- Tab Navigation -->
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <li class="nav-item"><a class="nav-link" href="loans_search.php">Search</a></li>
                    <li class="nav-item"><a class="nav-link" href="loans_act.php">Active Loans</a></li>
                    <li class="nav-item"><a class="nav-link active" href="loans_pend.php">Pending Loans</a></li>
                    <li class="nav-item"><a class="nav-link" href="loans_securities.php">Loan Securities</a></li>
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
                                <th>Loan No.</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Loan Period</th>
                                <th>Principal applied</th>
                                <th>Interest</th>
                                <th>Applied for on</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?PHP
                            $count = 0;
                            while($row_loanpend = db_fetch_assoc($query_loanpend)){
                                echo '<tr>
                                    <td><a href="loan.php?lid='.$row_loanpend['loan_id'].'" class="text-decoration-none">'.$row_loanpend['loan_no'].'</a></td>
                                    <td>'.$row_loanpend['cust_name'].' <small class="text-muted">('.$row_loanpend['cust_no'].')</small></td>
                                    <td><span class="badge bg-warning">'.$row_loanpend['loanstatus_status'].'</span></td>
                                    <td>'.$row_loanpend['loan_period'].'</td>
                                    <td>'.number_format($row_loanpend['loan_principal']).' '.$_SESSION['set_cur'].'</td>
                                    <td>'.number_format(($row_loanpend['loan_repaytotal'] - $row_loanpend['loan_principal'])).' '.$_SESSION['set_cur'].'</td>
                                    <td>'.date("d.m.Y",$row_loanpend['loan_date']).'</td>
                                </tr>';
                                array_push($_SESSION['rep_export'], array("Loan No." => $row_loanpend['loan_no'], "Customer" => $row_loanpend['cust_name'].' ('.$row_loanpend['cust_no'].')', "Status" => $row_loanpend['loanstatus_status'], "Loan Period" => $row_loanpend['loan_period'], "Principal" => $row_loanpend['loan_principal'], "Interest" => ($row_loanpend['loan_repaytotal'] - $row_loanpend['loan_principal']), "Repay Total" => $row_loanpend['loan_repaytotal'], "Applied for on" => date("d.m.Y",$row_loanpend['loan_date'])));
                                $count++;
                            }
                            ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="7"><strong><?PHP echo $count.' pending loan'; if ($count != 1) echo 's'; ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

<?php include 'includes/bootstrap_footer.php'; ?>
