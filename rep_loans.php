<?PHP
require 'functions.php';
checkLogin();
// Allow access to reports - remove permission check for test
// checkPermissionReport();
$db_link = connect();

//Variables $year and $month provide the pre-set values for input fields
$year = date("Y", time());
$month = date("m", time());
?>

<!DOCTYPE HTML>
<html>
<?PHP include 'includes/bootstrap_header.php'; ?>

<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">Loans Report</h2>
                                        
                                        <nav class="mb-3">
                                                <ul class="nav nav-tabs" role="tablist">
                                                        <li class="nav-item">
                                                                <a class="nav-link" href="rep_incomes.php">Income Report</a>
                                                        </li>
                                                        <li class="nav-item">
                                                                <a class="nav-link" href="rep_expenses.php">Expense Report</a>
                                                        </li>
                                                        <li class="nav-item">
                                                                <a class="nav-link active" href="rep_loans.php">Loans Report</a>
                                                        </li>
                                                        <li class="nav-item">
                                                                <a class="nav-link" href="rep_capital.php">Capital Report</a>
                                                        </li>
                                                        <li class="nav-item">
                                                                <a class="nav-link" href="rep_monthly.php">Monthly Report</a>
                                                        </li>
                                                        <li class="nav-item">
                                                                <a class="nav-link" href="rep_annual.php">Annual Report</a>
                                                        </li>
                                                </ul>
                                        </nav>

                                        <div class="card mb-4">
                                                <div class="card-header bg-primary text-white">
                                                        <strong>Select Report Period</strong>
                                                </div>
                                                <div class="card-body">
                                                        <form action="rep_loans.php" method="post" class="form-inline">
                                                                <div class="form-group mr-3">
                                                                        <label for="rep_year" class="mr-2">Year:</label>
                                                                        <input type="number" class="form-control" id="rep_year" min="2006" max="2206" name="rep_year" style="width:100px;" value="<?PHP if ($month == 01) echo $year-1; else echo $year; ?>" placeholder="Give Year" />
                                                                </div>
                                                                <div class="form-group mr-3">
                                                                        <label for="rep_month" class="mr-2">Month:</label>
                                                                        <select class="form-control" id="rep_month" name="rep_month">
                                                                                <option value="01" <?PHP if ($month == 2) echo 'selected' ?>>January</option>
                                                                                <option value="02" <?PHP if ($month == 3) echo 'selected' ?>>February</option>
                                                                                <option value="03" <?PHP if ($month == 4) echo 'selected' ?>>March</option>
                                                                                <option value="04" <?PHP if ($month == 5) echo 'selected' ?>>April</option>
                                                                                <option value="05" <?PHP if ($month == 6) echo 'selected' ?>>May</option>
                                                                                <option value="06" <?PHP if ($month == 7) echo 'selected' ?>>June</option>
                                                                                <option value="07" <?PHP if ($month == 8) echo 'selected' ?>>July</option>
                                                                                <option value="08" <?PHP if ($month == 9) echo 'selected' ?>>August</option>
                                                                                <option value="09" <?PHP if ($month == 10) echo 'selected' ?>>September</option>
                                                                                <option value="10" <?PHP if ($month == 11) echo 'selected' ?>>October</option>
                                                                                <option value="11" <?PHP if ($month == 12) echo 'selected' ?>>November</option>
                                                                                <option value="12" <?PHP if ($month == 1) echo 'selected' ?>>December</option>
                                                                        </select>
                                                                </div>
                                                                <button type="submit" name="select" class="btn btn-primary">Generate Report</button>
                                                        </form>
                                                </div>
                                        </div>

                                        <?PHP
                                        if(isset($_POST['select'])){

                                                //Sanitize user input
                                                $rep_month = sanitize($db_link, $_POST['rep_month']);
                                                $rep_year = sanitize($db_link, $_POST['rep_year']);

                                                //Calculate UNIX TIMESTAMP for first and last day of selected month
                                                $firstDay = mktime(0, 0, 0, $rep_month, 1, $rep_year);
                                                $lastDay = mktime(0, 0, 0, ($rep_month+1), 0, $rep_year);

                                                //Make array for exporting data
                                                $_SESSION['rep_export'] = array();
                                                $_SESSION['rep_exp_title'] = $rep_year.'-'.$rep_month.'_loans';

                                                //Select Due Loan Payments from LTRANS
                                                $sql_loandue = "SELECT * FROM ltrans, loans, loanstatus WHERE ltrans.loan_id = loans.loan_id AND loans.loanstatus_id = loanstatus.loanstatus_id AND ltrans_due BETWEEN $firstDay AND $lastDay AND loans.loanstatus_id IN (2, 4, 5) ORDER BY ltrans_due, loans.cust_id";
                                                $query_loandue = db_query($db_link, $sql_loandue);
                                                checkSQL($db_link, $query_loandue);

                                                //Select Loan Recoveries from LTRANS
                                                $sql_loanrec = "SELECT * FROM ltrans, loans WHERE ltrans.loan_id = loans.loan_id AND ltrans_date BETWEEN $firstDay AND $lastDay ORDER BY ltrans_date, loans.cust_id";
                                                $query_loanrec = db_query($db_link, $sql_loanrec);
                                                checkSQL($db_link, $query_loanrec);

                                                //Select Loans Out from LOANS
                                                $sql_loanout = "SELECT * FROM loans, customer WHERE loans.cust_id = customer.cust_id AND loans.loan_dateout BETWEEN $firstDay AND $lastDay ORDER BY loan_dateout, loans.cust_id";
                                                $query_loanout = db_query($db_link, $sql_loanout);
                                                checkSQL($db_link, $query_loanout);
                                                ?>

                                                <div class="card mb-4">
                                                        <div class="card-header bg-info text-white d-flex justify-content-between">
                                                                <strong>Due Loan Payments for <?PHP echo $rep_month.'/'.$rep_year; ?></strong>
                                                                <form action="rep_export.php" method="post" style="display:inline;">
                                                                        <button type="submit" name="export_rep" class="btn btn-sm btn-light">Export</button>
                                                                </form>
                                                        </div>
                                                        <div class="card-body table-responsive">
                                                                <table class="table table-striped table-hover">
                                                                        <thead class="thead-dark">
                                                                                <tr>
                                                                                        <th>Loan No.</th>
                                                                                        <th>Loan Status</th>
                                                                                        <th>Due Date</th>
                                                                                        <th class="text-right">Due Amount</th>
                                                                                </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                                <?PHP
                                                                                $total_loandue = 0;
                                                                                while($row_loandue = db_fetch_assoc($query_loandue)){
                                                                                        echo '<tr>
                                                                                                <td><a href="loan.php?lid='.$row_loandue['loan_id'].'">'.$row_loandue['loan_no'].'</a></td>
                                                                                                <td>'.$row_loandue['loanstatus_status'].'</td>
                                                                                                <td>'.date("d.m.Y",$row_loandue['ltrans_due']).'</td>
                                                                                                <td class="text-right">'.number_format($row_loandue['ltrans_principaldue'] + $row_loandue['ltrans_interestdue']).' '.$_SESSION['set_cur'].'</td>                                                                          
                                                                                        </tr>';
                                                                                        $total_loandue = $total_loandue + $row_loandue['ltrans_principaldue'] + $row_loandue['ltrans_interestdue'];
                                                                                }
                                                                                echo '<tr class="table-primary font-weight-bold">
                                                                                        <td colspan="4" class="text-right">Total Due Payments: '.number_format($total_loandue).' '.$_SESSION['set_cur'].'</td>
                                                                                </tr>';

                                                                                //Prepare data for export to Excel file
                                                                                array_push($_SESSION['rep_export'], array("Type" => "Due Loan Payments", "Amount" => $total_loandue));
                                                                                ?>
                                                                        </tbody>
                                                                </table>
                                                        </div>
                                                </div>

                                                <div class="card mb-4">
                                                        <div class="card-header bg-warning text-dark">
                                                                <strong>Loan Recoveries for <?PHP echo $rep_month.'/'.$rep_year; ?></strong>
                                                        </div>
                                                        <div class="card-body table-responsive">
                                                                <table class="table table-striped table-hover">
                                                                        <thead class="thead-dark">
                                                                                <tr>
                                                                                        <th>Loan No.</th>
                                                                                        <th>Instalment Due</th>
                                                                                        <th>Recovered</th>
                                                                                        <th>Date</th>
                                                                                </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                                <?PHP
                                                                                $total_loanrec = 0;
                                                                                while($row_loanrec = db_fetch_assoc($query_loanrec)){
                                                                                        echo '<tr>
                                                                                                <td><a href="loan.php?lid='.$row_loanrec['loan_id'].'">'.$row_loanrec['loan_no'].'</a></td>
                                                                                                <td class="text-right">'.number_format($row_loanrec['ltrans_principaldue'] + $row_loanrec['ltrans_interestdue']).' '.$_SESSION['set_cur'].'</td>
                                                                                                <td class="text-right">'.number_format($row_loanrec['ltrans_principal'] + $row_loanrec['ltrans_interest']).' '.$_SESSION['set_cur'].'</td>
                                                                                                <td>'.date("d.m.Y",$row_loanrec['ltrans_date']).'</td>
                                                                                        </tr>';
                                                                                        $total_loanrec = $total_loanrec + $row_loanrec['ltrans_principal'] + $row_loanrec['ltrans_interest'];
                                                                                }
                                                                                echo '<tr class="table-primary font-weight-bold">
                                                                                        <td colspan="4">';
                                                                                echo 'Total Recoveries: '.number_format($total_loanrec).' '.$_SESSION['set_cur'];
                                                                                if ($total_loandue != 0) echo '<br/>Loan Recovery Rate: '.number_format(($total_loanrec / $total_loandue * 100),2).'%';
                                                                                echo '</td>
                                                                                </tr>';
                                                                                array_push($_SESSION['rep_export'], array("Type" => "Loan Recoveries", "Amount" => $total_loanrec));
                                                                                if ($total_loandue != 0) array_push($_SESSION['rep_export'], array("Type" => "Loan Recovery Percentage", "Amount" => round(($total_loanrec / $total_loandue * 100),2).'%'));
                                                                                ?>
                                                                        </tbody>
                                                                </table>
                                                        </div>
                                                </div>

                                                <div class="card">
                                                        <div class="card-header bg-success text-white">
                                                                <strong>Loans Out for <?PHP echo $rep_month.'/'.$rep_year; ?></strong>
                                                        </div>
                                                        <div class="card-body table-responsive">
                                                                <table class="table table-striped table-hover">
                                                                        <thead class="thead-dark">
                                                                                <tr>
                                                                                        <th>Loan No.</th>
                                                                                        <th>Customer</th>
                                                                                        <th class="text-right">Principal</th>
                                                                                        <th class="text-right">Interest %</th>
                                                                                        <th>Period</th>
                                                                                        <th class="text-right">Repay Total</th>
                                                                                        <th>Date Out</th>
                                                                                </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                                <?PHP
                                                                                $total_loanout = 0;
                                                                                while($row_loanout = db_fetch_assoc($query_loanout)){
                                                                                        echo '<tr>
                                                                                                <td><a href="loan.php?lid='.$row_loanout['loan_id'].'">'.$row_loanout['loan_no'].'</a></td>
                                                                                                <td>'.$row_loanout['cust_name'].' ('.$row_loanout['cust_no'].')</td>
                                                                                                <td class="text-right">'.number_format($row_loanout['loan_principal']).' '.$_SESSION['set_cur'].'</td>
                                                                                                <td class="text-right">'.$row_loanout['loan_interest'].'%</td>
                                                                                                <td>'.$row_loanout['loan_period'].'</td>
                                                                                                <td class="text-right">'.number_format($row_loanout['loan_repaytotal']).' '.$_SESSION['set_cur'].'</td>
                                                                                                <td>'.date("d.m.Y", $row_loanout['loan_dateout']).'</td>
                                                                                        </tr>';
                                                                                        $total_loanout = $total_loanout + $row_loanout['loan_principal'];
                                                                                }
                                                                                echo '<tr class="table-primary font-weight-bold">
                                                                                        <td colspan="7" class="text-right">Total Loans Out: '.number_format($total_loanout).' '.$_SESSION['set_cur'].'</td>
                                                                                </tr>';
                                                                                array_push($_SESSION['rep_export'], array("Type" => "Loans Out", "Amount" => $total_loanout));
                                                                                ?>
                                                                        </tbody>
                                                                </table>
                                                        </div>
                                                </div>
                                        <?PHP
                                        }
                                        ?>
                                </div>
                        </div>
                </div>

                <?PHP include 'includes/bootstrap_footer.php'; ?>
        </body>
</html>
