<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();
$lastyear = date("Y", time())-1;
$suc_int = 0;
$suc_div = 0;

/**
 * DISTRIBUTE ANNUAL SAVINGS INTEREST
 */
if(isset($_POST['int_distribute'])){

        //Sanitize user input
        $int_rate = sanitize($db_link, $_POST['int_rate']);
        $int_year = sanitize($db_link, $_POST['int_year']);
        $timestamp = time();

        //Calculate UNIX TIMESTAMP for first and last day of selected year
        $int_year_beg = mktime(0, 0, 0, 1, 1, $int_year);
        $int_year_end = mktime(0, 0, 0, 1, 0, ($int_year+1));

        //Get all active customers in array
        $query_cust = getCustAct($db_link);
        $cust = array();
        while($row_cust = db_fetch_assoc($query_cust)){
                $cust[] = $row_cust;
        }

        //Get all savings in array
        $sql_sav = "SELECT * FROM savings WHERE cust_id IN (SELECT cust_id FROM customer WHERE cust_active = 1) AND sav_date < $int_year_end";
        $query_sav = db_query($db_link, $sql_sav);
        checkSQL($db_link, $query_sav);
        $savings = array();
        while ($row_sav = db_fetch_assoc($query_sav)){
                $savings[] = $row_sav;
        }

        // Compute Savings Factor
        $int_base = 0;
        $int_fact = 0;
        $int_total = 0;

        foreach ($cust as $c){

                foreach ($savings as $s){
                        if ($s['cust_id'] == $c['cust_id']){

                                if ($s['sav_date'] < $int_year_beg)
                                        $int_base = $int_base + $s['sav_amount'];

                                else {
                                        if ($s['sav_amount'] > 0)
                                                $int_base = $int_base + $s['sav_amount'] * round((($int_year_end - $s['sav_date'])/86400)/365,2);
                                        elseif ($s['sav_amount'] <= 0)
                                                $int_base = $int_base + $s['sav_amount'] * (1 - round((($s['sav_date'] - $int_year_beg)/86400)/365,2));
                                }
                        }
                }

                // Calculate annual interest for current customer
                $int_cust = round($int_base /100 * $int_rate,0);

                // Insert interest in SAVINGS
                if($int_cust > 0){
                        $sql_cust_int = "INSERT INTO savings (cust_id, sav_date, sav_amount, savtype_id, sav_created, user_id) VALUES ($c[cust_id], $int_year_end, $int_cust, 3, $timestamp, $_SESSION[log_id])";
                        $query_cust_int = db_query($db_link, $sql_cust_int);
                        checkSQL($db_link, $query_cust_int);

                        // Update savings account balance
                        updateSavingsBalance($db_link, $c['cust_id']);
                }
                $int_total = $int_total + $int_cust;
                $int_base = 0;
        }
        // Insert grand total distributed interest into expenses
        $sql_int_exp = "INSERT INTO expenses (exptype_id, exp_amount, exp_date, exp_text, exp_created, user_id) VALUES (19, $int_total, $int_year_end, 'Distributed Interest for $int_year', $timestamp, $_SESSION[log_id])";
        $query_int_exp = db_query($db_link, $sql_int_exp);
        checkSQL($db_link, $query_int_exp );

        $suc_int = 1;
}

/**
 * DISTRIBUTE ANNUAL DIVIDEND
 */
if(isset($_POST['div_distribute'])){

        //Sanitize user input
        $div_value = sanitize($db_link, $_POST['div_value']);
        $div_type = sanitize($db_link, $_POST['div_type']);
        $div_year = sanitize($db_link, $_POST['div_year']);
        $timestamp = time();

        //Calculate UNIX TIMESTAMP for first and last day of selected year
        $div_year_beg = mktime(0, 0, 0, 1, 1, $div_year);
        $div_year_end = mktime(0, 0, 0, 1, 0, ($div_year+1));

        //Get all active customers in array
        $query_cust = getCustAct($db_link);
        $cust = array();
        while($row_cust = db_fetch_assoc($query_cust)){
                $cust[] = $row_cust;
        }

        //Get all shares in array
        $sql_sh = "SELECT * FROM shares WHERE cust_id IN (SELECT cust_id FROM customer WHERE cust_active = 1) AND share_date < $div_year_end";
        $query_sh = db_query($db_link, $sql_sh);
        checkSQL($db_link, $query_sh);
        $shares = array();
        $share_count = 0;
        while ($row_sh = db_fetch_assoc($query_sh)){
                $shares[] = $row_sh;
                $share_count = $share_count + $row_sh['share_amount'];
        }

        //If entered dividend value is grand total, divide it amount by the number of eligble shares
        if($div_type == 2) $div_value = ceil($div_value / $share_count);

        // Compute Share Factor
        $div_fact = 0;
        $div_total = 0;
        foreach ($cust as $c){

                foreach ($shares as $s){

                        if ($s['cust_id'] == $c['cust_id']){
                                if ($s['share_date'] < $div_year_beg)
                                        $div_fact = $div_fact + $s['share_amount'];
                                else {
                                        if ($s['share_amount'] > 0)
                                                $div_fact = $div_fact + $s['share_amount'] * round(ceil(($div_year_end - $s['share_date'])/86400)/365,2);
                                        elseif ($s['share_amount'] <= 0)
                                                $div_fact = $div_fact + $s['share_amount'] * (1 - round(ceil(($s['share_date'] - $div_year_beg)/86400)/365,2));
                                }
                        }
                }

                // Calculate dividend for current customer
                $div_cust = round($div_fact * $div_value,0);

                // Insert dividend in SAVINGS
                if($div_cust > 0){
                        $sql_cust_div = "INSERT INTO savings (cust_id, sav_date, sav_amount, savtype_id, sav_created, user_id) VALUES ($c[cust_id], $div_year_end, $div_cust, 9, $timestamp, $_SESSION[log_id])";
                        $query_cust_div = db_query($db_link, $sql_cust_div);
                        checkSQL($db_link, $query_cust_div);

                        // Update savings account balance
                        updateSavingsBalance($db_link, $c['cust_id']);
                }

                $div_total = $div_total + $div_cust;
                $div_fact=0;
        }

        // Insert grand total distributed dividend into expenses
        $sql_div_exp = "INSERT INTO expenses (exptype_id, exp_amount, exp_date, exp_text, exp_created, user_id) VALUES (18, $div_total, $div_year_end, 'Distributed Dividend for $div_year', $timestamp, $_SESSION[log_id])";
        $query_div_exp = db_query($db_link, $sql_div_exp);
        checkSQL($db_link, $query_div_exp );

        $suc_div = 1;
}
?>

<!DOCTYPE HTML>
<html>
<?PHP include 'includes/bootstrap_header.php'; ?>

<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">Annual Accounts</h2>

                <nav class="mb-3">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link" href="start.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="books_expense.php">Expenses</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="books_income.php">Incomes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="books_annual.php">Annual Accounts</a>
                        </li>
                    </ul>
                </nav>

                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong>Important!</strong> Both operations may take some time to complete. Do not click anything until you see a confirmation message!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <strong>Annual Share Dividend</strong>
                            </div>
                            <div class="card-body">
                                <form action="books_annual.php" method="post">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Year</label>
                                        <input type="number" name="div_year" class="form-control" min="2000" max="<?PHP echo $lastyear; ?>" placeholder="Enter Year" value="<?PHP echo $lastyear; ?>" required />
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="form-label">Dividend Type</label>
                                        <select name="div_type" class="form-select">
                                            <option value="1">Dividend per share</option>
                                            <option value="2">Grand Total Dividend</option>
                                        </select>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="form-label">Dividend Value</label>
                                        <input type="number" name="div_value" class="form-control" min=".1" step="any" placeholder="<?PHP echo $_SESSION['set_cur']; ?>" required />
                                    </div>

                                    <div class="d-grid">
                                        <button type="submit" name="div_distribute" class="btn btn-success btn-lg">Distribute Dividend</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <strong>Annual Savings Interest</strong>
                            </div>
                            <div class="card-body">
                                <form action="books_annual.php" method="post">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Year</label>
                                        <input type="number" name="int_year" class="form-control" min="2000" max="<?PHP echo $lastyear; ?>" placeholder="Enter Year" value="<?PHP echo $lastyear; ?>" required />
                                    </div>

                                    <div class="form-group mb-3">
                                        <label class="form-label">Interest Rate (%)</label>
                                        <input type="number" name="int_rate" class="form-control" min=".1" step=".1" placeholder="Interest Rate (%)" required />
                                    </div>

                                    <div class="d-grid">
                                        <button type="submit" name="int_distribute" class="btn btn-success btn-lg">Distribute Interest</button>
                                    </div>
                                </form>
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

<?PHP
if($suc_int == 1) showMessage('Interest has been distributed.\n\nYou may now leave this page.');
if($suc_div == 1) showMessage('Dividend has been distributed.\n\nYou may now leave this page.');
?>
