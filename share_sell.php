<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();

$timestamp = time();
$error_message = '';

if (!isset($_GET['sh'])) {
    header('Location: stakeholder_search.php');
    exit;
}

$stakeholder_id = sanitize($db_link, $_GET['sh']);

$sql_stakeholder = "SELECT * FROM stakeholder WHERE stakeholder_id = '$stakeholder_id'";
$query_stakeholder = db_query($db_link, $sql_stakeholder);
$stakeholder = db_fetch_assoc($query_stakeholder);

if (!$stakeholder) {
    header('Location: stakeholder_search.php');
    exit;
}

getShareValue($db_link);

$sql_shares = "SELECT COALESCE(SUM(ss_amount), 0) as total_shares, COALESCE(SUM(ss_value), 0) as total_value 
               FROM stakeholder_shares WHERE stakeholder_id = '$stakeholder_id'";
$query_shares = db_query($db_link, $sql_shares);
$current_shares = db_fetch_assoc($query_shares);

if (isset($_POST['sell_shares'])) {
    $ss_date = strtotime(sanitize($db_link, $_POST['ss_date']));
    $ss_amount = intval(sanitize($db_link, $_POST['ss_amount']));
    $ss_receipt = sanitize($db_link, $_POST['ss_receipt']);
    $ss_notes = sanitize($db_link, $_POST['ss_notes']);
    $ss_value = $_SESSION['share_value'] * $ss_amount;

    if ($ss_amount <= 0) {
        $error_message = 'Please enter a valid number of shares.';
    } elseif ($ss_amount > $current_shares['total_shares']) {
        $error_message = 'Cannot sell more shares than currently held (' . $current_shares['total_shares'] . ' shares available).';
    } else {
        $sql_insert = "INSERT INTO stakeholder_shares (stakeholder_id, ss_date, ss_type, ss_amount, ss_value, ss_receipt, ss_notes, ss_created, user_id) 
                       VALUES ('$stakeholder_id', '$ss_date', 'sell', '-$ss_amount', '-$ss_value', '$ss_receipt', '$ss_notes', $timestamp, '$_SESSION[log_id]')";
        $query_insert = db_query($db_link, $sql_insert);
        
        if ($query_insert) {
            header('Location: stakeholder.php?id=' . $stakeholder_id);
            exit;
        } else {
            $error_message = 'Error recording share sale: ' . db_error($db_link);
        }
    }
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
                        <h2><i class="fa fa-minus-circle"></i> Sell Shares</h2>
                        <a href="stakeholder.php?id=<?PHP echo $stakeholder_id; ?>" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back to Stakeholder</a>
                    </div>

                    <p class="lead">Sell shares for <strong><?PHP echo $stakeholder['stakeholder_name']; ?> (<?PHP echo $stakeholder['stakeholder_no']; ?>)</strong></p>

                    <?PHP if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa fa-exclamation-circle"></i> <?PHP echo $error_message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?PHP endif; ?>

                    <?PHP if ($current_shares['total_shares'] <= 0): ?>
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i> This stakeholder has no shares to sell.
                        <a href="stakeholder.php?id=<?PHP echo $stakeholder_id; ?>" class="btn btn-sm btn-secondary ms-3">Go Back</a>
                    </div>
                    <?PHP else: ?>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-4">
                                <div class="card-header bg-warning text-dark py-2">
                                    <h6 class="mb-0">Record Share Sale</h6>
                                </div>
                                <div class="card-body">
                                    <form action="share_sell.php?sh=<?PHP echo $stakeholder_id; ?>" method="post">
                                        <div class="mb-3">
                                            <label for="ss_date" class="form-label fw-bold small">Date *</label>
                                            <input type="text" class="form-control datepicker" id="ss_date" name="ss_date" value="<?PHP echo date("d.m.Y", $timestamp); ?>" placeholder="DD.MM.YYYY" required />
                                        </div>

                                        <div class="mb-3">
                                            <label for="ss_receipt" class="form-label fw-bold small">Slip Number</label>
                                            <input type="text" class="form-control" id="ss_receipt" name="ss_receipt" />
                                        </div>

                                        <div class="mb-3">
                                            <label for="ss_amount" class="form-label fw-bold small">Number of Shares to Sell *</label>
                                            <select class="form-select" id="ss_amount" name="ss_amount" required>
                                                <?PHP
                                                $max_shares = min($current_shares['total_shares'], 100);
                                                for ($i = 1; $i <= $max_shares; $i++) {
                                                    echo '<option value="'.$i.'">'.$i.' @ '.number_format($_SESSION['share_value'] * $i, 2).' '.$_SESSION['set_cur'].'</option>';
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="ss_notes" class="form-label fw-bold small">Notes</label>
                                            <textarea class="form-control" id="ss_notes" name="ss_notes" rows="2"></textarea>
                                        </div>

                                        <div class="d-grid">
                                            <button type="submit" name="sell_shares" class="btn btn-warning btn-lg">
                                                <i class="fa fa-minus-circle"></i> Sell Shares
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="card shadow-sm">
                                <div class="card-header bg-info text-white py-2">
                                    <h6 class="mb-0">Current Holdings</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <h6 class="text-muted text-uppercase small fw-bold">Total Shares</h6>
                                            <div class="display-6 text-success fw-bold"><?PHP echo number_format($current_shares['total_shares']); ?></div>
                                        </div>
                                        <div class="col-md-4">
                                            <h6 class="text-muted text-uppercase small fw-bold">Total Value</h6>
                                            <div class="display-6 text-primary fw-bold"><?PHP echo number_format($current_shares['total_value'], 2); ?> <?PHP echo $_SESSION['set_cur']; ?></div>
                                        </div>
                                        <div class="col-md-4">
                                            <h6 class="text-muted text-uppercase small fw-bold">Share Price</h6>
                                            <div class="display-6 text-info fw-bold"><?PHP echo number_format($_SESSION['share_value'], 2); ?> <?PHP echo $_SESSION['set_cur']; ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?PHP endif; ?>
                </div>
            </div>
        </div>

        <?PHP include 'includes/bootstrap_footer.php'; ?>
    </body>
</html>
