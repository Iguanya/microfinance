<?PHP
require 'functions.php';
require 'function_loans.php';
checkLogin();
$db_link = connect();

$timestamp = time();
$success_message = '';
$error_message = '';

$sql_create_table = "CREATE TABLE IF NOT EXISTS loan_guarantor_verification (
    lgv_id INT AUTO_INCREMENT PRIMARY KEY,
    loan_id INT NOT NULL,
    guarantor_id INT NOT NULL,
    lgv_status ENUM('pending', 'verified', 'rejected') DEFAULT 'pending',
    lgv_notes TEXT,
    verified_by INT,
    verified_date INT,
    lgv_created INT NOT NULL,
    user_id INT NOT NULL,
    UNIQUE KEY unique_loan_guarantor (loan_id, guarantor_id),
    INDEX idx_loan_id (loan_id),
    INDEX idx_guarantor_id (guarantor_id)
)";
db_query($db_link, $sql_create_table);

if (!isset($_GET['lid']) && !isset($_SESSION['loan_id'])) {
    header('Location: loans_search.php');
    exit;
}

if (isset($_GET['lid'])) {
    $_SESSION['loan_id'] = sanitize($db_link, $_GET['lid']);
}

$sql_loan = "SELECT l.*, c.cust_name, c.cust_no, ls.loanstatus_name 
             FROM loans l 
             JOIN customer c ON l.cust_id = c.cust_id 
             JOIN loanstatus ls ON l.loanstatus_id = ls.loanstatus_id 
             WHERE l.loan_id = '$_SESSION[loan_id]'";
$query_loan = db_query($db_link, $sql_loan);
$loan = db_fetch_assoc($query_loan);

if (!$loan) {
    header('Location: loans_search.php');
    exit;
}

$guarantors = array();
for ($i = 1; $i <= 3; $i++) {
    $g_field = 'loan_guarant' . $i;
    if (!empty($loan[$g_field]) && $loan[$g_field] != '0') {
        $g_id = $loan[$g_field];
        $sql_g = "SELECT cust_id, cust_no, cust_name, cust_phone, cust_address FROM customer WHERE cust_id = '$g_id'";
        $query_g = db_query($db_link, $sql_g);
        $guarantor = db_fetch_assoc($query_g);
        
        if ($guarantor) {
            $sql_v = "SELECT * FROM loan_guarantor_verification WHERE loan_id = '$_SESSION[loan_id]' AND guarantor_id = '$g_id'";
            $query_v = db_query($db_link, $sql_v);
            $verification = db_fetch_assoc($query_v);
            
            if (!$verification) {
                $sql_insert_v = "INSERT INTO loan_guarantor_verification (loan_id, guarantor_id, lgv_status, lgv_created, user_id) 
                                 VALUES ('$_SESSION[loan_id]', '$g_id', 'pending', $timestamp, '$_SESSION[log_id]')";
                db_query($db_link, $sql_insert_v);
                $verification = array('lgv_status' => 'pending', 'lgv_notes' => '', 'verified_by' => null, 'verified_date' => null);
            }
            
            $guarantor['verification'] = $verification;
            $guarantor['position'] = $i;
            $guarantors[] = $guarantor;
        }
    }
}

if (isset($_POST['verify_guarantor'])) {
    $g_id = sanitize($db_link, $_POST['guarantor_id']);
    $status = sanitize($db_link, $_POST['verification_status']);
    $notes = sanitize($db_link, $_POST['verification_notes']);
    
    if (!in_array($status, array('pending', 'verified', 'rejected'))) {
        $error_message = 'Invalid verification status.';
    } else {
        $sql_update = "UPDATE loan_guarantor_verification 
                       SET lgv_status = '$status', 
                           lgv_notes = '$notes', 
                           verified_by = '$_SESSION[log_id]', 
                           verified_date = $timestamp 
                       WHERE loan_id = '$_SESSION[loan_id]' AND guarantor_id = '$g_id'";
        $result = db_query($db_link, $sql_update);
        
        if ($result) {
            $success_message = 'Guarantor verification status updated successfully.';
            header('Location: loan_verify_guarantors.php?lid=' . $_SESSION['loan_id'] . '&updated=1');
            exit;
        } else {
            $error_message = 'Failed to update verification status.';
        }
    }
}

if (isset($_GET['updated'])) {
    $success_message = 'Guarantor verification status updated successfully.';
}

$all_verified = true;
foreach ($guarantors as $g) {
    if ($g['verification']['lgv_status'] != 'verified') {
        $all_verified = false;
        break;
    }
}
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <?PHP include 'includes/bootstrap_header.php'; ?>
    <title>Verify Guarantors - <?PHP echo $loan['loan_no']; ?></title>
</head>
<body>
    <?PHP include 'includes/bootstrap_header_nav.php'; ?>

    <div class="container-fluid px-4 py-4">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="start.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="loans_search.php">Loans</a></li>
                        <li class="breadcrumb-item"><a href="loan.php?lid=<?PHP echo $_SESSION['loan_id']; ?>"><?PHP echo $loan['loan_no']; ?></a></li>
                        <li class="breadcrumb-item active">Verify Guarantors</li>
                    </ol>
                </nav>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark py-2">
                        <h5 class="mb-0"><i class="fa fa-user-check"></i> Guarantor Verification - <?PHP echo $loan['loan_no']; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Customer:</strong> <?PHP echo htmlspecialchars($loan['cust_name']); ?> (<?PHP echo $loan['cust_no']; ?>)
                            </div>
                            <div class="col-md-4">
                                <strong>Loan Amount:</strong> <?PHP echo number_format($loan['loan_principal'], 2); ?> <?PHP echo $_SESSION['set_cur']; ?>
                            </div>
                            <div class="col-md-4">
                                <strong>Status:</strong> 
                                <span class="badge bg-<?PHP echo $loan['loanstatus_id'] == 1 ? 'warning' : ($loan['loanstatus_id'] == 2 ? 'success' : 'secondary'); ?>">
                                    <?PHP echo $loan['loanstatus_name']; ?>
                                </span>
                            </div>
                        </div>

                        <?PHP if ($success_message): ?>
                            <div class="alert alert-success py-2"><?PHP echo $success_message; ?></div>
                        <?PHP endif; ?>

                        <?PHP if ($error_message): ?>
                            <div class="alert alert-danger py-2"><?PHP echo $error_message; ?></div>
                        <?PHP endif; ?>

                        <?PHP if ($all_verified): ?>
                            <div class="alert alert-success py-2">
                                <i class="fa fa-check-circle"></i> All guarantors have been verified. This loan can now be approved.
                                <a href="loan.php?lid=<?PHP echo $_SESSION['loan_id']; ?>" class="alert-link">Go to Loan Details</a>
                            </div>
                        <?PHP elseif (count($guarantors) > 0): ?>
                            <div class="alert alert-info py-2">
                                <i class="fa fa-info-circle"></i> All guarantors must be verified before the loan can be approved.
                            </div>
                        <?PHP endif; ?>
                    </div>
                </div>

                <?PHP if (count($guarantors) == 0): ?>
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i> No guarantors assigned to this loan.
                        <a href="loan.php?lid=<?PHP echo $_SESSION['loan_id']; ?>" class="alert-link">Return to Loan</a>
                    </div>
                <?PHP else: ?>
                    <?PHP foreach ($guarantors as $g): ?>
                        <div class="card shadow-sm mb-3">
                            <div class="card-header py-2 <?PHP 
                                echo $g['verification']['lgv_status'] == 'verified' ? 'bg-success text-white' : 
                                     ($g['verification']['lgv_status'] == 'rejected' ? 'bg-danger text-white' : 'bg-light'); 
                            ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        <i class="fa fa-user"></i> Guarantor <?PHP echo $g['position']; ?>: <?PHP echo htmlspecialchars($g['cust_name']); ?>
                                    </h6>
                                    <span class="badge <?PHP 
                                        echo $g['verification']['lgv_status'] == 'verified' ? 'bg-light text-success' : 
                                             ($g['verification']['lgv_status'] == 'rejected' ? 'bg-light text-danger' : 'bg-warning text-dark'); 
                                    ?>">
                                        <?PHP echo ucfirst($g['verification']['lgv_status']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <strong>Customer No:</strong> <?PHP echo $g['cust_no']; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Phone:</strong> <?PHP echo htmlspecialchars($g['cust_phone'] ?: 'N/A'); ?>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Address:</strong> <?PHP echo htmlspecialchars($g['cust_address'] ?: 'N/A'); ?>
                                    </div>
                                </div>

                                <?PHP if ($g['verification']['verified_date']): ?>
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <small class="text-muted">
                                                Last updated: <?PHP echo date('d M Y H:i', $g['verification']['verified_date']); ?>
                                                <?PHP if (!empty($g['verification']['lgv_notes'])): ?>
                                                    | Notes: <?PHP echo htmlspecialchars($g['verification']['lgv_notes']); ?>
                                                <?PHP endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                <?PHP endif; ?>

                                <form method="post" class="row g-2 align-items-end">
                                    <input type="hidden" name="guarantor_id" value="<?PHP echo $g['cust_id']; ?>">
                                    <div class="col-md-3">
                                        <label class="form-label small">Verification Status</label>
                                        <select name="verification_status" class="form-select">
                                            <option value="pending" <?PHP echo $g['verification']['lgv_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="verified" <?PHP echo $g['verification']['lgv_status'] == 'verified' ? 'selected' : ''; ?>>Verified</option>
                                            <option value="rejected" <?PHP echo $g['verification']['lgv_status'] == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small">Notes</label>
                                        <input type="text" name="verification_notes" class="form-control" placeholder="Verification notes..." value="<?PHP echo htmlspecialchars($g['verification']['lgv_notes'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" name="verify_guarantor" class="btn btn-primary w-100">
                                            <i class="fa fa-save"></i> Update Status
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?PHP endforeach; ?>
                <?PHP endif; ?>

                <div class="mt-3">
                    <a href="loan.php?lid=<?PHP echo $_SESSION['loan_id']; ?>" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back to Loan
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?PHP include 'includes/bootstrap_footer.php'; ?>
</body>
</html>
