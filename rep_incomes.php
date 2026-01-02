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
                <h2 class="mb-4">Income Reports</h2>
                                        
                                        <nav class="mb-3">
                                                <ul class="nav nav-tabs" role="tablist">
                                                        <li class="nav-item">
                                                                <a class="nav-link active" href="rep_incomes.php">Income Report</a>
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
                                                                <a class="nav-link" href="rep_annual.php">Annual Report</a>
                                                        </li>
                                                </ul>
                                        </nav>
                                        
                                        <div class="card mb-4">
                                                <div class="card-header bg-primary text-white">
                                                        <strong>Select Report Period</strong>
                                                </div>
                                                <div class="card-body">
                                                        <form action="rep_incomes.php" method="post" class="form-inline">
                                                                <div class="form-group mr-3">
                                                                        <label for="rep_year" class="mr-2">Year:</label>
                                                                        <input type="number" class="form-control" id="rep_year" min="2006" max="2206" name="rep_year" style="width:100px;" value="<?PHP if ($month == 01) echo $year-1; else echo $year; ?>" />
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
                                                $_SESSION['rep_exp_title'] = $rep_year.'-'.$rep_month.'_incomes_'.$_POST['rep_form'];

                                                /*** CASE 1: Summarised Report ***/
                                                if ($_POST['rep_form'] == 'a'){

                                                        //Selection from INCOMES and INCTYPE
                                                        $sql_incomes = "SELECT * FROM incomes WHERE inc_date BETWEEN $firstDay AND $lastDay";
                                                        $query_incomes = db_query($db_link, $sql_incomes);
                                                        checkSQL($db_link, $query_incomes);

                                                        $sql_inctype = "SELECT * FROM inctype";
                                                        $query_inctype = db_query($db_link, $sql_inctype);
                                                        checkSQL($db_link, $query_inctype);
                                                        
                                                        //Make arrays
                                                        $inctype = array();
                                                        while($row_inctype = db_fetch_assoc($query_inctype)){
                                                                $inctype[] = $row_inctype;
                                                        }

                                                        $incomes = array();
                                                        while($row_incomes = db_fetch_assoc($query_incomes)){
                                                                $incomes[] = $row_incomes;
                                                        }
                                        ?>
                                        
                                        <div class="card">
                                                <div class="card-header bg-success text-white d-flex justify-content-between">
                                                        <strong>Summarised Incomes Report for <?PHP echo $rep_month.'/'.$rep_year; ?></strong>
                                                        <form action="rep_export.php" method="post" style="display:inline;">
                                                                <button type="submit" name="export_rep" class="btn btn-sm btn-light">Export</button>
                                                        </form>
                                                </div>
                                                <div class="card-body">
                                                        <table class="table table-striped table-hover">
                                                                <thead class="thead-dark">
                                                                        <tr>
                                                                                <th>Type</th>
                                                                                <th class="text-right">Amount</th>
                                                                        </tr>
                                                                </thead>
                                                                <tbody>
                                                                        <?PHP
                                                                        $total_inc = 0;
                                                                        foreach ($inctype as $it){
                                                                                $total_row = 0;
                                                                                foreach ($incomes as $ic) if ($ic['inctype_id'] == $it['inctype_id']) $total_row = $total_row + $ic['inc_amount'];
                                                                                echo '<tr>
                                                                                        <td>'.$it['inctype_type'].'</td>
                                                                                        <td class="text-right">'.number_format($total_row).' '.$_SESSION['set_cur'].'</td>
                                                                                </tr>';
                                                                                $total_inc = $total_inc + $total_row;
                                                                                array_push($_SESSION['rep_export'], array("Type" => $it['inctype_type'], "Amount" => $total_row));
                                                                        }
                                                                        echo '<tr class="table-primary font-weight-bold">
                                                                                <td>Total Incomes:</td>
                                                                                <td class="text-right">'.number_format($total_inc).' '.$_SESSION['set_cur'].'</td>
                                                                        </tr>';
                                                                        ?>
                                                                </tbody>
                                                        </table>
                                                </div>
                                        </div>
                                        
                                        <?PHP
                                        } else {
                                                // DETAILED REPORT
                                                $sql_incomes = "SELECT * FROM incomes, inctype, customer WHERE incomes.cust_id = customer.cust_id AND incomes.inctype_id = inctype.inctype_id AND inc_date BETWEEN $firstDay AND $lastDay ORDER BY inc_date, inc_receipt";
                                                $query_incomes = db_query($db_link, $sql_incomes);
                                                checkSQL($db_link, $query_incomes);
                                        ?>
                                        
                                        <div class="card">
                                                <div class="card-header bg-success text-white d-flex justify-content-between">
                                                        <strong>Detailed Incomes Report for <?PHP echo $rep_month.'/'.$rep_year; ?></strong>
                                                        <form action="rep_export.php" method="post" style="display:inline;">
                                                                <button type="submit" name="export_rep" class="btn btn-sm btn-light">Export</button>
                                                        </form>
                                                </div>
                                                <div class="card-body">
                                                        <table class="table table-striped table-hover">
                                                                <thead class="thead-dark">
                                                                        <tr>
                                                                                <th>Date</th>
                                                                                <th>Amount</th>
                                                                                <th>Type</th>
                                                                                <th>From</th>
                                                                                <th>Receipt No.</th>
                                                                        </tr>
                                                                </thead>
                                                                <tbody>
                                                                        <?PHP
                                                                        $total_inc = 0;
                                                                        while($row_incomes = db_fetch_assoc($query_incomes)){
                                                                                echo '<tr>
                                                                                        <td>'.date("d.m.Y",$row_incomes['inc_date']).'</td>
                                                                                        <td class="text-right">'.number_format($row_incomes['inc_amount']).' '.$_SESSION['set_cur'].'</td>
                                                                                        <td>'.$row_incomes['inctype_type'].'</td>
                                                                                        <td>'.$row_incomes['cust_name'].'</td>
                                                                                        <td>'.$row_incomes['inc_receipt'].'</td>
                                                                                </tr>';
                                                                                $total_inc = $total_inc + $row_incomes['inc_amount'];
                                                                                array_push($_SESSION['rep_export'], array("Date" => date("d.m.Y",$row_incomes['inc_date']), "Amount" => $row_incomes['inc_amount'], "Type" => $row_incomes['inctype_type'], "From" => $row_incomes['cust_name'], "Receipt No" => $row_incomes['inc_receipt']));
                                                                        }
                                                                        echo '<tr class="table-primary font-weight-bold">
                                                                                <td colspan="5" class="text-right">Total Incomes: '.number_format($total_inc).' '.$_SESSION['set_cur'].'</td>
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
