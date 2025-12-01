<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();
$timestamp = time();

//NEW EXPENDITURE-Button
if(isset($_POST['expnew'])){
        
        //Sanitize user input
        $exptype_id = sanitize($db_link, $_POST['exptype_id']);
        $exp_amount = sanitize($db_link, $_POST['exp_amount']);
        $exp_date = strtotime(sanitize($db_link, $_POST['exp_date']));
        $exp_text = sanitize($db_link, $_POST['exp_text']);
        $exp_recipient = sanitize($db_link, $_POST['exp_recipient']);
        $exp_receipt = sanitize($db_link, $_POST['exp_receipt']);
        $exp_voucher = sanitize($db_link, $_POST['exp_voucher']);
        
        //Insert into expenses
        $sql_expnew = "INSERT INTO expenses (exptype_id, exp_amount, exp_date, exp_text, exp_recipient, exp_receipt, exp_voucher, exp_created, user_id) VALUES ('$exptype_id', '$exp_amount', '$exp_date','$exp_text', '$exp_recipient', '$exp_receipt', '$exp_voucher', '$timestamp', '$_SESSION[log_id]')";
        $query_expnew = db_query($db_link, $sql_expnew);
        checkSQL($db_link, $query_expnew);
}
                
//Select recent expenses from EXPENSES
$sixtydays = time() - convertDays(60);
$sql_expcur = "SELECT * FROM expenses LEFT JOIN exptype ON expenses.exptype_id = exptype.exptype_id WHERE exp_date > $sixtydays ORDER BY exp_date DESC, exp_voucher DESC";
$query_expcur = db_query($db_link, $sql_expcur);
checkSQL($db_link, $query_expcur);

//Select types of expenses from EXPTYPE
$sql_exptype = "SELECT * FROM exptype ORDER BY exptype_type";
$query_exptype = db_query($db_link, $sql_exptype);
checkSQL($db_link, $query_exptype);
?>

<!DOCTYPE HTML>
<html>
        <?PHP include 'includes/bootstrap_header.php'; ?>

        <body>
                <?PHP include 'includes/bootstrap_header_nav.php'; ?>

                <div class="container-fluid mt-4">
                        <div class="row">
                                <div class="col-12">
                                        <h2 class="mb-4">Expenses Management</h2>
                                        
                                        <nav class="mb-3">
                                                <ul class="nav nav-tabs" role="tablist">
                                                        <li class="nav-item">
                                                                <a class="nav-link" href="start.php">Dashboard</a>
                                                        </li>
                                                        <li class="nav-item">
                                                                <a class="nav-link active" href="books_expense.php">Expenses</a>
                                                        </li>
                                                        <li class="nav-item">
                                                                <a class="nav-link" href="books_income.php">Incomes</a>
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
                                                                        <strong>New Expense Entry</strong>
                                                                </div>
                                                                <div class="card-body">
                                                                        <form action="books_expense.php" method="post">
                                                                                <div class="form-group">
                                                                                        <label>Date</label>
                                                                                        <input type="text" id="datepicker" name="exp_date" class="form-control" value="<?PHP echo date("d.m.Y", $timestamp); ?>"/>
                                                                                </div>
                                                                                <div class="form-group">
                                                                                        <label>Type</label>
                                                                                        <select name="exptype_id" class="form-control">
                                                                                                <?PHP
                                                                                                while ($row_exptype = db_fetch_assoc($query_exptype)){
                                                                                                        echo '<option value="'.$row_exptype['exptype_id'].'">'.$row_exptype['exptype_type'].'</option>';
                                                                                                }
                                                                                                ?>
                                                                                        </select>
                                                                                </div>
                                                                                <div class="form-group">
                                                                                        <label>Amount</label>
                                                                                        <input type="number" name="exp_amount" class="form-control" placeholder="<?PHP echo $_SESSION['set_cur']; ?>" />
                                                                                </div>
                                                                                <div class="form-group">
                                                                                        <label>Paid To</label>
                                                                                        <input type="text" name="exp_recipient" class="form-control"/>
                                                                                </div>
                                                                                <div class="form-group">
                                                                                        <label>Voucher No</label>
                                                                                        <input type="text" name="exp_voucher" class="form-control"/>
                                                                                </div>
                                                                                <div class="form-group">
                                                                                        <label>Receipt No</label>
                                                                                        <input type="text" name="exp_receipt" class="form-control" placeholder="if any"/>
                                                                                </div>
                                                                                <div class="form-group">
                                                                                        <label>Details</label>
                                                                                        <input type="text" name="exp_text" class="form-control"/>
                                                                                </div>
                                                                                <button type="submit" name="expnew" class="btn btn-success btn-lg btn-block">Add Expense</button>
                                                                        </form>
                                                                </div>
                                                        </div>
                                                </div>
                                                
                                                <div class="col-md-7">
                                                        <div class="card">
                                                                <div class="card-header bg-success text-white">
                                                                        <strong>Recent Expenses (Last 60 Days)</strong>
                                                                </div>
                                                                <div class="card-body table-responsive">
                                                                        <table class="table table-striped table-hover table-sm">
                                                                                <thead class="thead-dark">
                                                                                        <tr>
                                                                                                <th>Date</th>
                                                                                                <th>Type</th>
                                                                                                <th>Amount</th>
                                                                                                <th>Paid To</th>
                                                                                                <th>Details</th>
                                                                                                <th>Voucher</th>
                                                                                                <th width="50px"></th>
                                                                                        </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                        <?PHP
                                                                                        while ($row_expcur = db_fetch_assoc($query_expcur)){
                                                                                                if ($row_expcur['cust_id'] != 0 AND $row_expcur['cust_id'] != NULL){
                                                                                                        $result_cust = getCustomer($row_expcur['cust_id']);
                                                                                                        $exp_recipient = $result_cust['cust_name'];
                                                                                                }
                                                                                                else $exp_recipient = $row_expcur['exp_recipient'];
                                                                                                echo '<tr>      
                                                                                                        <td>'.date("d.m.Y",$row_expcur['exp_date']).'</td>
                                                                                                        <td>'.$row_expcur['exptype_type'].'</td>
                                                                                                        <td class="text-right">'.number_format($row_expcur['exp_amount']).' '.$_SESSION['set_cur'].'</td>
                                                                                                        <td>'.$exp_recipient.'</td>
                                                                                                        <td>'.$row_expcur['exp_text'].'</td>
                                                                                                        <td>'.$row_expcur['exp_voucher'].'</td>
                                                                                                        <td>';
                                                                                                        if ($_SESSION['log_delete'] == 1) echo '<a href="books_expense_del.php?exp_id='.$row_expcur['exp_id'].'" class="btn btn-sm btn-danger">Delete</a>';
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
        </body>
</html>
