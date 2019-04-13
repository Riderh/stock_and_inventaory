<?php
session_start();
require("logout.php");

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
    <link href="css/supplier_advances.css" rel="stylesheet">

    <style media="screen">
      .hide{
        display: none;
      }
    </style>
</head>

<body class=" fixed-nav sticky-footer bg-dark"  id="page-top">
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
        <div class="card-header"> <h6>Supplier Advance List</h6>
             <button class="advanceadd" onclick="myFunction()"><i class="fa fa-refresh"></i></button>
           <button class="advanceaddbutton "><a href="add_supplier_advance.php" style="color: white;"> <i class="fa fa-plus"> Add Supplier Advance</i></a></button>
          </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm" id="dataTable" width="100%" cellspacing="0">
              <thead>
                <tr class="custtd">
                  <th>Sl No</th>
                  <th>Supplier Name</th>
                  <th>Date</th>
                  <th>Advance Type</th>
                  <th>Amount</th>
                  <th class="hide">Edit</th>
                  <th class="hide">Delete</th>

                 </tr>
              </thead>

              <tbody>
              	 <?php
                 require('dbconnect.php');
                 $custlistq ="SELECT HKSA.* ,HKP.first_name , HKP.last_name , HKAT.supplier_advance_type,HKSA.advance_date
                               FROM `hk_supplier_advances` AS HKSA
                               LEFT JOIN `hk_persons` AS HKP ON HKSA.person_id = HKP.id
                               LEFT JOIN `hk_supplier_advance_type` AS HKAT ON HKSA.advance_type_id = HKAT.id
                               WHERE HKSA.supplier_advances_active=1 ORDER BY HKSA.id DESC;";
                 $exe = mysqli_query($conn,$custlistq);
                 $i=1;
                 while($row = mysqli_fetch_array($exe))
                  {

                    $date = strtotime($row['advance_date']);
                    $date = date("d-m-Y",$date);
              ?>
                <tr class="custtd">
                  <td><?php echo $i++; ?></td>
                  <td><?php echo $row['first_name']. " " . $row['last_name']; ?></td>
                  <td> <?php echo $date; ?> </td>
                  <td><?php echo $row['supplier_advance_type']; ?></td>
                  <td><?php echo $row['amount']; ?></td>
                  <td class="hide"><form method="post" action="supplier_advance_module/supplier_advance_edit.php"><p data-placement="top" data-toggle="tooltip" title="Edit"> <button type="submit" name="edit"
                    value="<?php echo $row['id']; ?>" class="btn btn-primary btn-sm" ><span class="fa fa-pencil">
                    </span></button></p></form></td>


                  <td class="hide"><p data-placement="top" data-toggle="tooltip" title="Delete"><button class="btn btn-danger btn-sm staff" onclick="updateModalValue(<?php echo $row['id']; ?>, '<?php echo $row["first_name"]; ?>')" name="delete" value="<?php echo $row['id']; ?>"  name="delete" data-toggle="modal" data-target="#deleteModal" ><span class="fa fa-trash" ></span>
                  </button></p></td>
                </tr>
                <?php
                }
                ?>




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

      <!-- Delete Confirmation Modal-->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Do you want to delete?</h5>
            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times</span>
            </button>
          </div>
          <div class="modal-body" id="deleteModalName">Please confirm..</div>
          <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-dismiss="modal" onclick="flushValues()">Cancel</button>
<!--            <a class="btn btn-primary" href="login.html">Logout</a>-->
               <form method="POST" action="supplier_advance_module/supplier_advance_delete_handler.php">
                        <button class="btn btn-default" type="submit" name="delete" id="deleteModalButton" style="margin-bottom: -14px" value="">Delete</button>
                                        </form>
          </div>
        </div>
      </div>
    </div>
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
      function updateModalValue(deleteId, name) {

          $('#deleteModalButton').val(deleteId);
          $('#deleteModalName').html("Hey!.. "+ name +" will get deleted soon..");
      }

        function flushValues(){
            $('#deleteModalButton').val("");
        }
        function reloadFunction() {
    location.reload();
}
      </script>
     <script>
                  function test()
                  {
                    var oky  =  confirm("Are you sure want to delete?");
                    if(oky==true)
                    {
                      alert("Record deleted!...");
                      return true;
                    }
                    else
                    {
                      return false;
                    }
                    return true;
                  }
                  </script>
                  <script>
                  function myFunction() {
                  location.reload();
                  }
                  </script>


                  <?php
                        if($_SESSION['role']=='STAFF'){
                            echo "<script> function staff(){
                              $('.staff').attr('disabled','disabled');
                              $('.member').attr('disabled','disabled');
                               $('.member').removeAttr('href');
                               $('.staff').removeAttr('href');
                            }
                            staff();
                          </script>";
                        }elseif($_SESSION['role']=='MEMBER'){
                            echo "<script>
                                      function member(){
                                      $('.staff').attr('disabled','disabled');


                                      }
                              member();
                                  </script>";
                        }


                        ?>
  </div>

  <?php
  if(isset($_SESSION['message'])){
  $msg = $_SESSION['message'];
  ?>

  <script type="text/javascript">
  alert("<?php  echo $msg; ?>");
  </script>

  <?php
  unset($_SESSION['message']);
  }

   ?>


</body>

</html>
<?php } ?>
