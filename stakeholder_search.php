<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();

$search_results = array();
$search_performed = false;

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_performed = true;
    $search = sanitize($db_link, $_GET['search']);
    $sql_search = "SELECT s.*, 
                   COALESCE(SUM(ss.ss_amount), 0) as total_shares,
                   COALESCE(SUM(ss.ss_value), 0) as total_value
                   FROM stakeholder s
                   LEFT JOIN stakeholder_shares ss ON s.stakeholder_id = ss.stakeholder_id
                   WHERE s.stakeholder_name LIKE '%$search%' 
                   OR s.stakeholder_no LIKE '%$search%'
                   OR s.stakeholder_idno LIKE '%$search%'
                   OR s.stakeholder_phone LIKE '%$search%'
                   GROUP BY s.stakeholder_id
                   ORDER BY s.stakeholder_name";
    $query_search = db_query($db_link, $sql_search);
    while ($row = db_fetch_assoc($query_search)) {
        $search_results[] = $row;
    }
}

$sql_all = "SELECT s.*, 
            COALESCE(SUM(ss.ss_amount), 0) as total_shares,
            COALESCE(SUM(ss.ss_value), 0) as total_value
            FROM stakeholder s
            LEFT JOIN stakeholder_shares ss ON s.stakeholder_id = ss.stakeholder_id
            GROUP BY s.stakeholder_id
            ORDER BY s.stakeholder_name";
$query_all = db_query($db_link, $sql_all);
$all_stakeholders = array();
while ($row = db_fetch_assoc($query_all)) {
    $all_stakeholders[] = $row;
}
?>

<!DOCTYPE HTML>
<html>
    <?PHP include 'includes/bootstrap_header.php'; ?>
    <body>

        <div class="container-fluid mt-4">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2><i class="fa fa-briefcase"></i> Stakeholders & Shareholders</h2>
                        <a href="stakeholder_new.php" class="btn btn-success"><i class="fa fa-plus-circle"></i> New Stakeholder</a>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white py-2">
                            <h6 class="mb-0">Search Stakeholders</h6>
                        </div>
                        <div class="card-body">
                            <form action="stakeholder_search.php" method="get">
                                <div class="row">
                                    <div class="col-md-8">
                                        <input type="text" class="form-control" name="search" placeholder="Search by name, ID number, stakeholder number or phone..." value="<?PHP echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" />
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-grid gap-2 d-md-flex">
                                            <button type="submit" class="btn btn-primary flex-grow-1"><i class="fa fa-search"></i> Search</button>
                                            <a href="stakeholder_search.php" class="btn btn-secondary"><i class="fa fa-times"></i> Clear</a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?PHP if ($search_performed): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-info text-white py-2">
                            <h6 class="mb-0">Search Results (<?PHP echo count($search_results); ?> found)</h6>
                        </div>
                        <div class="card-body">
                            <?PHP if (count($search_results) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Stakeholder No</th>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>ID Number</th>
                                            <th>Phone</th>
                                            <th>Total Shares</th>
                                            <th>Share Value</th>
                                            <th>Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?PHP foreach ($search_results as $row): ?>
                                        <tr>
                                            <td class="fw-bold"><?PHP echo $row['stakeholder_no']; ?></td>
                                            <td><?PHP echo $row['stakeholder_name']; ?></td>
                                            <td><span class="badge bg-<?PHP echo $row['stakeholder_type'] == 'individual' ? 'primary' : 'warning'; ?>"><?PHP echo ucfirst($row['stakeholder_type']); ?></span></td>
                                            <td><?PHP echo $row['stakeholder_idno'] ?: '-'; ?></td>
                                            <td><?PHP echo $row['stakeholder_phone'] ?: '-'; ?></td>
                                            <td class="fw-bold"><?PHP echo number_format($row['total_shares']); ?></td>
                                            <td class="fw-bold text-success"><?PHP echo number_format($row['total_value'], 2); ?> <?PHP echo $_SESSION['set_cur']; ?></td>
                                            <td><span class="badge bg-<?PHP echo $row['stakeholder_active'] == 1 ? 'success' : 'secondary'; ?>"><?PHP echo $row['stakeholder_active'] == 1 ? 'Active' : 'Inactive'; ?></span></td>
                                            <td class="text-center">
                                                <a href="stakeholder.php?id=<?PHP echo $row['stakeholder_id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fa fa-eye"></i> View</a>
                                            </td>
                                        </tr>
                                        <?PHP endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?PHP else: ?>
                            <div class="alert alert-warning mb-0">
                                <i class="fa fa-exclamation-triangle"></i> No stakeholders found matching your search criteria.
                            </div>
                            <?PHP endif; ?>
                        </div>
                    </div>
                    <?PHP endif; ?>

                    <div class="card shadow-sm">
                        <div class="card-header bg-secondary text-white py-2 d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">All Stakeholders (<?PHP echo count($all_stakeholders); ?>)</h6>
                        </div>
                        <div class="card-body">
                            <?PHP if (count($all_stakeholders) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Stakeholder No</th>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>ID Number</th>
                                            <th>Phone</th>
                                            <th>Total Shares</th>
                                            <th>Share Value</th>
                                            <th>Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?PHP foreach ($all_stakeholders as $row): ?>
                                        <tr>
                                            <td class="fw-bold"><?PHP echo $row['stakeholder_no']; ?></td>
                                            <td><?PHP echo $row['stakeholder_name']; ?></td>
                                            <td><span class="badge bg-<?PHP echo $row['stakeholder_type'] == 'individual' ? 'primary' : 'warning'; ?>"><?PHP echo ucfirst($row['stakeholder_type']); ?></span></td>
                                            <td><?PHP echo $row['stakeholder_idno'] ?: '-'; ?></td>
                                            <td><?PHP echo $row['stakeholder_phone'] ?: '-'; ?></td>
                                            <td class="fw-bold"><?PHP echo number_format($row['total_shares']); ?></td>
                                            <td class="fw-bold text-success"><?PHP echo number_format($row['total_value'], 2); ?> <?PHP echo $_SESSION['set_cur']; ?></td>
                                            <td><span class="badge bg-<?PHP echo $row['stakeholder_active'] == 1 ? 'success' : 'secondary'; ?>"><?PHP echo $row['stakeholder_active'] == 1 ? 'Active' : 'Inactive'; ?></span></td>
                                            <td class="text-center">
                                                <a href="stakeholder.php?id=<?PHP echo $row['stakeholder_id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fa fa-eye"></i> View</a>
                                            </td>
                                        </tr>
                                        <?PHP endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?PHP else: ?>
                            <div class="alert alert-info mb-0">
                                <i class="fa fa-info-circle"></i> No stakeholders registered yet. <a href="stakeholder_new.php" class="alert-link">Add the first stakeholder</a>.
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
