<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();

getCustID($db_link);

//Generate timestamp
$timestamp = time();

//Get current share value
getShareValue($db_link);

//Get current customer's details
$result_cust = getCustomer($db_link, $_SESSION['cust_id']);

//Get current customer's share balance
$share_balance = getShareBalance($db_link, $_SESSION['cust_id']);

//SELL SHARE-Button
if (isset($_POST['sharesell'])){

        //Sanitize user input
        $share_date = strtotime(sanitize($db_link, $_POST['share_date']));
        $share_receipt = sanitize($db_link, $_POST['share_receipt']);
        $share_amount = (sanitize($db_link, $_POST['share_amount'])) * (-1);
        $share_value = $_SESSION['share_value'] * $share_amount;

        //Insert into SHARES
        $sql_insert_sh = "INSERT INTO shares (cust_id, share_date, share_amount, share_value, share_receipt, share_created, user_id) VALUES ('$_SESSION[cust_id]', '$share_date', '$share_amount', '$share_value', '$share_receipt', $timestamp, '$_SESSION[log_id]')";
        $query_insert_sh = db_query($db_link, $sql_insert_sh);
        checkSQL($db_link, $query_insert_sh);

        header('Location: acc_share_sale.php?cust='.$_SESSION['cust_id']);
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
                                        <h2 class="mb-4"><i class="fa fa-money"></i> Sell Shares</h2>
                                        <p class="lead">Sell shares for <strong><?PHP echo $result_cust['cust_name'].' ('.$result_cust['cust_no'].')'; ?></strong></p>

                                        <div class="row">
                                                <div class="col-md-4">
                                                        <div class="card">
                                                                <div class="card-header bg-primary text-white">
                                                                        <strong>Record Share Sale</strong>
                                                                </div>
                                                                <div class="card-body">
                                                                        <form action="acc_share_sale.php" method="post">
                                                                                <div class="form-group">
                                                                                        <label for="share_date" class="font-weight-bold">Date</label>
                                                                                        <input type="text" class="form-control datepicker" id="share_date" name="share_date" value="<?PHP echo date("d.m.Y", $timestamp); ?>" placeholder="DD.MM.YYYY" required />
                                                                                </div>

                                                                                <div class="form-group">
                                                                                        <label for="share_receipt" class="font-weight-bold">Receipt No *</label>
                                                                                        <input type="number" class="form-control" id="share_receipt" name="share_receipt" <?PHP if(isset($_GET['rec'])) echo 'value="'.$_GET['rec'].'"'; ?> required />
                                                                                </div>

                                                                                <div class="form-group">
                                                                                        <label for="share_amount" class="font-weight-bold">Number of Shares to Sell *</label>
                                                                                        <select class="form-control" id="share_amount" name="share_amount" required>
                                                                                                <?PHP
                                                                                                if ($share_balance['amount'] > 0) {
                                                                                                        for ($i = 1; $i <= $share_balance['amount']; $i++) {
                                                                                                                echo '<option value="'.$i.'">'.$i.' @ '.number_format($_SESSION['share_value'] * $i, 2).' '.$_SESSION['set_cur'].'</option>';
                                                                                                        }
                                                                                                } else {
                                                                                                        echo '<option value="">No shares available to sell</option>';
                                                                                                }
                                                                                                ?>
                                                                                        </select>
                                                                                </div>

                                                                                <button type="submit" name="sharesell" class="btn btn-danger btn-lg btn-block" <?PHP if ($share_balance['amount'] <= 0) echo 'disabled'; ?>>
                                                                                        <i class="fa fa-minus-circle"></i> Sell Shares
                                                                                </button>
                                                                                <a href="customer.php?cust=<?PHP echo $_SESSION['cust_id']; ?>" class="btn btn-secondary btn-block mt-2">
                                                                                        <i class="fa fa-arrow-left"></i> Back to Customer
                                                                                </a>
                                                                        </form>
                                                                </div>
                                        </div>
                                </div>

                                <div class="col-md-8">
                                        <?PHP include 'acc_share_list.php'; ?>
                                </div>
                        </div>
                </div>
        </div>

                <?PHP include 'includes/bootstrap_footer.php'; ?>
        </body>
</html>
