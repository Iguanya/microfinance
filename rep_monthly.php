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
                <h2 class="mb-4">Monthly Report</h2>
                                        
                                        <nav class="mb-3">
                                                <ul class="nav nav-tabs" role="tablist">
                                                        <li class="nav-item">
                                                                <a class="nav-link" href="rep_incomes.php">Income Report</a>
                                                        </li>
                                                        <li class="nav-item">
                                                                <a class="nav-link" href="rep_expenses.php">Expense Report</a>
                                                        </li>
                                                        <li class="nav-item">
                                                                <a class="nav-link" href="rep_loans.php">Loans Report</a>
                                                        </li>
                                                        <li class="nav-item">
                                                                <a class="nav-link" href="rep_capital.php">Capital Report</a>
                                                        </li>
                                                        <li class="nav-item">
                                                                <a class="nav-link active" href="rep_monthly.php">Monthly Report</a>
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
                                                        <form action="rep_monthly.php" method="post" class="form-inline">
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
                                                $_SESSION['rep_exp_title'] = $rep_year.'-'.$rep_month.'_monthly-report';


                                                /**** INCOME RELATED DATA ****/

                                                //Select INCOMES and INCTYPE
                                                $sql_incomes = "SELECT * FROM incomes WHERE inc_date BETWEEN $firstDay AND $lastDay";
                                                $query_incomes = db_query($db_link, $sql_incomes);
                                                checkSQL($db_link, $query_incomes);

                                                $sql_inctype = "SELECT * FROM inctype";
                                                $query_inctype = db_query($db_link, $sql_inctype);
                                                checkSQL($db_link, $query_inctype);


                                                /**** EXPENDITURE RELATED DATA ****/

                                                //Select Expenses and EXPTYPE
                                                $sql_expendit = "SELECT * FROM expenses WHERE exp_date BETWEEN $firstDay AND $lastDay ORDER BY exp_date";
                                                $query_expendit = db_query($db_link, $sql_expendit);
                                                checkSQL($db_link, $query_expendit);

                                                $sql_exptype = "SELECT * FROM exptype";
                                                $query_exptype = db_query($db_link, $sql_exptype);
                                                checkSQL($db_link, $query_exptype);


                                                /**** LOAN RELATED DATA ****/

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

                                                <div class="row">
                                                        <div class="col-md-6">
                                                                <!-- INCOMES TABLE -->
                                                                <div class="card mb-4">
                                                                        <div class="card-header bg-success text-white d-flex justify-content-between">
                                                                                <strong>Incomes for <?PHP echo $rep_month.'/'.$rep_year; ?></strong>
                                                                                <form action="rep_export.php" method="post" style="display:inline;">
                                                                                        <button type="submit" name="export_rep" class="btn btn-sm btn-light">Export</button>
                                                                                </form>
                                                                        </div>
                                                                        <div class="card-body table-responsive">
                                                                                <table class="table table-sm table-striped">
                                                                                        <thead class="thead-dark">
                                                                                                <tr>
                                                                                                        <th>Type</th>
                                                                                                        <th class="text-right">Amount</th>
                                                                                                </tr>
                                                                                        </thead>
                                                                                        <tbody>
                                                                                                <?PHP
                                                                                                //Make array for income types
                                                                                                $inctype = array();
                                                                                                while($row_inctype = db_fetch_assoc($query_inctype)){
                                                                                                        $inctype[] = $row_inctype;
                                                                                                }

                                                                                                //Make array for all incomes for selected month
                                                                                                $incomes = array();
                                                                                                while($row_incomes = db_fetch_assoc($query_incomes)){
                                                                                                        $incomes[] = $row_incomes;
                                                                                                }

                                                                                                //Iterate over income types and add matching incomes to $total
                                                                                                $total_inc = 0;
                                                                                                foreach ($inctype as $it){
                                                                                                        $total_row = 0;
                                                                                                        foreach ($incomes as $ic) if ($ic['inctype_id'] == $it['inctype_id']) $total_row = $total_row + $ic['inc_amount'];
                                                                                                        echo '<tr>
                                                                                                                <td>'.$it['inctype_type'].'</td>
                                                                                                                <td class="text-right">'.number_format($total_row).' '.$_SESSION['set_cur'].'</td>
                                                                                                        </tr>';
                                                                                                        $total_inc = $total_inc + $total_row;

                                                                                                        //Prepare INCOME data for export to Excel file
                                                                                                        array_push($_SESSION['rep_export'], array("Type" => $it['inctype_type'], "Amount" => $total_row));
                                                                                                }

                                                                                                //Total Incomes Amount
                                                                                                echo '  <tr class="table-primary font-weight-bold">
                                                                                                        <td>Total Incomes:</td>
                                                                                                        <td class="text-right">'.number_format($total_inc).' '.$_SESSION['set_cur'].'</td>
                                                                                                </tr>';
                                                                                                array_push($_SESSION['rep_export'], array("Type" => "Total Incomes", "Amount" => $total_inc));
                                                                                                ?>
                                                                                        </tbody>
                                                                                </table>
                                                                        </div>
                                                                </div>
                                                                
                                                                <!-- EXPENSES TABLE -->
                                                                <div class="card mb-4">
                                                                        <div class="card-header bg-danger text-white">
                                                                                <strong>Expenses for <?PHP echo $rep_month.'/'.$rep_year ?></strong>
                                                                        </div>
                                                                        <div class="card-body table-responsive">
                                                                                <table class="table table-sm table-striped">
                                                                                        <thead class="thead-dark">
                                                                                                <tr>
                                                                                                        <th>Type</th>
                                                                                                        <th class="text-right">Amount</th>
                                                                                                </tr>
                                                                                        </thead>
                                                                                        <tbody>
                                                                                                <?PHP

                                                                                                $exptype = array();
                                                                                                while($row_exptype = db_fetch_assoc($query_exptype)){
                                                                                                        $exptype[] = $row_exptype;
                                                                                                }

                                                                                                $expendit = array();
                                                                                                while($row_expendit = db_fetch_assoc($query_expendit)){
                                                                                                        $expendit[] = $row_expendit;
                                                                                                }

                                                                                                $total_exp = 0;
                                                                                                foreach ($exptype as $et){
                                                                                                        $total_row = 0;
                                                                                                        foreach ($expendit as $ex) if ($ex['exptype_id'] == $et['exptype_id']) $total_row = $total_row + $ex['exp_amount'];
                                                                                                        echo '<tr>
                                                                                                                <td>'.$et['exptype_type'].'</td>
                                                                                                                <td class="text-right">'.number_format($total_row).' '.$_SESSION['set_cur'].'</td>
                                                                                                        </tr>';
                                                                                                        $total_exp = $total_exp + $total_row;

                                                                                                        //Prepare EXPENSE data for export to Excel file
                                                                                                        array_push($_SESSION['rep_export'], array("Type" => $et['exptype_type'], "Amount" => $total_row));
                                                                                                }

                                                                                                //Total expenses Amount Line
                                                                                                echo '<tr class="table-primary font-weight-bold">
                                                                                                        <td>Total expenses:</td>
                                                                                                        <td class="text-right">'.number_format($total_exp).' '.$_SESSION['set_cur'].'</td>
                                                                                                </tr>';
                                                                                                array_push($_SESSION['rep_export'], array("Type" => "Total Expenses", "Amount" => $total_exp));
                                                                                                ?>
                                                                                        </tbody>
                                                                                </table>
                                                                        </div>
                                                                </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                                <!-- LOANS DUE TABLE -->
                                                                <div class="card mb-4">
                                                                        <div class="card-header bg-info text-white">
                                                                                <strong>Due Loan Payments for <?PHP echo $rep_month.'/'.$rep_year; ?></strong>
                                                                        </div>
                                                                        <div class="card-body table-responsive">
                                                                                <table class="table table-sm table-striped">
                                                                                        <thead class="thead-dark">
                                                                                                <tr>
                                                                                                        <th>Loan No.</th>
                                                                                                        <th class="text-right">Due Amount</th>
                                                                                                </tr>
                                                                                        </thead>
                                                                                        <tbody>
                                                                                                <?PHP
                                                                                                $total_loandue = 0;
                                                                                                while($row_loandue = db_fetch_assoc($query_loandue)){
                                                                                                        echo '<tr>
                                                                                                                <td><a href="loan.php?lid='.$row_loandue['loan_id'].'">'.$row_loandue['loan_no'].'</a></td>
                                                                                                                <td class="text-right">'.number_format($row_loandue['ltrans_principaldue'] + $row_loandue['ltrans_interestdue']).' '.$_SESSION['set_cur'].'</td>
                                                                                                        </tr>';
                                                                                                        $total_loandue = $total_loandue + $row_loandue['ltrans_principaldue'] + $row_loandue['ltrans_interestdue'];
                                                                                                }
                                                                                                echo '<tr class="table-primary font-weight-bold">
                                                                                                        <td colspan="2" class="text-right">Total: '.number_format($total_loandue).' '.$_SESSION['set_cur'].'</td>
                                                                                                </tr>';

                                                                                                //Prepare data for export
                                                                                                array_push($_SESSION['rep_export'], array("Type" => "Due Loan Payments", "Amount" => $total_loandue));
                                                                                                ?>
                                                                                        </tbody>
                                                                                </table>
                                                                        </div>
                                                                </div>

                                                                <!-- LOANS RECOVERIES TABLE -->
                                                                <div class="card">
                                                                        <div class="card-header bg-warning text-dark">
                                                                                <strong>Loan Recoveries for <?PHP echo $rep_month.'/'.$rep_year; ?></strong>
                                                                        </div>
                                                                        <div class="card-body table-responsive">
                                                                                <table class="table table-sm table-striped">
                                                                                        <thead class="thead-dark">
                                                                                                <tr>
                                                                                                        <th>Loan No.</th>
                                                                                                        <th class="text-right">Recovered</th>
                                                                                                </tr>
                                                                                        </thead>
                                                                                        <tbody>
                                                                                                <?PHP
                                                                                                $total_loanrec = 0;
                                                                                                while($row_loanrec = db_fetch_assoc($query_loanrec)){
                                                                                                        echo '<tr>
                                                                                                                <td><a href="loan.php?lid='.$row_loanrec['loan_id'].'">'.$row_loanrec['loan_no'].'</a></td>
                                                                                                                <td class="text-right">'.number_format($row_loanrec['ltrans_principal'] + $row_loanrec['ltrans_interest']).' '.$_SESSION['set_cur'].'</td>
                                                                                                        </tr>';
                                                                                                        $total_loanrec = $total_loanrec + $row_loanrec['ltrans_principal'] + $row_loanrec['ltrans_interest'];
                                                                                                }
                                                                                                echo '<tr class="table-primary font-weight-bold">
                                                                                                        <td colspan="2">';
                                                                                                echo 'Total: '.number_format($total_loanrec).' '.$_SESSION['set_cur'];
                                                                                                if ($total_loandue != 0) echo '<br/>Rate: '.number_format(($total_loanrec / $total_loandue * 100),2).'%';
                                                                                                echo '</td>
                                                                                                </tr>';

                                                                                                //Prepare data
                                                                                                array_push($_SESSION['rep_export'], array("Type" => "Loan Recoveries", "Amount" => $total_loanrec));
                                                                                                if ($total_loandue != 0) array_push($_SESSION['rep_export'], array("Type" => "Loan Recovery Rate", "Amount" => round(($total_loanrec / $total_loandue * 100),2).'%'));
                                                                                                ?>
                                                                                        </tbody>
                                                                                </table>
                                                                        </div>
                                                                </div>
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
