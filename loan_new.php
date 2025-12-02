<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();

$timestamp = time();
$search_results = array();
$customer_selected = false;

// Handle customer search
if (isset($_POST['search_customer'])){
        $search_term = sanitize($db_link, $_POST['search_term']);
        $search_type = sanitize($db_link, $_POST['search_type']);
        
        if($search_type == 'name') {
                $sql_search = "SELECT cust_id, cust_no, cust_name, cust_phone FROM customer WHERE cust_name LIKE '%$search_term%' LIMIT 20";
        } elseif($search_type == 'phone') {
                $sql_search = "SELECT cust_id, cust_no, cust_name, cust_phone FROM customer WHERE cust_phone LIKE '%$search_term%' LIMIT 20";
        } else {
                $sql_search = "SELECT cust_id, cust_no, cust_name, cust_phone FROM customer WHERE cust_no LIKE '%$search_term%' LIMIT 20";
        }
        
        $query_search = db_query($db_link, $sql_search);
        checkSQL($db_link, $query_search);
        
        while($row = db_fetch_assoc($query_search)) {
                $search_results[] = $row;
        }
}

// Handle customer selection
if (isset($_POST['select_customer'])){
        $_SESSION['cust_id'] = sanitize($db_link, $_POST['select_customer']);
        $customer_selected = true;
}

// If customer ID is already set, skip search
if (isset($_GET['cust'])) {
        $_SESSION['cust_id'] = sanitize($db_link, $_GET['cust']);
        $customer_selected = true;
} elseif (!isset($_SESSION['cust_id'])) {
        $_SESSION['cust_id'] = null;
}

// Only proceed with loan logic if customer is selected
if (!$_SESSION['cust_id']) {
        $customer_search_mode = true;
} else {
        $customer_search_mode = false;
}

// Handle new guarantor creation
if (isset($_POST['create_guarantor'])){
        $guarant_no = buildCustNo($db_link);
        $guarant_name = sanitize($db_link, $_POST['guarant_name']);
        $guarant_phone = sanitize($db_link, $_POST['guarant_phone']);
        $guarant_address = sanitize($db_link, $_POST['guarant_address']);
        
        $sql_insert_guarant = "INSERT INTO customer (cust_no, cust_name, cust_phone, cust_address, custsex_id, custmarried_id, custsick_id, cust_active, cust_since, cust_lastupd, user_id) VALUES ('$guarant_no', '$guarant_name', '$guarant_phone', '$guarant_address', '1', '1', '1', '1', $timestamp, $timestamp, '$_SESSION[log_id]')";
        $query_insert_guarant = db_query($db_link, $sql_insert_guarant);
        checkSQL($db_link, $query_insert_guarant);
        
        $sql_insert_savbal = "INSERT INTO savbalance (cust_id, savbal_balance, savbal_date, savbal_created, user_id) VALUES (LAST_INSERT_ID(), '0', $timestamp, $timestamp, '$_SESSION[log_id]')";
        $query_insert_savbal = db_query($db_link, $sql_insert_savbal);
        
        header('Location: loan_new.php?cust='.$_SESSION['cust_id'].'&guarantor_created=1');
}

// Get current customer's details (only if customer selected)
if ($_SESSION['cust_id']) {
        $result_cust = getCustomer($db_link, $_SESSION['cust_id']);
        $savbalance = getSavingsBalance($db_link, $_SESSION['cust_id']);
} else {
        $result_cust = array();
        $savbalance = 0;
}

//NEW LOAN-Button
if (isset($_POST['newloan'])){

        //Calculate new Loan Number
        $sql_loanno = "SELECT loan_id FROM loans WHERE cust_id = '$_SESSION[cust_id]'";
        $query_loanno = db_query($db_link, $sql_loanno);
        checkSQL($db_link, $query_loanno);
        $numberofloans = array();
        while ($row_loanno = db_fetch_array($query_loanno)) $numberofloans[] = $row_loanno;
        $loan_no = 'L-'.$result_cust['cust_no'].'-'.(count($numberofloans) + 1);

        //Sanitize user input
        $loan_date = strtotime(sanitize($db_link, $_POST['loan_date']));
        $loan_principal = sanitize($db_link, $_POST['loan_principal']);
        $loan_interest = sanitize($db_link, $_POST['loan_interest']);
        $loan_period = sanitize($db_link, $_POST['loan_period']);
        $loan_purpose = sanitize($db_link, $_POST['loan_purpose']);
        $loan_sec1 = sanitize($db_link, $_POST['loan_sec1']);
        $loan_sec2 = sanitize($db_link, $_POST['loan_sec2']);;
        $loan_guarant1 = sanitize($db_link, $_POST['loan_guarant1']);
        $loan_guarant2 = sanitize($db_link, $_POST['loan_guarant2']);
        $loan_guarant3 = sanitize($db_link, $_POST['loan_guarant3']);
        $loan_appfee_receipt = sanitize($db_link, $_POST['loan_appfee_receipt']);
        if($_SESSION['set_xl1'] != "") $loan_xtra1 = sanitize($db_link, $_POST['loan_xtra1']);
        else $loan_xtra1 = NULL;
        if($_SESSION['fee_xl1'] != 0) $loan_xtraFee1 = $_SESSION['fee_xl1'];
        else $loan_xtraFee1 = NULL;

        //Calculate expected total interest, monthly rates, and fees
        $loan_principaldue = round($loan_principal / $loan_period, -3);
        $loan_interesttotal = ceil((($loan_principal / 100 * $loan_interest) * $loan_period)/50)*50;
        $loan_interestdue = round($loan_principal / 100 * $loan_interest);
        $loan_repaytotal = $loan_principal + $loan_interesttotal;
        $loan_rate = $loan_principaldue + $loan_interestdue;
        $loan_fee = $loan_principal / 100 * $_SESSION['fee_loan'];
        $loan_insurance = $loan_principal / 100 * $_SESSION['fee_loaninsurance'];

        //Insert Loan into LOANS
        $sql_insert_loan = "INSERT INTO loans (cust_id, loanstatus_id, loan_no, loan_date, loan_issued, loan_principal, loan_interest, loan_appfee_receipt, loan_fee, loan_insurance, loan_rate, loan_period, loan_repaytotal, loan_purpose, loan_guarant1, loan_guarant2, loan_guarant3, loan_created, loan_xtra1, loan_xtraFee1, user_id) VALUES ('$_SESSION[cust_id]', '1', '$loan_no', '$loan_date', '0', '$loan_principal', '$loan_interest', '$loan_appfee_receipt', '$loan_fee', '$loan_insurance', '$loan_rate', '$loan_period', $loan_repaytotal, '$loan_purpose', '$loan_guarant1', '$loan_guarant2', '$loan_guarant3', $timestamp, '$loan_xtra1', '$loan_xtraFee1', '$_SESSION[log_id]')";
        $query_insert_loan = db_query($db_link, $sql_insert_loan);
        checkSQL($db_link, $query_insert_loan);

        //Retrieve LOAN_ID of newly created loan from LOANS and pass to SESSION variable
        $sql_newloanid = "SELECT MAX(loan_id) FROM loans WHERE cust_id = '$_SESSION[cust_id]'";
        $query_newloanid = db_query($db_link, $sql_newloanid);
        checkSQL($db_link, $query_newloanid);
        $result_newloanid = db_fetch_assoc($query_newloanid);
        $_SESSION['loan_id'] = $result_newloanid['MAX(loan_id)'];

        //Insert loan securities into SECURITIES
        if($loan_sec1 != ""){
                $sql_insert_sec1 = "INSERT INTO securities (cust_id, loan_id, sec_no, sec_name, sec_value, sec_path, sec_returned) VALUES ($_SESSION[cust_id], $_SESSION[loan_id], '1', '$loan_sec1', 0, '', 0)";
                $query_insert_sec1 = db_query($db_link, $sql_insert_sec1);
                checkSQL($db_link, $query_insert_sec1);
        }
        if($loan_sec2 != ""){
                $sql_insert_sec2 = "INSERT INTO securities (cust_id, loan_id, sec_no, sec_name, sec_value, sec_path, sec_returned) VALUES ($_SESSION[cust_id], $_SESSION[loan_id], '2', '$loan_sec2', 0, '', 0)";
                $query_insert_sec2 = db_query($db_link, $sql_insert_sec2);
                checkSQL($db_link, $query_insert_sec2);
        }

        //Insert Loan Application Fee into INCOMES
        $sql_inc_laf = "INSERT INTO incomes (cust_id, loan_id, inctype_id, inc_amount, inc_date, inc_receipt, inc_created, user_id) VALUES ('$_SESSION[cust_id]', '$_SESSION[loan_id]', '7', '$_SESSION[fee_loanappl]', '$loan_date', '$loan_appfee_receipt', $timestamp, '$_SESSION[log_id]')";
        $query_inc_laf = db_query($db_link, $sql_inc_laf);
        checkSQL($db_link, $query_inc_laf);

        //Refer to LOAN_SEC.PHP
        header('Location: loan_sec.php?lid='.$_SESSION['loan_id']);
}

/* SELECT LEGITIMATE GUARANTORS FROM CUSTOMER */

//Select all customers except current one (only if customer selected)
if ($_SESSION['cust_id']) {
        $query_cust = getCustOther($db_link);
        $guarantors = array();
} else {
        $query_cust = null;
        $guarantors = array();
}
if ($query_cust && $_SESSION['cust_id']) {
        if ($_SESSION['set_maxguar'] == ""){
                while ($row_cust = db_fetch_assoc($query_cust)){
                        if ($row_cust['cust_active'] == 1) $guarantors[] = $row_cust;
                }
        }
        else {
                //Select all guarantors of active loans
                $sql_guarantact = "SELECT loan_guarant1, loan_guarant2, loan_guarant3 FROM loans WHERE loanstatus_id = 2";
                $query_guarantact = db_query($db_link, $sql_guarantact);
                checkSQL($db_link, $query_guarantact);
                $guarantact = array();
                while($row_guarantact = db_fetch_assoc($query_guarantact)){
                        $guarantact[] = $row_guarantact;
                }

                //Choose only those customers as legitimate guarantors who are not guarantors for more than a specified number of active loans

                while ($row_cust = db_fetch_assoc($query_cust)){
                        $guarant_count = 0;

                        foreach($guarantact as $ga){
                                if ($ga['loan_guarant1'] == $row_cust['cust_id']) $guarant_count = $guarant_count + 1;
                                if ($ga['loan_guarant2'] == $row_cust['cust_id']) $guarant_count = $guarant_count + 1;
                                if ($ga['loan_guarant3'] == $row_cust['cust_id']) $guarant_count = $guarant_count + 1;
                        }

                        if ($guarant_count < $_SESSION['set_maxguar']) $guarantors[] = $row_cust;
                }
        }
}

// Compute Maximum and Minimum principal amount
if ($_SESSION['set_maxlp'] != "" AND $_SESSION['set_maxpsr'] != ""){
        if(($savbalance * ($_SESSION['set_maxpsr']/100)) < $_SESSION['set_maxlp'])
                $maxlp = ($savbalance * ($_SESSION['set_maxpsr']/100));
        else
                $maxlp = $_SESSION['set_maxlp'];
}
elseif ($_SESSION['set_maxlp'] == "" AND $_SESSION['set_maxpsr'] != "")
        $maxlp = ($savbalance * ($_SESSION['set_maxpsr']/100));
elseif ($_SESSION['set_maxlp'] != "" AND $_SESSION['set_maxpsr'] == "")
        $maxlp = $_SESSION['set_maxlp'];
else $maxlp = "";

if ($_SESSION['set_minlp'] != "") $minlp = $_SESSION['set_minlp'];
else $minlp = 1;
?>

<!DOCTYPE HTML>
<html>
        <?PHP include 'includes/bootstrap_header.php'; ?>
        <body>
                <?PHP include 'includes/bootstrap_header_nav.php'; ?>

                <div class="container-fluid mt-4">
                        <div class="row">
                                <div class="col-12">
                                        <h2 class="mb-4"><i class="fa fa-file-text"></i> New Loan Application</h2>

                                        <?PHP if($customer_search_mode): ?>
                                        <!-- CUSTOMER SEARCH SECTION -->
                                        <div class="card mb-4">
                                                <div class="card-header bg-info text-white">
                                                        <strong><i class="fa fa-search"></i> Select Customer</strong>
                                                </div>
                                                <div class="card-body">
                                                        <form action="loan_new.php" method="post" class="mb-4">
                                                                <div class="row">
                                                                        <div class="col-md-8">
                                                                                <div class="input-group">
                                                                                        <input type="text" class="form-control" name="search_term" placeholder="Search here..." required />
                                                                                        <select class="form-control" name="search_type" style="max-width: 150px;">
                                                                                                <option value="name">By Name</option>
                                                                                                <option value="id">By ID</option>
                                                                                                <option value="phone">By Phone</option>
                                                                                        </select>
                                                                                        <button class="btn btn-info" type="submit" name="search_customer">
                                                                                                <i class="fa fa-search"></i> Search
                                                                                        </button>
                                                                                </div>
                                                                        </div>
                                                                </div>
                                                        </form>

                                                        <?PHP if(count($search_results) > 0): ?>
                                                        <div class="table-responsive">
                                                                <table class="table table-striped table-hover">
                                                                        <thead class="thead-dark">
                                                                                <tr>
                                                                                        <th>Customer No</th>
                                                                                        <th>Name</th>
                                                                                        <th>Phone</th>
                                                                                        <th>Action</th>
                                                                                </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                                <?PHP foreach($search_results as $cust): ?>
                                                                                <tr>
                                                                                        <td><strong><?PHP echo $cust['cust_no']; ?></strong></td>
                                                                                        <td><?PHP echo $cust['cust_name']; ?></td>
                                                                                        <td><?PHP echo $cust['cust_phone']; ?></td>
                                                                                        <td>
                                                                                                <form action="loan_new.php" method="post" style="display:inline;">
                                                                                                        <input type="hidden" name="select_customer" value="<?PHP echo $cust['cust_id']; ?>" />
                                                                                                        <button type="submit" class="btn btn-success btn-sm">
                                                                                                                <i class="fa fa-check"></i> Select
                                                                                                        </button>
                                                                                                </form>
                                                                                        </td>
                                                                                </tr>
                                                                                <?PHP endforeach; ?>
                                                                        </tbody>
                                                                </table>
                                                        </div>
                                                        <?PHP elseif(isset($_POST['search_customer'])): ?>
                                                        <div class="alert alert-warning"><i class="fa fa-info-circle"></i> No customers found matching your search criteria.</div>
                                                        <?PHP endif; ?>
                                                </div>
                                        </div>
                                        <?PHP endif; ?>

                                        <?PHP if(!$customer_search_mode): ?>
                                        <p class="lead">Customer: <strong><?PHP echo $result_cust['cust_name'].' ('.$result_cust['cust_no'].')'; ?></strong></p>

                                        <?PHP if(isset($_GET['guarantor_created'])): ?>
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <strong>Success!</strong> New guarantor has been created successfully. You can now select them in the form below.
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                </button>
                                        </div>
                                        <?PHP endif; ?>

                                        <div class="card">
                                                <div class="card-header bg-primary text-white">
                                                        <strong>Loan Details</strong>
                                                </div>
                                                <div class="card-body">
                                                        <form action="loan_new.php" method="post">
                                                                <div class="row">
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Principal Amount *</label>
                                                                                        <input type="number" class="form-control" name="loan_principal" id="loan_principal" placeholder="Loan Sum in <?PHP echo $_SESSION['set_cur']; ?>" min="<?PHP echo $minlp; ?>" max="<?PHP echo $maxlp; ?>" step="0.01" onChange="calc_rate(<?PHP echo $_SESSION['fee_loan']; ?>)" required />
                                                                                </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Loan Period (Months) *</label>
                                                                                        <input type="number" class="form-control" name="loan_period" id="loan_period" placeholder="Number of Months" onChange="calc_rate(<?PHP echo $_SESSION['fee_loan']; ?>)" required />
                                                                                </div>
                                                                        </div>
                                                                </div>

                                                                <div class="row">
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Interest Rate (% per month) *</label>
                                                                                        <input type="text" class="form-control" name="loan_interest" id="loan_interest" value="<?PHP echo $_SESSION['fee_loaninterestrate']; ?>" onChange="calc_rate(<?PHP echo $_SESSION['fee_loan']; ?>)" required />
                                                                                </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Loan Purpose *</label>
                                                                                        <input type="text" class="form-control" name="loan_purpose" placeholder="Purpose for the Loan" required />
                                                                                </div>
                                                                        </div>
                                                                </div>

                                                                <div class="row">
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Security 1</label>
                                                                                        <input type="text" class="form-control" name="loan_sec1" placeholder="First Security (e.g., Land, Equipment)" />
                                                                                </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Security 2</label>
                                                                                        <input type="text" class="form-control" name="loan_sec2" placeholder="Second Security" />
                                                                                </div>
                                                                        </div>
                                                                </div>

                                                                <hr class="my-4" />
                                                                <h5 class="mb-3"><i class="fa fa-users"></i> Guarantors</h5>

                                                                <?PHP
                                                                for($i=1; $i<=$_SESSION['set_maxguar']; $i++){ ?>
                                                                <div class="row mb-3">
                                                                        <div class="col-md-6">
                                                                                <label class="font-weight-bold">Guarantor <?PHP echo $i ?> *</label>
                                                                                <select class="form-control" name="loan_guarant<?PHP echo $i ?>" required>
                                                                                        <option value="">-- Select a Guarantor --</option>
                                                                                        <?PHP
                                                                                        foreach ($guarantors as $g){
                                                                                                echo '<option value="'.$g['cust_id'].'">'.$g['cust_no'].' - '.$g['cust_name'].'</option>';
                                                                                        }
                                                                                        ?>
                                                                                </select>
                                                                        </div>
                                                                        <?PHP if($i == 1): ?>
                                                                        <div class="col-md-6">
                                                                                <label class="font-weight-bold">&nbsp;</label>
                                                                                <button type="button" class="btn btn-info btn-block" data-toggle="collapse" data-target="#new-guarantor">
                                                                                        <i class="fa fa-user-plus"></i> Create New Guarantor
                                                                                </button>
                                                                        </div>
                                                                        <?PHP endif; ?>
                                                                </div>
                                                                <?PHP } ?>

                                                                <!-- Create New Guarantor Form -->
                                                                <div class="collapse" id="new-guarantor">
                                                                        <div class="card card-body bg-light mt-3 mb-3">
                                                                                <h6 class="mb-3"><i class="fa fa-user-plus"></i> Register New Guarantor</h6>
                                                                                <form action="loan_new.php" method="post">
                                                                                        <div class="form-group">
                                                                                                <label class="font-weight-bold">Guarantor Name *</label>
                                                                                                <input type="text" class="form-control" name="guarant_name" placeholder="Full Name" required />
                                                                                        </div>
                                                                                        <div class="row">
                                                                                                <div class="col-md-6">
                                                                                                        <div class="form-group">
                                                                                                                <label class="font-weight-bold">Phone Number</label>
                                                                                                                <input type="text" class="form-control" name="guarant_phone" placeholder="Contact Number" />
                                                                                                        </div>
                                                                                                </div>
                                                                                                <div class="col-md-6">
                                                                                                        <div class="form-group">
                                                                                                                <label class="font-weight-bold">Address</label>
                                                                                                                <input type="text" class="form-control" name="guarant_address" placeholder="Residential Address" />
                                                                                                        </div>
                                                                                                </div>
                                                                                        </div>
                                                                                        <button type="submit" name="create_guarantor" class="btn btn-success">
                                                                                                <i class="fa fa-check"></i> Create & Refresh List
                                                                                        </button>
                                                                                </form>
                                                                        </div>
                                                                </div>

                                                                <hr class="my-4" />
                                                                <h5 class="mb-3"><i class="fa fa-calculator"></i> Fees & Charges</h5>

                                                                <div class="row">
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Loan Fee</label>
                                                                                        <input type="text" class="form-control" name="loan_fee" id="loan_fee" disabled />
                                                                                </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Loan Insurance</label>
                                                                                        <input type="text" class="form-control" name="loan_insurance" id="loan_insurance" disabled />
                                                                                </div>
                                                                        </div>
                                                                </div>

                                                                <div class="row">
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Application Date *</label>
                                                                                        <input type="text" class="form-control datepicker" name="loan_date" placeholder="DD.MM.YYYY" value="<?PHP echo date("d.m.Y",$timestamp) ?>" required />
                                                                                </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Application Fee Receipt *</label>
                                                                                        <input type="number" class="form-control" name="loan_appfee_receipt" placeholder="Receipt Number" required />
                                                                                </div>
                                                                        </div>
                                                                </div>

                                                                <?PHP if($_SESSION['set_xl1'] != ""): ?>
                                                                <div class="form-group">
                                                                        <label class="font-weight-bold"><?PHP echo $_SESSION['set_xl1']; ?></label>
                                                                        <input type="text" class="form-control" name="loan_xtra1" id="loan_xtra1" />
                                                                </div>
                                                                <?PHP endif; ?>

                                                                <button type="submit" name="newloan" class="btn btn-success btn-lg btn-block mt-3">
                                                                        <i class="fa fa-check"></i> Submit Loan Application
                                                                </button>
                                                                <a href="customer.php?cust=<?PHP echo $_SESSION['cust_id']; ?>" class="btn btn-secondary btn-block mt-2">
                                                                        <i class="fa fa-arrow-left"></i> Cancel
                                                                </a>
                                                        </form>
                                                </div>
                                        </div>
                                </div>
                        </div>
                </div>

                <?PHP include 'includes/bootstrap_footer.php'; ?>
        </body>
</html>
