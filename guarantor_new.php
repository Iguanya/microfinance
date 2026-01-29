<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();

$timestamp = time();
$success_message = '';
$error_message = '';

$sql_create_table = "CREATE TABLE IF NOT EXISTS guarantor (
    guarantor_id INT AUTO_INCREMENT PRIMARY KEY,
    guarantor_no VARCHAR(20) NOT NULL UNIQUE,
    guarantor_name VARCHAR(100) NOT NULL,
    guarantor_phone VARCHAR(50),
    guarantor_idno VARCHAR(50),
    guarantor_address TEXT,
    guarantor_employer VARCHAR(100),
    guarantor_occupation VARCHAR(100),
    cust_id INT DEFAULT NULL,
    guarantor_active TINYINT(1) DEFAULT 1,
    guarantor_created INT NOT NULL,
    guarantor_lastupd INT NOT NULL,
    user_id INT NOT NULL,
    INDEX idx_cust_id (cust_id),
    INDEX idx_guarantor_no (guarantor_no)
)";
db_query($db_link, $sql_create_table);

function buildGuarantorNo($db_link) {
    $sql = "SELECT MAX(guarantor_id) as max_id FROM guarantor";
    $query = db_query($db_link, $sql);
    $result = db_fetch_assoc($query);
    $next_id = ($result['max_id'] ?? 0) + 1;
    return 'G-' . str_pad($next_id, 4, '0', STR_PAD_LEFT);
}

if (isset($_POST['create_guarantor'])) {
    $guarantor_no = buildGuarantorNo($db_link);
    $guarantor_name = sanitize($db_link, $_POST['guarantor_name']);
    $guarantor_phone = sanitize($db_link, $_POST['guarantor_phone']);
    $guarantor_idno = sanitize($db_link, $_POST['guarantor_idno']);
    $guarantor_address = sanitize($db_link, $_POST['guarantor_address']);
    $guarantor_employer = sanitize($db_link, $_POST['guarantor_employer']);
    $guarantor_occupation = sanitize($db_link, $_POST['guarantor_occupation']);
    $cust_id = !empty($_POST['cust_id']) ? sanitize($db_link, $_POST['cust_id']) : 'NULL';
    
    if (empty($guarantor_name)) {
        $error_message = 'Guarantor name is required.';
    } else {
        $sql_insert = "INSERT INTO guarantor (guarantor_no, guarantor_name, guarantor_phone, guarantor_idno, guarantor_address, guarantor_employer, guarantor_occupation, cust_id, guarantor_active, guarantor_created, guarantor_lastupd, user_id) 
                       VALUES ('$guarantor_no', '$guarantor_name', '$guarantor_phone', '$guarantor_idno', '$guarantor_address', '$guarantor_employer', '$guarantor_occupation', $cust_id, 1, $timestamp, $timestamp, '$_SESSION[log_id]')";
        $query_insert = db_query($db_link, $sql_insert);
        
        if ($query_insert) {
            $new_id = $db_link->lastInsertId();
            header('Location: guarantor.php?id=' . $new_id . '&created=1');
            exit;
        } else {
            $error_message = 'Failed to create guarantor: ' . db_error($db_link);
        }
    }
}

$sql_customers = "SELECT cust_id, cust_no, cust_name FROM customer WHERE cust_active = 1 ORDER BY cust_name";
$query_customers = db_query($db_link, $sql_customers);
$customers = array();
while ($row = db_fetch_assoc($query_customers)) {
    $customers[] = $row;
}
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <?PHP include 'includes/bootstrap_header.php'; ?>
    <title>New Guarantor</title>
</head>
<body>
    <?PHP include 'includes/bootstrap_header_nav.php'; ?>

    <div class="container-fluid px-4 py-4">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="start.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="guarantor_search.php">Guarantors</a></li>
                        <li class="breadcrumb-item active">New Guarantor</li>
                    </ol>
                </nav>

                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white py-2">
                        <h5 class="mb-0"><i class="fa fa-user-plus"></i> Register New Guarantor</h5>
                    </div>
                    <div class="card-body">
                        <?PHP if ($error_message): ?>
                            <div class="alert alert-danger py-2"><?PHP echo $error_message; ?></div>
                        <?PHP endif; ?>

                        <form method="post">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="guarantor_name" class="form-control" required placeholder="Enter full name">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Phone Number</label>
                                    <input type="text" name="guarantor_phone" class="form-control" placeholder="Enter phone number">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">ID Number</label>
                                    <input type="text" name="guarantor_idno" class="form-control" placeholder="National ID / Passport">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Occupation</label>
                                    <input type="text" name="guarantor_occupation" class="form-control" placeholder="Occupation">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Employer</label>
                                    <input type="text" name="guarantor_employer" class="form-control" placeholder="Employer name">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Link to Customer (Optional)</label>
                                    <select name="cust_id" class="form-select">
                                        <option value="">-- Not linked to any customer --</option>
                                        <?PHP foreach ($customers as $c): ?>
                                            <option value="<?PHP echo $c['cust_id']; ?>"><?PHP echo htmlspecialchars($c['cust_name']); ?> (<?PHP echo $c['cust_no']; ?>)</option>
                                        <?PHP endforeach; ?>
                                    </select>
                                    <small class="text-muted">Link this guarantor to a customer if they are also a customer</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Address</label>
                                <textarea name="guarantor_address" class="form-control" rows="2" placeholder="Physical address"></textarea>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="guarantor_search.php" class="btn btn-secondary">
                                    <i class="fa fa-times"></i> Cancel
                                </a>
                                <button type="submit" name="create_guarantor" class="btn btn-success">
                                    <i class="fa fa-save"></i> Create Guarantor
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?PHP include 'includes/bootstrap_footer.php'; ?>
</body>
</html>
