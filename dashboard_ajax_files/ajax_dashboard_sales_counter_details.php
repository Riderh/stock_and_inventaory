<?php
include_once("../dbconnect.php");
if($_REQUEST['empid']) {
	$sql = "SELECT COUNT(id) AS counter,SUM(total_amount_received) AS total FROM hk_sales WHERE bill_date ='".$_REQUEST['empid']."'";


	$resultset = mysqli_query($conn, $sql) or die("database error:". mysqli_error($conn));

	$data = array();
$i=0;
	while( $rows = mysqli_fetch_assoc($resultset) ) {
		$data[$i] = $rows;
        $i++;
	}
	echo json_encode($data);
} else {
	echo 0;
}
?>