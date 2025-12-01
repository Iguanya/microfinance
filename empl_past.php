<?PHP
require 'functions.php';
checkLogin();
$db_link = connect();

//Make array for exporting data
$rep_year = date("Y",time());
$rep_month = date("m",time());
$_SESSION['rep_export'] = array();
$_SESSION['rep_exp_title'] = $rep_year.'-'.$rep_month.'_empl-former';

$query_emplpast = getEmplPast($db_link);
?>

<!DOCTYPE HTML>
<html>
        <?PHP include 'includes/bootstrap_header.php'; ?>

        <body>
                <?PHP include 'includes/bootstrap_header_nav.php'; ?>

                <div class="container-fluid mt-4">
                        <div class="row">
                                <div class="col-12">
                                        <h2 class="mb-4">Employee Management</h2>
                                        
                                        <nav class="mb-3">
                                                <ul class="nav nav-tabs" role="tablist">
                                                        <li class="nav-item">
                                                                <a class="nav-link" href="empl_new.php">Add New Employee</a>
                                                        </li>
                                                        <li class="nav-item">
                                                                <a class="nav-link" href="empl_curr.php">Current Employees</a>
                                                        </li>
                                                        <li class="nav-item">
                                                                <a class="nav-link active" href="empl_past.php">Former Employees</a>
                                                        </li>
                                                </ul>
                                        </nav>

                                        <div class="card">
                                                <div class="card-header bg-success text-white d-flex justify-content-between">
                                                        <strong>Former Employees</strong>
                                                        <form action="rep_export.php" method="post" style="display:inline;">
                                                                <button type="submit" name="export_rep" class="btn btn-sm btn-light">Export</button>
                                                        </form>
                                                </div>
                                                <div class="card-body table-responsive">
                                                        <table class="table table-striped table-hover">
                                                                <thead class="thead-dark">
                                                                        <tr>
                                                                                <th>Empl. No.</th>
                                                                                <th>Name</th>
                                                                                <th>Position</th>
                                                                                <th>Gender</th>
                                                                                <th>DoB</th>
                                                                                <th>Address</th>
                                                                                <th>Phone No.</th>
                                                                                <th>Email</th>
                                                                                <th>Date In</th>
                                                                                <th>Date Out</th>
                                                                        </tr>
                                                                </thead>
                                                                <tbody>
                                                                        <?PHP
                                                                        $count = 0;
                                                                        while ($row_emplpast = db_fetch_assoc($query_emplpast)){
                                                                                echo '<tr class="clickable" onclick="window.location=\'employee.php?empl='.$row_emplpast['empl_id'].'\'">
                                                                                        <td><a href="employee.php?empl='.$row_emplpast['empl_id'].'">'.$row_emplpast['empl_no'].'</a></td>
                                                                                        <td>'.$row_emplpast['empl_name'].'</td>
                                                                                        <td>'.$row_emplpast['empl_position'].'</td>
                                                                                        <td>'.$row_emplpast['emplsex_name'].'</td>
                                                                                        <td>'.date("d.m.Y",$row_emplpast['empl_dob']).'</td>
                                                                                        <td>'.$row_emplpast['empl_address'].'</td>
                                                                                        <td>'.$row_emplpast['empl_phone'].'</td>
                                                                                        <td>'.$row_emplpast['empl_email'].'</td>
                                                                                        <td>'.date("d.m.Y",$row_emplpast['empl_in']).'</td>
                                                                                        <td>'.date("d.m.Y",$row_emplpast['empl_out']).'</td>
                                                                                </tr>';

                                                                                array_push($_SESSION['rep_export'], array("Empl. No." => $row_emplpast['empl_no'], "Employee Name" => $row_emplpast['empl_name'], "DoB" => date("d.m.Y",$row_emplpast['empl_dob']), "Gender" => $row_emplpast['emplsex_name'], "Address" => $row_emplpast['empl_address'], "Phone No." => $row_emplpast['empl_phone'], "Email" => $row_emplpast['empl_email'], "Empl. In" => date("d.m.Y",$row_emplpast['empl_in']), "Empl. Out" => date("d.m.Y",$row_emplpast['empl_out'])));

                                                                                $count++;
                                                                        }
                                                                        ?>
                                                                </tbody>
                                                                <tfoot>
                                                                        <tr class="table-primary font-weight-bold">
                                                                                <td colspan="10">
                                                                                        <?PHP
                                                                                        echo $count.' former employee';
                                                                                        if ($count != 1) echo 's';
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
