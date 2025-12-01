<?PHP
// Select Savings Transactions from SAVINGS
// Note: $db_link and $sav_balance are defined in parent file scope
$sql_sav = "SELECT * FROM savings LEFT JOIN savtype ON savings.savtype_id = savtype.savtype_id LEFT JOIN user ON savings.user_id = user.user_id WHERE cust_id = '$_SESSION[cust_id]' ORDER BY sav_date DESC, sav_id DESC";
$query_sav = db_query($db_link, $sql_sav);
checkSQL($db_link, $query_sav);

// Make array for exporting data
$sav_exp_date = date("Y-m-d",time());
$_SESSION['sav_export'] = array();
$_SESSION['sav_exp_title'] = $_SESSION['cust_id'].'_savings_'.$sav_exp_date;
?>

<div class="card">
        <div class="card-header bg-success text-white">
                <div class="d-flex justify-content-between align-items-center">
                        <strong><i class="fa fa-piggy-bank"></i> Savings Account Statement</strong>
                        <form action="acc_sav_export.php" method="post" style="display:inline;">
                                <button type="submit" name="export_rep" class="btn btn-sm btn-light">
                                        <i class="fa fa-download"></i> Export
                                </button>
                        </form>
                </div>
        </div>
        <div class="card-body">
                <div class="alert alert-info">
                        <strong>Balance:</strong> <?PHP echo number_format($sav_balance, 2); ?> <?PHP echo $_SESSION['set_cur']; ?>
                </div>
                <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm">
                                <thead class="thead-dark">
                                        <tr>
                                                <th>Date</th>
                                                <?PHP if ($_SESSION['set_sfx'] == 1) echo '<th>Fixed Until</th>'; ?>
                                                <th>Transaction Type</th>
                                                <th>Amount</th>
                                                <th>Receipt</th>
                                                <th>Slip</th>
                                                <th>Depositor</th>
                                                <th>Authorized By</th>
                                                <th>Action</th>
                                        </tr>
                                </thead>
                                <tbody>
                                        <?PHP
                                        while($row_sav = db_fetch_assoc($query_sav)){
                                                echo '<tr>
                                                        <td>'.date("d.m.Y",$row_sav['sav_date']).'</td>';
                                                if ($_SESSION['set_sfx'] == 1){
                                                        if($row_sav['sav_fixed'] != 0) echo '<td>'.date("d.m.Y",$row_sav['sav_fixed']).'</td>';
                                                        else echo '<td><em class="text-muted">-</em></td>';
                                                }
                                                echo '<td><span class="badge badge-info">'.$row_sav['savtype_type'].'</span></td>
                                                        <td><strong>'.number_format($row_sav['sav_amount'], 2).' '.$_SESSION['set_cur'].'</strong></td>
                                                        <td>'.$row_sav['sav_receipt'].'</td>
                                                        <td>'.$row_sav['sav_slip'].'</td>
                                                        <td>'.$row_sav['sav_payer'].'</td>
                                                        <td><small>'.$row_sav['user_name'].'</small></td>';
                                                        if ($_SESSION['log_delete'] == 1 and ($row_sav['savtype_id'] == 1 or $row_sav['savtype_id'] == 2)) echo '<td><a href="acc_sav_del.php?sav_id='.$row_sav['sav_id'].'" class="btn btn-sm btn-danger" onClick="return randCheck();"><i class="fa fa-trash"></i></a></td>';
                                                        else echo '<td></td>';
                                                echo '</tr>';

                                                //Prepare data for export to Excel file
                                                array_push($_SESSION['sav_export'], array("Date" => date("d.m.Y",$row_sav['sav_date']), "Transaction Type" => $row_sav['savtype_type'], "Amount" => $row_sav['sav_amount'], "Receipt" => $row_sav['sav_receipt'], "W/draw Slip" => $row_sav['sav_slip']));
                                        }
                                        ?>
                                </tbody>
                        </table>
                </div>
        </div>
</div>
