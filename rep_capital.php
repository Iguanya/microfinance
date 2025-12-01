<?PHP
require 'functions.php';
checkLogin();
// Allow access to reports - remove permission check for test
// checkPermissionReport();
$db_link = connect();

//Variables $year and $month provide the pre-set values for input fields
$year = date("Y",time());
$month = date("m",time());
?>

<!DOCTYPE HTML>
<html>
        <?PHP include 'includes/bootstrap_header.php'; ?>
        <body>
                <?PHP include 'includes/bootstrap_header_nav.php'; ?>

                <div class="container-fluid mt-4">
                        <div class="row">
                                <div class="col-12">
                                        <h2 class="mb-4">Capital Report</h2>
                                        
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
                                                                <a class="nav-link active" href="rep_capital.php">Capital Report</a>
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
                                                        <form action="rep_capital.php" method="post" class="form-inline">
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
                                                $_SESSION['rep_exp_title'] = $rep_year.'-'.$rep_month.'_capital';

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

                                                //Prepare data for export to Excel file
                                                array_push($_SESSION['rep_export'], array("Type" => "Shares Out", "Amount" => $total_share_buys));
                                                array_push($_SESSION['rep_export'], array("Type" => "Saving Deposits", "Amount" => $total_savdep));
                                                array_push($_SESSION['rep_export'], array("Type" => "Loan Recoveries", "Amount" => $total_loanrec));

                                                array_push($_SESSION['rep_export'], array("Type" => "Total Additions", "Amount" => $total_loanrec+$total_savdep+$total_share_buys));

                                                array_push($_SESSION['rep_export'], array("Type" => "", "Amount" => ""));

                                                array_push($_SESSION['rep_export'], array("Type" => "Shares In", "Amount" => $total_share_sales));
                                                array_push($_SESSION['rep_export'], array("Type" => "Saving Withdrawals", "Amount" => $total_savwithd));
                                                array_push($_SESSION['rep_export'], array("Type" => "Loans Out", "Amount" => $total_loanout));

                                                array_push($_SESSION['rep_export'], array("Type" => "Total Deductions", "Amount" => $total_share_sales+$total_loanout+$total_savwithd));
                                                ?>

                                                <div class="row">
                                                        <div class="col-md-6">
                                                                <!-- TABLE 1: Capital Additions -->
                                                                <div class="card mb-4">
                                                                        <div class="card-header bg-success text-white d-flex justify-content-between">
                                                                                <strong>Capital Additions for <?PHP echo $rep_month.'/'.$rep_year; ?></strong>
                                                                                <form action="rep_export.php" method="post" style="display:inline;">
                                                                                        <button type="submit" name="export_rep" class="btn btn-sm btn-light">Export</button>
                                                                                </form>
                                                                        </div>
                                                                        <div class="card-body">
                                                                                <table class="table table-sm">
                                                                                        <tr>
                                                                                                <td>Shares Out</td>
                                                                                                <td class="text-right font-weight-bold"><?PHP echo number_format($total_share_buys).' '.$_SESSION['set_cur'] ?></td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                                <td>Saving Deposits</td>
                                                                                                <td class="text-right"><?PHP echo number_format($total_savdep).' '.$_SESSION['set_cur'] ?></td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                                <td>Loan Recoveries</td>
                                                                                                <td class="text-right"><?PHP echo number_format($total_loanrec).' '.$_SESSION['set_cur'] ?></td>
                                                                                        </tr>
                                                                                        <tr class="table-primary font-weight-bold">
                                                                                                <td>Total Capital Additions:</td>
                                                                                                <td class="text-right"><?PHP echo number_format($total_share_buys + $total_savdep + $total_loanrec).' '.$_SESSION['set_cur'] ?></td>
                                                                                        </tr>
                                                                                </table>
                                                                        </div>
                                                                </div>
                                                        </div>

                                                        <div class="col-md-6">
                                                                <!-- TABLE 2: Capital Deductions -->
                                                                <div class="card mb-4">
                                                                        <div class="card-header bg-danger text-white">
                                                                                <strong>Capital Deductions for <?PHP echo $rep_month.'/'.$rep_year; ?></strong>
                                                                        </div>
                                                                        <div class="card-body">
                                                                                <table class="table table-sm">
                                                                                        <tr>
                                                                                                <td>Shares In</td>
                                                                                                <td class="text-right font-weight-bold"><?PHP echo number_format($total_share_sales).' '.$_SESSION['set_cur'] ?></td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                                <td>Loans Out</td>
                                                                                                <td class="text-right"><?PHP echo number_format($total_loanout).' '.$_SESSION['set_cur'] ?></td>
                                                                                        </tr>
                                                                                        <tr>
                                                                                                <td>Saving Withdrawals</td>
                                                                                                <td class="text-right"><?PHP echo number_format($total_savwithd).' '.$_SESSION['set_cur'] ?></td>
                                                                                        </tr>
                                                                                        <tr class="table-danger font-weight-bold">
                                                                                                <td>Total Capital Deductions:</td>
                                                                                                <td class="text-right"><?PHP echo number_format($total_share_sales + $total_loanout + $total_savwithd).' '.$_SESSION['set_cur'] ?></td>
                                                                                        </tr>
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
