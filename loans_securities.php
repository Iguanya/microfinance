<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();

$rep_year = date("Y",time());
$rep_month = date("m",time());

//Make array for exporting data
$_SESSION['rep_export'] = array();
$_SESSION['rep_exp_title'] = $rep_year.'-'.$rep_month.'_loan-securities';

//Select loans that have securities
$sql_loans = "SELECT * FROM loans LEFT JOIN loanstatus ON loans.loanstatus_id = loanstatus.loanstatus_id LEFT JOIN customer ON loans.cust_id = customer.cust_id WHERE loan_id IN (SELECT DISTINCT loan_id FROM securities WHERE sec_returned = 0) ORDER BY loan_dateout, loans.cust_id";
$query_loans = db_query($db_link, $sql_loans);
checkSQL($db_link, $query_loans);
?>

<!DOCTYPE HTML>
<html>
        <?PHP include 'includes/bootstrap_header.php'; ?>

        <body>
                <div class="container-fluid mt-4">
                        <div class="row">
                                <div class="col-12">
                                        <h2 class="mb-4">Loan Securities</h2>
                                        
                                        <nav class="mb-3">
                                                <ul class="nav nav-tabs" role="tablist">
                                                        <li class="nav-item">
                                                                <a class="nav-link" href="loans_search.php">Search</a>
                                                        </li>
                                                        <li class="nav-item">
                                                                <a class="nav-link" href="loans_act.php">Active Loans</a>
                                                        </li>
                                                        <li class="nav-item">
                                                                <a class="nav-link" href="loans_pend.php">Pending Loans</a>
                                                        </li>
                                                        <li class="nav-item">
                                                                <a class="nav-link active" href="loans_securities.php">Loan Securities</a>
                                                        </li>
                                                </ul>
                                        </nav>

                                        <div class="card">
                                                <div class="card-header bg-success text-white d-flex justify-content-between">
                                                        <strong>Current Loan Securities</strong>
                                                        <form action="rep_export.php" method="post" style="display:inline;">
                                                                <button type="submit" name="export_rep" class="btn btn-sm btn-light">Export</button>
                                                        </form>
                                                </div>
                                                <div class="card-body table-responsive">
                                                        <table class="table table-striped table-hover">
                                                                <thead class="thead-dark">
                                                                        <tr>
                                                                                <th>Loan No.</th>
                                                                                <th>Customer</th>
                                                                                <th>Loan Status</th>
                                                                                <th>Security 1</th>
                                                                                <th>Security 2</th>
                                                                        </tr>
                                                                </thead>
                                                                <tbody>
                                                                        <?PHP
                                                                        $count = 0;
                                                                        while ($row_loans = db_fetch_assoc($query_loans)){
                                                                                $securities = getLoanSecurities($db_link, $row_loans['loan_id']);
                                                                                $security1 = NULL;
                                                                                $security2 = NULL;
                                                                                foreach ($securities as $s){
                                                                                        if ($s['sec_no'] == 1) $security1 = $s;
                                                                                        elseif ($s['sec_no'] == 2) $security2 = $s;
                                                                                }
                                                                                echo '<tr>
                                                                                        <td><a href="loan.php?lid='.$row_loans['loan_id'].'">'.$row_loans['loan_no'].'</a></td>
                                                                                        <td>'.$row_loans['cust_name'].' (<a href="customer.php?cust='.$row_loans['cust_id'].'">'.$row_loans['cust_no'].'</a>)</td>
                                                                                        <td>'.$row_loans['loanstatus_status'].'</td>
                                                                                        <td><a href="security.php?security='.$security1['sec_id'].'">'.$security1['sec_name'].'</a></td>
                                                                                        <td><a href="security.php?security='.$security2['sec_id'].'">'.$security2['sec_name'].'</a></td>
                                                                                </tr>';

                                                                                // Export Array
                                                                                array_push($_SESSION['rep_export'], array("Loan No." => $row_loans['loan_no'], "Customer" => $row_loans['cust_name'].' ('.$row_loans['cust_no'].')', "Status" => $row_loans['loanstatus_status'],"Security 1" => $security1['sec_name'], "Security 2" => $security2['sec_name']));

                                                                                $count++;
                                                                        }
                                                                        ?>
                                                                </tbody>
                                                                <tfoot>
                                                                        <tr class="table-primary font-weight-bold">
                                                                                <td colspan="5">
                                                                                        <?PHP
                                                                                        echo $count.' loan';
                                                                                        if ($count != 1) echo 's';
                                                                                        echo ' with securities';
                                                                                        ?>
                                                                                </td>
                                                                        </tr>
                                                                </tfoot>
                                                        </table>
                                                </div>
                                        </div>
                                </div>
                        </div>
                </div>

                <?PHP include 'includes/bootstrap_footer.php'; ?>
        </body>
</html>
