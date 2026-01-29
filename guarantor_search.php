<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();

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

$search_results = array();
$search_performed = false;

if (isset($_POST['search']) || isset($_GET['q'])) {
    $search_performed = true;
    $search_term = isset($_POST['search_term']) ? sanitize($db_link, $_POST['search_term']) : sanitize($db_link, $_GET['q']);
    $search_type = isset($_POST['search_type']) ? sanitize($db_link, $_POST['search_type']) : 'name';
    
    if ($search_type == 'name') {
        $sql_search = "SELECT g.*, c.cust_no, c.cust_name as linked_customer 
                       FROM guarantor g 
                       LEFT JOIN customer c ON g.cust_id = c.cust_id 
                       WHERE g.guarantor_name LIKE '%$search_term%' 
                       ORDER BY g.guarantor_name LIMIT 50";
    } elseif ($search_type == 'phone') {
        $sql_search = "SELECT g.*, c.cust_no, c.cust_name as linked_customer 
                       FROM guarantor g 
                       LEFT JOIN customer c ON g.cust_id = c.cust_id 
                       WHERE g.guarantor_phone LIKE '%$search_term%' 
                       ORDER BY g.guarantor_name LIMIT 50";
    } elseif ($search_type == 'idno') {
        $sql_search = "SELECT g.*, c.cust_no, c.cust_name as linked_customer 
                       FROM guarantor g 
                       LEFT JOIN customer c ON g.cust_id = c.cust_id 
                       WHERE g.guarantor_idno LIKE '%$search_term%' 
                       ORDER BY g.guarantor_name LIMIT 50";
    } else {
        $sql_search = "SELECT g.*, c.cust_no, c.cust_name as linked_customer 
                       FROM guarantor g 
                       LEFT JOIN customer c ON g.cust_id = c.cust_id 
                       WHERE g.guarantor_no LIKE '%$search_term%' 
                       ORDER BY g.guarantor_name LIMIT 50";
    }
    
    $query_search = db_query($db_link, $sql_search);
    while ($row = db_fetch_assoc($query_search)) {
        $search_results[] = $row;
    }
}

$sql_recent = "SELECT g.*, c.cust_no, c.cust_name as linked_customer 
               FROM guarantor g 
               LEFT JOIN customer c ON g.cust_id = c.cust_id 
               ORDER BY g.guarantor_lastupd DESC LIMIT 10";
$query_recent = db_query($db_link, $sql_recent);
$recent_guarantors = array();
while ($row = db_fetch_assoc($query_recent)) {
    $recent_guarantors[] = $row;
}
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <?PHP include 'includes/bootstrap_header.php'; ?>
    <title>Search Guarantors</title>
</head>
<body>
    <?PHP include 'includes/bootstrap_header_nav.php'; ?>

    <div class="container-fluid px-4 py-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4><i class="fa fa-user-shield"></i> Guarantors</h4>
                    <a href="guarantor_new.php" class="btn btn-success">
                        <i class="fa fa-plus"></i> New Guarantor
                    </a>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white py-2">
                        <h6 class="mb-0"><i class="fa fa-search"></i> Search Guarantors</h6>
                    </div>
                    <div class="card-body">
                        <form method="post" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Search By</label>
                                <select name="search_type" class="form-select">
                                    <option value="name">Name</option>
                                    <option value="phone">Phone</option>
                                    <option value="idno">ID Number</option>
                                    <option value="no">Guarantor No</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Search Term</label>
                                <input type="text" name="search_term" class="form-control" placeholder="Enter search term..." required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" name="search" class="btn btn-primary w-100">
                                    <i class="fa fa-search"></i> Search
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <?PHP if ($search_performed): ?>
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white py-2">
                        <h6 class="mb-0"><i class="fa fa-list"></i> Search Results (<?PHP echo count($search_results); ?> found)</h6>
                    </div>
                    <div class="card-body">
                        <?PHP if (count($search_results) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Guarantor No</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>ID Number</th>
                                        <th>Linked Customer</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?PHP foreach ($search_results as $g): ?>
                                    <tr>
                                        <td><?PHP echo $g['guarantor_no']; ?></td>
                                        <td><?PHP echo htmlspecialchars($g['guarantor_name']); ?></td>
                                        <td><?PHP echo htmlspecialchars($g['guarantor_phone'] ?: '-'); ?></td>
                                        <td><?PHP echo htmlspecialchars($g['guarantor_idno'] ?: '-'); ?></td>
                                        <td>
                                            <?PHP if ($g['linked_customer']): ?>
                                                <a href="customer.php?cust=<?PHP echo $g['cust_id']; ?>"><?PHP echo htmlspecialchars($g['linked_customer']); ?></a>
                                            <?PHP else: ?>
                                                <span class="text-muted">-</span>
                                            <?PHP endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?PHP echo $g['guarantor_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?PHP echo $g['guarantor_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="guarantor.php?id=<?PHP echo $g['guarantor_id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fa fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?PHP endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?PHP else: ?>
                            <div class="alert alert-warning mb-0">No guarantors found matching your search.</div>
                        <?PHP endif; ?>
                    </div>
                </div>
                <?PHP endif; ?>

                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white py-2">
                        <h6 class="mb-0"><i class="fa fa-clock"></i> Recent Guarantors</h6>
                    </div>
                    <div class="card-body">
                        <?PHP if (count($recent_guarantors) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Guarantor No</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Linked Customer</th>
                                        <th>Last Updated</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?PHP foreach ($recent_guarantors as $g): ?>
                                    <tr>
                                        <td><?PHP echo $g['guarantor_no']; ?></td>
                                        <td><?PHP echo htmlspecialchars($g['guarantor_name']); ?></td>
                                        <td><?PHP echo htmlspecialchars($g['guarantor_phone'] ?: '-'); ?></td>
                                        <td>
                                            <?PHP if ($g['linked_customer']): ?>
                                                <a href="customer.php?cust=<?PHP echo $g['cust_id']; ?>"><?PHP echo htmlspecialchars($g['linked_customer']); ?></a>
                                            <?PHP else: ?>
                                                <span class="text-muted">-</span>
                                            <?PHP endif; ?>
                                        </td>
                                        <td><?PHP echo date('d M Y', $g['guarantor_lastupd']); ?></td>
                                        <td>
                                            <a href="guarantor.php?id=<?PHP echo $g['guarantor_id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fa fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?PHP endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?PHP else: ?>
                            <div class="alert alert-info mb-0">No guarantors registered yet. <a href="guarantor_new.php">Add the first guarantor</a>.</div>
                        <?PHP endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?PHP include 'includes/bootstrap_footer.php'; ?>
</body>
</html>
