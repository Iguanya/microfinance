<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();

$timestamp = time();
$error_message = '';
$success_message = '';

if (!isset($_GET['id'])) {
    header('Location: stakeholder_search.php');
    exit;
}

$stakeholder_id = sanitize($db_link, $_GET['id']);

if (isset($_POST['update'])) {
    $stakeholder_no = sanitize($db_link, $_POST['stakeholder_no']);
    $stakeholder_name = sanitize($db_link, $_POST['stakeholder_name']);
    $stakeholder_type = sanitize($db_link, $_POST['stakeholder_type']);
    $stakeholder_idno = sanitize($db_link, $_POST['stakeholder_idno']);
    $stakeholder_address = sanitize($db_link, $_POST['stakeholder_address']);
    $stakeholder_phone = sanitize($db_link, $_POST['stakeholder_phone']);
    $stakeholder_email = sanitize($db_link, $_POST['stakeholder_email']);
    $stakeholder_bank = sanitize($db_link, $_POST['stakeholder_bank']);
    $stakeholder_accno = sanitize($db_link, $_POST['stakeholder_accno']);
    $stakeholder_active = isset($_POST['stakeholder_active']) ? 1 : 0;
    $cust_id = !empty($_POST['cust_id']) ? sanitize($db_link, $_POST['cust_id']) : 'NULL';

    $cust_update = ($cust_id == 'NULL') ? 'NULL' : "'$cust_id'";
    $sql_update = "UPDATE stakeholder SET 
                   stakeholder_no = '$stakeholder_no',
                   stakeholder_name = '$stakeholder_name',
                   stakeholder_type = '$stakeholder_type',
                   stakeholder_idno = '$stakeholder_idno',
                   stakeholder_address = '$stakeholder_address',
                   stakeholder_phone = '$stakeholder_phone',
                   stakeholder_email = '$stakeholder_email',
                   stakeholder_bank = '$stakeholder_bank',
                   stakeholder_accno = '$stakeholder_accno',
                   stakeholder_active = $stakeholder_active,
                   stakeholder_lastupd = $timestamp,
                   cust_id = $cust_update,
                   user_id = '$_SESSION[log_id]'
                   WHERE stakeholder_id = '$stakeholder_id'";
    $query_update = db_query($db_link, $sql_update);
    
    if ($query_update) {
        $success_message = 'Stakeholder updated successfully.';
    } else {
        $error_message = 'Error updating stakeholder: ' . db_error($db_link);
    }
}

$sql_stakeholder = "SELECT s.*, c.cust_name, c.cust_no 
                    FROM stakeholder s 
                    LEFT JOIN customer c ON s.cust_id = c.cust_id 
                    WHERE s.stakeholder_id = '$stakeholder_id'";
$query_stakeholder = db_query($db_link, $sql_stakeholder);
$stakeholder = db_fetch_assoc($query_stakeholder);

if (!$stakeholder) {
    header('Location: stakeholder_search.php');
    exit;
}

$sql_shares = "SELECT * FROM stakeholder_shares WHERE stakeholder_id = '$stakeholder_id' ORDER BY ss_date DESC, ss_id DESC";
$query_shares = db_query($db_link, $sql_shares);
$shares_history = array();
$total_shares = 0;
$total_value = 0;
while ($row = db_fetch_assoc($query_shares)) {
    $shares_history[] = $row;
    $total_shares += $row['ss_amount'];
    $total_value += $row['ss_value'];
}

$sql_customers = "SELECT cust_id, cust_no, cust_name FROM customer WHERE cust_active = 1 ORDER BY cust_name";
$query_customers = db_query($db_link, $sql_customers);
$customers = array();
while ($row = db_fetch_assoc($query_customers)) {
    $customers[] = $row;
}

getShareValue($db_link);
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
                        <h2><i class="fa fa-briefcase"></i> <?PHP echo $stakeholder['stakeholder_name']; ?> (<?PHP echo $stakeholder['stakeholder_no']; ?>)</h2>
                        <a href="stakeholder_search.php" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back to List</a>
                    </div>

                    <?PHP if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa fa-exclamation-circle"></i> <?PHP echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?PHP endif; ?>

                    <?PHP if ($success_message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa fa-check-circle"></i> <?PHP echo $success_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?PHP endif; ?>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card border-start border-success border-4 shadow-sm">
                                <div class="card-body">
                                    <div class="text-success fw-bold text-uppercase mb-1 small">Total Shares</div>
                                    <div class="h4 mb-0 fw-bold"><?PHP echo number_format($total_shares); ?> shares</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-start border-primary border-4 shadow-sm">
                                <div class="card-body">
                                    <div class="text-primary fw-bold text-uppercase mb-1 small">Total Value</div>
                                    <div class="h4 mb-0 fw-bold"><?PHP echo number_format($total_value, 2); ?> <?PHP echo $_SESSION['set_cur']; ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-start border-info border-4 shadow-sm">
                                <div class="card-body">
                                    <div class="text-info fw-bold text-uppercase mb-1 small">Current Share Price</div>
                                    <div class="h4 mb-0 fw-bold"><?PHP echo number_format($_SESSION['share_value'], 2); ?> <?PHP echo $_SESSION['set_cur']; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <ul class="nav nav-tabs mb-4" id="stakeholderTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab" aria-controls="details" aria-selected="true">Details</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="shares-tab" data-bs-toggle="tab" data-bs-target="#shares" type="button" role="tab" aria-controls="shares" aria-selected="false">Share Transactions</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="stakeholderTabsContent">
                        <div class="tab-pane fade show active" id="details" role="tabpanel" aria-labelledby="details-tab">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card shadow-sm mb-4">
                                        <div class="card-header bg-primary text-white py-2">
                                            <h6 class="mb-0">Quick Actions</h6>
                                        </div>
                                        <div class="card-body p-2">
                                            <div class="d-grid gap-2">
                                                <a href="share_buy.php?sh=<?PHP echo $stakeholder_id; ?>" class="btn btn-success btn-sm"><i class="fa fa-plus-circle"></i> Buy Shares</a>
                                                <?PHP if ($total_shares > 0): ?>
                                                <a href="share_sell.php?sh=<?PHP echo $stakeholder_id; ?>" class="btn btn-warning btn-sm"><i class="fa fa-minus-circle"></i> Sell Shares</a>
                                                <a href="share_transfer.php?sh=<?PHP echo $stakeholder_id; ?>" class="btn btn-info btn-sm text-white"><i class="fa fa-exchange"></i> Transfer Shares</a>
                                                <?PHP endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <?PHP if ($stakeholder['cust_id']): ?>
                                    <div class="card shadow-sm mb-4">
                                        <div class="card-header bg-info text-white py-2">
                                            <h6 class="mb-0">Linked Customer</h6>
                                        </div>
                                        <div class="card-body">
                                            <p class="mb-2"><strong><?PHP echo $stakeholder['cust_name']; ?></strong></p>
                                            <p class="mb-2 text-muted"><?PHP echo $stakeholder['cust_no']; ?></p>
                                            <a href="customer.php?cust=<?PHP echo $stakeholder['cust_id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fa fa-user"></i> View Customer</a>
                                        </div>
                                    </div>
                                    <?PHP endif; ?>
                                </div>

                                <div class="col-md-8">
                                    <div class="card shadow-sm">
                                        <div class="card-header bg-primary text-white py-2">
                                            <h6 class="mb-0">Edit Stakeholder Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <form action="stakeholder.php?id=<?PHP echo $stakeholder_id; ?>" method="post">
                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="stakeholder_no" class="form-label fw-bold small">Stakeholder Number</label>
                                                        <input type="text" class="form-control" id="stakeholder_no" name="stakeholder_no" value="<?PHP echo $stakeholder['stakeholder_no']; ?>" required />
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="stakeholder_name" class="form-label fw-bold small">Name / Organization *</label>
                                                        <input type="text" class="form-control" id="stakeholder_name" name="stakeholder_name" value="<?PHP echo $stakeholder['stakeholder_name']; ?>" required />
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="stakeholder_type" class="form-label fw-bold small">Type</label>
                                                        <select class="form-select" id="stakeholder_type" name="stakeholder_type">
                                                            <option value="individual" <?PHP echo $stakeholder['stakeholder_type'] == 'individual' ? 'selected' : ''; ?>>Individual</option>
                                                            <option value="organization" <?PHP echo $stakeholder['stakeholder_type'] == 'organization' ? 'selected' : ''; ?>>Organization</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="stakeholder_idno" class="form-label fw-bold small">ID / Registration Number</label>
                                                        <input type="text" class="form-control" id="stakeholder_idno" name="stakeholder_idno" value="<?PHP echo $stakeholder['stakeholder_idno']; ?>" />
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="stakeholder_phone" class="form-label fw-bold small">Phone Number</label>
                                                        <input type="text" class="form-control" id="stakeholder_phone" name="stakeholder_phone" value="<?PHP echo $stakeholder['stakeholder_phone']; ?>" />
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="stakeholder_email" class="form-label fw-bold small">Email</label>
                                                        <input type="email" class="form-control" id="stakeholder_email" name="stakeholder_email" value="<?PHP echo $stakeholder['stakeholder_email']; ?>" />
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="stakeholder_address" class="form-label fw-bold small">Address</label>
                                                    <textarea class="form-control" id="stakeholder_address" name="stakeholder_address" rows="2"><?PHP echo $stakeholder['stakeholder_address']; ?></textarea>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="stakeholder_bank" class="form-label fw-bold small">Bank Name</label>
                                                        <input type="text" class="form-control" id="stakeholder_bank" name="stakeholder_bank" value="<?PHP echo $stakeholder['stakeholder_bank']; ?>" />
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="stakeholder_accno" class="form-label fw-bold small">Bank Account Number</label>
                                                        <input type="text" class="form-control" id="stakeholder_accno" name="stakeholder_accno" value="<?PHP echo $stakeholder['stakeholder_accno']; ?>" />
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="cust_id" class="form-label fw-bold small">Link to Customer (Optional)</label>
                                                    <select class="form-select" id="cust_id" name="cust_id">
                                                        <option value="">-- Not linked to any customer --</option>
                                                        <?PHP foreach ($customers as $cust): ?>
                                                        <option value="<?PHP echo $cust['cust_id']; ?>" <?PHP echo $stakeholder['cust_id'] == $cust['cust_id'] ? 'selected' : ''; ?>><?PHP echo $cust['cust_no'] . ' - ' . $cust['cust_name']; ?></option>
                                                        <?PHP endforeach; ?>
                                                    </select>
                                                </div>

                                                <div class="row align-items-center mt-2">
                                                    <div class="col-md-6">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="stakeholder_active" name="stakeholder_active" value="1" <?PHP if ($stakeholder['stakeholder_active'] == 1) echo 'checked'; ?> />
                                                            <label class="form-check-label fw-bold small" for="stakeholder_active">Active Stakeholder</label>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 text-end">
                                                        <small class="text-muted">Last updated: <?PHP echo date("d.m.Y H:i", $stakeholder['stakeholder_lastupd']); ?></small>
                                                    </div>
                                                </div>

                                                <div class="d-grid mt-4">
                                                    <button type="submit" name="update" class="btn btn-primary btn-lg">
                                                        <i class="fa fa-save"></i> Save Changes
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="shares" role="tabpanel" aria-labelledby="shares-tab">
                            <div class="card shadow-sm">
                                <div class="card-header bg-success text-white py-2 d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><i class="fa fa-certificate"></i> Share Transaction History</h6>
                                    <a href="share_buy.php?sh=<?PHP echo $stakeholder_id; ?>" class="btn btn-sm btn-light py-0 px-2 fw-bold"><i class="fa fa-plus-circle"></i> Buy Shares</a>
                                </div>
                                <div class="card-body">
                                    <?PHP if (count($shares_history) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover align-middle">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Type</th>
                                                    <th>Shares</th>
                                                    <th>Value</th>
                                                    <th>Receipt</th>
                                                    <th>Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?PHP foreach ($shares_history as $sh): ?>
                                                <tr>
                                                    <td><?PHP echo date("d.m.Y", $sh['ss_date']); ?></td>
                                                    <td>
                                                        <?PHP 
                                                        $type_badge = array(
                                                            'buy' => 'success',
                                                            'sell' => 'warning',
                                                            'transfer_in' => 'info',
                                                            'transfer_out' => 'secondary',
                                                            'dividend' => 'primary'
                                                        );
                                                        $badge_class = $type_badge[$sh['ss_type']] ?? 'secondary';
                                                        ?>
                                                        <span class="badge bg-<?PHP echo $badge_class; ?>"><?PHP echo ucfirst(str_replace('_', ' ', $sh['ss_type'])); ?></span>
                                                    </td>
                                                    <td class="fw-bold <?PHP echo $sh['ss_amount'] >= 0 ? 'text-success' : 'text-danger'; ?>"><?PHP echo ($sh['ss_amount'] >= 0 ? '+' : '') . number_format($sh['ss_amount']); ?></td>
                                                    <td class="fw-bold"><?PHP echo number_format($sh['ss_value'], 2); ?> <?PHP echo $_SESSION['set_cur']; ?></td>
                                                    <td><?PHP echo $sh['ss_receipt'] ?: '-'; ?></td>
                                                    <td><?PHP echo $sh['ss_notes'] ?: '-'; ?></td>
                                                </tr>
                                                <?PHP endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="alert alert-info py-2 mb-0 mt-3 shadow-sm" role="alert">
                                        <div class="row align-items-center text-center text-md-start">
                                            <div class="col-md-6"><strong>Current Holdings:</strong></div>
                                            <div class="col-md-6 text-md-end h5 mb-0 fw-bold"><?PHP echo number_format($total_shares); ?> shares = <?PHP echo number_format($total_value, 2); ?> <?PHP echo $_SESSION['set_cur']; ?></div>
                                        </div>
                                    </div>
                                    <?PHP else: ?>
                                    <div class="alert alert-warning mb-0">
                                        <i class="fa fa-info-circle"></i> No share transactions recorded yet. <a href="share_buy.php?sh=<?PHP echo $stakeholder_id; ?>" class="alert-link">Record first share purchase</a>.
                                    </div>
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
