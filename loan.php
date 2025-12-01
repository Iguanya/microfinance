<?PHP
require 'functions.php';
require 'function_loans.php';
checkLogin();
$db_link = connect();
getLoanID($db_link);

$timestamp = time();

// Select details of current loan from LOANS, LOANSTATUS, CUSTOMER
$sql_loan = "SELECT * FROM loans JOIN loanstatus ON loans.loanstatus_id = loanstatus.loanstatus_id JOIN customer ON loans.cust_id = customer.cust_id WHERE loans.loan_id = $_SESSION[loan_id]";
$query_loan = db_query($db_link, $sql_loan);
checkSQL($db_link, $query_loan);
$result_loan = db_fetch_assoc($query_loan);
$_SESSION['cust_id'] = $result_loan['cust_id'];

// Get current customer's savings account balance
$sav_balance = getSavingsBalance($db_link, $_SESSION['cust_id']);
$sav_fixed = getSavingsFixed($db_link, $_SESSION['cust_id']);

/** UPDATE STATUS Button **/
if (isset($_POST['updatestatus'])){
        // Sanitize user input
        $loan_principal = $_SESSION['loan_principal'];
        $loan_interest = $_SESSION['loan_interest'];
        $loan_period = $_SESSION['loan_period'];
        $loan_issued = $_SESSION['loan_issued'];

        $loan_fee_receipt = sanitize($db_link, $_POST['loan_fee_receipt']);
        $loan_status = sanitize($db_link, $_POST['loan_status']);
        $loan_dateout = strtotime(sanitize($db_link, $_POST['loan_dateout']));
        $loan_princp_approved = sanitize($db_link, $_POST['loan_principalapproved']);

        if($loan_status == 2 AND $loan_issued == 0){

                //Include module for interest calculation method according to system settings
                include ($_SESSION['set_intcalc']);

                //Insert Loan Fee into INCOMES
                $loan_fee = $loan_princp_approved / 100 * $_SESSION['fee_loan'];
                $sql_inc_lf = "INSERT INTO incomes (cust_id, loan_id, inctype_id, inc_amount, inc_date, inc_receipt, inc_created, user_id) VALUES ('$_SESSION[cust_id]', '$_SESSION[loan_id]', '3', '$loan_fee', '$loan_dateout', '$loan_fee_receipt', '$timestamp', '$_SESSION[log_id]')";
                $query_inc_lf = db_query($db_link, $sql_inc_lf);
                checkSQL($db_link, $query_inc_lf);

                //Insert Loan Insurance into INCOMES
                $loan_insurance = $loan_princp_approved / 100 * $_SESSION['fee_loaninsurance'];
                $sql_inc_ins = "INSERT INTO incomes (cust_id, loan_id, inctype_id, inc_amount, inc_date, inc_receipt, inc_created, user_id) VALUES ('$_SESSION[cust_id]', '$_SESSION[loan_id]', '10', '$loan_insurance', '$loan_dateout', '$loan_fee_receipt', '$timestamp', '$_SESSION[log_id]')";
                $query_inc_ins = db_query($db_link, $sql_inc_ins);
                checkSQL($db_link, $query_inc_ins);

                //Insert Additional Loan Fee into INCOMES
                if($_SESSION['fee_xl1'] != 0){
                        $loan_xtraFee1 = $_SESSION['fee_xl1'];
                        $sql_inc_xtraFee1 = "INSERT INTO incomes (cust_id, loan_id, inctype_id, inc_amount, inc_date, inc_receipt, inc_created, user_id) VALUES ('$_SESSION[cust_id]', '$_SESSION[loan_id]', '11', '$loan_xtraFee1', '$loan_dateout', '$loan_fee_receipt', '$timestamp', '$_SESSION[log_id]')";
                        $query_inc_xtraFee1 = db_query($db_link, $sql_inc_xtraFee1);
                        checkSQL($db_link, $query_inc_xtraFee1);
                }

                //Update loan information. Set loan to "Approved" and "Issued".
                $sql_issue = "UPDATE loans SET loanstatus_id = '$loan_status', loan_issued = '1', loan_dateout = '$loan_dateout', loan_principalapproved = '$loan_princp_approved', loan_fee = '$loan_fee', loan_fee_receipt = '$loan_fee_receipt', loan_insurance = '$loan_insurance', loan_insurance_receipt = '$loan_fee_receipt' WHERE loan_id = '$_SESSION[loan_id]'";
                $query_issue = db_query($db_link, $sql_issue);
                checkSQL($db_link, $query_issue);
        }

        else {
                $sql_update = "UPDATE loans SET loanstatus_id = '$_POST[loan_status]' WHERE loan_id = $_SESSION[loan_id]";
                $query_update = db_query($db_link, $sql_update);
                checkSQL($db_link, $query_update);
        }
        header('Location: loan.php?lid='.$_SESSION['loan_id']);
}

// Select Instalments from LTRANS
$sql_duedates = "SELECT * FROM ltrans LEFT JOIN user ON ltrans.user_id = user.user_id WHERE loan_id = $_SESSION[loan_id] ORDER BY ltrans_id";
$query_duedates = db_query($db_link, $sql_duedates);
checkSQL($db_link, $query_duedates);

// Select Guarantors from CUSTOMER
$sql_guarant = "SELECT cust_id, cust_no, cust_name FROM customer";
$query_guarant = db_query($db_link, $sql_guarant);
checkSQL($db_link, $query_guarant);
$guarantors = array();
while ($row_guarant = db_fetch_assoc($query_guarant)) $guarantors[] = $row_guarant;

// Select Securities from SECURITIES
$securities = getLoanSecurities($db_link, $_SESSION['loan_id']);
foreach ($securities as $s){
        if ($s['sec_no'] == 1) $security1 = $s;
        elseif ($s['sec_no'] == 2) $security2 = $s;
}

//Prepare array data export
$ltrans_exp_date = date("Y-m-d",time());
$_SESSION['ltrans_export'] = array();
$_SESSION['ltrans_exp_title'] = $_SESSION['cust_id'].'_loan_'.$ltrans_exp_date;
?>

<!DOCTYPE HTML>
<html>
        <?PHP include 'includes/bootstrap_header.php'; ?>
        <body>
                <?PHP include 'includes/bootstrap_header_nav.php'; ?>

                <div class="container-fluid mt-4">
                        <div class="row">
                                <div class="col-lg-8">
                                        <div class="card">
                                                <div class="card-header bg-primary text-white">
                                                        <h5 class="mb-0"><i class="fa fa-file-text"></i> Loan No. <?PHP echo $result_loan['loan_no'] ?></h5>
                                                </div>
                                                <div class="card-body">
                                                        <form name="loaninfo" action="loan.php" method="post">
                                                                <div class="row">
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Customer</label>
                                                                                        <input type="text" class="form-control" disabled value="<?PHP echo $result_loan['cust_name']?>" />
                                                                                </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Purpose</label>
                                                                                        <input type="text" class="form-control" disabled value="<?PHP echo $result_loan['loan_purpose']?>" />
                                                                                </div>
                                                                        </div>
                                                                </div>

                                                                <div class="row">
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Principal</label>
                                                                                        <input type="text" class="form-control" disabled value="<?PHP echo number_format($result_loan['loan_principal']).' '.$_SESSION['set_cur'] ?>" />
                                                                                </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Interest Rate</label>
                                                                                        <input type="text" class="form-control" disabled value="<?PHP echo $result_loan['loan_interest'].'% per month'?>" />
                                                                                </div>
                                                                        </div>
                                                                </div>

                                                                <div class="row">
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Period (Months)</label>
                                                                                        <input type="text" class="form-control" disabled value="<?PHP echo $result_loan['loan_period']?>" />
                                                                                </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Loan Fee</label>
                                                                                        <input type="text" class="form-control" disabled value="<?PHP echo number_format($result_loan['loan_fee']).' '.$_SESSION['set_cur'] ?>" />
                                                                                </div>
                                                                        </div>
                                                                </div>

                                                                <div class="form-group">
                                                                        <label class="font-weight-bold">Loan Insurance</label>
                                                                        <input type="text" class="form-control" disabled value="<?PHP echo number_format($result_loan['loan_insurance']).' '.$_SESSION['set_cur'] ?>" />
                                                                </div>

                                                                <div class="row">
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Application Date</label>
                                                                                        <input type="text" class="form-control" disabled value="<?PHP echo date("d.m.Y", $result_loan['loan_date']) ?>" />
                                                                                </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Principal Approved</label>
                                                                                        <?PHP
                                                                                        if($result_loan['loan_issued'] == 1)
                                                                                                echo '<input type="text" class="form-control" disabled value="'.number_format($result_loan['loan_principalapproved']).' '.$_SESSION['set_cur'].'" />';
                                                                                        else
                                                                                                echo '<input type="number" class="form-control" name="loan_principalapproved" placeholder="Approved Principal Amount" value="'.$result_loan['loan_principal'].'" />';
                                                                                        ?>
                                                                                </div>
                                                                        </div>
                                                                </div>

                                                                <div class="row">
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Issued On</label>
                                                                                        <input type="text" class="form-control datepicker" name="loan_dateout"
                                                                                                <?PHP if($result_loan['loan_issued'] == 1) { echo ' disabled'; echo ' value="'.date("d.m.Y", $result_loan['loan_dateout']).'" '; } ?>
                                                                                                placeholder="DD.MM.YYYY" />
                                                                                </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Status</label>
                                                                                        <select class="form-control" name="loan_status" id="loan_status">
                                                                                                <?PHP
                                                                                                $sql_loanstatus = "SELECT * FROM loanstatus";
                                                                                                $query_loanstatus = db_query($db_link, $sql_loanstatus);
                                                                                                while ($row_status = db_fetch_assoc($query_loanstatus)){
                                                                                                        echo '<option value="'.$row_status['loanstatus_id'].'"';
                                                                                                        if ($row_status['loanstatus_id'] == $result_loan['loanstatus_id']) echo ' selected ';
                                                                                                        echo '>'.$row_status['loanstatus_status'].'</option>';
                                                                                                }
                                                                                                ?>
                                                                                        </select>
                                                                                </div>
                                                                        </div>
                                                                </div>

                                                                <input type="hidden" name="loan_issued" id="loan_issued" value="<?PHP echo $result_loan['loan_issued']?>" />
                                                                <input type="hidden" name="loan_fee_receipt" id="loan_fee_receipt" value="" />
                                                                <button type="submit" name="updatestatus" class="btn btn-success btn-block">
                                                                        <i class="fa fa-save"></i> Update Loan Status
                                                                </button>
                                                        </form>
                                                </div>
                                        </div>
                                </div>
                                <div class="col-lg-4">
                                        <div class="card">
                                                <div class="card-header bg-info text-white">
                                                        <h5 class="mb-0"><i class="fa fa-calendar"></i> Payment Schedule</h5>
                                                </div>
                                                <div class="card-body" style="max-height:600px; overflow-y:auto;">
                                                        <div class="table-responsive">
                                                                <table class="table table-sm table-striped">
                                                                        <thead>
                                                                                <tr>
                                                                                        <th>Due Date</th>
                                                                                        <th>Amount</th>
                                                                                </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                                <?PHP
                                                                                while($row_duedates = db_fetch_assoc($query_duedates)){
                                                                                        echo '<tr><td>'.date("d.m.Y",$row_duedates['ltrans_due']).'</td><td>'.number_format(($row_duedates['ltrans_principaldue'] + $row_duedates['ltrans_interestdue'])).'</td></tr>';
                                                                                }
                                                                                ?>
                                                                        </tbody>
                                                                </table>
                                                        </div>
                                                </div>
                                        </div>
                                </div>
                        </div>
                </div>

                <?PHP include 'includes/bootstrap_footer.php'; ?>
        </body>
</html>
