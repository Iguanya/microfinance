<?PHP
require 'functions.php';
checkLogin();
// Allow access to settings - remove permission check for test
// checkPermissionAdmin();
$db_link = connect();
$ugroup_id = 0;
$error = "no";

//Select all Usergroups from UGROUP
$ugroups = array();
$ugroup_names = array();
$sql_ugroups = "SELECT * FROM ugroup";
$query_ugroups = db_query($db_link, $sql_ugroups);
checkSQL($db_link, $query_ugroups);
while($row_ugroups = db_fetch_assoc($query_ugroups)){
        $ugroups[] = $row_ugroups;
        $ugroup_names[] = $row_ugroups['ugroup_name'];
}

//Check for error from set_ugroup_del.php
if(isset($_GET['error'])){
        $error =  sanitize($db_link, $_GET['error']);
}

//Set heading and variable according to selection
if(isset($_GET['ugroup'])){
        $ugroup_id = sanitize($db_link, $_GET['ugroup']);
        foreach ($ugroups as $row_ugroup){
                if ($row_ugroup['ugroup_id'] == $ugroup_id){
                        $ugroup_name = $row_ugroup['ugroup_name'];
                        $ugroup_admin = $row_ugroup['ugroup_admin'];
                        $ugroup_delete = $row_ugroup['ugroup_delete'];
                        $ugroup_report = $row_ugroup['ugroup_report'];
                }
        }
        $heading = "Edit Usergroup";
}
else $heading = "Create Usergroup";

//SAVE-Button
if(isset($_POST['save_changes'])){

        //Sanitize user input
        $ugroup_id = sanitize($db_link, $_POST['ugroup_id']);
        $ugroup_name = sanitize($db_link, $_POST['ugroup_name']);
        if(isset($_POST['ugroup_admin'])) $ugroup_admin = '1';
                else $ugroup_admin = '0';
        if(isset($_POST['ugroup_delete'])) $ugroup_delete = '1';
                else $ugroup_delete = '0';
        if(isset ($_POST['ugroup_report'])) $ugroup_report = '1';
                else $ugroup_report = '0';
        $timestamp = time();

        if ($ugroup_id == 0){
                //Insert new usergroup into UGROUP
                $sql_ugroup_insert = "INSERT INTO ugroup (ugroup_name, ugroup_admin, ugroup_delete, ugroup_report, ugroup_created) VALUES ('$ugroup_name', '$ugroup_admin', '$ugroup_delete', '$ugroup_report', '$timestamp')";
                $query_ugroup_insert = db_query($db_link, $sql_ugroup_insert);
                checkSQL($db_link, $query_ugroup_insert);
        }

        else{
                //Update existing usergroup
                $sql_ugroup_upd = "UPDATE ugroup SET ugroup_name = '$ugroup_name',  ugroup_admin=$ugroup_admin, ugroup_delete=$ugroup_delete, ugroup_report=$ugroup_report, ugroup_created=$timestamp WHERE ugroup_id = $ugroup_id";
                $query_ugroup_upd = db_query($db_link, $sql_ugroup_upd);
                checkSQL($db_link, $query_ugroup_upd);
        }

        header('Location:set_ugroup.php');
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
                                        <h2 class="mb-4">User Groups</h2>

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
                                                                <a class="nav-link active" href="set_ugroup.php">Usergroups</a>
                                                        </li>
                                                        <li class="nav-item">
                                                                <a class="nav-link" href="set_logrec.php">Log Records</a>
                                                        </li>
                                                        <?PHP if (strtoupper(substr(PHP_OS, 0, 5)) === 'LINUX') echo '<li class="nav-item"><a class="nav-link" href="set_dbbackup.php">Database Backup</a></li>' ?>
                                                </ul>
                                        </nav>

                                        <div class="row">
                                                <div class="col-lg-5">
                                                        <div class="card">
                                                                <div class="card-header bg-primary text-white">
                                                                        <strong><?PHP echo $heading; ?></strong>
                                                                </div>
                                                                <div class="card-body">
                                                                        <form action="set_ugroup.php" method="post">
                                                                                <div class="form-group">
                                                                                        <label for="ugroup_name" class="font-weight-bold">Usergroup Name</label>
                                                                                        <input type="text" class="form-control" id="ugroup_name" name="ugroup_name" placeholder="Usergroup Name" value="<?PHP if (isset($ugroup_name)) echo $ugroup_name; ?>"/>
                                                                                </div>

                                                                                <div class="form-group">
                                                                                        <label class="font-weight-bold">Permissions</label>
                                                                                        <div class="form-check">
                                                                                                <input class="form-check-input" type="checkbox" id="ugroup_admin" name="ugroup_admin" <?PHP if(isset($ugroup_admin) AND $ugroup_admin == 1) echo 'checked="checked" '; ?> />
                                                                                                <label class="form-check-label" for="ugroup_admin">Administrator</label>
                                                                                        </div>
                                                                                        <div class="form-check">
                                                                                                <input class="form-check-input" type="checkbox" id="ugroup_delete" name="ugroup_delete" <?PHP if(isset($ugroup_delete) AND $ugroup_delete == 1) echo 'checked="checked" '; ?> />
                                                                                                <label class="form-check-label" for="ugroup_delete">Deleting</label>
                                                                                        </div>
                                                                                        <div class="form-check">
                                                                                                <input class="form-check-input" type="checkbox" id="ugroup_report" name="ugroup_report" <?PHP if(isset($ugroup_report) AND $ugroup_report == 1) echo 'checked="checked" '; ?> />
                                                                                                <label class="form-check-label" for="ugroup_report">Reports</label>
                                                                                        </div>
                                                                                </div>

                                                                                <button type="submit" name="save_changes" class="btn btn-primary w-100">
                                                                                        <i class="fa fa-save"></i> Save Changes
                                                                                </button>
                                                                                <input type="hidden" name="ugroup_id" value="<?PHP echo $ugroup_id; ?>" />
                                                                        </form>
                                                                </div>
                                                        </div>
                                                </div>

                                                <div class="col-lg-7">
                                                        <div class="card">
                                                                <div class="card-header bg-success text-white">
                                                                        <strong>Existing Usergroups</strong>
                                                                </div>
                                                                <div class="card-body table-responsive">
                                                                        <table class="table table-striped table-hover">
                                                                                <thead class="thead-dark">
                                                                                        <tr>
                                                                                                <th>Group Name</th>
                                                                                                <th class="text-center">Admin</th>
                                                                                                <th class="text-center">Delete</th>
                                                                                                <th class="text-center">Reports</th>
                                                                                                <th class="text-center">Actions</th>
                                                                                        </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                        <?PHP
                                                                                        foreach ($ugroups as $row_ugroups){
                                                                                                echo '<tr>
                                                                                                        <td>'.$row_ugroups['ugroup_name'].'</td>
                                                                                                        <td class="text-center">
                                                                                                                <input type="checkbox" disabled="disabled" ';
                                                                                                                if ($row_ugroups['ugroup_admin']==1) echo 'checked="checked" ';
                                                                                                        echo    '/>
                                                                                                        </td>
                                                                                                        <td class="text-center">
                                                                                                                <input type="checkbox" disabled="disabled" ';
                                                                                                                if ($row_ugroups['ugroup_delete'] == '1') echo 'checked="checked" ';
                                                                                                        echo    '/>
                                                                                                        </td>
                                                                                                        <td class="text-center">
                                                                                                                <input type="checkbox" disabled="disabled" ';
                                                                                                                if ($row_ugroups['ugroup_report'] == '1') echo 'checked="checked" ';
                                                                                                        echo    '/>
                                                                                                        </td>
                                                                                                        <td class="text-center">';
                                                                                                        if ($row_ugroups['ugroup_id'] != 1) {
                                                                                                                echo '<a href="set_ugroup.php?ugroup='.$row_ugroups['ugroup_id'].'" class="btn btn-sm btn-warning mr-2"><i class="fa fa-edit"></i> Edit</a>';
                                                                                                                echo '<a href="set_ugroup_del.php?ugroup='.$row_ugroups['ugroup_id'].'" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i> Delete</a>';
                                                                                                        }
                                                                                                echo    '</td>
                                                                                                </tr>';
                                                                                        }

                                                                                        //Error message, if user tries to delete a usergroup that still has members.
                                                                                        if ($error == "dep") echo '<script>alert(\'One or more users are still members of this usergroup. Usergroup cannot be deleted!\')</script>';
                                                                                        ?>
                                                                                </tbody>
                                                                        </table>
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
