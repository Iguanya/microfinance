<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();

//Generate timestamp
$timestamp = time();

//UPDATE-Button
if (isset($_POST['update'])){

        //Sanitize user input
        $sec_name = sanitize($db_link, $_POST['sec_name']);
        $sec_returned = sanitize($db_link, $_POST['sec_returned']);

        //Update SECURITY
        $sql_update = "UPDATE securities SET sec_name = '$sec_name', sec_returned = '$sec_returned', sec_lastupd = $timestamp, user_id = $_SESSION[log_id] WHERE sec_id = $_SESSION[sec_id]";
        $query_update = db_query($db_link, $sql_update);
        checkSQL($db_link, $query_update);

        //Forward to this page
        header('Location: security.php?security='.$_SESSION['sec_id']);
}

// Get sec_ID
if(isset($_GET['security'])) $_SESSION['sec_id'] = sanitize($db_link, $_GET['security']);
else header('Location:loans_securities.php');

//Select security from SECURITIES
$result_sec = getSecurity($db_link, $_SESSION['sec_id']);
?>

<!DOCTYPE HTML>
<html>
        <?PHP include 'includes/bootstrap_header.php'; ?>
        <body>
                <?PHP include 'includes/bootstrap_header_nav.php'; ?>

                <div class="container-fluid mt-4">
                        <div class="row">
                                <div class="col-12">
                                        <h2 class="mb-4"><i class="fa fa-lock"></i> Loan Security Details</h2>

                                        <div class="row">
                                                <div class="col-md-6">
                                                        <div class="card">
                                                                <div class="card-header bg-primary text-white">
                                                                        <strong>Security Information</strong>
                                                                </div>
                                                                <div class="card-body">
                                                                        <?PHP
                                                                        if (isset($result_sec['sec_path']) && !empty($result_sec['sec_path']))
                                                                                echo '<div class="mb-3"><img src="'.$result_sec['sec_path'].'" class="img-fluid rounded" style="max-width:100%; max-height:300px;" alt="Security"></div>';
                                                                        ?>

                                                                        <form action="security.php" method="post">
                                                                                <div class="form-group">
                                                                                        <label for="sec_name" class="font-weight-bold">Description</label>
                                                                                        <input type="text" class="form-control" id="sec_name" name="sec_name" value="<?PHP echo $result_sec['sec_name']; ?>" required />
                                                                                </div>

                                                                                <div class="form-check mb-3">
                                                                                        <input class="form-check-input" type="checkbox" id="sec_returned" name="sec_returned" value="1" <?PHP if ($result_sec['sec_returned']==1) echo 'checked'; ?> />
                                                                                        <label class="form-check-label" for="sec_returned">
                                                                                                <strong>Security Returned</strong>
                                                                                        </label>
                                                                                </div>

                                                                                <button type="submit" name="update" class="btn btn-success btn-block">
                                                                                        <i class="fa fa-save"></i> Save Changes
                                                                                </button>
                                                                                <a href="loans_securities.php" class="btn btn-secondary btn-block mt-2">
                                                                                        <i class="fa fa-arrow-left"></i> Back
                                                                                </a>
                                                                        </form>
                                                                </div>
                                                        </div>
                                                </div>

                                                <div class="col-md-6">
                                                        <div class="card">
                                                                <div class="card-header bg-info text-white">
                                                                        <strong>Related Details</strong>
                                                                </div>
                                                                <div class="card-body">
                                                                        <div class="list-group">
                                                                                <div class="list-group-item">
                                                                                        <strong>Customer:</strong> <a href="customer.php?cust=<?PHP echo $result_sec['cust_id']; ?>"><?PHP echo $result_sec['cust_name'].' ('.$result_sec['cust_no'].') '; ?></a>
                                                                                </div>
                                                                                <div class="list-group-item">
                                                                                        <strong>Loan No:</strong> <a href="loan.php?lid=<?PHP echo $result_sec['loan_id']; ?>"><?PHP echo $result_sec['loan_no']; ?></a>
                                                                                </div>
                                                                                <div class="list-group-item">
                                                                                        <strong>Security No:</strong> <span class="badge badge-primary"><?PHP echo $result_sec['sec_no']; ?></span>
                                                                                </div>
                                                                                <div class="list-group-item">
                                                                                        <strong>Date:</strong> <?PHP echo date("d.m.Y",$result_sec['sec_date']); ?>
                                                                                </div>
                                                                                <div class="list-group-item">
                                                                                        <strong>Status:</strong> 
                                                                                        <?PHP 
                                                                                                if($result_sec['sec_returned'] == 1) 
                                                                                                        echo '<span class="badge badge-success">Returned</span>';
                                                                                                else 
                                                                                                        echo '<span class="badge badge-warning">Not Returned</span>';
                                                                                        ?>
                                                                                </div>
                                                                        </div>
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
