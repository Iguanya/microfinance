<?PHP
require 'functions.php';
require 'function_pic.php';
checkLogin();
$db_link = connect();

//Check where Re-direct comes from
if(isset($_GET['from'])){
        $from = sanitize($db_link, $_GET['from']);
}

//SKIP-Button
if (isset($_POST['skip'])){
        if ($from == "new") header('Location: acc_share_buy.php?cust='.$_SESSION['cust_id'].'&rec='.$_SESSION['receipt_no']);
        else header('Location: customer.php?cust='.$_SESSION['cust_id']);
}

//UPLOAD-Button
if (isset($_POST['upload']) AND isset($_FILES['image'])){
        //Settings
        $max_file_size = 1024*2048; // 2048kb
        $valid_exts = array('jpeg', 'jpg', 'png', 'tif', 'tiff');
        $path = 'uploads/photos/customers/cust'.$_SESSION['cust_id'].'_';

        //Thumbnail Sizes
        $sizes = array(100 => 130, 146 => 190, 230 => 300);

        //Check for maximum file size
        if( $_FILES['image']['size'] < $max_file_size ){
                // Get file extension
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

                if (in_array($ext, $valid_exts)) {
                        //Resize image
                        $files = array();
                        foreach ($sizes as $width => $height) {
                                $files[] = resizeImage($width, $height, $path);
                        }
                        $sql_picpath = "UPDATE customer SET cust_pic = '$files[1]' WHERE cust_id = '$_SESSION[cust_id]'";
                        $query_picpath = db_query($db_link, $sql_picpath);
                        checkSQL($db_link, $query_picpath);

                        if ($from == "new")     header('Location: acc_share_buy.php?cust='.$_SESSION['cust_id'].'&rec='.$_SESSION['receipt_no']);
                        else header('Location:customer.php?cust='.$_SESSION['cust_id']);
                }
                else $error_msg = 'Unsupported file format. Please use: JPEG, PNG, or TIFF';
        }
        else $error_msg = 'Please choose an image smaller than 2048kB.';
}

$result_customer = getCustomer($db_link, $_SESSION['cust_id']);
?>

<!DOCTYPE HTML>
<html>
        <?PHP include 'includes/bootstrap_header.php'; ?>
        <body>

                <div class="container-fluid mt-4">
                        <div class="row">
                                <div class="col-md-8 offset-md-2">
                                        <div class="card">
                                                <div class="card-header bg-primary text-white">
                                                        <h5 class="mb-0"><i class="fa fa-camera"></i> Upload Customer Photo</h5>
                                                </div>
                                                <div class="card-body">
                                                        <p class="lead">Customer: <strong><?PHP echo $result_customer['cust_name'].' ('.$result_customer['cust_no'].')'; ?></strong></p>

                                                        <?php if(isset($error_msg)): ?>
                                                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                                <i class="fa fa-exclamation-circle"></i> <strong>Error:</strong> <?php echo $error_msg; ?>
                                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                </button>
                                                        </div>
                                                        <?php endif ?>

                                                        <form action="cust_new_pic.php" method="post" enctype="multipart/form-data">
                                                                <div class="form-group">
                                                                        <label class="font-weight-bold mb-3">Select Customer Photo</label>
                                                                        <div class="custom-file">
                                                                                <input type="file" class="custom-file-input" id="image" name="image" accept="image/*" required />
                                                                                <label class="custom-file-label" for="image">Choose file...</label>
                                                                        </div>
                                                                        <small class="form-text text-muted d-block mt-2">
                                                                                <i class="fa fa-info-circle"></i> Supported formats: JPEG, PNG, TIFF (max 2MB)
                                                                        </small>
                                                                </div>

                                                                <hr class="my-4" />

                                                                <button type="submit" name="upload" class="btn btn-success btn-lg">
                                                                        <i class="fa fa-upload"></i> Upload Photo
                                                                </button>
                                                                <button type="submit" name="skip" class="btn btn-secondary btn-lg">
                                                                        <i class="fa fa-forward"></i> Skip for Now
                                                                </button>
                                                        </form>
                                                </div>
                                        </div>
                                </div>
                        </div>
                </div>

                <?PHP include 'includes/bootstrap_footer.php'; ?>
        </body>
</html>
