<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();
getCustID($db_link);

unset($_SESSION['interest_sum'], $_SESSION['balance']);

//Generate timestamp
$timestamp = time();

//Calculate Balance on Savings account
$sav_balance = getSavingsBalance($db_link, $_SESSION['cust_id']);
$sav_fixed = getSavingsFixed($db_link, $_SESSION['cust_id']);

//UPDATE-Button
if (isset($_POST['update'])){

        //Sanitize user input
        $cust_no = sanitize($db_link, $_POST['cust_no']);
        $cust_name = sanitize($db_link, $_POST['cust_name']);
        $cust_dob = strtotime(sanitize($db_link, $_POST['cust_dob']));
        $custsex_id = sanitize($db_link, $_POST['custsex_id']);
        $cust_address = sanitize($db_link, $_POST['cust_address']);
        $cust_phone = sanitize($db_link, $_POST['cust_phone']);
        $cust_email = sanitize($db_link, $_POST['cust_email']);
        $cust_occup = sanitize($db_link, $_POST['cust_occup']);
        $custmarried_id = sanitize($db_link, $_POST['custmarried_id']);
        $cust_heir = sanitize($db_link, $_POST['cust_heir']);
        $cust_heirrel = sanitize($db_link, $_POST['cust_heirrel']);
        if ($cust_lengthres == 0 OR $cust_lengthres == NULL) $cust_lengthres = NULL;
        $custsick_id = sanitize($db_link, $_POST['custsick_id']);
        $cust_active = sanitize($db_link, $_POST['cust_active']);
        $timestamp = time();

        //Update CUSTOMER
        $sql_update = "UPDATE customer SET cust_no = '$cust_no', cust_name = '$cust_name', cust_dob = $cust_dob, custsex_id = $custsex_id, cust_address = '$cust_address', cust_phone = '$cust_phone', cust_email = '$cust_email', cust_occup = '$cust_occup', custmarried_id = $custmarried_id, cust_heir = '$cust_heir', cust_heirrel = '$cust_heirrel', custsick_id = $custsick_id, cust_active = '$cust_active', cust_lastupd = $timestamp, user_id = $_SESSION[log_id] WHERE cust_id = $_SESSION[cust_id]";
        $query_update = db_query($db_link, $sql_update);
        checkSQL($db_link, $query_update);
        header('Location: customer.php?cust='.$_SESSION['cust_id']);
}

//Get current customer's details
$result_cust = getCustomer($db_link, $_SESSION['cust_id']);

//Error-Message, if customer is not found
if ($result_cust['cust_id']==''){
        echo '<script>
                alert("Customer not found in database.");
                window.location = "cust_search.php";
        </script>';
}

//Select Marital Status from custmarried for dropdown-menu
$sql_mstat = "SELECT * FROM custmarried";
$query_mstat = db_query($db_link, $sql_mstat);
checkSQL($db_link, $query_mstat);

//Select Sicknesses from custsick for dropdown-menu
$sql_sick = "SELECT * FROM custsick";
$query_sick = db_query($db_link, $sql_sick);
checkSQL($db_link, $query_sick);

//Select Sexes from custsex for dropdown-menu
$sql_sex = "SELECT * FROM custsex";
$query_sex = db_query($db_link, $sql_sex);
checkSQL($db_link, $query_sex);

//Select Shares from SHARES
$sql_sha = "SELECT * FROM shares WHERE cust_id = '$_SESSION[cust_id]'";
$query_sha = db_query($db_link, $sql_sha);
checkSQL($db_link, $query_sha);
$share_amount = 0;
$share_value = 0;
while($row_shares = db_fetch_assoc($query_sha)){
        $share_amount = $share_amount + $row_shares['share_amount'];
        $share_value = $share_value + $row_shares['share_value'];
}

//Select the five most recent savings transactions for display
$sql_sav = "SELECT * FROM savings, savtype WHERE savings.savtype_id = savtype.savtype_id AND cust_id = '$_SESSION[cust_id]' ORDER BY sav_date DESC, sav_id DESC LIMIT 5" ;
$query_sav = db_query($db_link, $sql_sav);
checkSQL($db_link, $query_sav);

//Select all loans for current customer
$sql_loans = "SELECT * FROM loans, loanstatus WHERE loans.loanstatus_id = loanstatus.loanstatus_id AND cust_id = '$_SESSION[cust_id]'";
$query_loans = db_query($db_link, $sql_loans);
checkSQL($db_link, $query_loans);

//Calculate total loans outstanding
$total_loans_due = 0;
$total_loans_balance = 0;
$loans_data = array();
while ($row_loan = db_fetch_assoc($query_loans)){
        $loan_balances = getLoanBalance($db_link, $row_loan['loan_id']);
        $loans_data[] = $row_loan;
        if ($row_loan['loan_issued'] == 1) {
                $total_loans_due += $loan_balances['pdue'] + $loan_balances['idue'];
                $total_loans_balance += $loan_balances['balance'];
        }
}
?>

<!DOCTYPE HTML>
<html>
        <?PHP include 'includes/bootstrap_header.php'; ?>
        <body>
                <?PHP include 'includes/bootstrap_header_nav.php'; ?>

                <div class="container-fluid mt-4">
                        <div class="row">
                                <div class="col-12">
                                        <h2 class="mb-4">
                                                <i class="fa fa-user-circle"></i> 
                                                <?PHP echo $result_cust['cust_name'].' ('.$result_cust['cust_no'].')'; ?>
                                        </h2>

                                        <!-- Financial Summary Dashboard -->
                                        <div class="row mb-4">
                                                <div class="col-md-3">
                                                        <div class="card border-left-primary">
                                                                <div class="card-body">
                                                                        <div class="text-primary font-weight-bold text-uppercase mb-1">Savings Balance</div>
                                                                        <div class="h5 mb-0"><?PHP echo number_format($sav_balance, 2); ?> <?PHP echo $_SESSION['set_cur']; ?></div>
                                                                </div>
                                                        </div>
                                                </div>
                                                <div class="col-md-3">
                                                        <div class="card border-left-danger">
                                                                <div class="card-body">
                                                                        <div class="text-danger font-weight-bold text-uppercase mb-1">Loan Balance</div>
                                                                        <div class="h5 mb-0"><?PHP echo number_format($total_loans_balance, 2); ?> <?PHP echo $_SESSION['set_cur']; ?></div>
                                                                </div>
                                                        </div>
                                                </div>
                                                <div class="col-md-3">
                                                        <div class="card border-left-success">
                                                                <div class="card-body">
                                                                        <div class="text-success font-weight-bold text-uppercase mb-1">Shares Value</div>
                                                                        <div class="h5 mb-0"><?PHP echo number_format($share_value, 2); ?> <?PHP echo $_SESSION['set_cur']; ?></div>
                                                                </div>
                                                        </div>
                                                </div>
                                                <div class="col-md-3">
                                                        <div class="card border-left-info">
                                                                <div class="card-body">
                                                                        <div class="text-info font-weight-bold text-uppercase mb-1">Member Since</div>
                                                                        <div class="h5 mb-0"><?PHP echo date("d.m.Y", $result_cust['cust_since']); ?></div>
                                                                </div>
                                                        </div>
                                                </div>
                                        </div>

                                        <ul class="nav nav-tabs mb-4" role="tablist">
                                                <li class="nav-item">
                                                        <a class="nav-link active" id="details-tab" data-toggle="tab" href="#details" role="tab">Personal Details</a>
                                                </li>
                                                <li class="nav-item">
                                                        <a class="nav-link" id="savings-tab" data-toggle="tab" href="#savings" role="tab">Savings Account</a>
                                                </li>
                                                <li class="nav-item">
                                                        <a class="nav-link" id="loans-tab" data-toggle="tab" href="#loans" role="tab">Loans Account</a>
                                                </li>
                                                <li class="nav-item">
                                                        <a class="nav-link" id="shares-tab" data-toggle="tab" href="#shares" role="tab">Share Account</a>
                                                </li>
                                        </ul>

                                        <div class="tab-content">
                                                <!-- TAB 1: PERSONAL DETAILS -->
                                                <div class="tab-pane fade show active" id="details" role="tabpanel">
                                                        <div class="row">
                                                                <div class="col-md-4">
                                                                        <div class="card mb-4">
                                                                                <div class="card-body text-center">
                                                                                        <a href="cust_new_pic.php?from=customer&cust=<?PHP echo $_SESSION['cust_id']; ?>">
                                                                                                <?PHP
                                                                                                if (isset($result_cust['cust_pic']))
                                                                                                        echo '<img src="'.$result_cust['cust_pic'].'" class="rounded-circle" style="max-width: 200px; border: 3px solid #FF8C00;" title="Customer\'s picture">';
                                                                                                else {
                                                                                                        if ($result_cust['custsex_id'] == 2) echo '<img src="ico/custpic_f.png" class="rounded-circle" style="max-width: 200px; border: 3px solid #FF8C00;" title="Upload new picture" />';
                                                                                                        else echo '<img src="ico/custpic_m.png" class="rounded-circle" style="max-width: 200px; border: 3px solid #FF8C00;" title="Upload new picture" />';
                                                                                                }
                                                                                                ?>
                                                                        </a>
                                                                        </div>
                                                                </div>
                                                                <div class="card-footer bg-light text-center">
                                                                        <small class="text-muted">Click to update photo</small>
                                                                </div>
                                                        </div>

                                                        <div class="card">
                                                                <div class="card-header bg-primary text-white">
                                                                        <strong>Quick Actions</strong>
                                                                </div>
                                                                <div class="card-body">
                                                                        <?PHP
                                                                        if ($result_cust['cust_active'] == 1) {
                                                                                echo '<a href="acc_sav_depos.php?cust='.$_SESSION['cust_id'].'" class="btn btn-success btn-sm btn-block mb-2"><i class="fa fa-plus-circle"></i> Deposit</a>';
                                                                                echo '<a href="acc_sav_withd.php?cust='.$_SESSION['cust_id'].'" class="btn btn-warning btn-sm btn-block mb-2"><i class="fa fa-minus-circle"></i> Withdrawal</a>';
                                                                                echo '<a href="acc_share_buy.php?cust='.$_SESSION['cust_id'].'" class="btn btn-info btn-sm btn-block mb-2"><i class="fa fa-shopping-cart"></i> Buy Shares</a>';
                                                                                echo '<a href="acc_share_sale.php?cust='.$_SESSION['cust_id'].'" class="btn btn-secondary btn-sm btn-block mb-2"><i class="fa fa-money"></i> Sell Shares</a>';
                                                                                if (($timestamp-$result_cust['cust_since']) > convertMonths($_SESSION['set_minmemb'])) {
                                                                                        echo '<a href="loan_new.php?cust='.$_SESSION['cust_id'].'" class="btn btn-danger btn-sm btn-block"><i class="fa fa-file-o"></i> Apply Loan</a>';
                                                                                }
                                                                        }
                                                                        ?>
                                                                </div>
                                                        </div>
                                                </div>

                                                <div class="col-md-8">
                                                        <div class="card">
                                                                <div class="card-header bg-primary text-white">
                                                                        <strong>Edit Customer Details</strong>
                                                                </div>
                                                                <div class="card-body">
                                                                        <form action="customer.php" method="post">
                                                                                <div class="row">
                                                                                        <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                        <label for="cust_no" class="font-weight-bold">Customer No</label>
                                                                                                        <input type="text" class="form-control" id="cust_no" name="cust_no" value="<?PHP echo $result_cust['cust_no']; ?>" />
                                                                                                </div>
                                                                                        </div>
                                                                                        <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                        <label for="cust_name" class="font-weight-bold">Full Name *</label>
                                                                                                        <input type="text" class="form-control" id="cust_name" name="cust_name" value="<?PHP echo $result_cust['cust_name']; ?>" required />
                                                                                                </div>
                                                                                        </div>
                                                                                </div>

                                                                                <div class="row">
                                                                                        <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                        <label for="cust_dob" class="font-weight-bold">Date of Birth</label>
                                                                                                        <input type="text" class="form-control datepicker" id="cust_dob" name="cust_dob" value="<?PHP echo date("d.m.Y",$result_cust['cust_dob']); ?>" placeholder="DD.MM.YYYY" />
                                                                                                </div>
                                                                                        </div>
                                                                                        <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                        <label for="custsex_id" class="font-weight-bold">Gender</label>
                                                                                                        <select class="form-control" id="custsex_id" name="custsex_id">
                                                                                                                <?PHP
                                                                                                                $query_sex_2 = db_query($db_link, $sql_sex);
                                                                                                                while ($row_sex = db_fetch_assoc($query_sex_2)){
                                                                                                                        if($row_sex['custsex_id'] == $result_cust['custsex_id']){
                                                                                                                                echo '<option selected value="'.$row_sex['custsex_id'].'">'.$row_sex['custsex_name'].'</option>';
                                                                                                                        }
                                                                                                                        else echo '<option value="'.$row_sex['custsex_id'].'">'.$row_sex['custsex_name'].'</option>';
                                                                                                                }
                                                                                                                ?>
                                                                                        </select>
                                                                                        </div>
                                                                                </div>

                                                                                <div class="row">
                                                                                        <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                        <label for="cust_address" class="font-weight-bold">Address</label>
                                                                                                        <input type="text" class="form-control" id="cust_address" name="cust_address" value="<?PHP echo $result_cust['cust_address']; ?>" />
                                                                                                </div>
                                                                                        </div>
                                                                                        <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                        <label for="cust_phone" class="font-weight-bold">Phone Number</label>
                                                                                                        <input type="text" class="form-control" id="cust_phone" name="cust_phone" value="<?PHP echo $result_cust['cust_phone']; ?>" />
                                                                                                </div>
                                                                                        </div>
                                                                                </div>

                                                                                <div class="row">
                                                                                        <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                        <label for="cust_email" class="font-weight-bold">Email</label>
                                                                                                        <input type="email" class="form-control" id="cust_email" name="cust_email" value="<?PHP echo $result_cust['cust_email']; ?>" />
                                                                                                </div>
                                                                                        </div>
                                                                                        <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                        <label for="cust_occup" class="font-weight-bold">Occupation</label>
                                                                                                        <input type="text" class="form-control" id="cust_occup" name="cust_occup" value="<?PHP echo $result_cust['cust_occup']; ?>" />
                                                                                                </div>
                                                                                        </div>
                                                                                </div>

                                                                                <div class="row">
                                                                                        <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                        <label for="custmarried_id" class="font-weight-bold">Marital Status</label>
                                                                                                        <select class="form-control" id="custmarried_id" name="custmarried_id">
                                                                                                                <?PHP
                                                                                                                $query_mstat_2 = db_query($db_link, $sql_mstat);
                                                                                                                while ($row_mstat = db_fetch_assoc($query_mstat_2)){
                                                                                                                        if($row_mstat['custmarried_id'] == $result_cust['custmarried_id']){
                                                                                                                                echo '<option selected value="'.$row_mstat['custmarried_id'].'">'.$row_mstat['custmarried_status'].'</option>';
                                                                                                                        }
                                                                                                                        else echo '<option value="'.$row_mstat['custmarried_id'].'">'.$row_mstat['custmarried_status'].'</option>';
                                                                                                                }
                                                                                                                ?>
                                                                                        </select>
                                                                                        </div>
                                                                                        <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                        <label for="custsick_id" class="font-weight-bold">Health Status</label>
                                                                                                        <select class="form-control" id="custsick_id" name="custsick_id">
                                                                                                                <?PHP
                                                                                                                $query_sick_2 = db_query($db_link, $sql_sick);
                                                                                                                while ($row_sick = db_fetch_assoc($query_sick_2)){
                                                                                                                        if($row_sick['custsick_id'] == $result_cust['custsick_id']){
                                                                                                                                echo '<option selected value="'.$row_sick['custsick_id'].'">'.$row_sick['custsick_name'].'</option>';
                                                                                                                        }
                                                                                                                        else echo '<option value="'.$row_sick['custsick_id'].'">'.$row_sick['custsick_name'].'</option>';
                                                                                                                }
                                                                                                                ?>
                                                                                        </select>
                                                                                        </div>
                                                                                </div>

                                                                                <div class="row">
                                                                                        <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                        <label for="cust_heir" class="font-weight-bold">Representative Name</label>
                                                                                                        <input type="text" class="form-control" id="cust_heir" name="cust_heir" value="<?PHP echo $result_cust['cust_heir']; ?>" />
                                                                                                </div>
                                                                                        </div>
                                                                                        <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                        <label for="cust_heirrel" class="font-weight-bold">Relationship</label>
                                                                                                        <input type="text" class="form-control" id="cust_heirrel" name="cust_heirrel" value="<?PHP echo $result_cust['cust_heirrel']; ?>" />
                                                                                                </div>
                                                                                        </div>
                                                                                </div>

                                                                                <div class="row">
                                                                                        <div class="col-md-6">
                                                                                                <div class="form-check mt-3">
                                                                                                        <input class="form-check-input" type="checkbox" id="cust_active" name="cust_active" value="1" <?PHP if ($result_cust['cust_active']==1) echo 'checked'; ?> />
                                                                                                        <label class="form-check-label" for="cust_active">
                                                                                                                <strong>Active Customer</strong>
                                                                                                        </label>
                                                                                                </div>
                                                                                        </div>
                                                                                        <div class="col-md-6">
                                                                                                <div class="form-group mt-3">
                                                                                                        <small class="text-muted">Last updated: <?PHP echo date("d.m.Y H:i", $result_cust['cust_lastupd']); ?> by <?PHP echo $result_cust['user_name']; ?></small>
                                                                                                </div>
                                                                                        </div>
                                                                                </div>

                                                                                <button type="submit" name="update" class="btn btn-primary btn-lg btn-block mt-4">
                                                                                        <i class="fa fa-save"></i> Save Changes
                                                                                </button>
                                                                        </form>
                                                                </div>
                                                        </div>
                                                </div>
                                        </div>
                                </div>

                                <!-- TAB 2: SAVINGS ACCOUNT -->
                                <div class="tab-pane fade" id="savings" role="tabpanel">
                                        <div class="card">
                                                <div class="card-header bg-success text-white">
                                                        <strong><i class="fa fa-piggy-bank"></i> Savings Account - Recent Transactions</strong>
                                                </div>
                                                <div class="card-body table-responsive">
                                                        <table class="table table-striped table-hover">
                                                                <thead class="thead-dark">
                                                                        <tr>
                                                                                <th>Date</th>
                                                                                <th>Transaction Type</th>
                                                                                <th>Amount</th>
                                                                                <th>Receipt/Slip</th>
                                                                        </tr>
                                                                </thead>
                                                                <tbody>
                                                                        <?PHP
                                                                        $query_sav_2 = db_query($db_link, $sql_sav);
                                                                        while($row_sav = db_fetch_assoc($query_sav_2)) {
                                                                                echo '<tr>
                                                                                        <td>'.date("d.m.Y",$row_sav['sav_date']).'</td>
                                                                                        <td>'.$row_sav['savtype_type'].'</td>
                                                                                        <td><strong>'.number_format($row_sav['sav_amount'], 2).' '.$_SESSION['set_cur'].'</strong></td>';
                                                                                if ($row_sav['savtype_id'] == 2) echo '<td><span class="badge badge-warning">S '.$row_sav['sav_slip'].'</span></td>';
                                                                                else echo '<td><span class="badge badge-info">R '.$row_sav['sav_receipt'].'</span></td>';
                                                                                echo '</tr>';
                                                                        }
                                                                        ?>
                                                                </tbody>
                                                        </table>
                                                        <div class="alert alert-info" role="alert">
                                                                <strong>Current Balance:</strong> <?PHP echo number_format($sav_balance, 2); ?> <?PHP echo $_SESSION['set_cur']; ?>
                                                        </div>
                                                </div>
                                        </div>
                                </div>

                                <!-- TAB 3: LOANS ACCOUNT -->
                                <div class="tab-pane fade" id="loans" role="tabpanel">
                                        <div class="card">
                                                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                                                        <strong><i class="fa fa-money"></i> Loans Account</strong>
                                                        <?PHP 
                                                        if ($result_cust['cust_active'] == 1 && ($timestamp-$result_cust['cust_since']) > convertMonths($_SESSION['set_minmemb'])) {
                                                                echo '<a href="loan_new.php?cust='.$_SESSION['cust_id'].'" class="btn btn-sm btn-light"><i class="fa fa-plus-circle"></i> New Loan</a>';
                                                        }
                                                        ?>
                                                </div>
                                                <div class="card-body table-responsive">
                                                        <table class="table table-striped table-hover">
                                                                <thead class="thead-dark">
                                                                        <tr>
                                                                                <th>Loan No.</th>
                                                                                <th>Status</th>
                                                                                <th>Amount</th>
                                                                                <th>Balance</th>
                                                                                <th>Next Payment</th>
                                                                                <th>Action</th>
                                                                        </tr>
                                                                </thead>
                                                                <tbody>
                                                                        <?PHP
                                                                        $query_loans_2 = db_query($db_link, $sql_loans);
                                                                        while ($row_loan = db_fetch_assoc($query_loans_2)){

                                                                                //Select last unpaid Due Date from LTRANS
                                                                                $sql_ltrans = "SELECT MIN(ltrans_due) FROM ltrans, loans WHERE ltrans.loan_id = loans.loan_id AND loans.loanstatus_id = '2' AND loans.loan_id = '$row_loan[loan_id]' AND ltrans_due IS NOT NULL AND ltrans_date IS NULL";
                                                                                $query_ltrans = db_query($db_link, $sql_ltrans);
                                                                                checkSQL($db_link, $query_ltrans);
                                                                                $next_due = db_fetch_assoc($query_ltrans);

                                                                                // Get loan balances
                                                                                $loan_balances = getLoanBalance($db_link, $row_loan['loan_id']);

                                                                                echo '<tr>
                                                                                        <td><strong>'.$row_loan['loan_no'].'</strong></td>
                                                                                        <td><span class="badge badge-primary">'.$row_loan['loanstatus_status'].'</span></td>';
                                                                                if ($row_loan['loan_issued'] == 1) echo '
                                                                                        <td>'.number_format($loan_balances['pdue']+$loan_balances['idue'], 2).' '.$_SESSION['set_cur'].'</td>
                                                                                        <td>'.number_format($loan_balances['balance'], 2).' '.$_SESSION['set_cur'].'</td>';
                                                                                else echo '<td>'.number_format($row_loan['loan_principal'], 2).' '.$_SESSION['set_cur'].'</td>
                                                                                                 <td>N/A</td>';
                                                                                if ($row_loan['loanstatus_id'] == 2 and isset($next_due['MIN(ltrans_due)'])) {
                                                                                        $due_date = $next_due['MIN(ltrans_due)'];
                                                                                        $badge_class = ($due_date < time()) ? 'badge-danger' : 'badge-success';
                                                                                        echo '<td><span class="badge '.$badge_class.'">'.date("d.m.Y", $due_date).'</span></td>';
                                                                                }
                                                                                else echo '<td><span class="badge badge-secondary">N/A</span></td>';
                                                                                echo '<td><a href="loan.php?lid='.$row_loan['loan_id'].'" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i> View</a></td>
                                                                                </tr>';
                                                                        }
                                                                        ?>
                                                                </tbody>
                                                        </table>
                                                </div>
                                        </div>
                                </div>

                                <!-- TAB 4: SHARE ACCOUNT -->
                                <div class="tab-pane fade" id="shares" role="tabpanel">
                                        <div class="card">
                                                <div class="card-header bg-info text-white">
                                                        <strong><i class="fa fa-certificate"></i> Share Account</strong>
                                                </div>
                                                <div class="card-body">
                                                        <div class="row">
                                                                <div class="col-md-6">
                                                                        <h5>Total Shares Owned</h5>
                                                                        <p class="display-4 text-success"><?PHP echo $share_amount; ?></p>
                                                                </div>
                                                                <div class="col-md-6">
                                                                        <h5>Total Share Value</h5>
                                                                        <p class="display-4 text-info"><?PHP echo number_format($share_value, 2); ?> <?PHP echo $_SESSION['set_cur']; ?></p>
                                                                </div>
                                                        </div>
                                                        <?PHP if ($share_amount == 0 && $result_cust['cust_active'] == 1): ?>
                                                                <div class="alert alert-warning" role="alert">
                                                                        <i class="fa fa-exclamation-triangle"></i> This customer has not purchased any shares yet.
                                                                        <a href="acc_share_buy.php?cust=<?PHP echo $_SESSION['cust_id']; ?>" class="alert-link">Buy shares now</a>
                                                                </div>
                                                        <?PHP endif; ?>
                                                </div>
                                        </div>
                                </div>
                        </div>
                </div>

                <?PHP include 'includes/bootstrap_footer.php'; ?>
        </body>
</html>
