<?PHP
require 'functions.php';
checkLogin();
// Allow access to settings - remove permission check for test
// checkPermissionAdmin();
$db_link = connect();

//Save Changes
if (isset($_POST['upd_loans'])){
        //Update Interest Calculation Method
        $new_intcalcmethod = sanitize($db_link, $_POST['intcalcmethod']);
        $sql_upd_intcalcmethod = "UPDATE settings SET set_value = '$new_intcalcmethod' WHERE set_short = 'SET_ICL'";
        $query_upd_intcalcmethod = db_query($db_link, $sql_upd_intcalcmethod);
        checkSQL($db_link, $query_upd_intcalcmethod);

        //Update Interest Rate
        $new_loaninterest = sanitize($db_link, $_POST['loaninterest']);
        $sql_upd_loaninterest = "UPDATE fees SET fee_value = '$new_loaninterest' WHERE fee_short = 'FEE_LIR'";
        $query_upd_loaninterest = db_query($db_link, $sql_upd_loaninterest);
        checkSQL($db_link, $query_upd_loaninterest);

        //Update Minimum Loan Principal
        $new_minLP = sanitize($db_link, $_POST['minLP']);
        $sql_upd_minLP = "UPDATE settings SET set_value = '$new_minLP' WHERE set_short = 'SET_MLP'";
        $query_upd_minLP = db_query($db_link, $sql_upd_minLP);
        checkSQL($db_link, $query_upd_minLP);

        //Update Maximum Loan Principal
        $new_maxLP = sanitize($db_link, $_POST['maxLP']);
        $sql_upd_maxLP = "UPDATE settings SET set_value = '$new_maxLP' WHERE set_short = 'SET_XLP'";
        $query_upd_maxLP = db_query($db_link, $sql_upd_maxLP);
        checkSQL($db_link, $query_upd_maxLP);

        //Update Maximum Principal-Savings Ratio
        $new_maxPSR = sanitize($db_link, $_POST['maxPSR']);
        $sql_upd_maxPSR = "UPDATE settings SET set_value = '$new_maxPSR' WHERE set_short = 'SET_PSR'";
        $query_upd_maxPSR = db_query($db_link, $sql_upd_maxPSR);
        checkSQL($db_link, $query_upd_maxPSR);

        //Update Maximum Number of Guarantees any member can give
        $new_maxGuar = sanitize($db_link, $_POST['maxGuar']);
        $sql_upd_maxGuar = "UPDATE settings SET set_value = '$new_maxGuar' WHERE set_short = 'SET_GUA'";
        $query_upd_maxGuar = db_query($db_link, $sql_upd_maxGuar);
        checkSQL($db_link, $query_upd_maxGuar);

        //Update Minimum Length of Membership before Loan Application
        $new_minMemb = sanitize($db_link, $_POST['minMemb']);
        $sql_upd_minMemb = "UPDATE settings SET set_value = '$new_minMemb' WHERE set_short = 'SET_MEM'";
        $query_upd_minMemb = db_query($db_link, $sql_upd_minMemb);
        checkSQL($db_link, $query_upd_minMemb);

        //Update Auto-fine option
        $new_auf = sanitize($db_link, $_POST['autofine']);
        $sql_upd_auf = "UPDATE settings SET set_value = '$new_auf' WHERE set_short = 'SET_AUF'";
        $query_upd_auf = db_query($db_link, $sql_upd_auf);
        checkSQL($db_link, $query_upd_auf);

        //Update Additional Loans Input Field
        $new_xl1 = sanitize($db_link, $_POST['xtraField1']);
        $sql_upd_xl1 = "UPDATE settings SET set_value = '$new_xl1' WHERE set_short = 'SET_XL1'";
        $query_upd_xl1 = db_query($db_link, $sql_upd_xl1);
        checkSQL($db_link, $query_upd_xl1);

        //If auto-fine option is enabled, make sure dashboard shows loan default list
        if ($new_auf != NULL){
                $new_dash_right = "dashboard/dash_loandefaults.php";
                $sql_upd_dashr = "UPDATE settings SET set_value = '$new_dash_right' WHERE set_short = 'SET_DBR'";
                $query_upd_dashr = db_query($db_link, $sql_upd_dashr);
                checkSQL($db_link, $query_upd_dashr);
        }
}

//Get Settings and Fees
getSettings($db_link);
getFees($db_link);
?>

<!DOCTYPE HTML>
<html>
        <?PHP include 'includes/bootstrap_header.php'; ?>
        <body>
                <?PHP include 'includes/bootstrap_header_nav.php'; ?>

                <div class="container-fluid mt-4">
                        <div class="row">
                                <div class="col-12">
                                        <h2 class="mb-4">Loan Settings</h2>

                                        <nav class="mb-3">
                                                <ul class="nav nav-tabs" role="tablist">
                                                        <li class="nav-item">
                                                                <a class="nav-link" href="set_basic.php">Basic Settings</a>
                                                        </li>
                                                        <li class="nav-item">
                                                                <a class="nav-link active" href="set_loans.php">Loan Settings</a>
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
                                                        <?PHP if (strtoupper(substr(PHP_OS, 0, 5)) === 'LINUX') echo '<li class="nav-item"><a class="nav-link" href="set_dbbackup.php">Database Backup</a></li>' ?>
                                                </ul>
                                        </nav>

                                        <div class="row">
                                                <div class="col-lg-8 offset-lg-2">
                                                        <div class="card">
                                                                <div class="card-header bg-primary text-white">
                                                                        <strong>Configure Loan Settings</strong>
                                                                </div>
                                                                <div class="card-body">
                                                                        <form action="set_loans.php" method="post">
                                                                                <div class="row">
                                                                                        <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                        <label for="intcalcmethod" class="font-weight-bold">Interest Calculation Method</label>
                                                                                                        <select class="form-control" id="intcalcmethod" name="intcalcmethod">
                                                                                                                <option value="modules/mod_inter_fixed.php" <?PHP if ($_SESSION['set_intcalc']=="modules/mod_inter_fixed.php") echo 'selected="selected"'; ?> >Fixed</option>
                                                                                                                <option value="modules/mod_inter_float.php" <?PHP if ($_SESSION['set_intcalc']=="modules/mod_inter_float.php") echo 'selected="selected"'; ?>>Floating</option>
                                                                                                        </select>
                                                                                                </div>
                                                                                        </div>
                                                                                        <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                        <label for="loaninterest" class="font-weight-bold">Interest Rate (%)</label>
                                                                                                        <input type="text" class="form-control" id="loaninterest" name="loaninterest" value="<?PHP echo $_SESSION['fee_loaninterestrate'] ?>" placeholder="Percentage" />
                                                                                                </div>
                                                                                        </div>
                                                                                </div>

                                                                                <div class="row">
                                                                                        <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                        <label for="minMemb" class="font-weight-bold">Minimum Membership Length (Months)</label>
                                                                                                        <input type="number" min="0" class="form-control" id="minMemb" name="minMemb" value="<?PHP echo $_SESSION['set_minmemb'] ?>" placeholder="No Minimum" />
                                                                                                </div>
                                                                                        </div>
                                                                                        <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                        <label for="minLP" class="font-weight-bold">Minimum Loan Principal</label>
                                                                                                        <input type="number" min="0" step="0.01" class="form-control" id="minLP" name="minLP" value="<?PHP echo $_SESSION['set_minlp'] ?>" placeholder="No Minimum" />
                                                                                                </div>
                                                                                        </div>
                                                                                </div>

                                                                                <div class="row">
                                                                                        <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                        <label for="maxLP" class="font-weight-bold">Maximum Loan Principal</label>
                                                                                                        <input type="number" min="0" step="0.01" class="form-control" id="maxLP" name="maxLP" value="<?PHP echo $_SESSION['set_maxlp'] ?>" placeholder="No Maximum" />
                                                                                                </div>
                                                                                        </div>
                                                                                        <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                        <label for="maxPSR" class="font-weight-bold">Maximum Principal/Savings Ratio (%)</label>
                                                                                                        <input type="number" min="0" class="form-control" id="maxPSR" name="maxPSR" value="<?PHP echo $_SESSION['set_maxpsr'] ?>" placeholder="No Limit" />
                                                                                                </div>
                                                                                        </div>
                                                                                </div>

                                                                                <div class="row">
                                                                                        <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                        <label for="autofine" class="font-weight-bold">Auto-fine Defaulted Instalments After (Days)</label>
                                                                                                        <input type="number" min="0" class="form-control" id="autofine" name="autofine" value="<?PHP echo $_SESSION['set_auf'] ?>" placeholder="Auto-fining off"/>
                                                                                                </div>
                                                                                        </div>
                                                                                        <div class="col-md-6">
                                                                                                <div class="form-group">
                                                                                                        <label for="maxGuar" class="font-weight-bold">Maximum Number of Guarantees</label>
                                                                                                        <input type="number" min="0" class="form-control" id="maxGuar" name="maxGuar" value="<?PHP echo $_SESSION['set_maxguar'] ?>" placeholder="No Limit" />
                                                                                                </div>
                                                                                        </div>
                                                                                </div>

                                                                                <div class="form-group">
                                                                                        <label for="xtraField1" class="font-weight-bold">Additional Field</label>
                                                                                        <input type="text" class="form-control" id="xtraField1" name="xtraField1" value="<?PHP echo $_SESSION['set_xl1'] ?>" placeholder="No additional input field" />
                                                                                </div>

                                                                                <div class="text-center mt-4">
                                                                                        <button type="submit" name="upd_loans" class="btn btn-primary btn-lg">
                                                                                                <i class="fa fa-save"></i> Save Changes
                                                                                        </button>
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
