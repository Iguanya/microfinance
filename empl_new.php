<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();

//Generate timestamp
$timestamp = time();

//CREATE-Button
if (isset($_POST['create'])){

        //Sanitize user input
        $empl_no = sanitize($db_link, $_POST['empl_no']);
        $empl_name = sanitize($db_link, $_POST['empl_name']);
        $empl_dob = strtotime(sanitize($db_link, $_POST['empl_dob']));
        $emplsex_id = sanitize($db_link, $_POST['emplsex_id']);
        $emplmarried_id = sanitize($db_link, $_POST['emplmarried_id']);
        $empl_position = sanitize($db_link, $_POST['empl_position']);
        $empl_salary = sanitize($db_link, $_POST['empl_salary']);
        $empl_address = sanitize($db_link, $_POST['empl_address']);
        $empl_phone = sanitize($db_link, $_POST['empl_phone']);
        $empl_email = sanitize($db_link, $_POST['empl_email']);
        $empl_in = strtotime(sanitize($db_link, $_POST['empl_in']));

        //Insert new employee into EMPLOYEE
        $sql_insert = "INSERT INTO employee (empl_no, empl_name, empl_dob, emplsex_id, emplmarried_id, empl_position, empl_salary, empl_address, empl_phone, empl_email, empl_in, empl_lastupd, user_id) VALUES ('$empl_no', '$empl_name', '$empl_dob', '$emplsex_id', '$emplmarried_id', '$empl_position', '$empl_salary', '$empl_address', '$empl_phone', '$empl_email', $empl_in, $empl_in, '$_SESSION[log_id]')";
        $query_insert = db_query($db_link, $sql_insert);
        checkSQL($db_link, $query_insert);

        //Get new Employees's ID from EMPLOYEE
        $sql_maxid = "SELECT MAX(empl_id) FROM employee";
        $query_maxid = db_query($db_link, $sql_maxid);
        checkSQL($db_link, $query_maxid);
        $maxid = db_fetch_assoc($query_maxid);
        $_SESSION['empl_id'] = $maxid['MAX(empl_id)'];

        // Refer to empl_new_pic.php
        header('Location: empl_new_pic.php');
}

// Select sexes from EMPLSEX for dropdown-menu
$sql_sex = "SELECT * FROM emplsex";
$query_sex = db_query($db_link, $sql_sex);
checkSQL($db_link, $query_sex);

//Select Marital Status for Drop-down-Menu
$sql_mstat = "SELECT * FROM emplmarried";
$query_mstat = db_query($db_link, $sql_mstat);
checkSQL($db_link, $query_mstat);

//Build new EMPL_NO
$newEmplNo = buildEmplNo($db_link);
?>

<!DOCTYPE HTML>
<html>
        <?PHP include 'includes/bootstrap_header.php'; ?>

        <body>
                <?PHP include 'includes/bootstrap_header_nav.php'; ?>

                <div class="container-fluid mt-4">
                        <div class="row">
                                <div class="col-md-8 offset-md-2">
                                        <h2 class="mb-4">Add New Employee</h2>
                                        
                                        <nav class="mb-3">
                                                <ul class="nav nav-tabs" role="tablist">
                                                        <li class="nav-item">
                                                                <a class="nav-link active" href="empl_new.php">Add New Employee</a>
                                                        </li>
                                                        <li class="nav-item">
                                                                <a class="nav-link" href="empl_curr.php">Current Employees</a>
                                                        </li>
                                                        <li class="nav-item">
                                                                <a class="nav-link" href="empl_past.php">Former Employees</a>
                                                        </li>
                                                </ul>
                                        </nav>

                                        <div class="card">
                                                <div class="card-header bg-primary text-white">
                                                        <strong>Employee Information</strong>
                                                </div>
                                                <div class="card-body">
                                                        <form action="empl_new.php" method="post" enctype="multipart/form-data">
                                                                <div class="row">
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label>Employee Number</label>
                                                                                        <input type="text" name="empl_no" class="form-control" value="<?PHP echo $newEmplNo; ?>" readonly />
                                                                                </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label>Monthly Salary</label>
                                                                                        <input type="number" name="empl_salary" class="form-control" placeholder="<?PHP echo $_SESSION['set_cur']; ?>" />
                                                                                </div>
                                                                        </div>
                                                                </div>

                                                                <div class="row">
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label>Full Name *</label>
                                                                                        <input type="text" name="empl_name" class="form-control" placeholder="Full Name" required />
                                                                                </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label>Address</label>
                                                                                        <input type="text" name="empl_address" class="form-control" placeholder="Place of Residence" />
                                                                                </div>
                                                                        </div>
                                                                </div>

                                                                <div class="row">
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label>Gender *</label>
                                                                                        <select name="emplsex_id" class="form-control" required>
                                                                                                <?PHP
                                                                                                while ($row_sex = db_fetch_assoc($query_sex)){
                                                                                                        echo '<option value="'.$row_sex['emplsex_id'].'">'.$row_sex['emplsex_name'].'</option>';
                                                                                                }
                                                                                                ?>
                                                                                        </select>
                                                                                </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label>Phone No</label>
                                                                                        <input type="text" name="empl_phone" class="form-control" />
                                                                                </div>
                                                                        </div>
                                                                </div>

                                                                <div class="row">
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label>Date of Birth *</label>
                                                                                        <input type="text" id="datepicker" name="empl_dob" class="form-control" placeholder="DD.MM.YYYY" required />
                                                                                </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label>Email Address</label>
                                                                                        <input type="email" name="empl_email" class="form-control" placeholder="abc@xyz.com" />
                                                                                </div>
                                                                        </div>
                                                                </div>

                                                                <div class="row">
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label>Marital Status</label>
                                                                                        <select name="emplmarried_id" class="form-control">
                                                                                                <?PHP
                                                                                                while ($row_mstat = db_fetch_assoc($query_mstat)){
                                                                                                        echo '<option value="'.$row_mstat['emplmarried_id'].'">'.$row_mstat['emplmarried_status'].'</option>';
                                                                                                }
                                                                                                ?>
                                                                                        </select>
                                                                                </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                        <label>Employment Start Date</label>
                                                                                        <input type="text" id="datepicker2" name="empl_in" class="form-control" value="<?PHP echo date("d.m.Y", $timestamp) ?>" />
                                                                                </div>
                                                                        </div>
                                                                </div>

                                                                <div class="form-group">
                                                                        <label>Position / Job Title *</label>
                                                                        <input type="text" name="empl_position" class="form-control" placeholder="Job description" required />
                                                                </div>

                                                                <button type="submit" name="create" class="btn btn-success btn-lg">Continue</button>
                                                        </form>
                                                </div>
                                        </div>
                                </div>
                        </div>
                </div>

                <?PHP include 'includes/bootstrap_footer.php'; ?>
        </body>
</html>
