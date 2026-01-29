<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();

$timestamp = time();
$success_message = '';
$error_message = '';

if (!isset($_GET['id']) && !isset($_SESSION['guarantor_id'])) {
    header('Location: guarantor_search.php');
    exit;
}

if (isset($_GET['id'])) {
    $_SESSION['guarantor_id'] = sanitize($db_link, $_GET['id']);
}

$sql_guarantor = "SELECT g.*, c.cust_no, c.cust_name as linked_customer 
                  FROM guarantor g 
                  LEFT JOIN customer c ON g.cust_id = c.cust_id 
                  WHERE g.guarantor_id = '$_SESSION[guarantor_id]'";
$query_guarantor = db_query($db_link, $sql_guarantor);
$guarantor = db_fetch_assoc($query_guarantor);

if (!$guarantor) {
    header('Location: guarantor_search.php');
    exit;
}

if (isset($_POST['update_guarantor'])) {
    $guarantor_name = sanitize($db_link, $_POST['guarantor_name']);
    $guarantor_phone = sanitize($db_link, $_POST['guarantor_phone']);
    $guarantor_idno = sanitize($db_link, $_POST['guarantor_idno']);
    $guarantor_address = sanitize($db_link, $_POST['guarantor_address']);
    $guarantor_employer = sanitize($db_link, $_POST['guarantor_employer']);
    $guarantor_occupation = sanitize($db_link, $_POST['guarantor_occupation']);
    $cust_id = !empty($_POST['cust_id']) ? sanitize($db_link, $_POST['cust_id']) : 'NULL';
    $guarantor_active = isset($_POST['guarantor_active']) ? 1 : 0;
    
    if (empty($guarantor_name)) {
        $error_message = 'Guarantor name is required.';
    } else {
        $sql_update = "UPDATE guarantor SET 
                       guarantor_name = '$guarantor_name',
                       guarantor_phone = '$guarantor_phone',
                       guarantor_idno = '$guarantor_idno',
                       guarantor_address = '$guarantor_address',
                       guarantor_employer = '$guarantor_employer',
                       guarantor_occupation = '$guarantor_occupation',
                       cust_id = $cust_id,
                       guarantor_active = $guarantor_active,
                       guarantor_lastupd = $timestamp,
                       user_id = '$_SESSION[log_id]'
                       WHERE guarantor_id = '$_SESSION[guarantor_id]'";
        $result = db_query($db_link, $sql_update);
        
        if ($result) {
            header('Location: guarantor.php?id=' . $_SESSION['guarantor_id'] . '&updated=1');
            exit;
        } else {
            $error_message = 'Failed to update guarantor.';
        }
    }
}

if (isset($_GET['created'])) {
    $success_message = 'Guarantor created successfully.';
} elseif (isset($_GET['updated'])) {
    $success_message = 'Guarantor updated successfully.';
}

$sql_loans = "SELECT l.loan_id, l.loan_no, l.loan_principal, l.loan_date, c.cust_name, ls.loanstatus_name,
              CASE 
                  WHEN l.loan_guarant1 = '$_SESSION[guarantor_id]' THEN 1
                  WHEN l.loan_guarant2 = '$_SESSION[guarantor_id]' THEN 2
                  WHEN l.loan_guarant3 = '$_SESSION[guarantor_id]' THEN 3
              END as guarantor_position
              FROM loans l
              JOIN customer c ON l.cust_id = c.cust_id
              JOIN loanstatus ls ON l.loanstatus_id = ls.loanstatus_id
              WHERE l.loan_guarant1 = '$_SESSION[guarantor_id]' 
                 OR l.loan_guarant2 = '$_SESSION[guarantor_id]' 
                 OR l.loan_guarant3 = '$_SESSION[guarantor_id]'
              ORDER BY l.loan_date DESC";
$query_loans = db_query($db_link, $sql_loans);
$guaranteed_loans = array();
if ($query_loans) {
    while ($row = db_fetch_assoc($query_loans)) {
        $guaranteed_loans[] = $row;
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
    <title>Guarantor - <?PHP echo htmlspecialchars($guarantor['guarantor_name']); ?></title>
</head>
<body>
    <?PHP include 'includes/bootstrap_header_nav.php'; ?>

    <div class="container-fluid px-4 py-4">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="start.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="guarantor_search.php">Guarantors</a></li>
                        <li class="breadcrumb-item active"><?PHP echo htmlspecialchars($guarantor['guarantor_name']); ?></li>
                    </ol>
                </nav>

                <?PHP if ($success_message): ?>
                    <div class="alert alert-success py-2"><?PHP echo $success_message; ?></div>
                <?PHP endif; ?>

                <?PHP if ($error_message): ?>
                    <div class="alert alert-danger py-2"><?PHP echo $error_message; ?></div>
                <?PHP endif; ?>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-primary text-white py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="fa fa-user-shield"></i> <?PHP echo htmlspecialchars($guarantor['guarantor_name']); ?></h5>
                                    <span class="badge <?PHP echo $guarantor['guarantor_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?PHP echo $guarantor['guarantor_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <form method="post">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Guarantor No</label>
                                            <input type="text" class="form-control" value="<?PHP echo $guarantor['guarantor_no']; ?>" disabled>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Full Name <span class="text-danger">*</span></label>
                                            <input type="text" name="guarantor_name" class="form-control" value="<?PHP echo htmlspecialchars($guarantor['guarantor_name']); ?>" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Phone Number</label>
                                            <input type="text" name="guarantor_phone" class="form-control" value="<?PHP echo htmlspecialchars($guarantor['guarantor_phone']); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">ID Number</label>
                                            <input type="text" name="guarantor_idno" class="form-control" value="<?PHP echo htmlspecialchars($guarantor['guarantor_idno']); ?>">
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Occupation</label>
                                            <input type="text" name="guarantor_occupation" class="form-control" value="<?PHP echo htmlspecialchars($guarantor['guarantor_occupation']); ?>">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Employer</label>
                                            <input type="text" name="guarantor_employer" class="form-control" value="<?PHP echo htmlspecialchars($guarantor['guarantor_employer']); ?>">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Address</label>
                                        <textarea name="guarantor_address" class="form-control" rows="2"><?PHP echo htmlspecialchars($guarantor['guarantor_address']); ?></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Link to Customer</label>
                                            <select name="cust_id" class="form-select">
                                                <option value="">-- Not linked --</option>
                                                <?PHP foreach ($customers as $c): ?>
                                                    <option value="<?PHP echo $c['cust_id']; ?>" <?PHP echo $guarantor['cust_id'] == $c['cust_id'] ? 'selected' : ''; ?>>
                                                        <?PHP echo htmlspecialchars($c['cust_name']); ?> (<?PHP echo $c['cust_no']; ?>)
                                                    </option>
                                                <?PHP endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Status</label>
                                            <div class="form-check mt-2">
                                                <input type="checkbox" name="guarantor_active" class="form-check-input" id="guarantor_active" <?PHP echo $guarantor['guarantor_active'] ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="guarantor_active">Active</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <a href="guarantor_search.php" class="btn btn-secondary">
                                            <i class="fa fa-arrow-left"></i> Back
                                        </a>
                                        <button type="submit" name="update_guarantor" class="btn btn-success">
                                            <i class="fa fa-save"></i> Save Changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <?PHP if ($guarantor['linked_customer']): ?>
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-info text-white py-2">
                                <h6 class="mb-0"><i class="fa fa-link"></i> Linked Customer</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-2"><strong><?PHP echo htmlspecialchars($guarantor['linked_customer']); ?></strong></p>
                                <p class="text-muted mb-2"><?PHP echo $guarantor['cust_no']; ?></p>
                                <a href="customer.php?cust=<?PHP echo $guarantor['cust_id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fa fa-user"></i> View Customer
                                </a>
                            </div>
                        </div>
                        <?PHP endif; ?>

                        <div class="card shadow-sm">
                            <div class="card-header bg-warning text-dark py-2">
                                <h6 class="mb-0"><i class="fa fa-file-text"></i> Guaranteed Loans (<?PHP echo count($guaranteed_loans); ?>)</h6>
                            </div>
                            <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                                <?PHP if (count($guaranteed_loans) > 0): ?>
                                    <?PHP foreach ($guaranteed_loans as $loan): ?>
                                    <div class="border-bottom pb-2 mb-2">
                                        <div class="d-flex justify-content-between">
                                            <a href="loan.php?lid=<?PHP echo $loan['loan_id']; ?>" class="fw-bold text-decoration-none">
                                                <?PHP echo $loan['loan_no']; ?>
                                            </a>
                                            <span class="badge bg-secondary">G<?PHP echo $loan['guarantor_position']; ?></span>
                                        </div>
                                        <small class="text-muted">
                                            <?PHP echo htmlspecialchars($loan['cust_name']); ?><br>
                                            <?PHP echo number_format($loan['loan_principal'], 2); ?> <?PHP echo $_SESSION['set_cur']; ?>
                                        </small>
                                    </div>
                                    <?PHP endforeach; ?>
                                <?PHP else: ?>
                                    <p class="text-muted mb-0">No loans guaranteed yet.</p>
                                <?PHP endif; ?>
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
