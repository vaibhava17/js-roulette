<?php
require __DIR__.'/classes/db.config.php';
require __DIR__.'/classes/error.handler.php';

$db_connection = new Database();
$conn = $db_connection->dbConnection();
$error_handler = new ErrorHandler();

$data = json_decode(file_get_contents("php://input"));
$returnData = [];

if ($_SERVER["REQUEST_METHOD"] != "POST"):
  $returnData = $error_handler->getResponse(0, 404, 'Page Not Found!');
elseif (empty($data) || !isset($data->userid) || empty(trim($data->userid)) || !isset($data->mobile) || empty(trim($data->mobile))):
  $fields = ['fields' => ['userid', 'mobile']];
  $returnData = $error_handler->getResponse(0, 422, 'Please Fill in all Required Fields!', $fields);
else:
  $userid = trim($data->userid);
  $mobile = trim($data->mobile);
  $withdrawamount = trim($data->withdrawamount);
  $paymentmode = trim($data->paymentmode);
  $remainingbalance = trim($data->remainingbalance);
  $withdrawstatus = trim($data->withdrawstatus);
  
  
  if (strlen($mobile) > 10):
    $returnData = $error_handler->getResponse(0, 422, 'Invalid Mobile Number!');
  else:
    try {
      $check_mobile = "SELECT `mobile` FROM `withdrawal` WHERE `mobile`=:mobile";
      $check_mobile_stmt = $conn->prepare($check_mobile);
      $check_mobile_stmt->bindValue(':mobile', $mobile, PDO::PARAM_STR);
      $check_mobile_stmt->execute();
      if ($check_mobile_stmt->rowCount()):
        $returnData = $error_handler->getResponse(0, 422, 'This mobile is already in use!');
      else:
        
        $insert_query = "INSERT INTO `withdrawal` (`userid`,`remainingbalance`,`withdrawamount`,`paymentmode`,`withdrawstatus`,`mobile`) VALUES(:userid,:remainingbalance,:withdrawamount,:paymentmode,:withdrawstatus,:mobile)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bindValue(':userid', htmlspecialchars(strip_tags($userid)), PDO::PARAM_STR);
        $insert_stmt->bindValue(':remainingbalance', $remainingbalance, PDO::PARAM_STR);
        $insert_stmt->bindValue(':withdrawamount', $withdrawamount, PDO::PARAM_STR);
        $insert_stmt->bindValue(':paymentmode', $paymentmode, PDO::PARAM_STR);
        $insert_stmt->bindValue(':withdrawstatus', $withdrawstatus, PDO::PARAM_STR);
        $insert_stmt->bindValue(':mobile', $mobile, PDO::PARAM_STR);
       
       if
        ($insert_stmt->execute()):

        $returnData = $error_handler->getResponse(1, 201, 'Data added.');
        else:
          $returnData = $error_handler->getResponse(1, 403, 'Something went wrong.');
      endif;
    endif;
    } catch (PDOException $e) {
      $returnData = $error_handler->getResponse(0, 500, $e->getMessage());
    }
  endif;
endif;

echo json_encode($returnData);