<?PHP
//Select SHARES from database
// Note: $db_link is defined in parent file scope
$sql_sha = "SELECT * FROM shares, user WHERE shares.user_id = user.user_id AND cust_id = '$_SESSION[cust_id]' ORDER BY share_date DESC";
$query_sha = db_query($db_link, $sql_sha);
checkSQL($db_link, $query_sha);

//Make array for exporting data
$share_exp_date = date("Y-m-d",time());
$_SESSION['share_export'] = array();
$_SESSION['share_exp_title'] = $_SESSION['cust_id'].'_shares_'.$share_exp_date;
?>

<div class="card">
        <div class="card-header bg-info text-white">
                <div class="d-flex justify-content-between align-items-center">
                        <strong><i class="fa fa-certificate"></i> Share Account Statement</strong>
                        <form action="acc_share_export.php" method="post" style="display:inline;">
                                <button type="submit" name="export_rep" class="btn btn-sm btn-light">
                                        <i class="fa fa-download"></i> Export
                                </button>
                        </form>
                </div>
        </div>
        <div class="card-body">
                <div class="table-responsive">
                        <table class="table table-striped table-hover table-sm">
                                <thead class="thead-dark">
                                        <tr>
                                                <th>Date</th>
                                                <th>Number of Shares</th>
                                                <th>Value of Shares</th>
                                                <th>Receipt No.</th>
                                                <th>Authorized By</th>
                                                <th>Action</th>
                                        </tr>
                                </thead>
                                <tbody>
                                        <?PHP
                                        $amount_balance = 0;
                                        $value_balance = 0;
                                        while($row_sha = db_fetch_assoc($query_sha)){
                                                echo '<tr>
                                                        <td>'.date("d.m.Y",$row_sha['share_date']).'</td>
                                                        <td><strong>'.$row_sha['share_amount'].'</strong></td>
                                                        <td><strong>'.number_format($row_sha['share_value'], 2).' '.$_SESSION['set_cur'].'</strong></td>
                                                        <td>'.$row_sha['share_receipt'].'</td>
                                                        <td><small>'.$row_sha['user_name'].'</small></td>
                                                        <td>';
                                                        if($_SESSION['log_delete'] == 1) echo '<a href="acc_share_del.php?sha_id='.$row_sha['share_id'].'" class="btn btn-sm btn-danger" onClick="return randCheck()"><i class="fa fa-trash"></i></a>';
                                                echo '</td>
                                                </tr>';
                                                $amount_balance = $amount_balance + $row_sha['share_amount'];
                                                $value_balance = $value_balance + $row_sha['share_value'];
                                                
                                                //Prepare data for export to Excel file
                                                array_push($_SESSION['share_export'], array("Date" => date("d.m.Y",$row_sha['share_date']), "Amount of Shares" => $row_sha['share_amount'], "Share Value" => $row_sha['share_value'], "Receipt" => $row_sha['share_receipt']));
                                        }
                                        ?>
                                </tbody>
                                <tfoot>
                                        <tr class="table-info font-weight-bold">
                                                <td>Balance:</td>
                                                <td><?PHP echo $amount_balance; ?></td>
                                                <td><?PHP echo number_format($value_balance, 2); ?> <?PHP echo $_SESSION['set_cur']; ?></td>
                                                <td colspan="3"></td>
                                        </tr>
                                </tfoot>
                        </table>
                </div>
        </div>
</div>
