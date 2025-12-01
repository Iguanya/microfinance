<?PHP
require 'functions.php';
checkLogin();
// Allow access to settings - remove permission check for test
// checkPermissionAdmin();
$db_link = connect();

//Prepare initial information text about server OS
if (strtoupper(substr(PHP_OS, 0, 5)) === 'LINUX') {     $infoText = 'This seems to be a Linux system.<br/>Database backup will most probably succeed.'; }
else { $infoText = 'This does not seem to be a Linux system!</br/>Backup process will most likely fail!'; }

//BACKUP DATABASE Button
if (isset($_POST['db_backup'])){
        require_once 'config/config.php';
        $dbhost = DB_HOST;
        $dbuser = DB_USER;
        $dbpassword = DB_PASS;
        $dbname = DB_NAME;
        $dumpfile = 'backup/' . $dbname . '_' . date("Y-m-d_H-i-s") . '.sql.gz';
        passthru("mysqldump --user=$dbuser --password=$dbpassword --host=$dbhost $dbname | gzip -c  > $dumpfile");
        $infoText = "Backup created successfully: ".$dumpfile;
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
                                        <h2 class="mb-4">Database Backup</h2>

                                        <nav class="mb-3">
                                                <ul class="nav nav-tabs" role="tablist">
                                                        <li class="nav-item">
                                                                <a class="nav-link" href="set_basic.php">Basic Settings</a>
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
                                                        <?PHP if (strtoupper(substr(PHP_OS, 0, 5)) === 'LINUX') echo '<li class="nav-item"><a class="nav-link active" href="set_dbbackup.php">Database Backup</a></li>' ?>
                                                </ul>
                                        </nav>

                                        <div class="row">
                                                <div class="col-md-6 offset-md-3">
                                                        <div class="card">
                                                                <div class="card-header bg-primary text-white">
                                                                        <strong>Manual Database Backup</strong>
                                                                </div>
                                                                <div class="card-body">
                                                                        <div class="alert alert-info" role="alert">
                                                                                <strong><i class="fa fa-info-circle"></i> System Information:</strong><br/>
                                                                                <?php echo $infoText; ?>
                                                                        </div>

                                                                        <form action="set_dbbackup.php" method="post">
                                                                                <button type="submit" name="db_backup" class="btn btn-primary btn-lg w-100">
                                                                                        <i class="fa fa-download"></i> Backup Database
                                                                                </button>
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
