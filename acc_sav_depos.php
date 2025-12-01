<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();
getCustID($db_link);

//Generate timestamp
$timestamp = time();

// Update savings balance for current customer and store into variable
updateSavingsBalance ($db_link, $_SESSION['cust_id']);
$sav_balance = getSavingsBalance($db_link, $_SESSION['cust_id']);

// DEPOSIT-Button
if (isset($_POST['deposit'])){

        // Sanitize user input
        $sav_date = strtotime(sanitize($db_link, $_POST['sav_date']));
        $sav_amount = sanitize($db_link, $_POST['sav_amount']);
        $sav_receipt = sanitize($db_link, $_POST['sav_receipt']);
        $sav_payer = sanitize($db_link, $_POST['sav_payer']);
        if($_POST['sav_fixed'] != "") $sav_fixed = strtotime(sanitize($db_link, $_POST['sav_fixed']));
        else $sav_fixed = NULL;
        $savtype_id = sanitize($db_link, $_POST['savtype_id']);

        // Insert savings transaction into SAVINGS
        $sql_insert = "INSERT INTO savings (savtype_id, cust_id, sav_date, sav_amount, sav_receipt, sav_payer, sav_fixed, sav_created, user_id) VALUES ('$savtype_id', '$_SESSION[cust_id]', '$sav_date', '$sav_amount', '$sav_receipt', '$sav_payer', '$sav_fixed', '$timestamp', '$_SESSION[log_id]')";
        $query_insert = db_query($db_link, $sql_insert);
        checkSQL($db_link, $query_insert);

        // Update savings account balance
        updateSavingsBalance($db_link, $_SESSION['cust_id']);

        // Include Expense, if transaction was Savings Interest
        if ($savtype_id == 3){
                $sql_expense = "INSERT INTO expenses (cust_id, exptype_id, exp_amount, exp_date, exp_voucher, exp_created, user_id) VALUES ('$_SESSION[cust_id]', '19', '$sav_amount', '$sav_date', '$sav_receipt', '$timestamp', '$_SESSION[log_id]')";
                $query_expense = db_query($db_link, $sql_expense);
                checkSQL($db_link, $query_expense);
        }

        //Refer to acc_sav_depos.php
        header('Location: acc_sav_depos.php?cust='.$_SESSION['cust_id']);
}

//Get current customer's details
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
                                        <h2 class="mb-4"><i class="fa fa-plus-circle"></i> Savings Deposit</h2>
                                        <p class="lead">Deposit for <strong><?PHP echo $result_cust['cust_name'].' ('.$result_cust['cust_no'].')'; ?></strong></p>

                                        <div class="row">
                                                <div class="col-md-4">
                                                        <div class="card">
                                                                <div class="card-header bg-primary text-white">
                                                                        <strong>Record Deposit</strong>
                                                                </div>
                                                                <div class="card-body">
                                                                        <form action="acc_sav_depos.php" method="post">
                                                                                <div class="form-group">
                                                                                        <label for="sav_date" class="font-weight-bold">Date</label>
                                                                                        <input type="text" class="form-control datepicker" id="sav_date" name="sav_date" value="<?PHP echo date("d.m.Y",$timestamp); ?>" placeholder="DD.MM.YYYY" required />
                                                                                </div>

                                                                                <?PHP
                                                                                if ($_SESSION['set_sfx'] == 1) {
                                                                                        echo '
                                                                                        <div class="form-group">
                                                                                                <label for="savtype_id" class="font-weight-bold">Transaction Type</label>
                                                                                                <select class="form-control" id="savtype_id" name="savtype_id">
                                                                                                        <option value="1">Deposit</option>
                                                                                                        <option value="3">Savings Interest</option>
                                                                                                </select>
                                                                                        </div>
                                                                                        <div class="form-group">
                                                                                                <label for="sav_fixed" class="font-weight-bold">Fix Deposit Until</label>
                                                                                                <input type="text" class="form-control datepicker" id="sav_fixed" name="sav_fixed" placeholder="DD.MM.YYYY" />
                                                                                        </div>';
                                                                                } else {
                                                                                        echo '<input type="hidden" name="savtype_id" value="1" />';
                                                                                }
                                                                                ?>

                                                                                <div class="form-group">
                                                                                        <label for="sav_amount" class="font-weight-bold">Amount *</label>
                                                                                        <input type="number" class="form-control" id="sav_amount" name="sav_amount" placeholder="<?PHP echo $_SESSION['set_cur']; ?>" min="1" step="0.01" required />
                                                                                </div>

                                                                                <div class="form-group">
                                                                                        <label for="sav_receipt" class="font-weight-bold">Receipt No *</label>
                                                                                        <input type="number" class="form-control" id="sav_receipt" name="sav_receipt" placeholder="for Deposit Transaction" required />
                                                                                </div>

                                                                                <div class="form-group">
                                                                                        <label for="sav_payer" class="font-weight-bold">Depositor</label>
                                                                                        <input type="text" class="form-control" id="sav_payer" name="sav_payer" placeholder="if not account holder" />
                                                                                </div>

                                                                                <button type="submit" name="deposit" class="btn btn-success btn-lg btn-block">
                                                                                        <i class="fa fa-plus-circle"></i> Record Deposit
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
                </div>

                <?PHP include 'includes/bootstrap_footer.php'; ?>
        </body>
</html>
