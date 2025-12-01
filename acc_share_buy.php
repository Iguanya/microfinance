<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();
getCustID($db_link);

//Generate timestamp
$timestamp = time();

//Get current share value
getShareValue($db_link);

//BUY SHARE-Button
if (isset($_POST['sharebuy'])){

        //Sanitize user input
        $share_date = strtotime(sanitize($db_link, $_POST['share_date']));
        $share_receipt = sanitize($db_link, $_POST['share_receipt']);
        $share_amount = sanitize($db_link, $_POST['share_amount']);
        $share_value = $_SESSION['share_value'] * $share_amount;

        //Insert into SHARES
        $sql_insert_sh = "INSERT INTO shares (cust_id, share_date, share_amount, share_value, share_receipt, share_created, user_id) VALUES ('$_SESSION[cust_id]', '$share_date', '$share_amount', '$share_value', '$share_receipt', $timestamp, '$_SESSION[log_id]')";
        $query_insert_sh = db_query($db_link, $sql_insert_sh);
        checkSQL($db_link, $query_insert_sh);

        header('Location: acc_share_buy.php?cust='.$_SESSION['cust_id']);
}

//TRANSFER-Button
if (isset($_POST['shtrans'])){
        $shtrans_cust = sanitize($db_link, $_POST['shtrans_cust']);

        $sql_shfrom = "SELECT * FROM shares WHERE cust_id = '$shtrans_cust'";
        $query_shfrom = db_query($db_link, $sql_shfrom);
        checkSQL($db_link, $query_shfrom);

        $shfrom_amount = 0;
        $shfrom_value = 0;
        while($row_shfrom = db_fetch_assoc($query_shfrom)){
                $shfrom_amount = $shfrom_amount + $row_shfrom['share_amount'];
                $shfrom_value = $shfrom_value + $row_shfrom['share_value'];
        }

        //Insert into SHARES for Target Customer
        $sql_shto = "INSERT INTO shares (cust_id, share_date, share_amount, share_value, share_trans, share_transfrom, share_created, user_id) VALUES ('$_SESSION[cust_id]', '$timestamp', '$shfrom_amount', '$shfrom_value', '1', '$shtrans_cust', $timestamp, '$_SESSION[log_id]')";
        $query_shto = db_query($db_link, $sql_shto);
        checkSQL($db_link, $query_shto);

        //Empty Share Account for Source Customer
        $shfrom_amount_del = $shfrom_amount * (-1);
        $shfrom_value_del = $shfrom_value * (-1);
        $sql_shdel = "INSERT INTO shares (cust_id, share_date, share_amount, share_value, share_trans, share_created, user_id) VALUES ('$shtrans_cust', '$timestamp', '$shfrom_amount_del', '$shfrom_value_del', '1', $timestamp, '$_SESSION[log_id]')";
        $query_shdel = db_query($db_link, $sql_shdel);
        checkSQL($db_link, $query_shdel);

        //Set Source Customer inactive
        $sql_inactive = "UPDATE customer SET cust_active = '0', cust_lastupd = '$timestamp', user_id = '$_SESSION[log_id]' WHERE cust_id = '$shtrans_cust'";
        $query_inactive = db_query($db_link, $sql_inactive);

        header('Location: customer.php?cust='.$_SESSION['cust_id']);
}

//Get current customer's details
$result_cust = getCustomer($db_link, $_SESSION['cust_id']);

//Get all other customers
$query_custother = getCustOther($db_link);
?>

<!DOCTYPE HTML>
<html>
        <?PHP include 'includes/bootstrap_header.php'; ?>
        <body>
                <?PHP include 'includes/bootstrap_header_nav.php'; ?>

                <div class="container-fluid mt-4">
                        <div class="row">
                                <div class="col-12">
                                        <h2 class="mb-4"><i class="fa fa-shopping-cart"></i> Buy Shares</h2>
                                        <p class="lead">Purchase shares for <strong><?PHP echo $result_cust['cust_name'].' ('.$result_cust['cust_no'].')'; ?></strong></p>

                                        <div class="row">
                                                <div class="col-md-4">
                                                        <div class="card">
                                                                <div class="card-header bg-primary text-white">
                                                                        <strong>Record Share Purchase</strong>
                                                                </div>
                                                                <div class="card-body">
                                                                        <form action="acc_share_buy.php" method="post">
                                                                                <div class="form-group">
                                                                                        <label for="share_date" class="font-weight-bold">Date</label>
                                                                                        <input type="text" class="form-control datepicker" id="share_date" name="share_date" value="<?PHP echo date("d.m.Y", $timestamp); ?>" placeholder="DD.MM.YYYY" required />
                                                                                </div>

                                                                                <div class="form-group">
                                                                                        <label for="share_receipt" class="font-weight-bold">Receipt No *</label>
                                                                                        <input type="number" class="form-control" id="share_receipt" name="share_receipt" <?PHP if(isset($_GET['rec'])) echo 'value="'.$_GET['rec'].'"'; ?> required />
                                                                                </div>

                                                                                <div class="form-group">
                                                                                        <label for="share_amount" class="font-weight-bold">Number of Shares *</label>
                                                                                        <select class="form-control" id="share_amount" name="share_amount" required>
                                                                                                <?PHP
                                                                                                for ($i = 1; $i <= 10; $i++) {
                                                                                                        echo '<option value="'.$i.'">'.$i.' @ '.number_format($_SESSION['share_value'] * $i, 2).' '.$_SESSION['set_cur'].'</option>';
                                                                                                }
                                                                                                ?>
                                                                                        </select>
                                                                                </div>

                                                                                <button type="submit" name="sharebuy" class="btn btn-success btn-lg btn-block">
                                                                                        <i class="fa fa-plus-circle"></i> Buy Shares
                                                                                </button>
                                                                                <button type="button" class="btn btn-info btn-block mt-2" data-toggle="collapse" data-target="#transfer-section">
                                                                                        <i class="fa fa-exchange"></i> Transfer Shares
                                                                                </button>
                                                                                <a href="customer.php?cust=<?PHP echo $_SESSION['cust_id']; ?>" class="btn btn-secondary btn-block mt-2">
                                                                                        <i class="fa fa-arrow-left"></i> Back to Customer
                                                                                </a>
                                                                        </form>
                                                                </div>
                                        </div>

                                        <div class="collapse mt-3" id="transfer-section">
                                                <div class="card card-body bg-light">
                                                        <h5 class="mb-3">Transfer All Shares from Another Customer</h5>
                                                        <form action="acc_share_buy.php" method="post">
                                                                <div class="form-group">
                                                                        <label for="shtrans_cust" class="font-weight-bold">Select Source Customer</label>
                                                                        <select class="form-control" id="shtrans_cust" name="shtrans_cust" required>
                                                                                <option value="">Choose a customer...</option>
                                                                                <?PHP
                                                                                while ($row_custother = db_fetch_assoc($query_custother)){
                                                                                        echo '<option value="'.$row_custother['cust_id'].'">'.$row_custother['cust_no'].' - '.$row_custother['cust_name'].'</option>';
                                                                                }
                                                                                ?>
                                                                        </select>
                                                                </div>
                                                                <p class="small text-muted">This will transfer all shares from the selected customer to <?PHP echo $result_cust['cust_name']; ?> and deactivate the source customer.</p>
                                                                <button type="submit" name="shtrans" class="btn btn-warning btn-block">
                                                                        <i class="fa fa-exchange"></i> Execute Transfer
                                                                </button>
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
