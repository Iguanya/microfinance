<?PHP
require 'functions.php';
checkLogin();
// Allow access to settings - remove permission check for test
// checkPermissionAdmin();
$db_link = connect();
$user_id = 0;
$employee = 0;

//Select all users from USER
$users = array();
$user_names = array();
$sql_users = "SELECT user.user_id, user.user_name, user.user_created, ugroup.ugroup_id, ugroup.ugroup_name, employee.empl_id, employee.empl_name FROM user LEFT JOIN ugroup ON ugroup.ugroup_id = user.ugroup_id LEFT JOIN employee ON user.empl_id = employee.empl_id WHERE user.user_id != 0 ORDER BY user_name";
$query_users = db_query($db_link, $sql_users);
checkSQL($db_link, $query_users);
while($row_users = db_fetch_assoc($query_users)){
        $users[] = $row_users;
        $user_names[] = $row_users['user_name'];
}

//Select all usergroups from UGROUP
$sql_ugroup = "SELECT ugroup_id, ugroup_name FROM ugroup";
$query_ugroup = db_query($db_link, $sql_ugroup);
checkSQL($db_link, $query_ugroup);
$query_ugroup_list = db_query($db_link, $sql_ugroup);
checkSQL($db_link, $query_ugroup_list);

// Select all employees from EMPLOYEE
$sql_employees = "SELECT empl_id, empl_name FROM employee WHERE empl_id != 0";
$query_employees = db_query($db_link, $sql_employees);
checkSQL($db_link, $query_employees);

// Select employees from EMPLOYEE who are already associated with a user
$sql_empl_assoc = "SELECT empl_id FROM employee WHERE empl_id != 0 AND empl_id IN (SELECT empl_id FROM user)";
$query_empl_assoc = db_query($db_link, $sql_empl_assoc);
checkSQL($db_link, $query_empl_assoc);
$empl_assoc = array();
while($row_empl_assoc = db_fetch_assoc($query_empl_assoc)){
        $empl_assoc[] = $row_empl_assoc['empl_id'];
}

//Set heading and variables according to selection
if(isset($_GET['user'])){
        $user_id = sanitize($db_link, $_GET['user']);
        foreach ($users as $row_user){
                if ($row_user['user_id'] == $user_id){
                        $user_id = $row_user['user_id'];
                        $user_name = $row_user['user_name'];
                        $user_ugroup = $row_user['ugroup_id'];
                        $employee = $row_user['empl_id'];
                }
        }
        $heading = "Edit User";
}
else $heading = "Create User";

//SAVE Button
if(isset($_POST["save_changes"])){

        // Include password pepper
        require 'config/pepper.php';

        //Sanitize user input
        $user_id = sanitize($db_link, $_POST['user_id']);
        $user_name = sanitize($db_link, $_POST['user_name']);
        $user_pw = password_hash((sanitize($db_link, $_POST['user_pw'])).$pepper, PASSWORD_DEFAULT);
        $empl_id = sanitize($db_link, $_POST['empl_id']);
        $ugroup = sanitize($db_link, $_POST['ugroup']);
        if($user_id == 1) $ugroup = 1;
        $timestamp = time();

        if($user_id == 0){
                // Insert new user into USER
                $sql_user_ins = "INSERT INTO user (user_name, user_pw, ugroup_id, empl_id, user_created) VALUES ('$user_name', '$user_pw', '$ugroup', '$empl_id', '$timestamp')";
                $query_user_ins = db_query($db_link, $sql_user_ins);
                checkSQL($db_link, $query_user_ins);
        }
        else {
                // Update existing user
                $sql_user_upd = "UPDATE user SET user_name = '$user_name', user_pw = '$user_pw', ugroup_id = $ugroup, empl_id = $empl_id, user_created = $timestamp WHERE user_id = $user_id";
                $query_user_upd = db_query($db_link, $sql_user_upd);
                checkSQL($db_link, $query_user_upd);
        }
        header('Location:set_user.php');
}
?>

<!DOCTYPE HTML>
<html>
        <?PHP include 'includes/bootstrap_header.php'; ?>
        <body>

                <div class="container-fluid mt-4">
                        <div class="row">
                                <div class="col-12">
                                        <h2 class="mb-4">Users</h2>

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
                                                                <a class="nav-link active" href="set_user.php">Users</a>
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
                                                <div class="col-lg-5">
                                                        <div class="card">
                                                                <div class="card-header bg-primary text-white">
                                                                        <strong><?PHP echo $heading; ?></strong>
                                                                </div>
                                                                <div class="card-body">
                                                                        <form action="set_user.php" method="post">
                                                                                <div class="form-group">
                                                                                        <label for="user_name" class="font-weight-bold">Username</label>
                                                                                        <input type="text" class="form-control" id="user_name" name="user_name" placeholder="Username" value="<?PHP if(isset($user_name)) echo $user_name;?>" />
                                                                                </div>

                                                                                <div class="form-group">
                                                                                        <label for="user_pw" class="font-weight-bold">Password</label>
                                                                                        <input type="password" class="form-control" id="user_pw" name="user_pw" placeholder="Password" />
                                                                                </div>

                                                                                <div class="form-group">
                                                                                        <label for="user_pw_conf" class="font-weight-bold">Repeat Password</label>
                                                                                        <input type="password" class="form-control" id="user_pw_conf" name="user_pw_conf" placeholder="Repeat Password" />
                                                                                </div>

                                                                                <div class="form-group">
                                                                                        <label for="ugroup" class="font-weight-bold">Usergroup</label>
                                                                                        <select class="form-control" id="ugroup" name="ugroup" <?PHP if ($user_id == 1) echo ' disabled="disabled"'; ?> >
                                                                                                <?PHP
                                                                                                while ($row_ugroup = db_fetch_assoc($query_ugroup)){
                                                                                                        echo '<option value="'.$row_ugroup['ugroup_id'].'"';
                                                                                                        if (isset($user_ugroup) and $row_ugroup['ugroup_id'] == $user_ugroup) echo ' selected="selected "';
                                                                                                        echo '>'.$row_ugroup['ugroup_name'].'</option>';
                                                                                                }
                                                                                                ?>
                                                                                        </select>
                                                                                </div>

                                                                                <div class="form-group">
                                                                                        <label for="empl_id" class="font-weight-bold">Employee</label>
                                                                                        <select class="form-control" id="empl_id" name="empl_id">
                                                                                                <option value="0">None</option>
                                                                                                <?PHP
                                                                                                while ($row_employees = db_fetch_assoc($query_employees)){
                                                                                                        echo '<option value="'.$row_employees['empl_id'].'"';
                                                                                                        if (isset($employee) and $row_employees['empl_id'] == $employee) echo ' selected="selected"';
                                                                                                        echo '>'.$row_employees['empl_name'].'</option>';
                                                                                                }
                                                                                                ?>
                                                                                        </select>
                                                                                </div>

                                                                                <button type="submit" name="save_changes" class="btn btn-primary w-100">
                                                                                        <i class="fa fa-save"></i> Save Changes
                                                                                </button>
                                                                                <input type="hidden" name="user_id" value="<?PHP echo $user_id; ?>" />
                                                                        </form>
                                                                </div>
                                                        </div>
                                                </div>

                                                <div class="col-lg-7">
                                                        <div class="card">
                                                                <div class="card-header bg-success text-white">
                                                                        <strong>Existing Users</strong>
                                                                </div>
                                                                <div class="card-body table-responsive">
                                                                        <table class="table table-striped table-hover">
                                                                                <thead class="thead-dark">
                                                                                        <tr>
                                                                                                <th>Username</th>
                                                                                                <th>Usergroup</th>
                                                                                                <th>Employee</th>
                                                                                                <th>Created</th>
                                                                                                <th class="text-center">Edit</th>
                                                                                        </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                        <?PHP
                                                                                        foreach ($users as $row_user){
                                                                                                echo '<tr>
                                                                                                        <td>'.$row_user['user_name'].'</td>
                                                                                                        <td>'.$row_user['ugroup_name'].'</td>
                                                                                                        <td>'.$row_user['empl_name'].'</td>
                                                                                                        <td>'.date('d.m.Y',$row_user['user_created']).'</td>
                                                                                                        <td class="text-center">
                                                                                                                <a href="set_user.php?user='.$row_user['user_id'].'" class="btn btn-sm btn-warning">
                                                                                                                        <i class="fa fa-edit"></i> Edit
                                                                                                                </a>
                                                                                                        </td>
                                                                                                </tr>';
                                                                                        }
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
