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
    <link href="css/user.css" rel="stylesheet">

</head>

<body class="fixed-nav sticky-footer bg-dark" id="page-top">
  <!-- Navigation-->
  <?php require('header.php');
    ?>
  <div class="content-wrapper">
    <div class="container-fluid">
     <!-- customer details-->
        <form class="cust_line" method="post" action="users_module/user_creation_handler.php">

            <div class="row"> <h5 style="margin:-18px 0px 18px 0px " ><u>Add User</u></h5>
    <pre style="float:right">       								 (Note: Fields with <i class="fa fa-asterisk" style="font-size:10px;color:red"></i> mark are compulsory)</pre></div>
    <div class="row userrow">
        <div class="col-md-6">
       <label for="fname">First Name <span class="requiredfield">*</span></label>
   <input type="text" class="usertext userfname"  name="first_name" placeholder="User First Name"  required>


     <div class="userrow"></div>

             <label for="lname" >Last Name</label>
        <input type="text" class="usertext userlname" name="last_name" placeholder="User Surname" >
           <div class="userrow"></div>

            <label for="mobile">Mobile Number <span class="requiredfield">*</span></label>
        <input type="tel" class="usertext" name="mobile_number" placeholder="User Mobile No" maxlength="10" oninput="maxLengthCheckphone(this)" onblur="checkLength(this)" onkeypress='validate(event)' pattern="[0-9]{3}[0-9]{3}[0-9]{4}"   required>

               <div class="userrow"></div>
            <label for="email"  >Email Id </label>
            <input type="email" class="useremail usertextemail" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,3}$" name="email" placeholder="User Email Id" value="">
        </div>
             <div class="col-md-6">
       <label for="username">Username <span class="requiredfield">*</span></label>
   <input type="text" class=" usertextemail"  name="username" placeholder="Enter Username"  required>

                  <div class="userrow"></div>
         <div class="userrow"></div>
        <label for="password" >Password <span class="requiredfield">*</span></label>
        <input type="password" class="usertext userpassword" onblur="checkLength8(this)" maxlength="15" required pattern=".{8,}" title="password must contain atleast 8 characters" name="password" placeholder="Enter Password" required>

           <div class="userrow"></div>
        <label for="role" >Role <span class="requiredfield">*</span></label>
         <select id="role" class="userrole" name="role_id" required>
           <?php
            require('dbconnect.php');
            $u_type_q = " SELECT * FROM `hk_roles`";
            $exe = mysqli_query($conn,$u_type_q);
            while($row = mysqli_fetch_array($exe)){
            ?>
            <option value="<?php echo $row['id']; ?>"><?php echo $row['role']; ?></option>
            <?php
            }
            ?>

</select>
               </div>
    </div>






    <div class="row" style="margin-left:-130px;">

<!--
        <input  type="submit"  value="Submit">
        <input  type="reset"  value="Cancel">
-->

       <button class="buttonsubmit" type="submit"><a >Submit</a></button>
      <a href="user_list.php" style="text-decoration:none;" class="buttonreset"><span>Cancel</span></a>
    </div>
  </form>


        <!-- end of customer deatils-->
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
<!--
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
    </div>
-->
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
      <script src="js/supplierdetails.js"></script>


<script>
function checkLength8(el1) {
  if (el1.value.length < 9) {
    alert("length must be atleast 8 characters")
  }
}</script>



  </div>
</body>

</html>
<?php } ?>
