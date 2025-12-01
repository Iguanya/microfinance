<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();

//Select from LOANS depending on Search or not Search
if (isset($_POST['loan_no'])){
        $loan_search = sanitize($db_link, $_POST['loan_no']);
        $sql_loansearch = "SELECT * FROM loans LEFT JOIN loanstatus ON loans.loanstatus_id = loanstatus.loanstatus_id LEFT JOIN customer ON loans.cust_id = customer.cust_id WHERE loan_no LIKE '%$loan_search%'";
        $query_loansearch = db_query($db_link, $sql_loansearch);
        checkSQL($db_link, $query_loansearch);
}
elseif (isset($_POST['loan_status'])){
        $loan_search = sanitize($db_link, $_POST['loan_status']);
        $sql_loansearch = "SELECT * FROM loans LEFT JOIN loanstatus ON loans.loanstatus_id = loanstatus.loanstatus_id LEFT JOIN customer ON loans.cust_id = customer.cust_id WHERE loans.loanstatus_id = '$loan_search'";
        $query_loansearch = db_query($db_link, $sql_loansearch);
        checkSQL($db_link, $query_loansearch);
}
else header('Location: start.php');
?>

<!DOCTYPE HTML>
<html>
        <?PHP include 'includes/bootstrap_header.php'; ?>
        <body>
                <?PHP include 'includes/bootstrap_header_nav.php'; ?>

                <div class="container-fluid mt-4">
                        <div class="row">
                                <div class="col-12">
                                        <h2 class="mb-4"><i class="fa fa-search"></i> Loan Search Results</h2>

                                        <div class="table-responsive">
                                                <table class="table table-striped table-hover">
                                                        <thead class="thead-dark">
                                                                <tr>
                                                                        <th>Loan No.</th>
                                                                        <th>Customer</th>
                                                                        <th>Status</th>
                                                                        <th>Period</th>
                                                                        <th>Principal</th>
                                                                        <th>Interest</th>
                                                                        <th>Applied Date</th>
                                                                        <th>Issued</th>
                                                                        <th>Action</th>
                                                                </tr>
                                                        </thead>
                                                        <tbody>
                                                                <?PHP
                                                                while ($row_loansearch = db_fetch_assoc($query_loansearch)){
                                                                        $issued_date = ($row_loansearch['loan_dateout'] == 0) ? "<em class='text-muted'>Not Issued</em>" : date("d.m.Y", $row_loansearch['loan_dateout']);
                                                                        echo '<tr>
                                                                                <td><strong>'.$row_loansearch['loan_no'].'</strong></td>
                                                                                <td>'.$row_loansearch['cust_name'].' (<a href="customer.php?cust='.$row_loansearch['cust_id'].'" class="badge badge-info">'.$row_loansearch['cust_no'].'</a>)</td>
                                                                                <td><span class="badge badge-secondary">'.$row_loansearch['loanstatus_status'].'</span></td>
                                                                                <td>'.$row_loansearch['loan_period'].' months</td>
                                                                                <td>'.number_format($row_loansearch['loan_principal'], 2).' '.$_SESSION['set_cur'].'</td>
                                                                                <td>'.number_format(($row_loansearch['loan_repaytotal'] - $row_loansearch['loan_principal']), 2).' '.$_SESSION['set_cur'].'</td>
                                                                                <td>'.date("d.m.Y",$row_loansearch['loan_date']).'</td>
                                                                                <td>'.$issued_date.'</td>
                                                                                <td><a href="loan.php?lid='.$row_loansearch['loan_id'].'" class="btn btn-sm btn-primary"><i class="fa fa-eye"></i> View</a></td>
                                                                        </tr>';
                                                                }
                                                                ?>
                                                        </tbody>
                                                </table>
                                        </div>

                                        <a href="loans_search.php" class="btn btn-secondary mt-3">
                                                <i class="fa fa-arrow-left"></i> Back to Search
                                        </a>
                                </div>
                        </div>
                </div>

                <?PHP include 'includes/bootstrap_footer.php'; ?>
        </body>
</html>
