<?php
require('fpdf181/fpdf.php');
require ('dbconnect.php');

$cash_amount = 0;
$credit_amount = 0;
$ondate = $_POST["ondate"];
$fromdate = $_POST["fromdate"];
$todate = $_POST["todate"];

$actualOndate = $ondate;
$actualFromdate = $fromdate;
$actualTodate = $todate;

$typeDate = $_POST["typeDate"];


setlocale(LC_MONETARY, 'en_IN');

$returnArr = array();

$ondate = date("d-m-Y", strtotime($ondate));
$fromdate = date("d-m-Y", strtotime($fromdate));
$todate = date("d-m-Y", strtotime($todate));


class PDF extends FPDF
{
// Page header
function Header()
{
    // Logo
//    $this->Image('logo.png',10,6,30);
    // Arial bold 15
    $this->SetFont('Arial','B',15);
    // Move to the right
    // $this->Cell(80);
    // Title
    $this->Cell(190,10,'K.ABDUL KAREEM & SONS',0,0,'C');
    // Line break
    $this->Ln(20);
}

// Page footer
function Footer()
{
    // Position at 1.5 cm from bottom
    $this->SetY(-15);
    // Arial italic 8
    $this->SetFont('Arial','I',8);
    // Page number
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
}
}


 function retproducts($retId){
    require("dbconnect.php");

    $products = array();

    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id = $retId";
  $retExe = mysqli_query($conn,$retProductsQ);

$i = 0;
  while ($retRow = mysqli_fetch_array($retExe)) {
    # code...
    $products[$i]["particulars"] = $retRow["name"]." ".$retRow["type"]." ".-1*$retRow["quantity"]." ".$retRow["quantity_type"]." ".money_format('%!i', $retRow["amount"]*-1);

$i++;
  }

return $products;
  }


function retproductswithid($retid,$prodid){
  require("dbconnect.php");

    $products = array();

    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id = $retid AND HKSRP.product_id=$prodid";
  $retExe = mysqli_query($conn,$retProductsQ);

$i = 0;
  while ($retRow = mysqli_fetch_array($retExe)) {
    # code...
    $products[$i]["particulars"] = $retRow["name"]." ".$retRow["type"]." ".-1*$retRow["quantity"]." ".$retRow["quantity_type"]." ".money_format('%!i', $retRow["amount"]*-1);

$i++;
  }

return $products;
}


if($_POST["transaction_type"] == "cash"){
	if($typeDate=="onDate"){
		if($_POST["product"] == "allproducts"){
			if($_POST["customer"] == "allcustomers"){


        $print_array = array();
        $product_array = array();
        $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                  LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                  WHERE HKS.sales_transaction_type_id = '1' AND HKS.bill_date = '$actualOndate' AND HKS.sales_active = '1'";
                  $exe = mysqli_query($conn,$query);
                  $x = 0;
                  while($row = mysqli_fetch_array($exe)){
                    $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                    $print_array[$x]["bill_number"] = $row['bill_number'];
                    $print_array[$x]["total_amount_received"] = $row['total_amount_received'];
                    $cash_amount = $cash_amount+$row['total_amount_received'];
                    $sales_id = $row['id'];

                    $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                              FROM `hk_sales_products` AS HKSP
                              LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                              WHERE HKSP.sales_id = '$sales_id'";
                              $exe1 = mysqli_query($conn,$query1);
                              $y = 0;
                              while($row1 = mysqli_fetch_array($exe1)){
                                $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                                $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                                $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                                $product_array[$x][$y]["rate"] = $row1['rate'];
                                $product_array[$x][$y]["amount"] = $row1['amount'];
                                $y++;
                              }

// get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){



      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}


// assign return products details to $product_array[$x][$y] separate variables
// check return variables are set
// if return variables are set print it else donot


                    $x++;
                  }

              // report code for sales_return_direct

// function to get return products for return id





                  // get the sales  return billnumber, sales_return_id and amount and  customername
$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id IS NOT NULL AND HKSR.date='$actualOndate'
AND HKSR.transaction_type_id=1";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproducts($directSRRow["id"]);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}











				$pdf = new PDF();
				$pdf->AliasNbPages();
				$pdf->AddPage();

				// $pdf->SetFont('Arial', 'B', 20);
				//
				// $pdf->SetTextColor(0,0,255);
				// $pdf->Cell(190,5,'K.ABDUL KAREEM & SONS',0,10,'C');
				// $pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell(130,5,'Sales Register Report :',0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Date : '.$ondate,0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Cash Sales Of All Products',0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
        	$pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
        for($x = 0; $x<count($print_array); $x++ ){
          $pdf->Cell(20,9,$sl_no,0,0,'C');
          $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
          $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

          for($y = 0; $y<count($product_array[$x]); $y++){
            $pdf->Cell(20,9,'',0,0,'C');
            $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".money_format('%!i',$product_array[$x][$y]["amount"])),0,1,'C');
          }
          $sl_no++;

          if(!empty($returnArr[$x])){


// pdf
            $pdf->Cell(20,9,$sl_no,0,0,'C');
            $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

// $returnArr[$x][$index]['bill_number']
for($y = 0; $y< count($returnArr[$x]);$y++){
  $pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
  $pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


  for($index =0; $index<count($retProducts[$x][$y]); $index++){
    $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
    $pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
  }

$sl_no++;
  }
}



        }

        for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }




				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');

				$pdf->output();
			}
			else{
				$customer_id = $_POST["customer_id"];

        $print_array = array();
       $product_array = array();
       $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                 LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                 WHERE HKS.sales_transaction_type_id = '1' AND HKS.bill_date = '$actualOndate' AND HKP.id = '$customer_id' AND HKS.sales_active = '1'";
                 $exe = mysqli_query($conn,$query);
                 $x = 0;
                 while($row = mysqli_fetch_array($exe)){
                   $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                   $print_array[$x]["bill_number"] = $row['bill_number'];
                   $print_array[$x]["total_amount_received"] = $row['total_amount_received'];
                   $cash_amount = $cash_amount+$row['total_amount_received'];
                   $sales_id = $row['id'];

                   $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                             FROM `hk_sales_products` AS HKSP
                             LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                             WHERE HKSP.sales_id = '$sales_id'";
                             $exe1 = mysqli_query($conn,$query1);
                             $y = 0;
                             while($row1 = mysqli_fetch_array($exe1)){
                               $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                               $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                               $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                               $product_array[$x][$y]["rate"] = $row1['rate'];
                               $product_array[$x][$y]["amount"] = $row1['amount'];
                               $y++;
                             }


                             // get id from sales return table
                             $getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

                             $getIdExe = mysqli_query($conn,$getRId);





                               $index = 0;
                               while($getIdRow = mysqli_fetch_array($getIdExe)) {
                                 // code...

                                 if(empty($getIdRow)){
                                   $returnArr[$x][$index]['id'] =null;
                                   $returnArr[$x][$index]['bill_number'] = null;
                                   $returnArr[$x][$index]['returnAmount'] =null;
                                 }

                                 $returnArr[$x][$index]['id'] = $getIdRow["id"];
                                 $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
                                 $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
                                 $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
                                 $index++;
                               }






                             // get return products from sales_return_products for that id

                             error_reporting(E_ERROR | E_PARSE);

                             if(count($returnArr[$x])>0){
                               // echo count($returnArr[$x]);
                               // print_r($returnArr[$x]);
                               // echo "<br>";
                               for($a = 0; $a<count($returnArr[$x]); $a++){
                                 $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
                               left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
                               // echo "$retProductsQ <br>";
                                 $retProdcutExe = mysqli_query($conn,$retProductsQ);
                               echo mysqli_error($conn);
                               $indx =0;
                                 while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
                                   // code...
                                   $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
                                   // echo $retProducts[$x][$a][$indx]["Particulars"];
                                   $indx++;
                                 }


                               }
                             }





                   $x++;
                 }

$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id IS NOT NULL AND HKSR.date='$actualOndate' AND HKSR.person_id=$customer_id AND HKSR.transaction_type_id=1";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproducts($directSRRow["id"]);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}




				$pdf = new PDF();
				$pdf->AliasNbPages();
				$pdf->AddPage();

				// $pdf->SetFont('Arial', 'B', 20);
				//
				// $pdf->SetTextColor(0,0,255);
				// $pdf->Cell(190,5,'K.ABDUL KAREEM & SONS',0,10,'C');
				// $pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell(130,5,'Sales Register Report :',0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Date : '.$ondate,0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Cash Sales Of All Products',0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
        $pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
        for($x = 0; $x<count($print_array); $x++ ){
          $pdf->Cell(20,9,$sl_no,0,0,'C');
          $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
          $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

          for($y = 0; $y<count($product_array[$x]); $y++){
            $pdf->Cell(20,9,'',0,0,'C');
            $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
          }
          $sl_no++;



          if(!empty($returnArr[$x])){


// pdf
            $pdf->Cell(20,9,$sl_no,0,0,'C');
            $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

// $returnArr[$x][$index]['bill_number']
for($y = 0; $y< count($returnArr[$x]);$y++){
  $pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
  $pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


  for($index =0; $index<count($retProducts[$x][$y]); $index++){
    $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
    $pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
  }

$sl_no++;
  }
}

        }

   for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }


				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');

				$pdf->output();
			}
		}
		else{
			if($_POST["customer"] == "allcustomers"){
				$product_id = $_POST["product_id"];
				$product_name;
				$productQuery = "SELECT * from `hk_products` WHERE id = '$product_id'";
				$exe = mysqli_query($conn,$productQuery);
				while($row = mysqli_fetch_array($exe)){
					$product_name = $row['name']." ".$row['type'];
				}


        $print_array = array();
        $product_array = array();
        $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                  LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                  LEFT JOIN `hk_sales_products` AS HKSP ON HKSP.sales_id = HKS.id
                  WHERE HKS.sales_transaction_type_id = '1' AND HKS.bill_date = '$actualOndate' AND HKSP.product_id = '$product_id' AND HKS.sales_active = '1'";
                  $exe = mysqli_query($conn,$query);
                  $x = 0;
                  while($row = mysqli_fetch_array($exe)){
                    $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                    $print_array[$x]["bill_number"] = $row['bill_number'];
                    $print_array[$x]["total_amount_received"] = $row['total_amount_received'];
                    $cash_amount = $cash_amount+$row['total_amount_received'];
                    $sales_id = $row['id'];

                    $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                              FROM `hk_sales_products` AS HKSP
                              LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                              WHERE HKSP.sales_id = '$sales_id' AND HKSP.product_id = '$product_id'";
                              $exe1 = mysqli_query($conn,$query1);
                              $y = 0;
                              while($row1 = mysqli_fetch_array($exe1)){
                                $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                                $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                                $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                                $product_array[$x][$y]["rate"] = $row1['rate'];
                                $product_array[$x][$y]["amount"] = $row1['amount'];
                                $y++;
                              }



// get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}







                    $x++;
                  }


$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id IS NOT NULL AND HKSR.date='$actualOndate' AND HKSR.transaction_type_id=1";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproductswithid($directSRRow["id"],$product_id);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}





				$pdf = new PDF();
				$pdf->AliasNbPages();
				$pdf->AddPage();

				// $pdf->SetFont('Arial', 'B', 20);
				//
				// $pdf->SetTextColor(0,0,255);
				// $pdf->Cell(190,5,'K.ABDUL KAREEM & SONS',0,10,'C');
				// $pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell(130,5,'Sales Register Report :',0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Date : '.$ondate,0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Cash Sales Of '.$product_name,0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
        $pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
        for($x = 0; $x<count($print_array); $x++ ){
          $pdf->Cell(20,9,$sl_no,0,0,'C');
          $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
          $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

          for($y = 0; $y<count($product_array[$x]); $y++){
            $pdf->Cell(20,9,'',0,0,'C');
            $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
          }
          $sl_no++;


          if(!empty($returnArr[$x])){


// pdf
   $pdf->Cell(20,9,$sl_no,0,0,'C');
   $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

// $returnArr[$x][$index]['bill_number']
for($y = 0; $y< count($returnArr[$x]);$y++){
$pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
$pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


for($index =0; $index<count($retProducts[$x][$y]); $index++){
$pdf->Cell(20,9,'',0,0,'C');
// $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
$pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
}

$sl_no++;
}
}







        }

        for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }




				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');

				$pdf->output();
			}else{
				$customer_id = $_POST["customer_id"];
				$product_id = $_POST["product_id"];
				$product_name;
				$productQuery = "SELECT * from `hk_products` WHERE id = '$product_id'";
				$exe = mysqli_query($conn,$productQuery);
				while($row = mysqli_fetch_array($exe)){
					$product_name = $row['name']." ".$row['type'];
				}

        $print_array = array();
        $product_array = array();
        $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                  LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                  LEFT JOIN `hk_sales_products` AS HKSP ON HKSP.sales_id = HKS.id
                  WHERE HKS.sales_transaction_type_id = '1' AND HKS.bill_date = '$actualOndate' AND HKSP.product_id = '$product_id' AND HKP.id = '$customer_id' AND HKS.sales_active = '1'";
                  $exe = mysqli_query($conn,$query);
                  $x = 0;
                 while($row = mysqli_fetch_array($exe)){
                   $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                   $print_array[$x]["bill_number"] = $row['bill_number'];
                   $print_array[$x]["total_amount_received"] = $row['total_amount_received'];
                   $cash_amount = $cash_amount+$row['total_amount_received'];
                   $sales_id = $row['id'];

                   $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                             FROM `hk_sales_products` AS HKSP
                             LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                             WHERE HKSP.sales_id = '$sales_id' AND HKSP.product_id = '$product_id'";
                             $exe1 = mysqli_query($conn,$query1);
                             $y = 0;
                             while($row1 = mysqli_fetch_array($exe1)){
                               $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                               $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                               $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                               $product_array[$x][$y]["rate"] = $row1['rate'];
                               $product_array[$x][$y]["amount"] = $row1['amount'];
                               $y++;
                             }




// get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}




                   $x++;
                 }



$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id =$customer_id AND HKSR.date='$actualOndate' AND HKSR.transaction_type_id=1";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproductswithid($directSRRow["id"],$product_id);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}







				$pdf = new PDF();
				$pdf->AliasNbPages();
				$pdf->AddPage();
				//
				// $pdf->SetFont('Arial', 'B', 20);
				//
				// $pdf->SetTextColor(0,0,255);
				// $pdf->Cell(190,5,'K.ABDUL KAREEM & SONS',0,10,'C');
				// $pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell(130,5,'Sales Register Report :',0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Date : '.$ondate,0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Cash Sales Of '.$product_name,0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
				$pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
               for($x = 0; $x<count($print_array); $x++ ){
                 $pdf->Cell(20,9,$sl_no,0,0,'C');
                 $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
                 $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
                 $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

                 for($y = 0; $y<count($product_array[$x]); $y++){
                   $pdf->Cell(20,9,'',0,0,'C');
                   $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
                 }
                 $sl_no++;




          if(!empty($returnArr[$x])){


// pdf
            $pdf->Cell(20,9,$sl_no,0,0,'C');
            $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

// $returnArr[$x][$index]['bill_number']
for($y = 0; $y< count($returnArr[$x]);$y++){
  $pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
  $pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


  for($index =0; $index<count($retProducts[$x][$y]); $index++){
    $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
    $pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
  }

$sl_no++;
  }
}
}

        for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }


				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');

				$pdf->output();
			}


		}
	}
	else if($typeDate=="btDate"){
		if($_POST["product"] == "allproducts"){
			if($_POST["customer"] == "allcustomers"){

        $print_array = array();
        $product_array = array();
        $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                WHERE HKS.sales_transaction_type_id = '1' AND HKS.bill_date BETWEEN '$actualFromdate' AND '$actualTodate' AND HKS.sales_active = '1'";
                $exe = mysqli_query($conn,$query);
                $x = 0;
                while($row = mysqli_fetch_array($exe)){
                  $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                  $print_array[$x]["bill_number"] = $row['bill_number'];
                  $print_array[$x]["total_amount_received"] = $row['total_amount_received'];
                  $cash_amount = $cash_amount+$row['total_amount_received'];
                  $sales_id = $row['id'];

                  $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                            FROM `hk_sales_products` AS HKSP
                            LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                            WHERE HKSP.sales_id = '$sales_id'";
                            $exe1 = mysqli_query($conn,$query1);
                            $y = 0;
                            while($row1 = mysqli_fetch_array($exe1)){
                              $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                              $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                              $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                              $product_array[$x][$y]["rate"] = $row1['rate'];
                              $product_array[$x][$y]["amount"] = $row1['amount'];
                              $y++;
                            }




// get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}
    $x++;
}




$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id IS NOT NULL AND HKSR.transaction_type_id=1 AND HKSR.date between '$actualFromdate' AND '$actualTodate'";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproducts($directSRRow["id"]);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}







				$pdf = new PDF();
				$pdf->AliasNbPages();
				$pdf->AddPage();

				// $pdf->SetFont('Arial', 'B', 20);
				//
				// $pdf->SetTextColor(0,0,255);
				// $pdf->Cell(190,5,'K.ABDUL KAREEM & SONS',0,10,'C');
				// $pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell(130,5,'Sales Register Report :',0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Between Date : from '.$fromdate." to ".$todate,0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Cash Sales Of All Products',0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
				$pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
       for($x = 0; $x<count($print_array); $x++ ){
         $pdf->Cell(20,9,$sl_no,0,0,'C');
         $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
         $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
         $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

         for($y = 0; $y<count($product_array[$x]); $y++){
           $pdf->Cell(20,9,'',0,0,'C');
           $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
         }

         $sl_no++;

         if(!empty($returnArr[$x])){


// pdf
     $pdf->Cell(20,9,$sl_no,0,0,'C');
     $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

// $returnArr[$x][$index]['bill_number']
for($y = 0; $y< count($returnArr[$x]);$y++){
$pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
$pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


for($index =0; $index<count($retProducts[$x][$y]); $index++){
$pdf->Cell(20,9,'',0,0,'C');
// $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
$pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
}

$sl_no++;
}
}
}


        for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }


				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');

				$pdf->output();
			}else{
				$customer_id = $_POST["customer_id"];

        $print_array = array();
        $product_array = array();
        $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                WHERE HKS.sales_transaction_type_id = '1' AND HKS.bill_date BETWEEN '$actualFromdate' AND '$actualTodate' AND HKP.id = '$customer_id' AND HKS.sales_active = '1'";
                $exe = mysqli_query($conn,$query);
                $x = 0;
                while($row = mysqli_fetch_array($exe)){
                  $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                  $print_array[$x]["bill_number"] = $row['bill_number'];
                  $print_array[$x]["total_amount_received"] = $row['total_amount_received'];
                  $cash_amount = $cash_amount+$row['total_amount_received'];
                  $sales_id = $row['id'];

                  $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                            FROM `hk_sales_products` AS HKSP
                            LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                            WHERE HKSP.sales_id = '$sales_id'";
                            $exe1 = mysqli_query($conn,$query1);
                            $y = 0;
                            while($row1 = mysqli_fetch_array($exe1)){
                              $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                              $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                              $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                              $product_array[$x][$y]["rate"] = $row1['rate'];
                              $product_array[$x][$y]["amount"] = $row1['amount'];
                              $y++;
                            }



// get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}
  $x++;
}

$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id =$customer_id AND HKSR.transaction_type_id=1
AND HKSR.date between '$actualFromdate' AND '$actualTodate'";

// echo "$directRQ";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproducts($directSRRow["id"]);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}


				$pdf = new PDF();
				$pdf->AliasNbPages();
				$pdf->AddPage();

				// $pdf->SetFont('Arial', 'B', 20);
				//
				// $pdf->SetTextColor(0,0,255);
				// $pdf->Cell(190,5,'K.ABDUL KAREEM & SONS',0,10,'C');
				// $pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell(130,5,'Sales Register Report :',0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Between Date : from '.$fromdate." to ".$todate,0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Cash Sales Of All Products',0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
				$pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
       for($x = 0; $x<count($print_array); $x++ ){
         $pdf->Cell(20,9,$sl_no,0,0,'C');
         $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
         $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
         $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

         for($y = 0; $y<count($product_array[$x]); $y++){
           $pdf->Cell(20,9,'',0,0,'C');
           $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
         }
         $sl_no++;


          if(!empty($returnArr[$x])){


// pdf
            $pdf->Cell(20,9,$sl_no,0,0,'C');
            $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

// $returnArr[$x][$index]['bill_number']
for($y = 0; $y< count($returnArr[$x]);$y++){
  $pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
  $pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


  for($index =0; $index<count($retProducts[$x][$y]); $index++){
    $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
    $pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
  }

$sl_no++;
  }
}
}

        for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }



				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');

				$pdf->output();
			}

		}
		else{
			if($_POST["customer"] == "allcustomers"){
				$product_id = $_POST["product_id"];
				$product_name;
				$productQuery = "SELECT * from `hk_products` WHERE id = '$product_id'";
				$exe = mysqli_query($conn,$productQuery);
				while($row = mysqli_fetch_array($exe)){
					$product_name = $row['name']." ".$row['type'];
				}

        $print_array = array();
        $product_array = array();
        $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                LEFT JOIN `hk_sales_products` AS HKSP ON HKSP.sales_id = HKS.id
                WHERE HKS.sales_transaction_type_id = '1' AND HKS.bill_date BETWEEN '$actualFromdate' AND '$actualTodate' AND HKSP.product_id = '$product_id' AND HKS.sales_active = '1'";
                $exe = mysqli_query($conn,$query);
                $x = 0;
                while($row = mysqli_fetch_array($exe)){
                  $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                  $print_array[$x]["bill_number"] = $row['bill_number'];
                  $print_array[$x]["total_amount_received"] = $row['total_amount_received'];
                  $cash_amount = $cash_amount+$row['total_amount_received'];
                  $sales_id = $row['id'];

                  $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                            FROM `hk_sales_products` AS HKSP
                            LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                            WHERE HKSP.sales_id = '$sales_id' AND HKSP.product_id = '$product_id'";
                            $exe1 = mysqli_query($conn,$query1);
                            $y = 0;
                            while($row1 = mysqli_fetch_array($exe1)){
                              $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                              $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                              $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                              $product_array[$x][$y]["rate"] = $row1['rate'];
                              $product_array[$x][$y]["amount"] = $row1['amount'];
                              $y++;
                            }

// get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}
   $x++;
}

$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id IS NOT NULL AND HKSR.date='$actualOndate' AND HKSR.transaction_type_id=1";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproductswithid($directSRRow["id"],$product_id);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}




				$pdf = new PDF();
				$pdf->AliasNbPages();
				$pdf->AddPage();

				// $pdf->SetFont('Arial', 'B', 20);
				//
				// $pdf->SetTextColor(0,0,255);
				// $pdf->Cell(190,5,'K.ABDUL KAREEM & SONS',0,10,'C');
				// $pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell(130,5,'Sales Register Report :',0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Between Date : from '.$fromdate." to ".$todate,0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Cash Sales Of '.$product_name,0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
				$pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
       for($x = 0; $x<count($print_array); $x++ ){
         $pdf->Cell(20,9,$sl_no,0,0,'C');
         $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
         $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
         $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

         for($y = 0; $y<count($product_array[$x]); $y++){
           $pdf->Cell(20,9,'',0,0,'C');
           $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
         }
         $sl_no++;

         if(!empty($returnArr[$x])){


// pdf
  $pdf->Cell(20,9,$sl_no,0,0,'C');
  $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

// $returnArr[$x][$index]['bill_number']
for($y = 0; $y< count($returnArr[$x]);$y++){
$pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
$pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


for($index =0; $index<count($retProducts[$x][$y]); $index++){
$pdf->Cell(20,9,'',0,0,'C');
// $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
$pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
}

$sl_no++;
}
}
}

        for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }


				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');

				$pdf->output();
			}else{
				$customer_id = $_POST["customer_id"];
				$product_id = $_POST["product_id"];
				$product_name;
				$productQuery = "SELECT * from `hk_products` WHERE id = '$product_id'";
				$exe = mysqli_query($conn,$productQuery);
				while($row = mysqli_fetch_array($exe)){
					$product_name = $row['name']." ".$row['type'];
				}

        $print_array = array();
        $product_array = array();
        $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                LEFT JOIN `hk_sales_products` AS HKSP ON HKSP.sales_id = HKS.id
                WHERE HKS.sales_transaction_type_id = '1'
                AND HKS.bill_date BETWEEN '$actualFromdate' AND '$actualTodate'
                AND HKSP.product_id = '$product_id' AND HKS.person_id = '$customer_id' AND HKS.sales_active = '1'";
                $exe = mysqli_query($conn,$query);
                $x = 0;
                while($row = mysqli_fetch_array($exe)){
                  $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                  $print_array[$x]["bill_number"] = $row['bill_number'];
                  $print_array[$x]["total_amount_received"] = $row['total_amount_received'];
                  $cash_amount = $cash_amount+$row['total_amount_received'];
                  $sales_id = $row['id'];

                  $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                            FROM `hk_sales_products` AS HKSP
                            LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                            WHERE HKSP.sales_id = '$sales_id' AND HKSP.product_id = '$product_id'";
                            $exe1 = mysqli_query($conn,$query1);
                            $y = 0;
                            while($row1 = mysqli_fetch_array($exe1)){
                              $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                              $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                              $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                              $product_array[$x][$y]["rate"] = $row1['rate'];
                              $product_array[$x][$y]["amount"] = $row1['amount'];
                              $y++;
                            }

                            // get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}
   $x++;
}

$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id = $customer_id AND HKSR.transaction_type_id=1
AND HKSR.date between '$actualFromdate' AND '$actualTodate'
";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproductswithid($directSRRow["id"],$product_id);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}




				$pdf = new PDF();
				$pdf->AliasNbPages();
				$pdf->AddPage();

				// $pdf->SetFont('Arial', 'B', 20);
				//
				// $pdf->SetTextColor(0,0,255);
				// $pdf->Cell(190,5,'K.ABDUL KAREEM & SONS',0,10,'C');
				// $pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell(130,5,'Sales Register Report :',0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Between Date : from '.$fromdate." to ".$todate,0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Cash Sales Of '.$product_name,0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
				$pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
       for($x = 0; $x<count($print_array); $x++ ){
         $pdf->Cell(20,9,$sl_no,0,0,'C');
         $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
         $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
         $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

         for($y = 0; $y<count($product_array[$x]); $y++){
           $pdf->Cell(20,9,'',0,0,'C');
           $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
         }
         $sl_no++;

         if(!empty($returnArr[$x])){


// pdf
       $pdf->Cell(20,9,$sl_no,0,0,'C');
       $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

// $returnArr[$x][$index]['bill_number']
for($y = 0; $y< count($returnArr[$x]);$y++){
$pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
$pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


for($index =0; $index<count($retProducts[$x][$y]); $index++){
$pdf->Cell(20,9,'',0,0,'C');
// $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
$pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
}

$sl_no++;
}
}
}


for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }


				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');

				$pdf->output();
			}

		}
	}
}




else if($_POST["transaction_type"] == "credit"){
	if($typeDate=="onDate"){
		if($_POST["product"] == "allproducts"){
			if($_POST["customer"] == "allcustomers"){


       $print_array = array();
       $product_array = array();
       $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                 LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                 WHERE HKS.sales_transaction_type_id = '2' AND HKS.bill_date = '$actualOndate' AND HKS.sales_active = '1'";
                 $exe = mysqli_query($conn,$query);
                 $x = 0;
                 while($row = mysqli_fetch_array($exe)){
                   $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                   $print_array[$x]["bill_number"] = $row['bill_number'];
                   $print_array[$x]["total_amount_received"] = $row['total_amount'];
                   $cash_amount = $cash_amount+$row['total_amount'];
                   $sales_id = $row['id'];

                   $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                             FROM `hk_sales_products` AS HKSP
                             LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                             WHERE HKSP.sales_id = '$sales_id'";
                             $exe1 = mysqli_query($conn,$query1);
                             $y = 0;
                             while($row1 = mysqli_fetch_array($exe1)){
                               $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                               $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                               $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                               $product_array[$x][$y]["rate"] = $row1['rate'];
                               $product_array[$x][$y]["amount"] = $row1['amount'];
                               $y++;
                             }


// get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}
$x++;
}

$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id IS NOT NULL AND HKSR.date='$actualOndate' AND HKSR.transaction_type_id=2";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproducts($directSRRow["id"]);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}




				$pdf = new PDF();
				$pdf->AliasNbPages();
				$pdf->AddPage();

				// $pdf->SetFont('Arial', 'B', 20);
				//
				// $pdf->SetTextColor(0,0,255);
				// $pdf->Cell(190,5,'K.ABDUL KAREEM & SONS',0,10,'C');
				// $pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell(130,5,'Sales Register Report :',0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Date : '.$ondate,0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Credit Sales Of All Products',0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
				$pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
         for($x = 0; $x<count($print_array); $x++ ){
           $pdf->Cell(20,9,$sl_no,0,0,'C');
           $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
           $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
           $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

           for($y = 0; $y<count($product_array[$x]); $y++){
             $pdf->Cell(20,9,'',0,0,'C');
             $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
           }
           $sl_no++;

           if(!empty($returnArr[$x])){


// pdf
      $pdf->Cell(20,9,$sl_no,0,0,'C');
      $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

// $returnArr[$x][$index]['bill_number']
for($y = 0; $y< count($returnArr[$x]);$y++){
$pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
$pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


for($index =0; $index<count($retProducts[$x][$y]); $index++){
$pdf->Cell(20,9,'',0,0,'C');
// $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
$pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
}

$sl_no++;
}
}
 }


         for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }


				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');

				$pdf->output();
			}else{
				$customer_id = $_POST["customer_id"];

        $print_array = array();
        $product_array = array();
        $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                  LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                  WHERE HKS.sales_transaction_type_id = '2' AND HKS.bill_date = '$actualOndate' AND HKS.sales_active = '1' AND HKS.person_id = '$customer_id'";
                  $exe = mysqli_query($conn,$query);
                  $x = 0;
                  while($row = mysqli_fetch_array($exe)){
                    $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                    $print_array[$x]["bill_number"] = $row['bill_number'];
                    $print_array[$x]["total_amount_received"] = $row['total_amount'];
                    $cash_amount = $cash_amount+$row['total_amount'];
                    $sales_id = $row['id'];

                    $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                              FROM `hk_sales_products` AS HKSP
                              LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                              WHERE HKSP.sales_id = '$sales_id'";
                              $exe1 = mysqli_query($conn,$query1);
                              $y = 0;
                              while($row1 = mysqli_fetch_array($exe1)){
                                $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                                $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                                $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                                $product_array[$x][$y]["rate"] = $row1['rate'];
                                $product_array[$x][$y]["amount"] = $row1['amount'];
                                $y++;
                              }

                              // get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}
  $x++;
}

$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id = $customer_id AND HKSR.date='$actualOndate' AND HKSR.transaction_type_id=2";

// echo "$directRQ";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproducts($directSRRow["id"]);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}



				$pdf = new PDF();
				$pdf->AliasNbPages();
				$pdf->AddPage();

				// $pdf->SetFont('Arial', 'B', 20);
				//
				// $pdf->SetTextColor(0,0,255);
				// $pdf->Cell(190,5,'K.ABDUL KAREEM & SONS',0,10,'C');
				// $pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell(130,5,'Sales Register Report :',0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Date : '.$ondate,0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Credit Sales Of All Products',0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
				$pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
         for($x = 0; $x<count($print_array); $x++ ){
           $pdf->Cell(20,9,$sl_no,0,0,'C');
           $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
           $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
           $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

           for($y = 0; $y<count($product_array[$x]); $y++){
             $pdf->Cell(20,9,'',0,0,'C');
             $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
           }
           $sl_no++;

           if(!empty($returnArr[$x])){


// pdf
       $pdf->Cell(20,9,$sl_no,0,0,'C');
       $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

// $returnArr[$x][$index]['bill_number']
for($y = 0; $y< count($returnArr[$x]);$y++){
$pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
$pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


for($index =0; $index<count($retProducts[$x][$y]); $index++){
$pdf->Cell(20,9,'',0,0,'C');
// $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
$pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
}

$sl_no++;
}
}
 }


         for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }


				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');

				$pdf->output();
			}

		}
		else{
			if($_POST["customer"] == "allcustomers"){
				$product_id = $_POST["product_id"];
				$product_name;
				$productQuery = "SELECT * from `hk_products` WHERE id = '$product_id'";
				$exe = mysqli_query($conn,$productQuery);
				while($row = mysqli_fetch_array($exe)){
					$product_name = $row['name']." ".$row['type'];
				}

        $print_array = array();
        $product_array = array();
        $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                  LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                  LEFT JOIN `hk_sales_products` AS HKSP ON HKSP.sales_id = HKS.id
                  WHERE HKS.sales_transaction_type_id = '2' AND HKS.bill_date = '$actualOndate' AND HKS.sales_active = '1' AND HKSP.product_id = '$product_id'";
                  $exe = mysqli_query($conn,$query);
                  $x = 0;
                  while($row = mysqli_fetch_array($exe)){
                    $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                    $print_array[$x]["bill_number"] = $row['bill_number'];
                    $print_array[$x]["total_amount_received"] = $row['total_amount'];
                    $cash_amount = $cash_amount+$row['total_amount'];
                    $sales_id = $row['id'];

                    $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                              FROM `hk_sales_products` AS HKSP
                              LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                              WHERE HKSP.sales_id = '$sales_id' AND HKSP.product_id = '$product_id'";
                              $exe1 = mysqli_query($conn,$query1);
                              $y = 0;
                              while($row1 = mysqli_fetch_array($exe1)){
                                $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                                $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                                $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                                $product_array[$x][$y]["rate"] = $row1['rate'];
                                $product_array[$x][$y]["amount"] = $row1['amount'];
                                $y++;
                              }


// get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}
  $x++;
}

$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id IS NOT NULL AND HKSR.date='$actualOndate' AND HKSR.transaction_type_id=2";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproductswithid($directSRRow["id"],$product_id);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}





				$pdf = new PDF();
				$pdf->AliasNbPages();
				$pdf->AddPage();

				// $pdf->SetFont('Arial', 'B', 20);
				//
				// $pdf->SetTextColor(0,0,255);
				// $pdf->Cell(190,5,'K.ABDUL KAREEM & SONS',0,10,'C');
				// $pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell(130,5,'Sales Register Report :',0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Date : '.$ondate,0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Credit Sales Of '.$product_name,0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
				$pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
         for($x = 0; $x<count($print_array); $x++ ){
           $pdf->Cell(20,9,$sl_no,0,0,'C');
           $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
           $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
           $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

           for($y = 0; $y<count($product_array[$x]); $y++){
             $pdf->Cell(20,9,'',0,0,'C');
             $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
           }
           $sl_no++;

           if(!empty($returnArr[$x])){


// pdf
       $pdf->Cell(20,9,$sl_no,0,0,'C');
       $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

// $returnArr[$x][$index]['bill_number']
for($y = 0; $y< count($returnArr[$x]);$y++){
$pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
$pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


for($index =0; $index<count($retProducts[$x][$y]); $index++){
$pdf->Cell(20,9,'',0,0,'C');
// $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
$pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
}

$sl_no++;
}
}
 }


         for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }


				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');

				$pdf->output();
			}else{
				$customer_id = $_POST["customer_id"];
				$product_id = $_POST["product_id"];
				$product_name;
				$productQuery = "SELECT * from `hk_products` WHERE id = '$product_id'";
				$exe = mysqli_query($conn,$productQuery);
				while($row = mysqli_fetch_array($exe)){
					$product_name = $row['name']." ".$row['type'];
				}

        $print_array = array();
        $product_array = array();
        $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                  LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                  LEFT JOIN `hk_sales_products` AS HKSP ON HKSP.sales_id = HKS.id
                  WHERE HKS.sales_transaction_type_id = '2' AND HKS.bill_date = '$actualOndate' AND HKS.sales_active = '1' AND HKSP.product_id = '$product_id' AND HKS.person_id = '$customer_id'";
                  $exe = mysqli_query($conn,$query);
                  $x = 0;
                  while($row = mysqli_fetch_array($exe)){
                    $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                    $print_array[$x]["bill_number"] = $row['bill_number'];
                    $print_array[$x]["total_amount_received"] = $row['total_amount'];
                    $cash_amount = $cash_amount+$row['total_amount'];
                    $sales_id = $row['id'];

                    $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                              FROM `hk_sales_products` AS HKSP
                              LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                              WHERE HKSP.sales_id = '$sales_id' AND HKSP.product_id = '$product_id'";
                              $exe1 = mysqli_query($conn,$query1);
                              $y = 0;
                              while($row1 = mysqli_fetch_array($exe1)){
                                $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                                $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                                $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                                $product_array[$x][$y]["rate"] = $row1['rate'];
                                $product_array[$x][$y]["amount"] = $row1['amount'];
                                $y++;
                              }

                              // get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}
$x++;
}

$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id = $customer_id AND HKSR.date='$actualOndate' AND HKSR.transaction_type_id=2";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproductswithid($directSRRow["id"],$product_id);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}


				$pdf = new PDF();
				$pdf->AliasNbPages();
				$pdf->AddPage();

				// $pdf->SetFont('Arial', 'B', 20);
				//
				// $pdf->SetTextColor(0,0,255);
				// $pdf->Cell(190,5,'K.ABDUL KAREEM & SONS',0,10,'C');
				// $pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell(130,5,'Sales Register Report :',0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Date : '.$ondate,0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Credit Sales Of '.$product_name,0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
				$pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
         for($x = 0; $x<count($print_array); $x++ ){
           $pdf->Cell(20,9,$sl_no,0,0,'C');
           $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
           $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
           $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

           for($y = 0; $y<count($product_array[$x]); $y++){
             $pdf->Cell(20,9,'',0,0,'C');
             $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
           }
           $sl_no++;

           if(!empty($returnArr[$x])){


// pdf
           $pdf->Cell(20,9,$sl_no,0,0,'C');
           $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

// $returnArr[$x][$index]['bill_number']
for($y = 0; $y< count($returnArr[$x]);$y++){
 $pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
 $pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


 for($index =0; $index<count($retProducts[$x][$y]); $index++){
   $pdf->Cell(20,9,'',0,0,'C');
   // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
   $pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
 }

$sl_no++;
 }
}
 }

         for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }


				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');

				$pdf->output();
			}

		}
	}
	else if($typeDate=="btDate"){
		if($_POST["product"] == "allproducts"){
			if($_POST["customer"] == "allcustomers"){

        $print_array = array();
      $product_array = array();
      $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                WHERE HKS.sales_transaction_type_id = '2' AND HKS.bill_date BETWEEN '$actualFromdate' AND '$actualTodate' AND HKS.sales_active = '1'";
                $exe = mysqli_query($conn,$query);
                $x = 0;
                while($row = mysqli_fetch_array($exe)){
                  $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                  $print_array[$x]["bill_number"] = $row['bill_number'];
                  $print_array[$x]["total_amount_received"] = $row['total_amount'];
                  $cash_amount = $cash_amount+$row['total_amount'];
                  $sales_id = $row['id'];

                  $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                            FROM `hk_sales_products` AS HKSP
                            LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                            WHERE HKSP.sales_id = '$sales_id'";
                            $exe1 = mysqli_query($conn,$query1);
                            $y = 0;
                            while($row1 = mysqli_fetch_array($exe1)){
                              $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                              $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                              $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                              $product_array[$x][$y]["rate"] = $row1['rate'];
                              $product_array[$x][$y]["amount"] = $row1['amount'];
                              $y++;
                            }


// get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}
$x++;
}


$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id IS NOT NULL AND HKSR.transaction_type_id=2
AND HKSR.date BETWEEN '$actualFromdate' AND '$actualTodate'";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproducts($directSRRow["id"]);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}




				$pdf = new PDF();
				$pdf->AliasNbPages();
				$pdf->AddPage();

				// $pdf->SetFont('Arial', 'B', 20);
				//
				// $pdf->SetTextColor(0,0,255);
				// $pdf->Cell(190,5,'K.ABDUL KAREEM & SONS',0,10,'C');
				// $pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell(130,5,'Sales Register Report :',0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Between Date : from '.$fromdate." to ".$todate,0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Credit Sales Of All Products',0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
				$pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
        for($x = 0; $x<count($print_array); $x++ ){
          $pdf->Cell(20,9,$sl_no,0,0,'C');
          $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
          $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

          for($y = 0; $y<count($product_array[$x]); $y++){
            $pdf->Cell(20,9,'',0,0,'C');
            $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
          }
          $sl_no++;

          if(!empty($returnArr[$x])){


// pdf
         $pdf->Cell(20,9,$sl_no,0,0,'C');
         $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

// $returnArr[$x][$index]['bill_number']
for($y = 0; $y< count($returnArr[$x]);$y++){
$pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
$pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


for($index =0; $index<count($retProducts[$x][$y]); $index++){
 $pdf->Cell(20,9,'',0,0,'C');
 // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
 $pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
}

$sl_no++;
}
}
}


        for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }


				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');

				$pdf->output();
			}else{
				$customer_id = $_POST["customer_id"];

        $print_array = array();
       $product_array = array();
       $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                 LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                 WHERE HKS.sales_transaction_type_id = '2' AND HKS.bill_date BETWEEN '$actualFromdate' AND '$actualTodate' AND HKS.sales_active = '1' AND HKS.person_id = '$customer_id'";
                 $exe = mysqli_query($conn,$query);
                 $x = 0;
                 while($row = mysqli_fetch_array($exe)){
                   $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                   $print_array[$x]["bill_number"] = $row['bill_number'];
                   $print_array[$x]["total_amount_received"] = $row['total_amount'];
                   $cash_amount = $cash_amount+$row['total_amount'];
                   $sales_id = $row['id'];

                   $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                             FROM `hk_sales_products` AS HKSP
                             LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                             WHERE HKSP.sales_id = '$sales_id'";
                             $exe1 = mysqli_query($conn,$query1);
                             $y = 0;
                             while($row1 = mysqli_fetch_array($exe1)){
                               $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                               $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                               $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                               $product_array[$x][$y]["rate"] = $row1['rate'];
                               $product_array[$x][$y]["amount"] = $row1['amount'];
                               $y++;
                             }



// get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}
$x++;
}

$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id =$customer_id AND HKSR.transaction_type_id=2
AND HKSR.date BETWEEN '$actualFromdate' AND '$actualTodate'";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproducts($directSRRow["id"]);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}




				$pdf = new PDF();
				$pdf->AliasNbPages();
				$pdf->AddPage();

				// $pdf->SetFont('Arial', 'B', 20);
				//
				// $pdf->SetTextColor(0,0,255);
				// $pdf->Cell(190,5,'K.ABDUL KAREEM & SONS',0,10,'C');
				// $pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell(130,5,'Sales Register Report :',0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Between Date : from '.$fromdate." to ".$todate,0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Credit Sales Of All Products',0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
				$pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
         for($x = 0; $x<count($print_array); $x++ ){
           $pdf->Cell(20,9,$sl_no,0,0,'C');
           $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
           $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
           $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

           for($y = 0; $y<count($product_array[$x]); $y++){
             $pdf->Cell(20,9,'',0,0,'C');
             $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
           }
           $sl_no++;

           if(!empty($returnArr[$x])){


// pdf
           $pdf->Cell(20,9,$sl_no,0,0,'C');
           $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

// $returnArr[$x][$index]['bill_number']
for($y = 0; $y< count($returnArr[$x]);$y++){
 $pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
 $pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


 for($index =0; $index<count($retProducts[$x][$y]); $index++){
   $pdf->Cell(20,9,'',0,0,'C');
   // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
   $pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
 }

$sl_no++;
 }
}
}


        for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }


				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');

				$pdf->output();
			}

		}
		else{
			if($_POST["customer"] == "allcustomers"){
				$product_id = $_POST["product_id"];
				$product_name;
				$productQuery = "SELECT * from `hk_products` WHERE id = '$product_id'";
				$exe = mysqli_query($conn,$productQuery);
				while($row = mysqli_fetch_array($exe)){
					$product_name = $row['name']." ".$row['type'];
				}


        $print_array = array();
        $product_array = array();
        $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                  LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                  LEFT JOIN `hk_sales_products` AS HKSP ON HKSP.sales_id = HKS.id
                  WHERE HKS.sales_transaction_type_id = '2' AND HKS.bill_date BETWEEN '$actualFromdate' AND '$actualTodate' AND HKS.sales_active = '1' AND HKSP.product_id = '$product_id'";
                  $exe = mysqli_query($conn,$query);
                  $x = 0;
                  while($row = mysqli_fetch_array($exe)){
                    $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                    $print_array[$x]["bill_number"] = $row['bill_number'];
                    $print_array[$x]["total_amount_received"] = $row['total_amount'];
                    $cash_amount = $cash_amount+$row['total_amount'];
                    $sales_id = $row['id'];

                    $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                              FROM `hk_sales_products` AS HKSP
                              LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                              WHERE HKSP.sales_id = '$sales_id' AND HKSP.product_id = '$product_id'";
                              $exe1 = mysqli_query($conn,$query1);
                              $y = 0;
                              while($row1 = mysqli_fetch_array($exe1)){
                                $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                                $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                                $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                                $product_array[$x][$y]["rate"] = $row1['rate'];
                                $product_array[$x][$y]["amount"] = $row1['amount'];
                                $y++;
                              }



// get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}
 $x++;
}


$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id IS NOT NULL AND HKSR.transaction_type_id=2
AND HKSR.date BETWEEN '$actualFromdate' AND '$actualTodate'";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproductswithid($directSRRow["id"],$product_id);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}




				$pdf = new PDF();
				$pdf->AliasNbPages();
				$pdf->AddPage();

				// $pdf->SetFont('Arial', 'B', 20);
				//
				// $pdf->SetTextColor(0,0,255);
				// $pdf->Cell(190,5,'K.ABDUL KAREEM & SONS',0,10,'C');
				// $pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell(130,5,'Sales Register Report :',0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Between Date : from '.$fromdate." to ".$todate,0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Credit Sales Of '.$product_name,0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
				$pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
          for($x = 0; $x<count($print_array); $x++ ){
            $pdf->Cell(20,9,$sl_no,0,0,'C');
            $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
            $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
            $pdf->Cell(68,9,money_format('%!i',strtoupper($print_array[$x]["total_amount_received"])),0,1,'R');

            for($y = 0; $y<count($product_array[$x]); $y++){
              $pdf->Cell(20,9,'',0,0,'C');
              $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
            }
            $sl_no++;

            if(!empty($returnArr[$x])){


// pdf
          $pdf->Cell(20,9,$sl_no,0,0,'C');
          $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

// $returnArr[$x][$index]['bill_number']
for($y = 0; $y< count($returnArr[$x]);$y++){
$pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
$pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


for($index =0; $index<count($retProducts[$x][$y]); $index++){
  $pdf->Cell(20,9,'',0,0,'C');
  // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
  $pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
}

$sl_no++;
}
}
 }

         for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }


				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');

				$pdf->output();
			}
			else{
				$customer_id = $_POST["customer_id"];
				$product_id = $_POST["product_id"];
				$product_name;
				$productQuery = "SELECT * from `hk_products` WHERE id = '$product_id'";
				$exe = mysqli_query($conn,$productQuery);
				while($row = mysqli_fetch_array($exe)){
					$product_name = $row['name']." ".$row['type'];
				}

        $print_array = array();
        $product_array = array();
        $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                  LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                  LEFT JOIN `hk_sales_products` AS HKSP ON HKSP.sales_id = HKS.id
                  WHERE HKS.sales_transaction_type_id = '2' AND HKS.bill_date BETWEEN '$actualFromdate' AND '$actualTodate' AND HKS.sales_active = '1' AND HKSP.product_id = '$product_id' AND HKS.person_id = '$customer_id'";
                  $exe = mysqli_query($conn,$query);
                  $x = 0;
                  while($row = mysqli_fetch_array($exe)){
                    $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                    $print_array[$x]["bill_number"] = $row['bill_number'];
                    $print_array[$x]["total_amount_received"] = $row['total_amount'];
                    $cash_amount = $cash_amount+$row['total_amount'];
                    $sales_id = $row['id'];

                    $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                              FROM `hk_sales_products` AS HKSP
                              LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                              WHERE HKSP.sales_id = '$sales_id' AND HKSP.product_id = '$product_id'";
                              $exe1 = mysqli_query($conn,$query1);
                              $y = 0;
                              while($row1 = mysqli_fetch_array($exe1)){
                                $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                                $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                                $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                                $product_array[$x][$y]["rate"] = $row1['rate'];
                                $product_array[$x][$y]["amount"] = $row1['amount'];
                                $y++;
                              }



// get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}
 $x++;
}


$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id =$customer_id AND HKSR.transaction_type_id=2
AND HKSR.date BETWEEN '$actualFromdate' AND '$actualTodate'";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproductswithid($directSRRow["id"],$product_id);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}




				$pdf = new PDF();
				$pdf->AliasNbPages();
				$pdf->AddPage();

				// $pdf->SetFont('Arial', 'B', 20);
				//
				// $pdf->SetTextColor(0,0,255);
				// $pdf->Cell(190,5,'K.ABDUL KAREEM & SONS',0,10,'C');
				// $pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell(130,5,'Sales Register Report :',0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Between Date : from '.$fromdate." to ".$todate,0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Credit Sales Of '.$product_name,0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
				$pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
           for($x = 0; $x<count($print_array); $x++ ){
             $pdf->Cell(20,9,$sl_no,0,0,'C');
             $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
             $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
             $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

             for($y = 0; $y<count($product_array[$x]); $y++){
               $pdf->Cell(20,9,'',0,0,'C');
               $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
             }
             $sl_no++;


          if(!empty($returnArr[$x])){


// pdf
            $pdf->Cell(20,9,$sl_no,0,0,'C');
            $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

// $returnArr[$x][$index]['bill_number']
for($y = 0; $y< count($returnArr[$x]);$y++){
  $pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
  $pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


  for($index =0; $index<count($retProducts[$x][$y]); $index++){
    $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
    $pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
  }

$sl_no++;
  }
}
}


        for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }


				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');

				$pdf->output();
			}

		}
	}
}



else{
	if($typeDate=="onDate"){
		if($_POST["product"] == "allproducts"){
			if($_POST["customer"] == "allcustomers"){

        $print_array = array();
       $product_array = array();
       $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                 LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                 WHERE HKS.sales_transaction_type_id = '1' AND HKS.bill_date = '$actualOndate' AND HKS.sales_active = '1'";
                 $exe = mysqli_query($conn,$query);
                 $x = 0;
                 while($row = mysqli_fetch_array($exe)){
                   $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                   $print_array[$x]["bill_number"] = $row['bill_number'];
                   $print_array[$x]["total_amount_received"] = $row['total_amount_received'];
                   $cash_amount = $cash_amount+$row['total_amount_received'];
                   $sales_id = $row['id'];

                   $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                             FROM `hk_sales_products` AS HKSP
                             LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                             WHERE HKSP.sales_id = '$sales_id'";
                             $exe1 = mysqli_query($conn,$query1);
                             $y = 0;
                             while($row1 = mysqli_fetch_array($exe1)){
                               $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                               $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                               $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                               $product_array[$x][$y]["rate"] = $row1['rate'];
                               $product_array[$x][$y]["amount"] = $row1['amount'];
                               $y++;
                             }

                             // get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}
 $x++;
}

// cash sales

$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id IS NOT NULL AND HKSR.transaction_type_id=1
AND HKSR.date ='$actualOndate'";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproducts($directSRRow["id"]);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}


				$pdf = new PDF();
				$pdf->AliasNbPages();
				$pdf->AddPage();

				// $pdf->SetFont('Arial', 'B', 20);
				//
				// $pdf->SetTextColor(0,0,255);
				// $pdf->Cell(190,5,'K.ABDUL KAREEM & SONS',0,10,'C');
				// $pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell(130,5,'Sales Register Report :',0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Date : '.$ondate,0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Cash Sales Of All Products',0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
        $pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
         for($x = 0; $x<count($print_array); $x++ ){
           $pdf->Cell(20,9,$sl_no,0,0,'C');
           $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
           $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
           $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

           for($y = 0; $y<count($product_array[$x]); $y++){
             $pdf->Cell(20,9,'',0,0,'C');
             $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
           }
           $sl_no++;

           if(!empty($returnArr[$x])){


// pdf
        $pdf->Cell(20,9,$sl_no,0,0,'C');
        $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

// $returnArr[$x][$index]['bill_number']
for($y = 0; $y< count($returnArr[$x]);$y++){
$pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
$pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


for($index =0; $index<count($retProducts[$x][$y]); $index++){
$pdf->Cell(20,9,'',0,0,'C');
// $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
$pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
}

$sl_no++;
}
}
 }

 for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }


				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');
        $cash_amount = 0;
        $print_array = array();
       $product_array = array();
       $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                 LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                 WHERE HKS.sales_transaction_type_id = '2' AND HKS.bill_date = '$actualOndate' AND HKS.sales_active = '1'";
                 $exe = mysqli_query($conn,$query);
                 $x = 0;
                 while($row = mysqli_fetch_array($exe)){
                   $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                   $print_array[$x]["bill_number"] = $row['bill_number'];
                   $print_array[$x]["total_amount_received"] = $row['total_amount'];
                   $cash_amount = $cash_amount+$row['total_amount'];
                   $sales_id = $row['id'];

                   $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                             FROM `hk_sales_products` AS HKSP
                             LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                             WHERE HKSP.sales_id = '$sales_id'";
                             $exe1 = mysqli_query($conn,$query1);
                             $y = 0;
                             while($row1 = mysqli_fetch_array($exe1)){
                               $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                               $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                               $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                               $product_array[$x][$y]["rate"] = $row1['rate'];
                               $product_array[$x][$y]["amount"] = $row1['amount'];
                               $y++;
                             }



                             // get id from sales return table
                             $getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

                             $getIdExe = mysqli_query($conn,$getRId);





                               $index = 0;
                               while($getIdRow = mysqli_fetch_array($getIdExe)) {
                                 // code...

                                 if(empty($getIdRow)){
                                   $returnArr[$x][$index]['id'] =null;
                                   $returnArr[$x][$index]['bill_number'] = null;
                                   $returnArr[$x][$index]['returnAmount'] =null;
                                 }

                                 $returnArr[$x][$index]['id'] = $getIdRow["id"];
                                 $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
                                 $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
                                 $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
                                 $index++;
                               }






                             // get return products from sales_return_products for that id

                             error_reporting(E_ERROR | E_PARSE);

                             if(count($returnArr[$x])>0){
                               // echo count($returnArr[$x]);
                               // print_r($returnArr[$x]);
                               // echo "<br>";
                               for($a = 0; $a<count($returnArr[$x]); $a++){
                                 $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
                               left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
                               // echo "$retProductsQ <br>";
                                 $retProdcutExe = mysqli_query($conn,$retProductsQ);
                               echo mysqli_error($conn);
                               $indx =0;
                                 while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
                                   // code...
                                   $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
                                   // echo $retProducts[$x][$a][$indx]["Particulars"];
                                   $indx++;
                                 }


                               }
                             }

                   $x++;
                 }

// for credit sales
$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id IS NOT NULL AND HKSR.transaction_type_id=2
AND HKSR.date ='$actualOndate'";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproducts($directSRRow["id"]);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}





				$pdf->Ln();
				$pdf->SetFont('Arial', 'B', 12);
				$pdf->Cell(130,5,'Credit Sales Of All Products',0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
        $pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
       for($x = 0; $x<count($print_array); $x++ ){
         $pdf->Cell(20,9,$sl_no,0,0,'C');
         $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
         $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
         $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

         for($y = 0; $y<count($product_array[$x]); $y++){
           $pdf->Cell(20,9,'',0,0,'C');
           $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
         }
         $sl_no++;

         if(!empty($returnArr[$x])){


// pdf
            $pdf->Cell(20,9,$sl_no,0,0,'C');
            $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

// $returnArr[$x][$index]['bill_number']
for($y = 0; $y< count($returnArr[$x]);$y++){
  $pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
  $pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


  for($index =0; $index<count($retProducts[$x][$y]); $index++){
    $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
    $pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
  }

$sl_no++;
  }
}
}

 for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }

				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');

				$pdf->output();
			}else{
				$customer_id = $_POST["customer_id"];
				$pdf = new PDF();
				$pdf->AliasNbPages();
				$pdf->AddPage();

        $print_array = array();
       $product_array = array();
       $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                 LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                 WHERE HKS.sales_transaction_type_id = '1' AND HKS.bill_date = '$actualOndate' AND HKP.id = '$customer_id' AND HKS.sales_active = '1'";
                 $exe = mysqli_query($conn,$query);
                 $x = 0;
                 while($row = mysqli_fetch_array($exe)){
                   $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                   $print_array[$x]["bill_number"] = $row['bill_number'];
                   $print_array[$x]["total_amount_received"] = $row['total_amount_received'];
                   $cash_amount = $cash_amount+$row['total_amount_received'];
                   $sales_id = $row['id'];

                   $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                             FROM `hk_sales_products` AS HKSP
                             LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                             WHERE HKSP.sales_id = '$sales_id'";
                             $exe1 = mysqli_query($conn,$query1);
                             $y = 0;
                             while($row1 = mysqli_fetch_array($exe1)){
                               $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                               $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                               $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                               $product_array[$x][$y]["rate"] = $row1['rate'];
                               $product_array[$x][$y]["amount"] = $row1['amount'];
                               $y++;
                             }


// get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}
$x++;
}


// cash sales & cash return


$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id = $customer_id AND HKSR.transaction_type_id=1
AND HKSR.date ='$actualOndate'";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproducts($directSRRow["id"]);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}


				// $pdf->SetFont('Arial', 'B', 20);
				//
				// $pdf->SetTextColor(0,0,255);
				// $pdf->Cell(190,5,'K.ABDUL KAREEM & SONS',0,10,'C');
				// $pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell(130,5,'Sales Register Report :',0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Date : '.$ondate,0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Cash Sales Of All Products',0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
        $pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
        for($x = 0; $x<count($print_array); $x++ ){
          $pdf->Cell(20,9,$sl_no,0,0,'C');
          $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
          $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

          for($y = 0; $y<count($product_array[$x]); $y++){
            $pdf->Cell(20,9,'',0,0,'C');
            $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
          }
          $sl_no++;

          if(!empty($returnArr[$x])){


// pdf
           $pdf->Cell(20,9,$sl_no,0,0,'C');
           $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

// $returnArr[$x][$index]['bill_number']
for($y = 0; $y< count($returnArr[$x]);$y++){
 $pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
 $pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


 for($index =0; $index<count($retProducts[$x][$y]); $index++){
   $pdf->Cell(20,9,'',0,0,'C');
   // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
   $pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
 }

$sl_no++;
 }
}
}



        for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }



				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');
        $cash_amount = 0;
        $print_array = array();
        $product_array = array();
        $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                  LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                  WHERE HKS.sales_transaction_type_id = '2' AND HKS.bill_date = '$actualOndate' AND HKS.sales_active = '1' AND HKS.person_id = '$customer_id'";
                  $exe = mysqli_query($conn,$query);
                  $x = 0;
                  while($row = mysqli_fetch_array($exe)){
                    $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                    $print_array[$x]["bill_number"] = $row['bill_number'];
                    $print_array[$x]["total_amount_received"] = $row['total_amount'];
                    $cash_amount = $cash_amount+$row['total_amount'];
                    $sales_id = $row['id'];

                    $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                              FROM `hk_sales_products` AS HKSP
                              LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                              WHERE HKSP.sales_id = '$sales_id'";
                              $exe1 = mysqli_query($conn,$query1);
                              $y = 0;
                              while($row1 = mysqli_fetch_array($exe1)){
                                $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                                $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                                $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                                $product_array[$x][$y]["rate"] = $row1['rate'];
                                $product_array[$x][$y]["amount"] = $row1['amount'];
                                $y++;
                              }


// get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}
 $x++;
}

// for credit sales & return

$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id = $customer_id AND HKSR.transaction_type_id=2
AND HKSR.date = '$actualOndate'";



$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproducts($directSRRow["id"]);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}




				$pdf->Ln();
				$pdf->SetFont('Arial', 'B', 12);
				$pdf->Cell(130,5,'Credit Sales Of All Products',0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
        $pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
         for($x = 0; $x<count($print_array); $x++ ){
           $pdf->Cell(20,9,$sl_no,0,0,'C');
           $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
           $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
           $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

           for($y = 0; $y<count($product_array[$x]); $y++){
             $pdf->Cell(20,9,'',0,0,'C');
             $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
           }
           $sl_no++;



          if(!empty($returnArr[$x])){


// pdf
            $pdf->Cell(20,9,$sl_no,0,0,'C');
            $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

// $returnArr[$x][$index]['bill_number']
for($y = 0; $y< count($returnArr[$x]);$y++){
  $pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
  $pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


  for($index =0; $index<count($retProducts[$x][$y]); $index++){
    $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
    $pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
  }

$sl_no++;
  }
}
}



        for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }



				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');

				$pdf->output();
			}

		}
		else{
			if($_POST["customer"] == "allcustomers"){
				$product_id = $_POST["product_id"];
				$product_name;
				$productQuery = "SELECT * from `hk_products` WHERE id = '$product_id'";
				$exe = mysqli_query($conn,$productQuery);
				while($row = mysqli_fetch_array($exe)){
					$product_name = $row['name']." ".$row['type'];
				}

        $print_array = array();
        $product_array = array();
        $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                  LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                  LEFT JOIN `hk_sales_products` AS HKSP ON HKSP.sales_id = HKS.id
                  WHERE HKS.sales_transaction_type_id = '1' AND HKS.bill_date = '$actualOndate' AND HKSP.product_id = '$product_id' AND HKS.sales_active = '1'";
                  $exe = mysqli_query($conn,$query);
                  $x = 0;
                  while($row = mysqli_fetch_array($exe)){
                    $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                    $print_array[$x]["bill_number"] = $row['bill_number'];
                    $print_array[$x]["total_amount_received"] = $row['total_amount_received'];
                    $cash_amount = $cash_amount+$row['total_amount_received'];
                    $sales_id = $row['id'];

                    $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                              FROM `hk_sales_products` AS HKSP
                              LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                              WHERE HKSP.sales_id = '$sales_id' AND HKSP.product_id = '$product_id'";
                              $exe1 = mysqli_query($conn,$query1);
                              $y = 0;
                              while($row1 = mysqli_fetch_array($exe1)){
                                $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                                $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                                $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                                $product_array[$x][$y]["rate"] = $row1['rate'];
                                $product_array[$x][$y]["amount"] = $row1['amount'];
                                $y++;
                              }

// get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}
  $x++;
}

// for cash sales and cash return

$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id IS NOT NULL AND HKSR.transaction_type_id=1
AND HKSR.date = '$actualOndate'";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproductswithid($directSRRow["id"],$product_id);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}




				$pdf = new PDF();
				$pdf->AliasNbPages();
				$pdf->AddPage();

				// $pdf->SetFont('Arial', 'B', 20);
				//
				// $pdf->SetTextColor(0,0,255);
				// $pdf->Cell(190,5,'K.ABDUL KAREEM & SONS',0,10,'C');
				// $pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell(130,5,'Sales Register Report :',0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Date : '.$ondate,0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Cash Sales Of '.$product_name,0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
        $pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
          for($x = 0; $x<count($print_array); $x++ ){
            $pdf->Cell(20,9,$sl_no,0,0,'C');
            $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
            $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
            $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

            for($y = 0; $y<count($product_array[$x]); $y++){
              $pdf->Cell(20,9,'',0,0,'C');
              $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
            }
            $sl_no++;

                    if(!empty($returnArr[$x])){


// pdf
        $pdf->Cell(20,9,$sl_no,0,0,'C');
        $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

// $returnArr[$x][$index]['bill_number']
for($y = 0; $y< count($returnArr[$x]);$y++){
$pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
$pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


for($index =0; $index<count($retProducts[$x][$y]); $index++){
$pdf->Cell(20,9,'',0,0,'C');
// $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
$pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
}

$sl_no++;
}
}
}


        for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }


				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');
        $cash_amount = 0;
        $print_array = array();
        $product_array = array();
        $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                  LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                  LEFT JOIN `hk_sales_products` AS HKSP ON HKSP.sales_id = HKS.id
                  WHERE HKS.sales_transaction_type_id = '2' AND HKS.bill_date = '$actualOndate' AND HKS.sales_active = '1' AND HKSP.product_id = '$product_id'";
                  $exe = mysqli_query($conn,$query);
                  $x = 0;
                  while($row = mysqli_fetch_array($exe)){
                    $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                    $print_array[$x]["bill_number"] = $row['bill_number'];
                    $print_array[$x]["total_amount_received"] = $row['total_amount'];
                    $cash_amount = $cash_amount+$row['total_amount'];
                    $sales_id = $row['id'];

                    $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                              FROM `hk_sales_products` AS HKSP
                              LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                              WHERE HKSP.sales_id = '$sales_id' AND HKSP.product_id = '$product_id'";
                              $exe1 = mysqli_query($conn,$query1);
                              $y = 0;
                              while($row1 = mysqli_fetch_array($exe1)){
                                $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                                $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                                $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                                $product_array[$x][$y]["rate"] = $row1['rate'];
                                $product_array[$x][$y]["amount"] = $row1['amount'];
                                $y++;
                              }



// get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }

// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}
 $x++;
}


// for credit sales & credit_sales_return

$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id IS NOT NULL AND HKSR.transaction_type_id=2
AND HKSR.date = '$actualOndate'";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproductswithid($directSRRow["id"],$product_id);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}




				$pdf->Ln();
				$pdf->SetFont('Arial', 'B', 12);
				$pdf->Cell(130,5,'Credit Sales Of '.$product_name,0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
        $pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
        for($x = 0; $x<count($print_array); $x++ ){
          $pdf->Cell(20,9,$sl_no,0,0,'C');
          $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
          $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

          for($y = 0; $y<count($product_array[$x]); $y++){
            $pdf->Cell(20,9,'',0,0,'C');
            $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
          }
          $sl_no++;

          if(!empty($returnArr[$x])){


// pdf
         $pdf->Cell(20,9,$sl_no,0,0,'C');
         $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

// $returnArr[$x][$index]['bill_number']
for($y = 0; $y< count($returnArr[$x]);$y++){
$pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
$pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


for($index =0; $index<count($retProducts[$x][$y]); $index++){
 $pdf->Cell(20,9,'',0,0,'C');
 // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
 $pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'C');
}

$sl_no++;
}
}
}



        for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }



				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');
				$pdf->output();
			}else{
				$customer_id = $_POST["customer_id"];
				$product_id = $_POST["product_id"];
				$product_name;
				$productQuery = "SELECT * from `hk_products` WHERE id = '$product_id'";
				$exe = mysqli_query($conn,$productQuery);
				while($row = mysqli_fetch_array($exe)){
					$product_name = $row['name']." ".$row['type'];
				}

        $print_array = array();
        $product_array = array();
        $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                  LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                  LEFT JOIN `hk_sales_products` AS HKSP ON HKSP.sales_id = HKS.id
                  WHERE HKS.sales_transaction_type_id = '1' AND HKS.bill_date = '$actualOndate' AND HKSP.product_id = '$product_id' AND HKP.id = '$customer_id' AND HKS.sales_active = '1'";
                  $exe = mysqli_query($conn,$query);
                  $x = 0;
                 while($row = mysqli_fetch_array($exe)){
                   $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                   $print_array[$x]["bill_number"] = $row['bill_number'];
                   $print_array[$x]["total_amount_received"] = $row['total_amount_received'];
                   $cash_amount = $cash_amount+$row['total_amount_received'];
                   $sales_id = $row['id'];

                   $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                             FROM `hk_sales_products` AS HKSP
                             LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                             WHERE HKSP.sales_id = '$sales_id' AND HKSP.product_id = '$product_id'";
                             $exe1 = mysqli_query($conn,$query1);
                             $y = 0;
                             while($row1 = mysqli_fetch_array($exe1)){
                               $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                               $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                               $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                               $product_array[$x][$y]["rate"] = $row1['rate'];
                               $product_array[$x][$y]["amount"] = $row1['amount'];
                               $y++;
                             }



// get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}
$x++;
}

// cash sales & cash return

$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id =$customer_id AND HKSR.transaction_type_id=1
AND HKSR.date = '$actualOndate'";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproductswithid($directSRRow["id"],$product_id);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}






				$pdf = new PDF();
				$pdf->AliasNbPages();
				$pdf->AddPage();

				// $pdf->SetFont('Arial', 'B', 20);
				//
				// $pdf->SetTextColor(0,0,255);
				// $pdf->Cell(190,5,'K.ABDUL KAREEM & SONS',0,10,'C');
				// $pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell(130,5,'Sales Register Report :',0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Date : '.$ondate,0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Cash Sales Of '.$product_name,0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
        $pdf->Cell(20,9,'Sl No.','B',0,'C');
  			$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
              for($x = 0; $x<count($print_array); $x++ ){
                $pdf->Cell(20,9,$sl_no,0,0,'C');
                $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
                $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
                $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

                for($y = 0; $y<count($product_array[$x]); $y++){
                  $pdf->Cell(20,9,'',0,0,'C');
                  $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
                }
                $sl_no++;

                if(!empty($returnArr[$x])){


// pdf
        $pdf->Cell(20,9,$sl_no,0,0,'C');
        $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

// $returnArr[$x][$index]['bill_number']
for($y = 0; $y< count($returnArr[$x]);$y++){
$pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
$pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


for($index =0; $index<count($retProducts[$x][$y]); $index++){
$pdf->Cell(20,9,'',0,0,'C');
// $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
$pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
}

$sl_no++;
}
}
}

        for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }


       $pdf->Cell(122,9,'Total : ','T',0,'R');
       $pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');


        $cash_amount = 0;
        $print_array = array();
        $product_array = array();
        $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                  LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                  LEFT JOIN `hk_sales_products` AS HKSP ON HKSP.sales_id = HKS.id
                  WHERE HKS.sales_transaction_type_id = '2' AND HKS.bill_date = '$actualOndate' AND HKS.sales_active = '1' AND HKSP.product_id = '$product_id' AND HKS.person_id = '$customer_id'";
                  $exe = mysqli_query($conn,$query);
                  $x = 0;
                  while($row = mysqli_fetch_array($exe)){
                    $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                    $print_array[$x]["bill_number"] = $row['bill_number'];
                    $print_array[$x]["total_amount_received"] = $row['total_amount'];
                    $cash_amount = $cash_amount+$row['total_amount'];
                    $sales_id = $row['id'];

                    $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                              FROM `hk_sales_products` AS HKSP
                              LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                              WHERE HKSP.sales_id = '$sales_id' AND HKSP.product_id = '$product_id'";
                              $exe1 = mysqli_query($conn,$query1);
                              $y = 0;
                              while($row1 = mysqli_fetch_array($exe1)){
                                $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                                $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                                $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                                $product_array[$x][$y]["rate"] = $row1['rate'];
                                $product_array[$x][$y]["amount"] = $row1['amount'];
                                $y++;
                              }


// get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}
$x++;
}


// credit sales & return

$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id ='$customer_id' AND HKSR.transaction_type_id=2
AND HKSR.date = '$actualOndate'";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproducts($directSRRow["id"]);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}

				$pdf->Ln();
				$pdf->SetFont('Arial', 'B', 12);
				$pdf->Cell(130,5,'Credit Sales Of '.$product_name,0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
        $pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
           for($x = 0; $x<count($print_array); $x++ ){
             $pdf->Cell(20,9,$sl_no,0,0,'C');
             $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
             $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
             $pdf->Cell(68,9,strtoupper($print_array[$x]["total_amount_received"]),0,1,'R');

             for($y = 0; $y<count($product_array[$x]); $y++){
               $pdf->Cell(20,9,'',0,0,'C');
               $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
             }
             $sl_no++;

             if(!empty($returnArr[$x])){


  // pdf
        $pdf->Cell(20,9,$sl_no,0,0,'C');
        $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

  // $returnArr[$x][$index]['bill_number']
  for($y = 0; $y< count($returnArr[$x]);$y++){
    $pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
    $pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


    for($index =0; $index<count($retProducts[$x][$y]); $index++){
      $pdf->Cell(20,9,'',0,0,'C');
      // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
      $pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
    }

  $sl_no++;
    }
  }
}

for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }

				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');
				$pdf->output();
			}

		}
	}else if($typeDate=="btDate"){
		if($_POST["product"] == "allproducts"){
			if($_POST["customer"] == "allcustomers"){

        $print_array = array();
        $product_array = array();
        $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                WHERE HKS.sales_transaction_type_id = '1' AND HKS.bill_date BETWEEN '$actualFromdate' AND '$actualTodate' AND HKS.sales_active = '1'";
                $exe = mysqli_query($conn,$query);
                $x = 0;
                while($row = mysqli_fetch_array($exe)){
                  $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                  $print_array[$x]["bill_number"] = $row['bill_number'];
                  $print_array[$x]["total_amount_received"] = $row['total_amount_received'];
                  $cash_amount = $cash_amount+$row['total_amount_received'];
                  $sales_id = $row['id'];

                  $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                            FROM `hk_sales_products` AS HKSP
                            LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                            WHERE HKSP.sales_id = '$sales_id'";
                            $exe1 = mysqli_query($conn,$query1);
                            $y = 0;
                            while($row1 = mysqli_fetch_array($exe1)){
                              $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                              $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                              $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                              $product_array[$x][$y]["rate"] = $row1['rate'];
                              $product_array[$x][$y]["amount"] = $row1['amount'];
                              $y++;
                            }



// get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}
 $x++;
}



$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id IS NOT NULL AND HKSR.transaction_type_id=1
AND HKSR.date BETWEEN '$actualFromdate' AND '$actualTodate'";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproducts($directSRRow["id"]);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}


				$pdf = new PDF();
				$pdf->AliasNbPages();
				$pdf->AddPage();

				// $pdf->SetFont('Arial', 'B', 20);
				//
				// $pdf->SetTextColor(0,0,255);
				// $pdf->Cell(190,5,'K.ABDUL KAREEM & SONS',0,10,'C');
				// $pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell(130,5,'Sales Register Report :',0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Between Date : from '.$fromdate." to ".$todate,0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Cash Sales Of All Products',0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
        $pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
      for($x = 0; $x<count($print_array); $x++ ){
        $pdf->Cell(20,9,$sl_no,0,0,'C');
        $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
        $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
        $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

        for($y = 0; $y<count($product_array[$x]); $y++){
          $pdf->Cell(20,9,'',0,0,'C');
          $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
        }
        $sl_no++;

        if(!empty($returnArr[$x])){


		// pdf
			    $pdf->Cell(20,9,$sl_no,0,0,'C');
			    $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

		// $returnArr[$x][$index]['bill_number']
		for($y = 0; $y< count($returnArr[$x]);$y++){
		  $pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
		  $pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


		  for($index =0; $index<count($retProducts[$x][$y]); $index++){
		    $pdf->Cell(20,9,'',0,0,'C');
		    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
		    $pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
		  }

		$sl_no++;
		  }
		}
}

        for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }




				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');
        $cash_amount = 0;
        $print_array = array();
        $returnArr = array();
     $product_array = array();
     $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
               LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
               WHERE HKS.sales_transaction_type_id = '2' AND HKS.bill_date BETWEEN '$actualFromdate' AND '$actualTodate' AND HKS.sales_active = '1'";
               $exe = mysqli_query($conn,$query);
               $x = 0;
               while($row = mysqli_fetch_array($exe)){
                 $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                 $print_array[$x]["bill_number"] = $row['bill_number'];
                 $print_array[$x]["total_amount_received"] = $row['total_amount'];
                 $cash_amount = $cash_amount+$row['total_amount'];
                 $sales_id = $row['id'];

                 $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                           FROM `hk_sales_products` AS HKSP
                           LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                           WHERE HKSP.sales_id = '$sales_id'";
                           $exe1 = mysqli_query($conn,$query1);
                           $y = 0;
                           while($row1 = mysqli_fetch_array($exe1)){
                             $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                             $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                             $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                             $product_array[$x][$y]["rate"] = $row1['rate'];
                             $product_array[$x][$y]["amount"] = $row1['amount'];
                             $y++;
                           }


// get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}
 $x++;
}

// cash sales And return

$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id IS NOT NULL AND HKSR.transaction_type_id=2
AND HKSR.date BETWEEN '$actualFromdate' AND '$actualTodate'";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproducts($directSRRow["id"]);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}


				$pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->Cell(130,5,'Credit Sales Of All Products',0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
        $pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
          for($x = 0; $x<count($print_array); $x++ ){
            $pdf->Cell(20,9,$sl_no,0,0,'C');
            $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
            $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
            $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

            for($y = 0; $y<count($product_array[$x]); $y++){
              $pdf->Cell(20,9,'',0,0,'C');
              $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
            }
            $sl_no++;

            if(!empty($returnArr[$x])){


  // pdf
        $pdf->Cell(20,9,$sl_no,0,0,'C');
        $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

  // $returnArr[$x][$index]['bill_number']
  for($y = 0; $y< count($returnArr[$x]);$y++){
    $pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
    $pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


    for($index =0; $index<count($retProducts[$x][$y]); $index++){
      $pdf->Cell(20,9,'',0,0,'C');
      // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
      $pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
    }

  $sl_no++;
    }
  }
}

for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }

				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');

				$pdf->output();
			}else{
				$customer_id = $_POST["customer_id"];

        $print_array = array();
        $product_array = array();
        $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                WHERE HKS.sales_transaction_type_id = '1' AND HKS.bill_date BETWEEN '$actualFromdate' AND '$actualTodate' AND HKP.id = '$customer_id' AND HKS.sales_active = '1'";
                $exe = mysqli_query($conn,$query);
                $x = 0;
                while($row = mysqli_fetch_array($exe)){
                  $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                  $print_array[$x]["bill_number"] = $row['bill_number'];
                  $print_array[$x]["total_amount_received"] = $row['total_amount_received'];
                  $cash_amount = $cash_amount+$row['total_amount_received'];
                  $sales_id = $row['id'];

                  $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                            FROM `hk_sales_products` AS HKSP
                            LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                            WHERE HKSP.sales_id = '$sales_id'";
                            $exe1 = mysqli_query($conn,$query1);
                            $y = 0;
                            while($row1 = mysqli_fetch_array($exe1)){
                              $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                              $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                              $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                              $product_array[$x][$y]["rate"] = $row1['rate'];
                              $product_array[$x][$y]["amount"] = $row1['amount'];
                              $y++;
                            }


// get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}
 $x++;
}

// cash sales and return
$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id = $customer_id AND HKSR.transaction_type_id=1
AND HKSR.date BETWEEN '$actualFromdate' AND '$actualTodate'";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproducts($directSRRow["id"]);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}




				$pdf = new PDF();
				$pdf->AliasNbPages();
				$pdf->AddPage();

				// $pdf->SetFont('Arial', 'B', 20);
				//
				// $pdf->SetTextColor(0,0,255);
				// $pdf->Cell(190,5,'K.ABDUL KAREEM & SONS',0,10,'C');
				// $pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell(130,5,'Sales Register Report :',0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Between Date : from '.$fromdate." to ".$todate,0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Cash Sales Of All Products',0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
        $pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
     for($x = 0; $x<count($print_array); $x++ ){
       $pdf->Cell(20,9,$sl_no,0,0,'C');
       $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
       $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
       $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

       for($y = 0; $y<count($product_array[$x]); $y++){
         $pdf->Cell(20,9,'',0,0,'C');
         $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
       }
       $sl_no++;

       if(!empty($returnArr[$x])){


  // pdf
        $pdf->Cell(20,9,$sl_no,0,0,'C');
        $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

  // $returnArr[$x][$index]['bill_number']
  for($y = 0; $y< count($returnArr[$x]);$y++){
    $pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
    $pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


    for($index =0; $index<count($retProducts[$x][$y]); $index++){
      $pdf->Cell(20,9,'',0,0,'C');
      // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
      $pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
    }

  $sl_no++;
    }
  }
}

        for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }



				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');
        $cash_amount = 0;
        $print_array = array();
       $product_array = array();
       $returnArr = array();
       $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                 LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                 WHERE HKS.sales_transaction_type_id = '2' AND HKS.bill_date BETWEEN '$actualFromdate' AND '$actualTodate' AND HKS.sales_active = '1' AND HKS.person_id = '$customer_id'";
                 $exe = mysqli_query($conn,$query);
                 $x = 0;
                 while($row = mysqli_fetch_array($exe)){
                   $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                   $print_array[$x]["bill_number"] = $row['bill_number'];
                   $print_array[$x]["total_amount_received"] = $row['total_amount'];
                   $cash_amount = $cash_amount+$row['total_amount'];
                   $sales_id = $row['id'];

                   $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                             FROM `hk_sales_products` AS HKSP
                             LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                             WHERE HKSP.sales_id = '$sales_id'";
                             $exe1 = mysqli_query($conn,$query1);
                             $y = 0;
                             while($row1 = mysqli_fetch_array($exe1)){
                               $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                               $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                               $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                               $product_array[$x][$y]["rate"] = $row1['rate'];
                               $product_array[$x][$y]["amount"] = $row1['amount'];
                               $y++;
                             }


// get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}
  $x++;
}

// credit sales and purchase

$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id = $customer_id AND HKSR.transaction_type_id=2
AND HKSR.date BETWEEN '$actualFromdate' AND '$actualTodate'";

// echo "$directRQ";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproducts($directSRRow["id"]);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}




				$pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->Cell(130,5,'Credit Sales Of All Products',0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
        $pdf->Cell(20,9,'Sl No.','B',0,'C');
  			$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
           for($x = 0; $x<count($print_array); $x++ ){
             $pdf->Cell(20,9,$sl_no,0,0,'C');
             $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
             $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
             $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

             for($y = 0; $y<count($product_array[$x]); $y++){
               $pdf->Cell(20,9,'',0,0,'C');
               $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
             }
             $sl_no++;

             if(!empty($returnArr[$x])){


  // pdf
        $pdf->Cell(20,9,$sl_no,0,0,'C');
        $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

  // $returnArr[$x][$index]['bill_number']
  for($y = 0; $y< count($returnArr[$x]);$y++){
    $pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
    $pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


    for($index =0; $index<count($retProducts[$x][$y]); $index++){
      $pdf->Cell(20,9,'',0,0,'C');
      // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
      $pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
    }

  $sl_no++;
    }
  }
 }

for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }


				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');

				$pdf->output();
			}

		}
		else{
			if($_POST["customer"] == "allcustomers"){
				$product_id = $_POST["product_id"];
				$product_name;
				$productQuery = "SELECT * from `hk_products` WHERE id = '$product_id'";
				$exe = mysqli_query($conn,$productQuery);
				while($row = mysqli_fetch_array($exe)){
					$product_name = $row['name']." ".$row['type'];
				}

        $print_array = array();
       $product_array = array();
       $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
               LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
               LEFT JOIN `hk_sales_products` AS HKSP ON HKSP.sales_id = HKS.id
               WHERE HKS.sales_transaction_type_id = '1' AND HKS.bill_date BETWEEN '$actualFromdate' AND '$actualTodate' AND HKSP.product_id = '$product_id' AND HKS.sales_active = '1'";
               $exe = mysqli_query($conn,$query);
               $x = 0;
               while($row = mysqli_fetch_array($exe)){
                 $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                 $print_array[$x]["bill_number"] = $row['bill_number'];
                 $print_array[$x]["total_amount_received"] = $row['total_amount_received'];
                 $cash_amount = $cash_amount+$row['total_amount_received'];
                 $sales_id = $row['id'];

                 $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                           FROM `hk_sales_products` AS HKSP
                           LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                           WHERE HKSP.sales_id = '$sales_id' AND HKSP.product_id = '$product_id'";
                           $exe1 = mysqli_query($conn,$query1);
                           $y = 0;
                           while($row1 = mysqli_fetch_array($exe1)){
                             $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                             $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                             $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                             $product_array[$x][$y]["rate"] = $row1['rate'];
                             $product_array[$x][$y]["amount"] = $row1['amount'];
                             $y++;
                           }


// get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}
   $x++;
}

// cash  sales
$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id IS NOT NULL AND HKSR.transaction_type_id=1
AND HKSR.date BETWEEN '$actualFromdate' AND '$actualTodate'";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproductswithid($directSRRow["id"],$product_id);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}


				$pdf = new PDF();
				$pdf->AliasNbPages();
				$pdf->AddPage();

				// $pdf->SetFont('Arial', 'B', 20);
				//
				// $pdf->SetTextColor(0,0,255);
				// $pdf->Cell(190,5,'K.ABDUL KAREEM & SONS',0,10,'C');
				// $pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell(130,5,'Sales Register Report :',0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Between Date : from '.$fromdate." to ".$todate,0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Cash Sales Of '.$product_name,0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
        $pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
      for($x = 0; $x<count($print_array); $x++ ){
        $pdf->Cell(20,9,$sl_no,0,0,'C');
        $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
        $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
        $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

        for($y = 0; $y<count($product_array[$x]); $y++){
          $pdf->Cell(20,9,'',0,0,'C');
          $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
        }
        $sl_no++;

        if(!empty($returnArr[$x])){


  // pdf
        $pdf->Cell(20,9,$sl_no,0,0,'C');
        $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

  // $returnArr[$x][$index]['bill_number']
  for($y = 0; $y< count($returnArr[$x]);$y++){
    $pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
    $pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


    for($index =0; $index<count($retProducts[$x][$y]); $index++){
      $pdf->Cell(20,9,'',0,0,'C');
      // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
      $pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
    }

  $sl_no++;
    }
  }
}


        for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }

				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');
        $cash_amount = 0;
        $print_array = array();
        $returnArr = array();
        $product_array = array();
        $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                  LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                  LEFT JOIN `hk_sales_products` AS HKSP ON HKSP.sales_id = HKS.id
                  WHERE HKS.sales_transaction_type_id = '2' AND HKS.bill_date BETWEEN '$actualFromdate' AND '$actualTodate' AND HKS.sales_active = '1' AND HKSP.product_id = '$product_id'";
                  $exe = mysqli_query($conn,$query);
                  $x = 0;
                  while($row = mysqli_fetch_array($exe)){
                    $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                    $print_array[$x]["bill_number"] = $row['bill_number'];
                    $print_array[$x]["total_amount_received"] = $row['total_amount'];
                    $cash_amount = $cash_amount+$row['total_amount'];
                    $sales_id = $row['id'];

                    $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                              FROM `hk_sales_products` AS HKSP
                              LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                              WHERE HKSP.sales_id = '$sales_id' AND HKSP.product_id = '$product_id'";
                              $exe1 = mysqli_query($conn,$query1);
                              $y = 0;
                              while($row1 = mysqli_fetch_array($exe1)){
                                $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                                $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                                $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                                $product_array[$x][$y]["rate"] = $row1['rate'];
                                $product_array[$x][$y]["amount"] = $row1['amount'];
                                $y++;
                              }


// get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}
 $x++;
}


$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id IS NOT NULL AND HKSR.transaction_type_id=2
AND HKSR.date BETWEEN '$actualFromdate' AND '$actualTodate'";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproductswithid($directSRRow["id"],$product_id);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}



				$pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->Cell(130,5,'Credit Sales Of '.$product_name,0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
        $pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
            for($x = 0; $x<count($print_array); $x++ ){
              $pdf->Cell(20,9,$sl_no,0,0,'C');
              $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
              $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
              $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

              for($y = 0; $y<count($product_array[$x]); $y++){
                $pdf->Cell(20,9,'',0,0,'C');
                $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
              }
              $sl_no++;
              if(!empty($returnArr[$x])){


// pdf
      $pdf->Cell(20,9,$sl_no,0,0,'C');
      $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

// $returnArr[$x][$index]['bill_number']
for($y = 0; $y< count($returnArr[$x]);$y++){
  $pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
  $pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


  for($index =0; $index<count($retProducts[$x][$y]); $index++){
    $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
    $pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
  }

$sl_no++;
  }
}
}

        for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }


				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');

				$pdf->output();
			}else{
				$customer_id = $_POST["customer_id"];
				$product_id = $_POST["product_id"];
				$product_name;
				$productQuery = "SELECT * from `hk_products` WHERE id = '$product_id'";
				$exe = mysqli_query($conn,$productQuery);
				while($row = mysqli_fetch_array($exe)){
					$product_name = $row['name']." ".$row['type'];
				}
        $cash_amount = 0;
        $print_array = array();
        $product_array = array();
        $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                LEFT JOIN `hk_sales_products` AS HKSP ON HKSP.sales_id = HKS.id
                WHERE HKS.sales_transaction_type_id = '1'
                AND HKS.bill_date BETWEEN '$actualFromdate' AND '$actualTodate'
                AND HKSP.product_id = '$product_id' AND HKS.person_id = '$customer_id' AND HKS.sales_active = '1'";
                $exe = mysqli_query($conn,$query);
                $x = 0;
                while($row = mysqli_fetch_array($exe)){
                  $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                  $print_array[$x]["bill_number"] = $row['bill_number'];
                  $print_array[$x]["total_amount_received"] = $row['total_amount_received'];
                  $cash_amount = $cash_amount+$row['total_amount_received'];
                  $sales_id = $row['id'];

                  $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                            FROM `hk_sales_products` AS HKSP
                            LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                            WHERE HKSP.sales_id = '$sales_id' AND HKSP.product_id = '$product_id'";
                            $exe1 = mysqli_query($conn,$query1);
                            $y = 0;
                            while($row1 = mysqli_fetch_array($exe1)){
                              $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                              $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                              $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                              $product_array[$x][$y]["rate"] = $row1['rate'];
                              $product_array[$x][$y]["amount"] = $row1['amount'];
                              $y++;
                            }



// get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}
  $x++;
}

// cash

$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id = $customer_id AND HKSR.transaction_type_id=1
AND HKSR.date BETWEEN '$actualFromdate' AND '$actualTodate'";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproductswithid($directSRRow["id"],$product_id);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}


				$pdf = new PDF();
				$pdf->AliasNbPages();
				$pdf->AddPage();

				// $pdf->SetFont('Arial', 'B', 20);
				//
				// $pdf->SetTextColor(0,0,255);
				// $pdf->Cell(190,5,'K.ABDUL KAREEM & SONS',0,10,'C');
				// $pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->SetTextColor(0,0,0);
				$pdf->Cell(130,5,'Sales Register Report :',0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Between Date : from '.$fromdate." to ".$todate,0,1,'L');
				$pdf->Ln();
				$pdf->Cell(130,5,'Cash Sales Of '.$product_name,0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
				$pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
        for($x = 0; $x<count($print_array); $x++ ){
          $pdf->Cell(20,9,$sl_no,0,0,'C');
          $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
          $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

          for($y = 0; $y<count($product_array[$x]); $y++){
            $pdf->Cell(20,9,'',0,0,'C');
            $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
          }
          $sl_no++;







//pdf


			  if(!empty($returnArr[$x])){


		// pdf
			    $pdf->Cell(20,9,$sl_no,0,0,'C');
			    $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

		// $returnArr[$x][$index]['bill_number']
		for($y = 0; $y< count($returnArr[$x]);$y++){
		  $pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
		  $pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


		  for($index =0; $index<count($retProducts[$x][$y]); $index++){
		    $pdf->Cell(20,9,'',0,0,'C');
		    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
		    $pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
		  }

		$sl_no++;
		  }
		}
}



        for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }


				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');
        $cash_amount = 0;
        $returnArr = array();
        $print_array = array();
        $product_array = array();
        $query = "SELECT HKS.*,HKP.first_name,HKP.last_name FROM `hk_sales` AS HKS
                  LEFT JOIN `hk_persons` AS HKP ON HKS.person_id = HKP.id
                  LEFT JOIN `hk_sales_products` AS HKSP ON HKSP.sales_id = HKS.id
                  WHERE HKS.sales_transaction_type_id = '2' AND HKS.bill_date BETWEEN '$actualFromdate' AND '$actualTodate' AND HKS.sales_active = '1' AND HKSP.product_id = '$product_id' AND HKS.person_id = '$customer_id'";
                  $exe = mysqli_query($conn,$query);
                  $x = 0;
                  while($row = mysqli_fetch_array($exe)){
                    $print_array[$x]["customer_name"] = $row['first_name']." ".$row['last_name'];
                    $print_array[$x]["bill_number"] = $row['bill_number'];
                    $print_array[$x]["total_amount_received"] = $row['total_amount'];
                    $cash_amount = $cash_amount+$row['total_amount'];
                    $sales_id = $row['id'];

                    $query1 = "SELECT HKSP.quantity,HKSP.rate,HKSP.amount,HKP.name,HKP.type,HKP.quantity_type
                              FROM `hk_sales_products` AS HKSP
                              LEFT JOIN `hk_products` AS HKP ON HKSP.product_id = HKP.id
                              WHERE HKSP.sales_id = '$sales_id' AND HKSP.product_id = '$product_id'";
                              $exe1 = mysqli_query($conn,$query1);
                              $y = 0;
                              while($row1 = mysqli_fetch_array($exe1)){
                                $product_array[$x][$y]["product_name"] = $row1['name']." ".$row1['type'];
                                $product_array[$x][$y]["quantity_type"] = $row1['quantity_type'];
                                $product_array[$x][$y]["product_quantity"] = $row1['quantity'];
                                $product_array[$x][$y]["rate"] = $row1['rate'];
                                $product_array[$x][$y]["amount"] = $row1['amount'];
                                $y++;
                              }


// get id from sales return table
$getRId = "SELECT `id`,`sales_return_bill_number`,`amount_to_be_paid` FROM hk_sales_return WHERE `sales_id`='$sales_id'";

$getIdExe = mysqli_query($conn,$getRId);





  $index = 0;
  while($getIdRow = mysqli_fetch_array($getIdExe)) {
    // code...

    if(empty($getIdRow)){
      $returnArr[$x][$index]['id'] =null;
      $returnArr[$x][$index]['bill_number'] = null;
      $returnArr[$x][$index]['returnAmount'] =null;
    }

    $returnArr[$x][$index]['id'] = $getIdRow["id"];
    $returnArr[$x][$index]['bill_number'] = $getIdRow["sales_return_bill_number"];
    $returnArr[$x][$index]['returnAmount'] = $getIdRow["amount_to_be_paid"]*-1;
    $cash_amount = $cash_amount+$returnArr[$x][$index]['returnAmount'];
    $index++;
  }






// get return products from sales_return_products for that id

error_reporting(E_ERROR | E_PARSE);

if(count($returnArr[$x])>0){
  // echo count($returnArr[$x]);
  // print_r($returnArr[$x]);
  // echo "<br>";
  for($a = 0; $a<count($returnArr[$x]); $a++){
    $retProductsQ = "SELECT HKSRP.rate,HKSRP.amount,HKSRP.quantity,HKP.name,HKP.type,HKP.quantity_type FROM hk_sales_return_products AS HKSRP
  left JOIN hk_products AS HKP ON HKSRP.product_id = HKP.id WHERE HKSRP.sales_return_id =".$returnArr[$x][$a]['id'];
  // echo "$retProductsQ <br>";
    $retProdcutExe = mysqli_query($conn,$retProductsQ);
  echo mysqli_error($conn);
  $indx =0;
    while ($retProductRow = mysqli_fetch_array($retProdcutExe)) {
      // code...
      $retProducts[$x][$a][$indx]["Particulars"] = $retProductRow["name"]." ".$retProductRow["type"]." ".-1*$retProductRow["quantity"]." ". $retProductRow["quantity_type"];
      // echo $retProducts[$x][$a][$indx]["Particulars"];
      $indx++;
    }


  }
}
 $x++;
}

// credit
$directRQ = "SELECT HKSR.id,HKSR.sales_return_bill_number,HKSR.amount_to_be_paid,HKP.first_name,HKP.last_name FROM hk_sales_return AS HKSR
left JOIN hk_persons AS HKP ON HKP.id = HKSR.person_id
WHERE sales_return_active=1 AND person_id =$customer_id AND HKSR.transaction_type_id=2
AND HKSR.date BETWEEN '$actualFromdate' AND '$actualTodate'";

$returnArray = array();
// $retunProdArr = array();
$retIndex = 0;

$directRExe = mysqli_query($conn,$directRQ);
while($directSRRow = mysqli_fetch_array($directRExe)){
$returnArray[$retIndex]["name"] = $directSRRow["first_name"]." ".$directSRRow["last_name"];
$returnArray[$retIndex]["recipt_no"] = $directSRRow["sales_return_bill_number"];
$returnArray[$retIndex]["amount"] = $directSRRow["amount_to_be_paid"];

$retunProdArr[$retIndex] = array();
$retunProdArr[$retIndex]=retproductswithid($directSRRow["id"],$product_id);

// print_r($retunProdArr[$retIndex]);
  // get the sales_return_products

$retIndex++;

}




				$pdf->Ln();
				$pdf->SetFont('Arial','B',12);
				$pdf->Cell(130,5,'Credit Sales Of '.$product_name,0,1,'L');

				$pdf->SetFont('Arial','B',12);

				$pdf->Ln(5);
				$width_cell=array(60,50,40);
				$pdf->SetFillColor(255,255,255);
				$pdf->Cell(20,9,'Sl No.','B',0,'C');
				$pdf->Cell(60,9,'Particulars/Name','B',0,'C');
				$pdf->Cell(42,9,'Receipt No','B',0,'C');
				$pdf->Cell(68,9,'Amount','B',1,'C');

				$pdf->SetFont('Arial','',10);
				$pdf->SetTextColor(0,0,0);

        $sl_no = 1;
             for($x = 0; $x<count($print_array); $x++ ){
               $pdf->Cell(20,9,$sl_no,0,0,'C');
               $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');
               $pdf->Cell(42,9,$print_array[$x]["bill_number"],0,0,'C');
               $pdf->Cell(68,9,money_format('%!i',$print_array[$x]["total_amount_received"]),0,1,'R');

               for($y = 0; $y<count($product_array[$x]); $y++){
                 $pdf->Cell(20,9,'',0,0,'C');
                 $pdf->Cell(60,9,strtoupper($product_array[$x][$y]["product_name"]." ".$product_array[$x][$y]["product_quantity"]." ".$product_array[$x][$y]["quantity_type"]." ".$product_array[$x][$y]["amount"]),0,1,'L');
               }
               $sl_no++;


			  if(!empty($returnArr[$x])){


		// pdf
			    $pdf->Cell(20,9,$sl_no,0,0,'C');
			    $pdf->Cell(60,9,strtoupper($print_array[$x]["customer_name"]),0,0,'L');

		// $returnArr[$x][$index]['bill_number']
		for($y = 0; $y< count($returnArr[$x]);$y++){
		  $pdf->Cell(42,9,$returnArr[$x][$y]['bill_number']." (SR)",0,0,'C');
		  $pdf->Cell(68,9,money_format('%!i',$returnArr[$x][$y]['returnAmount']),0,1,'R');


		  for($index =0; $index<count($retProducts[$x][$y]); $index++){
		    $pdf->Cell(20,9,'',0,0,'C');
		    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
		    $pdf->Cell(60,9,strtoupper($retProducts[$x][$y][$index]["Particulars"]),0,1,'L');
		  }

		$sl_no++;
		  }
		}
}

        for($x =0;$x<count($returnArray);$x++){
          $pdf->Cell(20,9,$sl_no++,0,0,'C');
          $pdf->Cell(60,9,strtoupper($returnArray[$x]["name"]),0,0,'L');
          $pdf->Cell(42,9,$returnArray[$x]["recipt_no"]." (SR)",0,0,'C');
          $pdf->Cell(68,9,money_format('%!i',$returnArray[$x]["amount"]*-1),0,1,'R');
// print return products
          $cash_amount = $cash_amount-$returnArray[$x]["amount"];

            for($y = 0; $y<count($retunProdArr[$x]);$y++){
              $pdf->Cell(20,9,'',0,0,'C');
    // $pdf->Cell(60,9,$retProducts[$y]["Particulars"],0,1,'C');
               $pdf->Cell(60,9,strtoupper($retunProdArr[$x][$y]["particulars"]),0,1,'L');
            }


        }


				$pdf->Cell(122,9,'Total : ','T',0,'R');
				$pdf->Cell(68,9,money_format('%!i',$cash_amount),'T',1,'R');

				$pdf->output();
			}

		}
	}
}


?>
