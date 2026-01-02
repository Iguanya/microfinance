<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();
$timestamp = time();

//NEW INCOME-Button
if(isset($_POST['incnew'])){
        
        //Sanitize user input
        $inctype_id = sanitize($db_link, $_POST['inctype_id']);
        $inc_amount = sanitize($db_link, $_POST['inc_amount']);
        $inc_date = strtotime(sanitize($db_link, $_POST['inc_date']));
        $inc_text = sanitize($db_link, $_POST['inc_text']);
        $inc_recipient = sanitize($db_link, $_POST['cust_id']);
        $inc_loan = sanitize($db_link, $_POST['loan_id']);
        $inc_receipt = sanitize($db_link, $_POST['inc_receipt']);
        if($inc_recipient == 0) $inc_recipient = NULL;
        if($inc_loan == 0) $inc_loan = NULL;
        
        //Insert into INCOMES
        $sql_incnew = "INSERT INTO incomes (cust_id, loan_id, inctype_id, inc_amount, inc_date, inc_receipt, inc_text, inc_created, user_id) VALUES ('$inc_recipient', '$inc_loan', '$inctype_id', '$inc_amount', '$inc_date', '$inc_receipt', '$inc_text', '$timestamp', '$_SESSION[log_id]')";
        $query_incnew = db_query($db_link, $sql_incnew);
        checkSQL($db_link, $query_incnew);
}

//Select recent incomes from INCOMES
$sixtydays = time() - convertDays(60);
$sql_inccur = "SELECT * FROM incomes LEFT JOIN inctype ON incomes.inctype_id = inctype.inctype_id LEFT JOIN customer ON incomes.cust_id = customer.cust_id WHERE inc_date > $sixtydays ORDER BY inc_date DESC, inc_receipt DESC, incomes.cust_id";
$query_inccur = db_query($db_link, $sql_inccur);
checkSQL($db_link, $query_inccur);

//Select Types of Incomes from INCTYPE
$sql_inctype = "SELECT * FROM inctype ORDER BY inctype_type";
$query_inctype = db_query($db_link, $sql_inctype);
checkSQL($db_link, $query_inctype);

//Select Customers from CUSTOMER
$sql_custfrom = "SELECT * FROM customer WHERE cust_active = 1";
$query_custfrom = db_query($db_link, $sql_custfrom);
checkSQL($db_link, $query_custfrom);
$custfrom = array();
while ($row_custfrom = db_fetch_assoc($query_custfrom)){
        $custfrom[] = $row_custfrom;
};

//Select Loans from LOANS
$sql_loans = "SELECT * FROM loans INNER JOIN customer ON loans.cust_id = customer.cust_id WHERE loanstatus_id IN (1,2) ORDER BY cust_no, loan_no";
$query_loans = db_query($db_link, $sql_loans);
checkSQL($db_link, $query_loans);
$loans = array();
while ($row_loans = db_fetch_assoc($query_loans)){
        $loans[] = $row_loans;
};
?>

<!DOCTYPE HTML>
<html>
<?PHP include 'includes/bootstrap_header.php'; ?>

<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="mb-4">Income Management</h2>

                <nav class="mb-3">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link" href="start.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="books_expense.php">Expenses</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="books_income.php">Incomes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="books_annual.php">Annual Accounts</a>
                        </li>
                    </ul>
                </nav>

                <div class="row">
                    <div class="col-md-5">
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <strong>New Income Entry</strong>
                            </div>
                            <div class="card-body">
                                <form action="books_income.php" method="post">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Date</label>
                                        <input type="text" id="datepicker" name="inc_date" class="form-control" value="<?PHP echo date("d.m.Y", $timestamp); ?>" />
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label">Type</label>
                                        <select name="inctype_id" class="form-select">
                                            <?PHP
                                            $no_show = array(2, 4, 5);
                                            while ($row_inctype = db_fetch_assoc($query_inctype)) {
                                                if (!in_array($row_inctype['inctype_id'], $no_show)) {
                                                    echo '<option value="' . $row_inctype['inctype_id'] . '">' . $row_inctype['inctype_type'] . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label">Amount</label>
                                        <input type="number" name="inc_amount" class="form-control" placeholder="<?PHP echo $_SESSION['set_cur']; ?>" />
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label">Receipt No</label>
                                        <input type="text" name="inc_receipt" class="form-control" />
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label">Received From</label>
                                        <select name="cust_id" class="form-select">
                                            <option value="0" selected>N/A</option>
                                            <?PHP
                                            foreach ($custfrom as $cf) {
                                                echo '<option value="' . $cf['cust_id'] . '">' . $cf['cust_no'] . ' ' . $cf['cust_name'] . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label">Loan (Optional)</label>
                                        <select name="loan_id" class="form-select">
                                            <option value="0" selected>N/A</option>
                                            <?PHP
                                            foreach ($loans as $ln) {
                                                echo '<option value="' . $ln['loan_id'] . '">' . $ln['loan_no'] . ' (' . $ln['cust_name'] . ')</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label">Details</label>
                                        <input type="text" name="inc_text" class="form-control" />
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" name="incnew" class="btn btn-success btn-lg">Add Income</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <strong>Recent Incomes (Last 60 Days)</strong>
                            </div>
                            <div class="card-body table-responsive">
                                <table class="table table-striped table-hover table-sm">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th class="text-end">Amount</th>
                                            <th>From</th>
                                            <th>Receipt</th>
                                            <th>Details</th>
                                            <th width="50px"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?PHP
                                        $no_delete = array(2, 4, 5);
                                        while ($row_inccur = db_fetch_assoc($query_inccur)) {
                                            echo '<tr>      
                                                <td>' . date("d.m.Y", $row_inccur['inc_date']) . '</td>
                                                <td>' . $row_inccur['inctype_type'] . '</td>
                                                <td class="text-end">' . number_format($row_inccur['inc_amount']) . ' ' . $_SESSION['set_cur'] . '</td>
                                                <td>' . $row_inccur['cust_name'] . ' (' . $row_inccur['cust_no'] . ')</td>
                                                <td>' . $row_inccur['inc_receipt'] . '</td>
                                                <td>' . $row_inccur['inc_text'] . '</td>
                                                <td>';
                                            if ($_SESSION['log_delete'] == 1 and (!in_array($row_inccur['inctype_id'], $no_delete) or $row_inccur['cust_id'] == 0)) echo '<a href="books_income_del.php?inc_id=' . $row_inccur['inc_id'] . '" class="btn btn-sm btn-danger">Delete</a>';
                                            echo '</td>   
                                                </tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?PHP include 'includes/bootstrap_footer.php'; ?>
    <script>
        $(function() {
            $("#datepicker").datepicker({
                dateFormat: 'dd.mm.yy'
            });
        });
    </script>
</body>
</html>
