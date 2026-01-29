<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();

$timestamp = time();
$error_message = '';
$success_message = '';

getShareValue($db_link);

$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : 0;

$start_date = strtotime("$year-01-01");
$end_date = strtotime("$year-12-31 23:59:59");

if ($month > 0) {
    $start_date = strtotime("$year-$month-01");
    $end_date = strtotime("$year-$month-" . date('t', $start_date) . " 23:59:59");
}

$sql_transactions = "SELECT ss.*, s.stakeholder_name, s.stakeholder_no 
                     FROM stakeholder_shares ss 
                     JOIN stakeholder s ON ss.stakeholder_id = s.stakeholder_id 
                     WHERE ss.ss_date >= $start_date AND ss.ss_date <= $end_date 
                     ORDER BY ss.ss_date DESC, ss.ss_id DESC";
$query_transactions = db_query($db_link, $sql_transactions);
$transactions = array();
$total_bought = 0;
$total_sold = 0;
while ($row = db_fetch_assoc($query_transactions)) {
    $transactions[] = $row;
    if ($row['ss_type'] == 'buy') {
        $total_bought += $row['ss_value'];
    } elseif ($row['ss_type'] == 'sell') {
        $total_sold += abs($row['ss_value']);
    }
}

$sql_summary = "SELECT 
                COALESCE(SUM(ss_amount), 0) as total_shares,
                COALESCE(SUM(ss_value), 0) as total_value
                FROM stakeholder_shares";
$query_summary = db_query($db_link, $sql_summary);
$summary = db_fetch_assoc($query_summary);

$sql_stakeholder_count = "SELECT COUNT(*) as count FROM stakeholder WHERE stakeholder_active = 1";
$query_count = db_query($db_link, $sql_stakeholder_count);
$stakeholder_count = db_fetch_assoc($query_count);

$years = array();
for ($i = date('Y'); $i >= date('Y') - 5; $i--) {
    $years[] = $i;
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
                        <h2><i class="fa fa-line-chart"></i> Share Capital Accounting</h2>
                        <a href="stakeholder_search.php" class="btn btn-secondary"><i class="fa fa-briefcase"></i> Manage Stakeholders</a>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border-start border-success border-4 shadow-sm">
                                <div class="card-body">
                                    <div class="text-success fw-bold text-uppercase mb-1 small">Total Share Capital</div>
                                    <div class="h4 mb-0 fw-bold"><?PHP echo number_format($summary['total_value'], 2); ?> <?PHP echo $_SESSION['set_cur']; ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-start border-primary border-4 shadow-sm">
                                <div class="card-body">
                                    <div class="text-primary fw-bold text-uppercase mb-1 small">Total Shares Issued</div>
                                    <div class="h4 mb-0 fw-bold"><?PHP echo number_format($summary['total_shares']); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-start border-info border-4 shadow-sm">
                                <div class="card-body">
                                    <div class="text-info fw-bold text-uppercase mb-1 small">Active Shareholders</div>
                                    <div class="h4 mb-0 fw-bold"><?PHP echo number_format($stakeholder_count['count']); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-start border-warning border-4 shadow-sm">
                                <div class="card-body">
                                    <div class="text-warning fw-bold text-uppercase mb-1 small">Current Share Price</div>
                                    <div class="h4 mb-0 fw-bold"><?PHP echo number_format($_SESSION['share_value'], 2); ?> <?PHP echo $_SESSION['set_cur']; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white py-2">
                            <h6 class="mb-0">Filter Transactions</h6>
                        </div>
                        <div class="card-body">
                            <form action="books_shares.php" method="get" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small">Year</label>
                                    <select class="form-select" name="year">
                                        <?PHP foreach ($years as $y): ?>
                                        <option value="<?PHP echo $y; ?>" <?PHP echo $y == $year ? 'selected' : ''; ?>><?PHP echo $y; ?></option>
                                        <?PHP endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small">Month</label>
                                    <select class="form-select" name="month">
                                        <option value="0" <?PHP echo $month == 0 ? 'selected' : ''; ?>>All Months</option>
                                        <?PHP 
                                        $months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
                                        for ($m = 1; $m <= 12; $m++): ?>
                                        <option value="<?PHP echo $m; ?>" <?PHP echo $m == $month ? 'selected' : ''; ?>><?PHP echo $months[$m-1]; ?></option>
                                        <?PHP endfor; ?>
                                    </select>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100"><i class="fa fa-filter"></i> Filter</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-success text-white py-2">
                                    <h6 class="mb-0">Period Summary: Capital Inflows</h6>
                                </div>
                                <div class="card-body text-center">
                                    <div class="display-5 text-success fw-bold"><?PHP echo number_format($total_bought, 2); ?> <?PHP echo $_SESSION['set_cur']; ?></div>
                                    <p class="text-muted mb-0">Share purchases (new capital)</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-header bg-warning text-dark py-2">
                                    <h6 class="mb-0">Period Summary: Capital Outflows</h6>
                                </div>
                                <div class="card-body text-center">
                                    <div class="display-5 text-warning fw-bold"><?PHP echo number_format($total_sold, 2); ?> <?PHP echo $_SESSION['set_cur']; ?></div>
                                    <p class="text-muted mb-0">Share redemptions (capital returned)</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-secondary text-white py-2">
                            <h6 class="mb-0">Share Transactions for <?PHP echo $month > 0 ? $months[$month-1] . ' ' : ''; ?><?PHP echo $year; ?></h6>
                        </div>
                        <div class="card-body">
                            <?PHP if (count($transactions) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Date</th>
                                            <th>Stakeholder</th>
                                            <th>Type</th>
                                            <th>Shares</th>
                                            <th>Value</th>
                                            <th>Receipt</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?PHP foreach ($transactions as $t): ?>
                                        <tr>
                                            <td><?PHP echo date("d.m.Y", $t['ss_date']); ?></td>
                                            <td><a href="stakeholder.php?id=<?PHP echo $t['stakeholder_id']; ?>"><?PHP echo $t['stakeholder_name']; ?></a></td>
                                            <td>
                                                <?PHP 
                                                $type_badge = array(
                                                    'buy' => 'success',
                                                    'sell' => 'warning',
                                                    'transfer_in' => 'info',
                                                    'transfer_out' => 'secondary',
                                                    'dividend' => 'primary'
                                                );
                                                $badge_class = $type_badge[$t['ss_type']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?PHP echo $badge_class; ?>"><?PHP echo ucfirst(str_replace('_', ' ', $t['ss_type'])); ?></span>
                                            </td>
                                            <td class="fw-bold <?PHP echo $t['ss_amount'] >= 0 ? 'text-success' : 'text-danger'; ?>"><?PHP echo ($t['ss_amount'] >= 0 ? '+' : '') . number_format($t['ss_amount']); ?></td>
                                            <td class="fw-bold"><?PHP echo number_format($t['ss_value'], 2); ?> <?PHP echo $_SESSION['set_cur']; ?></td>
                                            <td><?PHP echo $t['ss_receipt'] ?: '-'; ?></td>
                                            <td><small><?PHP echo $t['ss_notes'] ?: '-'; ?></small></td>
                                        </tr>
                                        <?PHP endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?PHP else: ?>
                            <div class="alert alert-info mb-0">
                                <i class="fa fa-info-circle"></i> No share transactions found for this period.
                            </div>
                            <?PHP endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?PHP include 'includes/bootstrap_footer.php'; ?>
    </body>
</html>
