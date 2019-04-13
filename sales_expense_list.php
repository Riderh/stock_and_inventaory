<?php
session_start();
//require("logout.php");

if($_SESSION['username']==""){
    header("Location: loginn.php");
}
else{
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>HK</title>
  <!-- Bootstrap core CSS-->
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom fonts for this template-->
  <link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
  <!-- Page level plugin CSS-->
  <link href="vendor/datatables/dataTables.bootstrap4.css" rel="stylesheet">
  <!-- Custom styles for this template-->
  <link href="css/sb-admin.css" rel="stylesheet">
  <link href="css/expenselist.css" rel="stylesheet">
</head>

<body class="fixed-nav sticky-footer bg-dark" id="page-top">
  <!-- Navigation-->
  <?php
    require('header.php');
    ?>
  <div class="content-wrapper">
    <div class="container-fluid">
      <!-- Breadcrumbs-->
<!--
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="#">Dashboard</a>
        </li>
        <li class="breadcrumb-item active">Tables</li>
      </ol>
-->
      <!-- Example DataTables Card-->
      <div class="card mb-3">
        <div class="card-header"> <h6>Sales Expense List</h6>
            <button class="expenseadd" onclick="myFunction()"><i class="fa fa-refresh" style="color: white;"></i></button>
<!--            <button class="expenseaddbutton"><a href="expenses_type.php" style="color: white;"> <i class="fa fa-plus"> Add Expenses Type</i></a></button>-->
            </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm" id="dataTable" width="100%" cellspacing="0">
              <thead>
                <tr class="custtd">
                  <th>Sl No</th>
                  <th>Sales Bill No</th>
                  <th>Customer Name</th>
                  <th>Expense Type</th>
                  <th>Amount</th>
<!--                <th>Delete</th>-->

                 </tr>
              </thead>

              <tbody>
                <?php
                             require('dbconnect.php');
                             $orderlistq ="SELECT HKSE.*,HKS.bill_number,HKP.first_name,HKP.last_name,HKET.expenses_type
                                          FROM `hk_sales_expenses` AS HKSE
                                          LEFT JOIN `hk_sales` AS HKS ON HKSE.sales_id = HKS.id
                                          LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                                          LEFT JOIN `hk_expenses_type` AS HKET ON HKSE.expense_type_id = HKET.id
                                          ORDER BY HKSE.id DESC
                                          ";
                             $exe = mysqli_query($conn,$orderlistq);

                                 $x=0;//To show Serial numbers irrespective of Order ID
                             while($row = mysqli_fetch_array($exe)){
                             ?>
           <tr class="custtd">
             <td scope="row"><?php
                 echo  ++$x;
             ?>
           </td>
                  <td><?php echo $row['bill_number']; ?></td>
                  <td><?php echo $row['first_name']." ".$row['last_name']; ?></td>
               <td><?php echo $row['expenses_type']; ?></td>
               <td><?php echo $row['amount']; ?></td>
                </tr>
              <?php } ?>


              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
    <!-- /.container-fluid-->
    <!-- /.content-wrapper-->
    <footer class="sticky-footer">
      <div class="container">
        <div class="text-center">
          <small>MAHAT INNOVATIONS</small>
        </div>
      </div>
    </footer>
    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
      <i class="fa fa-angle-up"></i>
    </a>
    <!-- Logout Modal-->
    <!-- <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
          <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
            <a class="btn btn-primary" href="login.html">Logout</a>
          </div>
        </div>
      </div>
    </div> -->
    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Page level plugin JavaScript-->
    <script src="vendor/datatables/jquery.dataTables.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.js"></script>
    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin.min.js"></script>
    <!-- Custom scripts for this page-->
    <script src="js/sb-admin-datatables.min.js"></script>
      <script>
function myFunction() {
    location.reload();
}
</script>
  </div>
</body>

</html>
<?php
} ?>
