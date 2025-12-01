<?PHP
require 'functions.php';
checkLogin();
// Allow access to reports - remove permission check for test
// checkPermissionReport();
$db_link = connect();

//Variable $year provides the pre-set values for input fields
$year = (date("Y",time()))-1;
$capital_total = 0;
$capital_additions = 0;
$capital_deductions = 0;
?>

<!DOCTYPE HTML>
<html>
        <?PHP include 'includes/bootstrap_header.php'; ?>
        <body>
                <?PHP include 'includes/bootstrap_header_nav.php'; ?>

                <div class="container-fluid mt-4">
                        <div class="row">
                                <div class="col-12">
                                        <h2 class="mb-4">Annual Report</h2>
                                        
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
                                                                <a class="nav-link" href="rep_monthly.php">Monthly Report</a>
                                                        </li>
                                                        <li class="nav-item">
                                                                <a class="nav-link active" href="rep_annual.php">Annual Report</a>
                                                        </li>
                                                </ul>
                                        </nav>
                                        
                                        <div class="card mb-3">
                                                <div class="card-header bg-primary text-white">
                                                        <strong>Select Report Period</strong>
                                                </div>
                                                <div class="card-body">
                                                        <form action="rep_annual.php" method="post" class="form-inline">
                                                                <div class="form-group mr-3">
                                                                        <label for="rep_year" class="mr-2">Year:</label>
                                                                        <input type="number" class="form-control" id="rep_year" min="2006" max="2206" name="rep_year" style="width:100px;" value="<?PHP echo $year; ?>" placeholder="Give Year" />
                                                                </div>
                                                                <button type="submit" name="select" class="btn btn-primary">Generate Report</button>
                                                        </form>
                                                </div>
                                        </div>
                                        
                                        <?PHP
                                        if(isset($_POST['select'])){
                                                //Sanitize user input
                                                $rep_year = sanitize($db_link, $_POST['rep_year']);
                                                
                                                //Calculate UNIX TIMESTAMP for first and last day of selected year
                                                $firstDay = mktime(0, 0, 0, 1, 1, $rep_year);
                                                $lastDay = mktime(0, 0, 0, 1, 0, ($rep_year+1));
                                                
                                                //Make array for exporting data
                                                $_SESSION['rep_export'] = array();
                                                $_SESSION['rep_exp_title'] = $rep_year.'_annual-report';
                                                
                                                
                                                /**** INCOME RELATED DATA ****/
                                                
                                                //Select INCOMES and INCTYPE
                                                $sql_incomes = "SELECT * FROM incomes WHERE inc_date BETWEEN $firstDay AND $lastDay";
                                                $query_incomes = db_query($db_link, $sql_incomes);
                                                checkSQL($db_link, $query_incomes);
                                                
                                                $sql_inctype = "SELECT * FROM inctype";
                                                $query_inctype = db_query($db_link, $sql_inctype);
                                                checkSQL($db_link, $query_inctype);
                                                
                                                
                                                /**** EXPENDITURE RELATED DATA ****/
                                                
                                                //Select expenses and EXPTYPE
                                                $sql_expendit = "SELECT * FROM expenses WHERE exp_date BETWEEN $firstDay AND $lastDay ORDER BY exp_date";
                                                $query_expendit = db_query($db_link, $sql_expendit);
                                                checkSQL($db_link, $query_expendit);
                                                
                                                $sql_exptype = "SELECT * FROM exptype";
                                                $query_exptype = db_query($db_link, $sql_exptype);
                                                checkSQL($db_link, $query_exptype);
                                                
                                                
                                                /**** CAPITAL RELATED DATA ****/
                                                
                                                //Select bought and sold Shares from SHARES
                                                $sql_shares = "SELECT * FROM shares WHERE share_date BETWEEN $firstDay AND $lastDay";
                                                $query_shares = db_query($db_link, $sql_shares);
                                                checkSQL($db_link, $query_shares);
                                                $total_share_buys = 0;
                                                $total_share_sales = 0;
                                                while($row_shares = db_fetch_assoc($query_shares)){
                                                        if($row_shares['share_amount'] >= 0){
                                                                $total_share_buys = $total_share_buys + $row_shares['share_value'];
                                                        }
                                                        elseif($row_shares['share_amount'] < 0){
                                                                $total_share_sales = $total_share_sales + $row_shares['share_value'] * (-1);
                                                        }
                                                }
                                                
                                                //Select Saving Deposits from SAVINGS
                                                $sql_savdep = "SELECT * FROM savings WHERE sav_date BETWEEN $firstDay AND $lastDay AND savtype_id = 1";
                                                $query_savdep = db_query($db_link, $sql_savdep);
                                                checkSQL($db_link, $query_savdep);
                                                $total_savdep = 0;
                                                while($row_savdep = db_fetch_assoc($query_savdep)){
                                                        $total_savdep = $total_savdep + $row_savdep['sav_amount'];
                                                }
                                                
                                                //Select Loan Recoveries from LTRANS
                                                $sql_loanrec = "SELECT * FROM ltrans WHERE ltrans_date BETWEEN $firstDay AND $lastDay";
                                                $query_loanrec = db_query($db_link, $sql_loanrec);
                                                checkSQL($db_link, $query_loanrec);
                                                $total_loanrec = 0;
                                                while($row_loanrec = db_fetch_assoc($query_loanrec)){
                                                        $total_loanrec = $total_loanrec + $row_loanrec['ltrans_principal'];
                                                }
                                                
                                                //Select Saving Withdrawals from SAVINGS
                                                $sql_savwithd = "SELECT * FROM savings WHERE sav_date BETWEEN $firstDay AND $lastDay AND savtype_id = 2";
                                                $query_savwithd = db_query($db_link, $sql_savwithd);
                                                checkSQL($db_link, $query_savwithd);
                                                $total_savwithd = 0;
                                                while($row_savwithd = db_fetch_assoc($query_savwithd)){
                                                        $total_savwithd = $total_savwithd + $row_savwithd['sav_amount'];
                                                }
                                                $total_savwithd = $total_savwithd * (-1);
                                                
                                                //Select Loans Out from LOANS
                                                $sql_loanout = "SELECT * FROM loans WHERE loan_dateout BETWEEN $firstDay AND $lastDay";
                                                $query_loanout = db_query($db_link, $sql_loanout);
                                                checkSQL($db_link, $query_loanout);
                                                $total_loanout = 0;
                                                while($row_loanout = db_fetch_assoc($query_loanout)){
                                                        $total_loanout = $total_loanout + $row_loanout['loan_principal'];
                                                }

                                                
                                                /**** LOAN RELATED DATA ****/
                                                
                                                //Select Due Loan Payments from LTRANS
                                                $sql_loandue = "SELECT * FROM ltrans, loans, loanstatus WHERE ltrans.loan_id = loans.loan_id AND loans.loanstatus_id = loanstatus.loanstatus_id AND ltrans_due BETWEEN $firstDay AND $lastDay AND loans.loanstatus_id IN (2, 4, 5) ORDER BY ltrans_due, loans.cust_id";
                                                $query_loandue = db_query($db_link, $sql_loandue);
                                                checkSQL($db_link, $query_loandue);
                                                
                                                //Select Loan Recoveries from LTRANS
                                                $sql_loanrec2 = "SELECT * FROM ltrans, loans WHERE ltrans.loan_id = loans.loan_id AND ltrans_date BETWEEN $firstDay AND $lastDay ORDER BY ltrans_date, loans.cust_id";
                                                $query_loanrec2 = db_query($db_link, $sql_loanrec2);
                                                checkSQL($db_link, $query_loanrec2);
                                                
                                                //Select Loans Out from LOANS
                                                $sql_loanout2 = "SELECT * FROM loans, customer WHERE loans.cust_id = customer.cust_id AND loans.loan_dateout BETWEEN $firstDay AND $lastDay ORDER BY loan_dateout, loans.cust_id";
                                                $query_loanout2 = db_query($db_link, $sql_loanout2);
                                                checkSQL($db_link, $query_loanout2);
                                                ?>      
                                                
                                                <div class="row">
                                                        <div class="col-md-6">
                                                                <!-- INCOMES TABLE -->
                                                                <div class="card mb-4">
                                                                        <div class="card-header bg-success text-white d-flex justify-content-between">
                                                                                <strong>Incomes for <?PHP echo $rep_year; ?></strong>
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
                                                                                                
                                                                                                //Make array for all incomes for selected year
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
                                                                                                echo '<tr class="table-primary font-weight-bold">
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
                                                                                <strong>Expenses for <?PHP echo $rep_year; ?></strong>
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
                                                                                                array_push($_SESSION['rep_export'], array("Type" => "Total expenses", "Amount" => $total_exp));
                                                                                                ?>
                                                                                        </tbody>
                                                                                </table>
                                                                        </div>
                                                                </div>
                                                        </div>
                                                        
                                                        <div class="col-md-6">
                                                                <!-- LOANS SUMMARY TABLE -->
                                                                <div class="card mb-4">
                                                                        <div class="card-header bg-info text-white">
                                                                                <strong>Loan Summary for <?PHP echo $rep_year; ?></strong>
                                                                        </div>
                                                                        <div class="card-body table-responsive">
                                                                                <table class="table table-sm">
                                                                                        <tbody>
                                                                                                <?PHP
                                                                                                $total_loandue_all = 0;
                                                                                                $total_loanrec_all = 0;
                                                                                                while($row_loandue = db_fetch_assoc($query_loandue)){
                                                                                                        $total_loandue_all = $total_loandue_all + $row_loandue['ltrans_principaldue'] + $row_loandue['ltrans_interestdue'];
                                                                                                }
                                                                                                while($row_loanrec2 = db_fetch_assoc($query_loanrec2)){
                                                                                                        $total_loanrec_all = $total_loanrec_all + $row_loanrec2['ltrans_principal'] + $row_loanrec2['ltrans_interest'];
                                                                                                }
                                                                                                
                                                                                                echo '<tr><td>Total Due Payments:</td><td class="text-right font-weight-bold">'.number_format($total_loandue_all).' '.$_SESSION['set_cur'].'</td></tr>';
                                                                                                echo '<tr><td>Total Recoveries:</td><td class="text-right font-weight-bold">'.number_format($total_loanrec_all).' '.$_SESSION['set_cur'].'</td></tr>';
                                                                                                if ($total_loandue_all != 0) echo '<tr><td>Recovery Rate:</td><td class="text-right font-weight-bold">'.number_format(($total_loanrec_all / $total_loandue_all * 100),2).'%</td></tr>';
                                                                                                echo '<tr><td>Total Loans Out:</td><td class="text-right font-weight-bold">'.number_format($total_loanout).' '.$_SESSION['set_cur'].'</td></tr>';
                                                                                                
                                                                                                array_push($_SESSION['rep_export'], array("Type" => "Due Payments", "Amount" => $total_loandue_all));
                                                                                                array_push($_SESSION['rep_export'], array("Type" => "Recoveries", "Amount" => $total_loanrec_all));
                                                                                                if ($total_loandue_all != 0) array_push($_SESSION['rep_export'], array("Type" => "Recovery Rate", "Amount" => number_format(($total_loanrec_all / $total_loandue_all * 100),2).'%'));
                                                                                                array_push($_SESSION['rep_export'], array("Type" => "Loans Out", "Amount" => $total_loanout));
                                                                                                ?>
                                                                                        </tbody>
                                                                                </table>
                                                                        </div>
                                                                </div>
                                                                
                                                                <!-- CAPITAL SUMMARY TABLE -->
                                                                <div class="card">
                                                                        <div class="card-header bg-warning text-dark">
                                                                                <strong>Capital Summary for <?PHP echo $rep_year; ?></strong>
                                                                        </div>
                                                                        <div class="card-body table-responsive">
                                                                                <table class="table table-sm">
                                                                                        <tbody>
                                                                                                <tr><td>Shares Out:</td><td class="text-right">'.number_format($total_share_buys).' '.$_SESSION['set_cur'].'</td></tr>
                                                                                                <tr><td>Saving Deposits:</td><td class="text-right">'.number_format($total_savdep).' '.$_SESSION['set_cur'].'</td></tr>
                                                                                                <tr class="table-primary font-weight-bold"><td>Capital Additions:</td><td class="text-right">'.number_format($total_share_buys + $total_savdep + $total_loanrec).' '.$_SESSION['set_cur'].'</td></tr>
                                                                                                <tr class="table-light"><td colspan="2"></td></tr>
                                                                                                <tr><td>Shares In:</td><td class="text-right">'.number_format($total_share_sales).' '.$_SESSION['set_cur'].'</td></tr>
                                                                                                <tr><td>Loans Out:</td><td class="text-right">'.number_format($total_loanout).' '.$_SESSION['set_cur'].'</td></tr>
                                                                                                <tr><td>Saving Withdrawals:</td><td class="text-right">'.number_format($total_savwithd).' '.$_SESSION['set_cur'].'</td></tr>
                                                                                                <tr class="table-danger font-weight-bold"><td>Capital Deductions:</td><td class="text-right">'.number_format($total_share_sales + $total_loanout + $total_savwithd).' '.$_SESSION['set_cur'].'</td></tr>
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
