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
                                        <h2 class="mb-4">Expense Report</h2>
                                        
                                        <nav class="mb-3">
                                                <ul class="nav nav-tabs" role="tablist">
                                                        <li class="nav-item">
                                                                <a class="nav-link" href="rep_incomes.php">Income Report</a>
                                                        </li>
                                                        <li class="nav-item">
                                                                <a class="nav-link active" href="rep_expenses.php">Expense Report</a>
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
                                                                <a class="nav-link" href="rep_annual.php">Annual Report</a>
                                                        </li>
                                                </ul>
                                        </nav>

                                        <div class="card mb-4">
                                                <div class="card-header bg-primary text-white">
                                                        <strong>Select Report Period</strong>
                                                </div>
                                                <div class="card-body">
                                                        <form action="rep_expenses.php" method="post" class="form-inline">
                                                                <div class="form-group mr-3">
                                                                        <label for="rep_year" class="mr-2">Year:</label>
                                                                        <input type="number" class="form-control" id="rep_year" min="2014" max="2214" name="rep_year" style="width:100px;" value="<?PHP if ($month == 01) echo $year-1; else echo $year; ?>" placeholder="Give Year" />
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
                                                                <div class="form-group mr-3">
                                                                        <label for="rep_form" class="mr-2">Format:</label>
                                                                        <select class="form-control" id="rep_form" name="rep_form">
                                                                                <option value="d" selected>Detailed Report</option>
                                                                                <option value="a">Summarised Report</option>
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
                                                $_SESSION['rep_exp_title'] = $rep_year.'-'.$rep_month.'_expenses_'.$_POST['rep_form'];

                                                /*** CASE 1: Summarised Report ***/
                                                if ($_POST['rep_form'] == 'a'){
                                                        $sql_expendit = "SELECT * FROM expenses WHERE exp_date BETWEEN $firstDay AND $lastDay ORDER BY exp_date";
                                                        $query_expendit = db_query($db_link, $sql_expendit);
                                                        checkSQL($db_link, $query_expendit);

                                                        $sql_exptype = "SELECT * FROM exptype";
                                                        $query_exptype = db_query($db_link, $sql_exptype);
                                                        checkSQL($db_link, $query_exptype);
                                                        ?>

                                                        <div class="card">
                                                                <div class="card-header bg-success text-white d-flex justify-content-between">
                                                                        <strong>Summarised Expenses for <?PHP echo $rep_month.'/'.$rep_year ?></strong>
                                                                        <form action="rep_export.php" method="post" style="display:inline;">
                                                                                <button type="submit" name="export_rep" class="btn btn-sm btn-light">Export</button>
                                                                        </form>
                                                                </div>
                                                                <div class="card-body table-responsive">
                                                                        <table class="table table-striped table-hover">
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

                                                                                                //Prepare data for export to Excel file
                                                                                                array_push($_SESSION['rep_export'], array("Type" => $et['exptype_type'], "Amount" => $total_row));
                                                                                        }
                                                                                        echo '<tr class="table-primary font-weight-bold">
                                                                                                <td>Total expenses:</td>
                                                                                                <td class="text-right">'.number_format($total_exp).' '.$_SESSION['set_cur'].'</td>
                                                                                        </tr>';
                                                                                        ?>
                                                                                </tbody>
                                                                        </table>
                                                                </div>
                                                        </div>
                                        <?PHP
                                        }

                                        /*** CASE 2: Detailed Report ***/
                                        else{
                                                $sql_expendit = "SELECT * FROM expenses, exptype WHERE expenses.exptype_id = exptype.exptype_id AND exp_date BETWEEN $firstDay AND $lastDay ORDER BY exp_date";
                                                $query_expendit = db_query($db_link, $sql_expendit);
                                                checkSQL($db_link, $query_expendit);
                                                ?>

                                                <div class="card">
                                                        <div class="card-header bg-success text-white d-flex justify-content-between">
                                                                <strong>Detailed Expenses for <?PHP echo $rep_month.'/'.$rep_year ?></strong>
                                                                <form action="rep_export.php" method="post" style="display:inline;">
                                                                        <button type="submit" name="export_rep" class="btn btn-sm btn-light">Export</button>
                                                                </form>
                                                        </div>
                                                        <div class="card-body table-responsive">
                                                                <table class="table table-striped table-hover">
                                                                        <thead class="thead-dark">
                                                                                <tr>
                                                                                        <th>Date</th>
                                                                                        <th>Type</th>
                                                                                        <th>Recipient</th>
                                                                                        <th>Details</th>
                                                                                        <th>Receipt No.</th>
                                                                                        <th>Voucher No.</th>
                                                                                        <th class="text-right">Amount</th>
                                                                                </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                                <?PHP
                                                                                $total_exp = 0;
                                                                                while($row_expendit = db_fetch_assoc($query_expendit)){
                                                                                        echo '<tr>
                                                                                                <td>'.date("d.m.Y",$row_expendit['exp_date']).'</td>
                                                                                                <td>'.$row_expendit['exptype_type'].'</td>
                                                                                                <td>'.$row_expendit['exp_recipient'].'</td>
                                                                                                <td>'.$row_expendit['exp_text'].'</td>
                                                                                                <td>'.$row_expendit['exp_receipt'].'</td>
                                                                                                <td>'.$row_expendit['exp_voucher'].'</td>
                                                                                                <td class="text-right">'.number_format($row_expendit['exp_amount']).' '.$_SESSION['set_cur'].'</td>
                                                                                        </tr>';
                                                                                        $total_exp = $total_exp + $row_expendit['exp_amount'];

                                                                                        //Prepare data for export to Excel file
                                                                                        array_push($_SESSION['rep_export'], array("Date" => date("d.m.Y",$row_expendit['exp_date']), "Type" => $row_expendit['exptype_type'], "Recipient" => $row_expendit['exp_recipient'], "Details" => $row_expendit['exp_text'], "Receipt No" => $row_expendit['exp_receipt'], "Voucher No" => $row_expendit['exp_voucher'],"Amount" => $row_expendit['exp_amount']));
                                                                                        }
                                                                                        echo '<tr class="table-primary font-weight-bold">
                                                                                                <td colspan="7" class="text-right">Total expenses: '.number_format($total_exp).' '.$_SESSION['set_cur'].'</td>
                                                                                        </tr>';
                                                                                ?>
                                                                        </tbody>
                                                                </table>
                                                        </div>
                                                </div>
                                        <?PHP
                                        }
                                }
                                ?>
                        </div>
                        </div>
                </div>

                <?PHP include 'includes/bootstrap_footer.php'; ?>
        </body>
</html>
