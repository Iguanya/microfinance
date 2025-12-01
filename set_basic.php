<?PHP
require 'functions.php';
checkLogin();
// Allow access to settings - remove permission check for test
// checkPermissionAdmin();
$db_link = connect();

if (isset($_POST['upd_genset'])){

        //Generate timestamp
        $timestamp = time();

        //Update Currency Abbreviation
        $new_cur_short = sanitize($db_link, $_POST['cur_short']);
        $sql_upd_cur_short = "UPDATE settings SET set_value = '$new_cur_short' WHERE set_short = 'SET_CUR'";
        $query_upd_cur_short = db_query($db_link, $sql_upd_cur_short);
        checkSQL($db_link, $query_upd_cur_short);

        //Update Minimum Savings Balance
        $new_minsavbal = sanitize($db_link, $_POST['minsavbal']);
        $sql_upd_minsavbal = "UPDATE settings SET set_value = '$new_minsavbal' WHERE set_short = 'SET_MSB'";
        $query_upd_minsavbal = db_query($db_link, $sql_upd_minsavbal);
        checkSQL($db_link, $query_upd_minsavbal);

        //Update Account Deactivation option
        $new_deact = sanitize($db_link, $_POST['deactivate']);
        $sql_upd_deact = "UPDATE settings SET set_value = '$new_deact' WHERE set_short = 'SET_DEA'";
        $query_upd_deact = db_query($db_link, $sql_upd_deact);
        checkSQL($db_link, $query_upd_deact);

        //Update Dashboard Settings Left
        $new_dash_left = sanitize($db_link, $_POST['dash_left']);
        if ($new_deact != NULL )$new_dash_left = "dashboard/dash_subscr.php";
        $sql_upd_dashl = "UPDATE settings SET set_value = '$new_dash_left' WHERE set_short = 'SET_DBL'";
        $query_upd_dashl = db_query($db_link, $sql_upd_dashl);
        checkSQL($db_link, $query_upd_dashl);

        //Update Dashboard Settings Right
        $new_dash_right = sanitize($db_link, $_POST['dash_right']);
        if ($_SESSION['set_auf'] != NULL) $new_dash_right = "dashboard/dash_loandefaults.php";
        $sql_upd_dashr = "UPDATE settings SET set_value = '$new_dash_right' WHERE set_short = 'SET_DBR'";
        $query_upd_dashr = db_query($db_link, $sql_upd_dashr);
        checkSQL($db_link, $query_upd_dashr);

        //Update Customer Number Format
        $new_cno = sanitize($db_link, $_POST['cnformat']);
        $sql_upd_cno = "UPDATE settings SET set_value = '$new_cno' WHERE set_short = 'SET_CNO'";
        $query_upd_cno = db_query($db_link, $sql_upd_cno);
        checkSQL($db_link, $query_upd_cno);

        //Update Employee Number Format
        $new_eno = sanitize($db_link, $_POST['enformat']);
        $sql_upd_eno = "UPDATE settings SET set_value = '$new_eno' WHERE set_short = 'SET_ENO'";
        $query_upd_eno = db_query($db_link, $sql_upd_eno);
        checkSQL($db_link, $query_upd_eno);

        //Update Fixed-term Savings Deposits
        if (isset($_POST['savFixed'])) $new_savFixed = sanitize($db_link, $_POST['savFixed']);
        else $new_savFixed = 0;
        $sql_upd_savFixed = "UPDATE settings SET set_value = '$new_savFixed' WHERE set_short = 'SET_SFX'";
        $query_upd_savFixed = db_query($db_link, $sql_upd_savFixed);
        checkSQL($db_link, $query_upd_savFixed);

        //Update Customer Search by ID
        if (isset($_POST['csearchID'])) $new_csearchID = sanitize($db_link, $_POST['csearchID']);
        else $new_csearchID = 0;
        $sql_upd_csearchID = "UPDATE settings SET set_value = '$new_csearchID' WHERE set_short = 'SET_CSI'";
        $query_upd_csearchID = db_query($db_link, $sql_upd_csearchID);
        checkSQL($db_link, $query_upd_csearchID);
}

//Get Settings and fill session variables
getSettings($db_link);
?>

<!DOCTYPE HTML>
<html>
        <?PHP include 'includes/bootstrap_header.php'; ?>

        <body>
                <?PHP include 'includes/bootstrap_header_nav.php'; ?>

                <div class="container-fluid mt-4">
                        <div class="row">
                                <div class="col-12">
                                        <h2 class="mb-4">Settings</h2>
                                        
                                        <nav class="mb-3">
                                                <ul class="nav nav-tabs" role="tablist">
                                                        <li class="nav-item">
                                                                <a class="nav-link active" href="set_basic.php">Basic Settings</a>
                                                        </li>
                                                        <li class="nav-item">
                                                                <a class="nav-link" href="set_loans.php">Loan Settings</a>
                                                        </li>
                                                        <li class="nav-item">
                                                                <a class="nav-link" href="set_fees.php">Fees & Charges</a>
                                                        </li>
                                                        <li class="nav-item">
                                                                <a class="nav-link" href="set_user.php">Users</a>
                                                        </li>
                                                        <li class="nav-item">
                                                                <a class="nav-link" href="set_ugroup.php">Usergroups</a>
                                                        </li>
                                                        <li class="nav-item">
                                                                <a class="nav-link" href="set_logrec.php">Log Records</a>
                                                        </li>
                                                </ul>
                                        </nav>
                                        
                                        <div class="card">
                                                <div class="card-header bg-primary text-white">
                                                        <strong>Basic Settings</strong>
                                                </div>
                                                <div class="card-body">
                                                        <form action="set_basic.php" method="post">
                                                                <div class="form-group">
                                                                        <label>Dashboard Left</label>
                                                                        <select name="dash_left" class="form-control">
                                                                                <option value="dashboard/dash_none.php" <?PHP if ($_SESSION['set_dashl'] == "dashboard/dash_none.php") echo "selected" ?>>None</option>
                                                                                <option value="dashboard/dash_subscr.php" <?PHP if ($_SESSION['set_dashl'] == "dashboard/dash_subscr.php") echo "selected" ?>>Overdue Subscriptions</option>
                                                                                <option value="dashboard/dash_loandefaults.php" <?PHP if ($_SESSION['set_dashl'] == "dashboard/dash_loandefaults.php") echo "selected" ?>>Defaulted Loan Instalments</option>
                                                                                <option value="dashboard/dash_stat_cust.php" <?PHP if ($_SESSION['set_dashl'] == "dashboard/dash_stat_cust.php") echo "selected" ?>>Statistics: Customers</option>
                                                                                <option value="dashboard/dash_stat_finance.php" <?PHP if ($_SESSION['set_dashl'] == "dashboard/dash_stat_finance.php") echo "selected" ?>>Statistics: Finances</option>
                                                                        </select>
                                                                </div>

                                                                <div class="form-group">
                                                                        <label>Dashboard Right</label>
                                                                        <select name="dash_right" class="form-control">
                                                                                <option value="dashboard/dash_none.php" <?PHP if ($_SESSION['set_dashr'] == "dashboard/dash_none.php") echo "selected" ?>>None</option>
                                                                                <option value="dashboard/dash_subscr.php" <?PHP if ($_SESSION['set_dashr'] == "dashboard/dash_subscr.php") echo "selected" ?>>Overdue Subscriptions</option>
                                                                                <option value="dashboard/dash_loandefaults.php" <?PHP if ($_SESSION['set_dashr'] == "dashboard/dash_loandefaults.php") echo "selected" ?>>Defaulted Loan Instalments</option>
                                                                                <option value="dashboard/dash_stat_cust.php" <?PHP if ($_SESSION['set_dashr'] == "dashboard/dash_stat_cust.php") echo "selected" ?>>Statistics: Customers</option>
                                                                                <option value="dashboard/dash_stat_finance.php" <?PHP if ($_SESSION['set_dashr'] == "dashboard/dash_stat_finance.php") echo "selected" ?>>Statistics: Finances</option>
                                                                        </select>
                                                                </div>

                                                                <div class="form-group">
                                                                        <label>Currency Abbreviation</label>
                                                                        <input type="text" name="cur_short" class="form-control" value="<?PHP echo $_SESSION['set_cur'] ?>" />
                                                                </div>

                                                                <div class="form-group">
                                                                        <label>Customer Search by ID</label>
                                                                        <div class="custom-control custom-radio">
                                                                                <input type="radio" id="csearchID1" name="csearchID" value="1" class="custom-control-input" <?PHP if ($_SESSION['set_csi'] == 1) echo 'checked'; ?> />
                                                                                <label class="custom-control-label" for="csearchID1">On</label>
                                                                        </div>
                                                                        <div class="custom-control custom-radio">
                                                                                <input type="radio" id="csearchID2" name="csearchID" value="0" class="custom-control-input" <?PHP if ($_SESSION['set_csi'] != 1) echo 'checked'; ?> />
                                                                                <label class="custom-control-label" for="csearchID2">Off</label>
                                                                        </div>
                                                                </div>

                                                                <div class="form-group">
                                                                        <label>Customer Number Format</label>
                                                                        <input type="text" name="cnformat" class="form-control" value="<?PHP echo $_SESSION['set_cno']; ?>" placeholder="Customer No. Format"/>
                                                                </div>

                                                                <div class="form-group">
                                                                        <label>Employee Number Format</label>
                                                                        <input type="text" name="enformat" class="form-control" value="<?PHP echo $_SESSION['set_eno']; ?>" placeholder="Employee No. Format"/>
                                                                </div>

                                                                <div class="form-group">
                                                                        <label>Minimum Savings Balance</label>
                                                                        <input type="number" min="0" name="minsavbal" class="form-control" value="<?PHP echo $_SESSION['set_msb']; ?>" placeholder="<?PHP echo $_SESSION['set_cur']; ?>" />
                                                                </div>

                                                                <div class="form-group">
                                                                        <label>Fixed-term Saving Deposits</label>
                                                                        <div class="custom-control custom-radio">
                                                                                <input type="radio" id="savFixed1" name="savFixed" value="1" class="custom-control-input" <?PHP if ($_SESSION['set_sfx'] == 1) echo 'checked'; ?> />
                                                                                <label class="custom-control-label" for="savFixed1">On</label>
                                                                        </div>
                                                                        <div class="custom-control custom-radio">
                                                                                <input type="radio" id="savFixed2" name="savFixed" value="0" class="custom-control-input" <?PHP if ($_SESSION['set_sfx'] != 1) echo 'checked'; ?> />
                                                                                <label class="custom-control-label" for="savFixed2">Off</label>
                                                                        </div>
                                                                </div>

                                                                <div class="form-group">
                                                                        <label>Auto-deactivate unrenewed accounts after (Months)</label>
                                                                        <input type="number" name="deactivate" min="0" class="form-control" value="<?PHP echo $_SESSION['set_deact']; ?>" placeholder="Auto-deactivation off" />
                                                                </div>

                                                                <button type="submit" name="upd_genset" class="btn btn-success btn-lg">Save Changes</button>
                                        </form>
                                </div>
                        </div>
                        </div>
                </div>

                <?PHP include 'includes/bootstrap_footer.php'; ?>
        </body>
</html>
