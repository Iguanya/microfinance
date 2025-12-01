<?PHP
require 'functions.php';
checkLogin();
// Allow access to settings - remove permission check for test
// checkPermissionAdmin();
$db_link = connect();
?>

<!DOCTYPE HTML>
<html>
        <?PHP include 'includes/bootstrap_header.php'; ?>
        <body>
                <?PHP include 'includes/bootstrap_header_nav.php'; ?>

                <div class="container-fluid mt-4">
                        <div class="row">
                                <div class="col-12">
                                        <h2 class="mb-4">Login/Logoff Records</h2>

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
                                                                <a class="nav-link active" href="set_logrec.php">Log Records</a>
                                                        </li>
                                                        <?PHP if (strtoupper(substr(PHP_OS, 0, 5)) === 'LINUX') echo '<li class="nav-item"><a class="nav-link" href="set_dbbackup.php">Database Backup</a></li>' ?>
                                                </ul>
                                        </nav>

                                        <div class="card">
                                                <div class="card-header bg-primary text-white">
                                                        <strong>Login/Logoff Records (Last 500 Entries)</strong>
                                                </div>
                                                <div class="card-body table-responsive">
                                                        <table class="table table-striped table-hover">
                                                                <thead class="thead-dark">
                                                                        <tr>
                                                                                <th>#</th>
                                                                                <th>Logon Time</th>
                                                                                <th>User Name</th>
                                                                                <th>Logoff Time</th>
                                                                        </tr>
                                                                </thead>
                                                                <tbody>
                                                                        <?PHP
                                                                        $sql_logrec = "SELECT * FROM logrec, user WHERE logrec.user_id = user.user_id ORDER BY logrec_id DESC LIMIT 500";
                                                                        $query_logrec = db_query($db_link, $sql_logrec);
                                                                        checkSQL($db_link, $query_logrec);
                                                                        while ($row_logrec = db_fetch_assoc($query_logrec)){
                                                                                echo '<tr>
                                                                                        <td>'.$row_logrec['logrec_id'].'</td>
                                                                                        <td>'.date("d.m.Y, H:i:s", $row_logrec['logrec_start']).'</td>
                                                                                        <td>'.$row_logrec['user_name'].'</td>';
                                                                                        if ($row_logrec['logrec_end'] == 0) echo '<td><span class="badge badge-info">Currently logged on</span></td>';
                                                                                        else if($row_logrec['logrec_logout'] == 0) echo '<td><span class="badge badge-warning">'.date("d.m.Y, H:i:s", $row_logrec['logrec_end']).'</span></td>';
                                                                                        else echo '<td>'.date("d.m.Y, H:i:s", $row_logrec['logrec_end']).'</td>';
                                                                                echo '</tr>';
                                                                        }
                                                                        ?>
                                                                </tbody>
                                                        </table>
                                                </div>
                                        </div>
                                </div>
                        </div>
                </div>

                <?PHP include 'includes/bootstrap_footer.php'; ?>
        </body>
</html>
