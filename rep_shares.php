<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();

getShareValue($db_link);

$sql_register = "SELECT s.*, 
                 COALESCE(SUM(ss.ss_amount), 0) as total_shares,
                 COALESCE(SUM(ss.ss_value), 0) as total_value
                 FROM stakeholder s
                 LEFT JOIN stakeholder_shares ss ON s.stakeholder_id = ss.stakeholder_id
                 GROUP BY s.stakeholder_id
                 HAVING total_shares > 0
                 ORDER BY total_shares DESC, s.stakeholder_name";
$query_register = db_query($db_link, $sql_register);
$shareholders = array();
$grand_total_shares = 0;
$grand_total_value = 0;
while ($row = db_fetch_assoc($query_register)) {
    $shareholders[] = $row;
    $grand_total_shares += $row['total_shares'];
    $grand_total_value += $row['total_value'];
}

$sql_total_authorized = "SELECT * FROM settings WHERE set_short = 'SET_AUTHSHARES'";
$query_authorized = db_query($db_link, $sql_total_authorized);
$authorized_shares = db_fetch_assoc($query_authorized);
$total_authorized = $authorized_shares ? $authorized_shares['set_value'] : 100000;
$unissued_shares = $total_authorized - $grand_total_shares;
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
                        <h2><i class="fa fa-certificate"></i> Share Register Report</h2>
                        <div>
                            <a href="books_shares.php" class="btn btn-primary"><i class="fa fa-line-chart"></i> Share Accounting</a>
                            <button onclick="window.print()" class="btn btn-secondary"><i class="fa fa-print"></i> Print</button>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card border-start border-success border-4 shadow-sm">
                                <div class="card-body">
                                    <div class="text-success fw-bold text-uppercase mb-1 small">Total Issued Shares</div>
                                    <div class="h4 mb-0 fw-bold"><?PHP echo number_format($grand_total_shares); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-start border-primary border-4 shadow-sm">
                                <div class="card-body">
                                    <div class="text-primary fw-bold text-uppercase mb-1 small">Share Capital Value</div>
                                    <div class="h4 mb-0 fw-bold"><?PHP echo number_format($grand_total_value, 2); ?> <?PHP echo $_SESSION['set_cur']; ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-start border-info border-4 shadow-sm">
                                <div class="card-body">
                                    <div class="text-info fw-bold text-uppercase mb-1 small">Shareholders</div>
                                    <div class="h4 mb-0 fw-bold"><?PHP echo count($shareholders); ?></div>
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
                            <h6 class="mb-0">Register of Shareholders</h6>
                        </div>
                        <div class="card-body">
                            <?PHP if (count($shareholders) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Stakeholder No</th>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>ID Number</th>
                                            <th>Phone</th>
                                            <th class="text-end">Shares Held</th>
                                            <th class="text-end">Value</th>
                                            <th class="text-end">% Ownership</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?PHP 
                                        $rank = 1;
                                        foreach ($shareholders as $sh): 
                                            $ownership = $grand_total_shares > 0 ? ($sh['total_shares'] / $grand_total_shares) * 100 : 0;
                                        ?>
                                        <tr>
                                            <td><?PHP echo $rank++; ?></td>
                                            <td class="fw-bold"><?PHP echo $sh['stakeholder_no']; ?></td>
                                            <td><a href="stakeholder.php?id=<?PHP echo $sh['stakeholder_id']; ?>"><?PHP echo $sh['stakeholder_name']; ?></a></td>
                                            <td><span class="badge bg-<?PHP echo $sh['stakeholder_type'] == 'individual' ? 'primary' : 'warning'; ?>"><?PHP echo ucfirst($sh['stakeholder_type']); ?></span></td>
                                            <td><?PHP echo $sh['stakeholder_idno'] ?: '-'; ?></td>
                                            <td><?PHP echo $sh['stakeholder_phone'] ?: '-'; ?></td>
                                            <td class="text-end fw-bold"><?PHP echo number_format($sh['total_shares']); ?></td>
                                            <td class="text-end fw-bold text-success"><?PHP echo number_format($sh['total_value'], 2); ?> <?PHP echo $_SESSION['set_cur']; ?></td>
                                            <td class="text-end">
                                                <div class="progress" style="height: 20px; min-width: 80px;">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?PHP echo $ownership; ?>%;" aria-valuenow="<?PHP echo $ownership; ?>" aria-valuemin="0" aria-valuemax="100"><?PHP echo number_format($ownership, 2); ?>%</div>
                                                </div>
                                            </td>
                                        </tr>
                                        <?PHP endforeach; ?>
                                    </tbody>
                                    <tfoot class="table-secondary">
                                        <tr class="fw-bold">
                                            <td colspan="6" class="text-end">TOTALS:</td>
                                            <td class="text-end"><?PHP echo number_format($grand_total_shares); ?></td>
                                            <td class="text-end text-success"><?PHP echo number_format($grand_total_value, 2); ?> <?PHP echo $_SESSION['set_cur']; ?></td>
                                            <td class="text-end">100.00%</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <?PHP else: ?>
                            <div class="alert alert-info mb-0">
                                <i class="fa fa-info-circle"></i> No shareholders with shares found. <a href="stakeholder_search.php" class="alert-link">Add stakeholders</a> and record share purchases.
                            </div>
                            <?PHP endif; ?>
                        </div>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-header bg-secondary text-white py-2">
                            <h6 class="mb-0">Share Capital Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th class="table-light">Authorized Share Capital</th>
                                            <td class="text-end"><?PHP echo number_format($total_authorized); ?> shares</td>
                                        </tr>
                                        <tr>
                                            <th class="table-light">Issued Shares</th>
                                            <td class="text-end text-success fw-bold"><?PHP echo number_format($grand_total_shares); ?> shares</td>
                                        </tr>
                                        <tr>
                                            <th class="table-light">Unissued Shares</th>
                                            <td class="text-end text-muted"><?PHP echo number_format($unissued_shares); ?> shares</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tr>
                                            <th class="table-light">Current Share Price</th>
                                            <td class="text-end"><?PHP echo number_format($_SESSION['share_value'], 2); ?> <?PHP echo $_SESSION['set_cur']; ?></td>
                                        </tr>
                                        <tr>
                                            <th class="table-light">Total Share Capital</th>
                                            <td class="text-end text-success fw-bold"><?PHP echo number_format($grand_total_value, 2); ?> <?PHP echo $_SESSION['set_cur']; ?></td>
                                        </tr>
                                        <tr>
                                            <th class="table-light">Report Date</th>
                                            <td class="text-end"><?PHP echo date("d.m.Y H:i"); ?></td>
                                        </tr>
                                    </table>
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
