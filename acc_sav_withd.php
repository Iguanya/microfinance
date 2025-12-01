<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();
getCustID($db_link);

//Generate Timestamp
$timestamp = time();

// Update savings balance for current customer and store into variable
updateSavingsBalance ($db_link, $_SESSION['cust_id']);
$sav_balance = getSavingsBalance($db_link, $_SESSION['cust_id']);
$sav_fixed = getSavingsFixed($db_link, $_SESSION['cust_id']);

// WITHDRAW-Button
if (isset($_POST['withdraw'])){

        //Sanitize user input
        $sav_amount = sanitize($db_link, $_POST['sav_amount'])*(-1);
        $sav_slip = sanitize($db_link, $_POST['sav_slip']);
        $sav_receipt = sanitize($db_link, $_POST['sav_receipt']);
        $sav_date = strtotime(sanitize($db_link, $_POST['sav_date']));
        $sav_deduct = sanitize($db_link, $_POST['sav_deduct']);

        // Insert into SAVINGS
        $sql_insert = "INSERT INTO savings (cust_id, sav_date, sav_amount, savtype_id, sav_receipt, sav_slip, sav_created, user_id) VALUES ('$_SESSION[cust_id]', '$sav_date', $sav_amount, '2', '$sav_receipt', '$sav_slip', '$timestamp', '$_SESSION[log_id]')";
        $query_insert = db_query($db_link, $sql_insert);
        checkSQL($db_link, $query_insert);

        // Update savings account balance
        updateSavingsBalance($db_link, $_SESSION['cust_id']);

        // Get SAV_ID for the latest entry
        $sql_savid = "SELECT MAX(sav_id) FROM savings WHERE cust_id = '$_SESSION[cust_id]' AND sav_receipt = '$sav_receipt' AND sav_created = '$timestamp'";
        $query_savid = db_query($db_link, $sql_savid);
        checkSQL($db_link, $query_savid);
        $sav_id = db_fetch_array($query_savid);

        // Insert Fee into INCOMES
        $sql_insert_income = "INSERT INTO incomes (cust_id, inctype_id, sav_id, inc_amount, inc_date, inc_receipt, inc_created, user_id) VALUES ('$_SESSION[cust_id]', '2', '$sav_id[0]', '$_SESSION[fee_withdraw]', '$sav_date', '$sav_receipt', '$timestamp', '$_SESSION[log_id]')";
        $query_insert_income = db_query($db_link, $sql_insert_income);
        checkSQL($db_link, $query_insert_income);

        // Insert Fee into SAVINGS, if applicable
        if($sav_deduct == 1){
                $fee_withdraw_neg = ($_SESSION['fee_withdraw'] * -1);

                $sql_insert_fee = "INSERT INTO savings (sav_mother, cust_id, sav_date, sav_amount, savtype_id, sav_receipt, sav_slip, sav_created, user_id) VALUES ('$sav_id[0]', '$_SESSION[cust_id]', '$sav_date', '$fee_withdraw_neg', '4', '$sav_receipt', '$sav_slip', '$timestamp', '$_SESSION[log_id]')";
                $query_insert_fee = db_query($db_link, $sql_insert_fee);
                checkSQL($db_link, $query_insert_fee);

                // Update savings account balance
                updateSavingsBalance($db_link, $_SESSION['cust_id']);
        }

        // Forward to acc_sav_withd.php
        header('Location: acc_sav_withd.php?cust='.$_SESSION['cust_id']);
}

// Get current customer's details
$result_cust = getCustomer($db_link, $_SESSION['cust_id']);
?>

<!DOCTYPE HTML>
<html>
        <?PHP include 'includes/bootstrap_header.php'; ?>
        <body>
                <?PHP include 'includes/bootstrap_header_nav.php'; ?>

                <div class="container-fluid mt-4">
                        <div class="row">
                                <div class="col-12">
                                        <h2 class="mb-4"><i class="fa fa-minus-circle"></i> Savings Withdrawal</h2>
                                        <p class="lead">Withdrawal for <strong><?PHP echo $result_cust['cust_name'].' ('.$result_cust['cust_no'].')'; ?></strong></p>

                                        <div class="row">
                                                <div class="col-md-4">
                                                        <div class="card">
                                                                <div class="card-header bg-primary text-white">
                                                                        <strong>Record Withdrawal</strong>
                                                                </div>
                                                                <div class="card-body">
                                                                        <form action="acc_sav_withd.php" method="post">
                                                                                <div class="form-group">
                                                                                        <label for="sav_date" class="font-weight-bold">Date</label>
                                                                                        <input type="text" class="form-control datepicker" id="sav_date" name="sav_date" value="<?PHP echo date("d.m.Y",$timestamp); ?>" placeholder="DD.MM.YYYY" required />
                                                                                </div>

                                                                                <div class="form-group">
                                                                                        <label for="sav_amount" class="font-weight-bold">Amount *</label>
                                                                                        <input type="number" class="form-control" id="sav_amount" name="sav_amount" placeholder="<?PHP echo $_SESSION['set_cur']; ?>" min="1" step="0.01" required />
                                                                                </div>

                                                                                <div class="form-group">
                                                                                        <label for="sav_slip" class="font-weight-bold">Withdrawal Slip No *</label>
                                                                                        <input type="number" class="form-control" id="sav_slip" name="sav_slip" placeholder="Slip No." required />
                                                                                </div>

                                                                                <div class="form-group">
                                                                                        <label for="sav_receipt" class="font-weight-bold">Receipt No *</label>
                                                                                        <input type="number" class="form-control" id="sav_receipt" name="sav_receipt" placeholder="for Withdrawal Fee" required />
                                                                                </div>

                                                                                <div class="form-check">
                                                                                        <input class="form-check-input" type="checkbox" id="sav_deduct" name="sav_deduct" value="1" />
                                                                                        <label class="form-check-label" for="sav_deduct">
                                                                                                <strong>Deduct withdrawal fee from savings</strong>
                                                                                        </label>
                                                                                </div>

                                                                                <button type="submit" name="withdraw" class="btn btn-warning btn-lg btn-block mt-3">
                                                                                        <i class="fa fa-minus-circle"></i> Record Withdrawal
                                                                                </button>
                                                                                <a href="customer.php?cust=<?PHP echo $_SESSION['cust_id']; ?>" class="btn btn-secondary btn-block mt-2">
                                                                                        <i class="fa fa-arrow-left"></i> Back to Customer
                                                                                </a>
                                                                        </form>
                                                                </div>
                                        </div>
                                </div>

                                <div class="col-md-8">
                                        <?PHP include 'acc_sav_list.php'; ?>
                                </div>
                        </div>
                </div>
        </div>

                <?PHP include 'includes/bootstrap_footer.php'; ?>
        </body>
</html>
