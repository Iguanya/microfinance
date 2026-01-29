<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();
getCustID($db_link);

unset($_SESSION['interest_sum'], $_SESSION['balance']);

//Generate timestamp
$timestamp = time();

//Calculate Balance on Savings account
updateSavingsBalance($db_link, $_SESSION['cust_id']);
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
        $custsick_id = sanitize($db_link, $_POST['custsick_id']);
        $cust_active = isset($_POST['cust_active']) ? 1 : 0;
        $timestamp = time();

        //Update CUSTOMER
        $sql_update = "UPDATE customer SET cust_no = '$cust_no', cust_name = '$cust_name', cust_dob = $cust_dob, custsex_id = $custsex_id, cust_address = '$cust_address', cust_phone = '$cust_phone', cust_email = '$cust_email', cust_occup = '$cust_occup', custmarried_id = $custmarried_id, cust_heir = '$cust_heir', cust_heirrel = '$cust_heirrel', custsick_id = $custsick_id, cust_active = '$cust_active', cust_lastupd = $timestamp, user_id = $_SESSION[log_id] WHERE cust_id = $_SESSION[cust_id]";
        $query_update = db_query($db_link, $sql_update);
        checkSQL($db_link, $query_update);
        header('Location: customer.php?cust='.$_SESSION['cust_id']);
        exit;
}

//Get current customer's details
$result_cust = getCustomer($db_link, $_SESSION['cust_id']);

//Error-Message, if customer is not found
if ($result_cust['cust_id']==''){
        echo '<script>
                alert("Customer not found in database.");
                window.location = "cust_search.php";
        </script>';
        exit;
}

//Select Marital Status from custmarried for dropdown-menu
$sql_mstat = "SELECT * FROM custmarried";
$query_mstat = db_query($db_link, $sql_mstat);

//Select Sicknesses from custsick for dropdown-menu
$sql_sick = "SELECT * FROM custsick";
$query_sick = db_query($db_link, $sql_sick);

//Select Sexes from custsex for dropdown-menu
$sql_sex = "SELECT * FROM custsex";
$query_sex = db_query($db_link, $sql_sex);

//Check if customer is linked to a stakeholder (for share capital)
$sql_stakeholder = "SELECT s.*, 
                    COALESCE(SUM(ss.ss_amount), 0) as total_shares,
                    COALESCE(SUM(ss.ss_value), 0) as total_value
                    FROM stakeholder s
                    LEFT JOIN stakeholder_shares ss ON s.stakeholder_id = ss.stakeholder_id
                    WHERE s.cust_id = '$_SESSION[cust_id]'
                    GROUP BY s.stakeholder_id";
$query_stakeholder = db_query($db_link, $sql_stakeholder);
$linked_stakeholder = db_fetch_assoc($query_stakeholder);
$share_amount = $linked_stakeholder ? $linked_stakeholder['total_shares'] : 0;
$share_value = $linked_stakeholder ? $linked_stakeholder['total_value'] : 0;

//Select the five most recent savings transactions for display
$sql_sav = "SELECT * FROM savings, savtype WHERE savings.savtype_id = savtype.savtype_id AND cust_id = '$_SESSION[cust_id]' ORDER BY sav_date DESC, sav_id DESC LIMIT 5" ;
$query_sav = db_query($db_link, $sql_sav);

//Select all loans for current customer
$sql_loans = "SELECT * FROM loans, loanstatus WHERE loans.loanstatus_id = loanstatus.loanstatus_id AND cust_id = '$_SESSION[cust_id]'";
$query_loans = db_query($db_link, $sql_loans);

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
                                                        <div class="card border-start border-primary border-4 shadow-sm">
                                                                <div class="card-body">
                                                                        <div class="text-primary fw-bold text-uppercase mb-1 small">Savings Balance</div>
                                                                        <div class="h5 mb-0 fw-bold"><?PHP echo number_format($sav_balance, 2); ?> <?PHP echo $_SESSION['set_cur']; ?></div>
                                                                </div>
                                                        </div>
                                                </div>
                                                <div class="col-md-3">
                                                        <div class="card border-start border-danger border-4 shadow-sm">
                                                                <div class="card-body">
                                                                        <div class="text-danger fw-bold text-uppercase mb-1 small">Loan Balance</div>
                                                                        <div class="h5 mb-0 fw-bold"><?PHP echo number_format($total_loans_balance, 2); ?> <?PHP echo $_SESSION['set_cur']; ?></div>
                                                                </div>
                                                        </div>
                                                </div>
                                                <div class="col-md-3">
                                                        <div class="card border-start border-success border-4 shadow-sm">
                                                                <div class="card-body">
                                                                        <div class="text-success fw-bold text-uppercase mb-1 small">Shares Value</div>
                                                                        <div class="h5 mb-0 fw-bold">
                                                                                <?PHP if ($linked_stakeholder): ?>
                                                                                <a href="stakeholder.php?id=<?PHP echo $linked_stakeholder['stakeholder_id']; ?>" class="text-decoration-none text-success"><?PHP echo number_format($share_value, 2); ?> <?PHP echo $_SESSION['set_cur']; ?></a>
                                                                                <?PHP else: ?>
                                                                                <?PHP echo number_format($share_value, 2); ?> <?PHP echo $_SESSION['set_cur']; ?>
                                                                                <?PHP endif; ?>
                                                                        </div>
                                                                </div>
                                                        </div>
                                                </div>
                                                <div class="col-md-3">
                                                        <div class="card border-start border-info border-4 shadow-sm">
                                                                <div class="card-body">
                                                                        <div class="text-info fw-bold text-uppercase mb-1 small">Member Since</div>
                                                                        <div class="h5 mb-0 fw-bold"><?PHP echo date("d.m.Y", $result_cust['cust_since']); ?></div>
                                                                </div>
                                                        </div>
                                                </div>
                                        </div>

                                        <ul class="nav nav-tabs mb-4" id="customerTabs" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                        <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab" aria-controls="details" aria-selected="true">Personal Details</button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                        <button class="nav-link" id="savings-tab" data-bs-toggle="tab" data-bs-target="#savings" type="button" role="tab" aria-controls="savings" aria-selected="false">Savings Account</button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                        <button class="nav-link" id="loans-tab" data-bs-toggle="tab" data-bs-target="#loans" type="button" role="tab" aria-controls="loans" aria-selected="false">Loans Account</button>
                                                </li>
                                        </ul>

                                        <div class="tab-content" id="customerTabsContent">
                                                <!-- TAB 1: PERSONAL DETAILS -->
                                                <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                                                        <div class="row">
                                                                <div class="col-md-4">
                                                                        <div class="card mb-4 shadow-sm">
                                                                                <div class="card-body text-center">
                                                                                        <a href="cust_new_pic.php?from=customer&cust=<?PHP echo $_SESSION['cust_id']; ?>">
                                                                                                <?PHP
                                                                                                if (isset($result_cust['cust_pic']))
                                                                                                        echo '<img src="'.$result_cust['cust_pic'].'" class="rounded-circle img-fluid shadow-sm" style="max-width: 200px; border: 3px solid #FF8C00;" title="Customer\'s picture">';
                                                                                                else {
                                                                                                        if ($result_cust['custsex_id'] == 2) echo '<img src="ico/custpic_f.png" class="rounded-circle img-fluid shadow-sm" style="max-width: 200px; border: 3px solid #FF8C00;" title="Upload new picture" />';
                                                                                                        else echo '<img src="ico/custpic_m.png" class="rounded-circle img-fluid shadow-sm" style="max-width: 200px; border: 3px solid #FF8C00;" title="Upload new picture" />';
                                                                                                }
                                                                                                ?>
                                                                                        </a>
                                                                                </div>
                                                                                <div class="card-footer bg-light text-center py-2">
                                                                                        <small class="text-muted">Click photo to update</small>
                                                                                </div>
                                                                        </div>

                                                                        <div class="card mb-4 shadow-sm">
                                                                                <div class="card-header bg-primary text-white py-2">
                                                                                        <h6 class="mb-0">Quick Actions</h6>
                                                                                </div>
                                                                                <div class="card-body p-2">
                                                                                        <div class="d-grid gap-2">
                                                                                                <?PHP
                                                                                                if ($result_cust['cust_active'] == 1) {
                                                                                                        echo '<a href="acc_sav_depos.php?cust='.$_SESSION['cust_id'].'" class="btn btn-success btn-sm"><i class="fa fa-plus-circle"></i> Deposit</a>';
                                                                                                        echo '<a href="acc_sav_withd.php?cust='.$_SESSION['cust_id'].'" class="btn btn-warning btn-sm"><i class="fa fa-minus-circle"></i> Withdrawal</a>';
                                                                                                        if (($timestamp-$result_cust['cust_since']) > convertMonths($_SESSION['set_minmemb'])) {
                                                                                                                echo '<a href="loan_new.php?cust='.$_SESSION['cust_id'].'" class="btn btn-danger btn-sm"><i class="fa fa-file-o"></i> Apply Loan</a>';
                                                                                                        }
                                                                                                }
                                                                                                if ($linked_stakeholder) {
                                                                                                        echo '<hr class="my-2">';
                                                                                                        echo '<a href="stakeholder.php?id='.$linked_stakeholder['stakeholder_id'].'" class="btn btn-info btn-sm text-white"><i class="fa fa-briefcase"></i> View Shares</a>';
                                                                                                }
                                                                                                ?>
                                                                                        </div>
                                                                                </div>
                                                                        </div>
                                                                </div>

                                                                <div class="col-md-8">
                                                                        <div class="card shadow-sm">
                                                                                <div class="card-header bg-primary text-white py-2">
                                                                                        <h6 class="mb-0">Edit Customer Details</h6>
                                                                                </div>
                                                                                <div class="card-body">
                                                                                        <form action="customer.php" method="post">
                                                                                                <div class="row">
                                                                                                        <div class="col-md-6 mb-3">
                                                                                                                <label for="cust_no" class="form-label fw-bold small">Customer No</label>
                                                                                                                <input type="text" class="form-control" id="cust_no" name="cust_no" value="<?PHP echo $result_cust['cust_no']; ?>" />
                                                                                                        </div>
                                                                                                        <div class="col-md-6 mb-3">
                                                                                                                <label for="cust_name" class="form-label fw-bold small">Full Name *</label>
                                                                                                                <input type="text" class="form-control" id="cust_name" name="cust_name" value="<?PHP echo $result_cust['cust_name']; ?>" required />
                                                                                                        </div>
                                                                                                </div>

                                                                                                <div class="row">
                                                                                                        <div class="col-md-6 mb-3">
                                                                                                                <label for="cust_dob" class="form-label fw-bold small">Date of Birth</label>
                                                                                                                <input type="text" class="form-control datepicker" id="cust_dob" name="cust_dob" value="<?PHP echo date("d.m.Y",$result_cust['cust_dob']); ?>" placeholder="DD.MM.YYYY" />
                                                                                                        </div>
                                                                                                        <div class="col-md-6 mb-3">
                                                                                                                <label for="custsex_id" class="form-label fw-bold small">Gender</label>
                                                                                                                <select class="form-select" id="custsex_id" name="custsex_id">
                                                                                                                        <?PHP
                                                                                                                        $query_sex_2 = db_query($db_link, $sql_sex);
                                                                                                                        while ($row_sex = db_fetch_assoc($query_sex_2)){
                                                                                                                                echo '<option value="'.$row_sex['custsex_id'].'"'.($row_sex['custsex_id'] == $result_cust['custsex_id'] ? ' selected' : '').'>'.$row_sex['custsex_name'].'</option>';
                                                                                                                        }
                                                                                                                        ?>
                                                                                                                </select>
                                                                                                        </div>
                                                                                                </div>

                                                                                                <div class="row">
                                                                                                        <div class="col-md-6 mb-3">
                                                                                                                <label for="cust_address" class="form-label fw-bold small">Address</label>
                                                                                                                <input type="text" class="form-control" id="cust_address" name="cust_address" value="<?PHP echo $result_cust['cust_address']; ?>" />
                                                                                                        </div>
                                                                                                        <div class="col-md-6 mb-3">
                                                                                                                <label for="cust_phone" class="form-label fw-bold small">Phone Number</label>
                                                                                                                <input type="text" class="form-control" id="cust_phone" name="cust_phone" value="<?PHP echo $result_cust['cust_phone']; ?>" />
                                                                                                        </div>
                                                                                                </div>

                                                                                                <div class="row">
                                                                                                        <div class="col-md-6 mb-3">
                                                                                                                <label for="cust_email" class="form-label fw-bold small">Email</label>
                                                                                                                <input type="email" class="form-control" id="cust_email" name="cust_email" value="<?PHP echo $result_cust['cust_email']; ?>" />
                                                                                                        </div>
                                                                                                        <div class="col-md-6 mb-3">
                                                                                                                <label for="cust_occup" class="form-label fw-bold small">Occupation</label>
                                                                                                                <input type="text" class="form-control" id="cust_occup" name="cust_occup" value="<?PHP echo $result_cust['cust_occup']; ?>" />
                                                                                                        </div>
                                                                                                </div>

                                                                                                <div class="row">
                                                                                                        <div class="col-md-6 mb-3">
                                                                                                                <label for="custmarried_id" class="form-label fw-bold small">Marital Status</label>
                                                                                                                <select class="form-select" id="custmarried_id" name="custmarried_id">
                                                                                                                        <?PHP
                                                                                                                        $query_mstat_2 = db_query($db_link, $sql_mstat);
                                                                                                                        while ($row_mstat = db_fetch_assoc($query_mstat_2)){
                                                                                                                                echo '<option value="'.$row_mstat['custmarried_id'].'"'.($row_mstat['custmarried_id'] == $result_cust['custmarried_id'] ? ' selected' : '').'>'.$row_mstat['custmarried_status'].'</option>';
                                                                                                                        }
                                                                                                                        ?>
                                                                                                                </select>
                                                                                                        </div>
                                                                                                        <div class="col-md-6 mb-3">
                                                                                                                <label for="custsick_id" class="form-label fw-bold small">Health Status</label>
                                                                                                                <select class="form-select" id="custsick_id" name="custsick_id">
                                                                                                                        <?PHP
                                                                                                                        $query_sick_2 = db_query($db_link, $sql_sick);
                                                                                                                        while ($row_sick = db_fetch_assoc($query_sick_2)){
                                                                                                                                echo '<option value="'.$row_sick['custsick_id'].'"'.($row_sick['custsick_id'] == $result_cust['custsick_id'] ? ' selected' : '').'>'.$row_sick['custsick_name'].'</option>';
                                                                                                                        }
                                                                                                                        ?>
                                                                                                                </select>
                                                                                                        </div>
                                                                                                </div>

                                                                                                <div class="row">
                                                                                                        <div class="col-md-6 mb-3">
                                                                                                                <label for="cust_heir" class="form-label fw-bold small">Representative Name</label>
                                                                                                                <input type="text" class="form-control" id="cust_heir" name="cust_heir" value="<?PHP echo $result_cust['cust_heir']; ?>" />
                                                                                                        </div>
                                                                                                        <div class="col-md-6 mb-3">
                                                                                                                <label for="cust_heirrel" class="form-label fw-bold small">Relationship</label>
                                                                                                                <input type="text" class="form-control" id="cust_heirrel" name="cust_heirrel" value="<?PHP echo $result_cust['cust_heirrel']; ?>" />
                                                                                                        </div>
                                                                                                </div>

                                                                                                <div class="row align-items-center mt-2">
                                                                                                        <div class="col-md-6">
                                                                                                                <div class="form-check">
                                                                                                                        <input class="form-check-input" type="checkbox" id="cust_active" name="cust_active" value="1" <?PHP if ($result_cust['cust_active']==1) echo 'checked'; ?> />
                                                                                                                        <label class="form-check-label fw-bold small" for="cust_active">Active Customer</label>
                                                                                                                </div>
                                                                                                        </div>
                                                                                                        <div class="col-md-6 text-end">
                                                                                                                <small class="text-muted">Last updated: <?PHP echo date("d.m.Y H:i", $result_cust['cust_lastupd']); ?></small>
                                                                                                        </div>
                                                                                                </div>

                                                                                                <div class="d-grid mt-4">
                                                                                                        <button type="submit" name="update" class="btn btn-primary btn-lg">
                                                                                                                <i class="fa fa-save"></i> Save Changes
                                                                                                        </button>
                                                                                                </div>
                                                                                        </form>
                                                                                </div>
                                                                        </div>
                                                                </div>
                                                        </div>
                                                </div>

                                                <!-- TAB 2: SAVINGS ACCOUNT -->
                                                <div class="tab-pane fade" id="savings" role="tabpanel" aria-labelledby="savings-tab">
                                                        <div class="card shadow-sm">
                                                                <div class="card-header bg-success text-white py-2">
                                                                        <h6 class="mb-0"><i class="fa fa-piggy-bank"></i> Savings Account - Recent Transactions</h6>
                                                                </div>
                                                                <div class="card-body">
                                                                        <div class="table-responsive">
                                                                                <table class="table table-striped table-hover align-middle">
                                                                                        <thead class="table-dark">
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
                                                                                                                <td class="fw-bold text-'.($row_sav['sav_amount'] >= 0 ? 'success' : 'danger').'">'.number_format($row_sav['sav_amount'], 2).' '.$_SESSION['set_cur'].'</td>
                                                                                                                <td>';
                                                                                                        if ($row_sav['savtype_id'] == 2) echo '<span class="badge bg-warning text-dark">S '.$row_sav['sav_slip'].'</span>';
                                                                                                        else echo '<span class="badge bg-info text-white">R '.$row_sav['sav_receipt'].'</span>';
                                                                                                        echo '</td></tr>';
                                                                                                }
                                                                                                ?>
                                                                                        </tbody>
                                                                                </table>
                                                                        </div>
                                                                        <div class="alert alert-info py-2 mb-0 mt-3 shadow-sm" role="alert">
                                                                                <div class="row align-items-center text-center text-md-start">
                                                                                        <div class="col-md-6"><strong>Current Balance:</strong></div>
                                                                                        <div class="col-md-6 text-md-end h5 mb-0 fw-bold"><?PHP echo number_format($sav_balance, 2); ?> <?PHP echo $_SESSION['set_cur']; ?></div>
                                                                                </div>
                                                                        </div>
                                                                </div>
                                                        </div>
                                                </div>

                                                <!-- TAB 3: LOANS ACCOUNT -->
                                                <div class="tab-pane fade" id="loans" role="tabpanel" aria-labelledby="loans-tab">
                                                        <div class="card shadow-sm">
                                                                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center py-2">
                                                                        <h6 class="mb-0"><i class="fa fa-money"></i> Loans Account</h6>
                                                                        <?PHP 
                                                                        if ($result_cust['cust_active'] == 1 && ($timestamp-$result_cust['cust_since']) > convertMonths($_SESSION['set_minmemb'])) {
                                                                                echo '<a href="loan_new.php?cust='.$_SESSION['cust_id'].'" class="btn btn-sm btn-light py-0 px-2 fw-bold"><i class="fa fa-plus-circle"></i> New Loan</a>';
                                                                        }
                                                                        ?>
                                                                </div>
                                                                <div class="card-body">
                                                                        <div class="table-responsive">
                                                                                <table class="table table-striped table-hover align-middle">
                                                                                        <thead class="table-dark">
                                                                                                <tr>
                                                                                                        <th>Loan No.</th>
                                                                                                        <th>Status</th>
                                                                                                        <th>Amount</th>
                                                                                                        <th>Balance</th>
                                                                                                        <th>Next Payment</th>
                                                                                                        <th class="text-center">Action</th>
                                                                                                </tr>
                                                                                        </thead>
                                                                                        <tbody>
                                                                                                <?PHP
                                                                                                foreach ($loans_data as $row_loan){
                                                                                                        //Select last unpaid Due Date from LTRANS
                                                                                                        $sql_ltrans = "SELECT MIN(ltrans_due) FROM ltrans, loans WHERE ltrans.loan_id = loans.loan_id AND loans.loanstatus_id = '2' AND loans.loan_id = '$row_loan[loan_id]' AND ltrans_due IS NOT NULL AND ltrans_date IS NULL";
                                                                                                        $query_ltrans = db_query($db_link, $sql_ltrans);
                                                                                                        $next_due = db_fetch_assoc($query_ltrans);

                                                                                                        // Get loan balances
                                                                                                        $loan_balances = getLoanBalance($db_link, $row_loan['loan_id']);

                                                                                                        echo '<tr>
                                                                                                                <td class="fw-bold">'.$row_loan['loan_no'].'</td>
                                                                                                                <td><span class="badge bg-primary">'.$row_loan['loanstatus_status'].'</span></td>';
                                                                                                        if ($row_loan['loan_issued'] == 1) echo '
                                                                                                                <td>'.number_format($loan_balances['pdue']+$loan_balances['idue'], 2).' '.$_SESSION['set_cur'].'</td>
                                                                                                                <td class="fw-bold">'.number_format($loan_balances['balance'], 2).' '.$_SESSION['set_cur'].'</td>';
                                                                                                        else echo '<td>'.number_format($row_loan['loan_principal'], 2).' '.$_SESSION['set_cur'].'</td>
                                                                                                                        <td class="text-muted small">N/A</td>';
                                                                                                        if ($row_loan['loanstatus_id'] == 2 and isset($next_due['MIN(ltrans_due)'])) {
                                                                                                                $due_date = $next_due['MIN(ltrans_due)'];
                                                                                                                $badge_class = ($due_date < time()) ? 'bg-danger' : 'bg-success';
                                                                                                                echo '<td><span class="badge '.$badge_class.'">'.date("d.m.Y", $due_date).'</span></td>';
                                                                                                        }
                                                                                                        else echo '<td><span class="badge bg-secondary">N/A</span></td>';
                                                                                                        echo '<td class="text-center"><a href="loan.php?lid='.$row_loan['loan_id'].'" class="btn btn-sm btn-outline-primary"><i class="fa fa-eye"></i> View</a></td>
                                                                                                        </tr>';
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
                        </div>
                </div>
                <?PHP include 'includes/bootstrap_footer.php'; ?>
        </body>
</html>
