<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();

//Generate timestamp
$timestamp = time();

//CREATE-Button
if (isset($_POST['create'])){

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
        $cust_since = strtotime(sanitize($db_link, $_POST['cust_since']));
        $_SESSION['receipt_no'] = sanitize($db_link, $_POST['receipt_no']);

        //Insert new Customer into CUSTOMER
        $sql_insert = "INSERT INTO customer (cust_no, cust_name, cust_dob, custsex_id, cust_address, cust_phone, cust_email, cust_occup, custmarried_id, cust_heir, cust_heirrel, cust_since, custsick_id, cust_lastsub, cust_active, cust_lastupd, user_id) VALUES ('$cust_no', '$cust_name', '$cust_dob', '$custsex_id', '$cust_address', '$cust_phone', '$cust_email', '$cust_occup', $custmarried_id, '$cust_heir', '$cust_heirrel', $cust_since, $custsick_id, $cust_since, '1', $timestamp, $_SESSION[log_id])";
        $query_insert = db_query($db_link, $sql_insert);
        checkSQL($db_link, $query_insert);

        //Get new Customer's ID from CUSTOMER
        $sql_maxid = "SELECT MAX(cust_id) FROM customer";
        $query_maxid = db_query($db_link, $sql_maxid);
        checkSQL($db_link, $query_maxid);
        $maxid = db_fetch_assoc($query_maxid);
        $_SESSION['cust_id'] = $maxid['MAX(cust_id)'];

        //Insert Entrance Fee and Stationary Sales into INCOMES
        $sql_insert_fee = "INSERT INTO incomes (cust_id, inctype_id, inc_amount, inc_date, inc_receipt, inc_text, inc_created, user_id) VALUES ($_SESSION[cust_id], '1', $_SESSION[fee_entry], $cust_since, '$_SESSION[receipt_no]', 'Entrance Fee', $timestamp, $_SESSION[log_id]), ($_SESSION[cust_id], '6', $_SESSION[fee_stationary], $cust_since, '$_SESSION[receipt_no]', 'Stationary', $timestamp, $_SESSION[log_id])";
        $query_insert_fee = db_query($db_link, $sql_insert_fee);
        checkSQL($db_link, $query_insert_fee);

        //Create a new empty SAVBALANCE entry for the new customer
        $sql_insert_savbal = "INSERT INTO savbalance (cust_id, savbal_balance, savbal_fixed, savbal_date, savbal_created, user_id) VALUES ('$_SESSION[cust_id]', '0', '0', '$timestamp', '$timestamp', '$_SESSION[log_id]')";
        $query_insert_savbal = db_query($db_link, $sql_insert_savbal);
        checkSQL($db_link, $query_insert_savbal);

        //Refer to cust_new_pic.php
        header('Location: cust_new_pic.php?from=new');
}

//Select Marital Status for Drop-down-Menu
$sql_mstat = "SELECT * FROM custmarried";
$query_mstat = db_query($db_link, $sql_mstat);
checkSQL($db_link, $query_mstat);

//Select Sicknesses for Drop-down-Menu
$sql_sick = "SELECT * FROM custsick";
$query_sick = db_query($db_link, $sql_sick);
checkSQL($db_link, $query_sick);

//Select Sexes from custsex for dropdown-menu
$sql_sex = "SELECT * FROM custsex";
$query_sex = db_query($db_link, $sql_sex);
checkSQL($db_link, $query_sex);

//Build new CUST_NO
$newCustNo = buildCustNo($db_link);
?>

<!DOCTYPE HTML>
<html>
        <?PHP include 'includes/bootstrap_header.php'; ?>
        <body>
                <div class="container-fluid mt-4">
                        <div class="row">
                                <div class="col-12">
                                        <h2 class="mb-4"><i class="fa fa-user-plus"></i> New Customer Registration</h2>
                                        <p class="lead">Customer No: <strong><?PHP echo $newCustNo; ?></strong></p>

                                        <div class="card">
                                                <div class="card-header bg-primary text-white">
                                                        <strong>Customer Information</strong>
                                                </div>
                                                <div class="card-body">
                                                        <form action="cust_new.php" method="post">
                                                                <input type="hidden" name="cust_no" value="<?PHP echo $newCustNo; ?>" />
                                                                <div class="row">
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Customer Number</label>
                                                                                        <input type="text" class="form-control" value="<?PHP echo $newCustNo; ?>" disabled />
                                                                                </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Full Name *</label>
                                                                                        <input type="text" class="form-control" name="cust_name" placeholder="Full Name" required />
                                                                                </div>
                                                                        </div>
                                                                </div>

                                                                <div class="row">
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Gender</label>
                                                                                        <select class="form-control" name="custsex_id">
                                                                                                <?PHP
                                                                                                while ($row_sex = db_fetch_assoc($query_sex)){
                                                                                                        echo '<option value="'.$row_sex['custsex_id'].'">'.$row_sex['custsex_name'].'</option>';
                                                                                                }
                                                                                                ?>
                                                                                        </select>
                                                                                </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Date of Birth</label>
                                                                                        <input type="text" class="form-control datepicker" name="cust_dob" placeholder="DD.MM.YYYY" required />
                                                                                </div>
                                                                        </div>
                                                                </div>

                                                                <div class="row">
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Address</label>
                                                                                        <input type="text" class="form-control" name="cust_address" placeholder="Place of Residence" />
                                                                                </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Phone Number</label>
                                                                                        <input type="text" class="form-control" name="cust_phone" />
                                                                                </div>
                                                                        </div>
                                                                </div>

                                                                <div class="row">
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Email Address</label>
                                                                                        <input type="email" class="form-control" name="cust_email" placeholder="abc@xyz.com" />
                                                                                </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Occupation</label>
                                                                                        <input type="text" class="form-control" name="cust_occup" />
                                                                                </div>
                                                                        </div>
                                                                </div>

                                                                <div class="row">
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Marital Status</label>
                                                                                        <select class="form-control" name="custmarried_id">
                                                                                                <?PHP
                                                                                                while ($row_mstat = db_fetch_assoc($query_mstat)){
                                                                                                        echo '<option value="'.$row_mstat['custmarried_id'].'">'.$row_mstat['custmarried_status'].'</option>';
                                                                                                }
                                                                                                ?>
                                                                                        </select>
                                                                                </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Sickness/Medical Condition</label>
                                                                                        <select class="form-control" name="custsick_id">
                                                                                                <?PHP
                                                                                                while ($row_sick = db_fetch_assoc($query_sick)){
                                                                                                        echo '<option value="'.$row_sick['custsick_id'].'">'.$row_sick['custsick_name'].'</option>';
                                                                                                }
                                                                                                ?>
                                                                                        </select>
                                                                                </div>
                                                                        </div>
                                                                </div>

                                                                <div class="row">
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Representative Name</label>
                                                                                        <input type="text" class="form-control" name="cust_heir" placeholder="Authorized Representative" />
                                                                                </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Relation to Representative</label>
                                                                                        <input type="text" class="form-control" name="cust_heirrel" placeholder="e.g. Wife, Secretary, etc." />
                                                                                </div>
                                                                        </div>
                                                                </div>

                                                                <div class="row">
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Member Since</label>
                                                                                        <input type="text" class="form-control datepicker" name="cust_since" value="<?PHP echo date("d.m.Y", $timestamp) ?>" />
                                                                                </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Active</label>
                                                                                        <div class="form-check mt-2">
                                                                                                <input class="form-check-input" type="checkbox" id="active" disabled checked />
                                                                                                <label class="form-check-label" for="active">
                                                                                                        Enabled
                                                                                                </label>
                                                                                        </div>
                                                                                </div>
                                                                        </div>
                                                                </div>

                                                                <input type="hidden" name="receipt_no" id="receipt_no" value="0" />

                                                                <button type="submit" name="create" class="btn btn-success btn-lg btn-block mt-3">
                                                                        <i class="fa fa-check"></i> Continue
                                                                </button>
                                                                <a href="cust_act.php" class="btn btn-secondary btn-block mt-2">
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
