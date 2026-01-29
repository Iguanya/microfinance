<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();

$timestamp = time();
$error_message = '';
$success_message = '';

if (isset($_POST['create'])) {
    $stakeholder_no = sanitize($db_link, $_POST['stakeholder_no']);
    $stakeholder_name = sanitize($db_link, $_POST['stakeholder_name']);
    $stakeholder_type = sanitize($db_link, $_POST['stakeholder_type']);
    $stakeholder_idno = sanitize($db_link, $_POST['stakeholder_idno']);
    $stakeholder_address = sanitize($db_link, $_POST['stakeholder_address']);
    $stakeholder_phone = sanitize($db_link, $_POST['stakeholder_phone']);
    $stakeholder_email = sanitize($db_link, $_POST['stakeholder_email']);
    $stakeholder_bank = sanitize($db_link, $_POST['stakeholder_bank']);
    $stakeholder_accno = sanitize($db_link, $_POST['stakeholder_accno']);
    $cust_id = !empty($_POST['cust_id']) ? sanitize($db_link, $_POST['cust_id']) : 'NULL';

    if (empty($stakeholder_name)) {
        $error_message = 'Stakeholder name is required.';
    } else {
        $sql_check = "SELECT stakeholder_id FROM stakeholder WHERE stakeholder_no = '$stakeholder_no'";
        $query_check = db_query($db_link, $sql_check);
        if (db_fetch_assoc($query_check)) {
            $error_message = 'A stakeholder with this number already exists.';
        } else {
            $cust_insert = ($cust_id == 'NULL') ? 'NULL' : "'$cust_id'";
            $sql_insert = "INSERT INTO stakeholder (stakeholder_no, stakeholder_name, stakeholder_type, stakeholder_idno, stakeholder_address, stakeholder_phone, stakeholder_email, stakeholder_bank, stakeholder_accno, stakeholder_active, stakeholder_created, stakeholder_lastupd, cust_id, user_id) 
                           VALUES ('$stakeholder_no', '$stakeholder_name', '$stakeholder_type', '$stakeholder_idno', '$stakeholder_address', '$stakeholder_phone', '$stakeholder_email', '$stakeholder_bank', '$stakeholder_accno', 1, $timestamp, $timestamp, $cust_insert, '$_SESSION[log_id]')";
            $query_insert = db_query($db_link, $sql_insert);
            
            if ($query_insert) {
                $new_id = $db_link->lastInsertId();
                header('Location: stakeholder.php?id=' . $new_id);
                exit;
            } else {
                $error_message = 'Error creating stakeholder: ' . db_error($db_link);
            }
        }
    }
}

$sql_next_no = "SELECT MAX(CAST(SUBSTRING(stakeholder_no, 4) AS UNSIGNED)) as max_no FROM stakeholder WHERE stakeholder_no LIKE 'SH-%'";
$query_next_no = db_query($db_link, $sql_next_no);
$row_next = db_fetch_assoc($query_next_no);
$next_no = 'SH-' . str_pad(($row_next['max_no'] ?? 0) + 1, 4, '0', STR_PAD_LEFT);

$sql_customers = "SELECT cust_id, cust_no, cust_name FROM customer WHERE cust_active = 1 ORDER BY cust_name";
$query_customers = db_query($db_link, $sql_customers);
$customers = array();
while ($row = db_fetch_assoc($query_customers)) {
    $customers[] = $row;
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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fa fa-plus-circle"></i> New Stakeholder</h2>
                        <a href="stakeholder_search.php" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back to List</a>
                    </div>

                    <?PHP if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa fa-exclamation-circle"></i> <?PHP echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?PHP endif; ?>

                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white py-2">
                            <h6 class="mb-0">Stakeholder Details</h6>
                        </div>
                        <div class="card-body">
                            <form action="stakeholder_new.php" method="post">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="stakeholder_no" class="form-label fw-bold small">Stakeholder Number *</label>
                                        <input type="text" class="form-control" id="stakeholder_no" name="stakeholder_no" value="<?PHP echo isset($_POST['stakeholder_no']) ? $_POST['stakeholder_no'] : $next_no; ?>" required />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="stakeholder_name" class="form-label fw-bold small">Name / Organization *</label>
                                        <input type="text" class="form-control" id="stakeholder_name" name="stakeholder_name" value="<?PHP echo isset($_POST['stakeholder_name']) ? $_POST['stakeholder_name'] : ''; ?>" required />
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="stakeholder_type" class="form-label fw-bold small">Type *</label>
                                        <select class="form-select" id="stakeholder_type" name="stakeholder_type" required>
                                            <option value="individual" <?PHP echo (isset($_POST['stakeholder_type']) && $_POST['stakeholder_type'] == 'individual') ? 'selected' : ''; ?>>Individual</option>
                                            <option value="organization" <?PHP echo (isset($_POST['stakeholder_type']) && $_POST['stakeholder_type'] == 'organization') ? 'selected' : ''; ?>>Organization</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="stakeholder_idno" class="form-label fw-bold small">ID / Registration Number</label>
                                        <input type="text" class="form-control" id="stakeholder_idno" name="stakeholder_idno" value="<?PHP echo isset($_POST['stakeholder_idno']) ? $_POST['stakeholder_idno'] : ''; ?>" />
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="stakeholder_phone" class="form-label fw-bold small">Phone Number</label>
                                        <input type="text" class="form-control" id="stakeholder_phone" name="stakeholder_phone" value="<?PHP echo isset($_POST['stakeholder_phone']) ? $_POST['stakeholder_phone'] : ''; ?>" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="stakeholder_email" class="form-label fw-bold small">Email</label>
                                        <input type="email" class="form-control" id="stakeholder_email" name="stakeholder_email" value="<?PHP echo isset($_POST['stakeholder_email']) ? $_POST['stakeholder_email'] : ''; ?>" />
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="stakeholder_address" class="form-label fw-bold small">Address</label>
                                    <textarea class="form-control" id="stakeholder_address" name="stakeholder_address" rows="2"><?PHP echo isset($_POST['stakeholder_address']) ? $_POST['stakeholder_address'] : ''; ?></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="stakeholder_bank" class="form-label fw-bold small">Bank Name</label>
                                        <input type="text" class="form-control" id="stakeholder_bank" name="stakeholder_bank" value="<?PHP echo isset($_POST['stakeholder_bank']) ? $_POST['stakeholder_bank'] : ''; ?>" />
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="stakeholder_accno" class="form-label fw-bold small">Bank Account Number</label>
                                        <input type="text" class="form-control" id="stakeholder_accno" name="stakeholder_accno" value="<?PHP echo isset($_POST['stakeholder_accno']) ? $_POST['stakeholder_accno'] : ''; ?>" />
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="cust_id" class="form-label fw-bold small">Link to Customer (Optional)</label>
                                    <select class="form-select" id="cust_id" name="cust_id">
                                        <option value="">-- Not linked to any customer --</option>
                                        <?PHP foreach ($customers as $cust): ?>
                                        <option value="<?PHP echo $cust['cust_id']; ?>" <?PHP echo (isset($_POST['cust_id']) && $_POST['cust_id'] == $cust['cust_id']) ? 'selected' : ''; ?>><?PHP echo $cust['cust_no'] . ' - ' . $cust['cust_name']; ?></option>
                                        <?PHP endforeach; ?>
                                    </select>
                                    <small class="text-muted">Link this stakeholder to an existing customer if they are also a borrower.</small>
                                </div>

                                <div class="d-grid mt-4">
                                    <button type="submit" name="create" class="btn btn-success btn-lg">
                                        <i class="fa fa-save"></i> Create Stakeholder
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
