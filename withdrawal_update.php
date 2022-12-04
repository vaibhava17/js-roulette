<?php
require __DIR__.'/classes/db.config.php';
require __DIR__.'/classes/error.handler.php';

$db_connection = new Database();
$conn = $db_connection->dbConnection();
$error_handler = new ErrorHandler();

$data = json_decode(file_get_contents("php://input"));
$returnData = [];
$withdrawid = isset($_GET['withdrawid']) ? $_GET['withdrawid'] : null;
$mobile = isset($_GET['mobile']) ? $_GET['mobile'] : null;

if ($_SERVER["REQUEST_METHOD"] != "PUT"):
  $returnData = $error_handler->getResponse(0, 404, 'Page Not Found!');
else:
  if (empty($data) && ($mobile || $withdrawid)):
    $returnData = $error_handler->getResponse(0, 422, 'Invalid Data! Please try again.');
  else:
    try{
      $fetch_user = "SELECT * FROM withdrawal WHERE mobile=:mobile OR withdrawid=:mobile";
      $fetch_user_stmt = $conn->prepare($fetch_user);
      $fetch_user_stmt->bindValue(':mobile',$withdrawid ?? $mobile, PDO::PARAM_STR);
      $fetch_user_stmt->execute();
      if ($fetch_user_stmt->rowCount()):
        $withdraw = $fetch_user_stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($withdraw)):
          $update_user = "UPDATE `withdrawal` SET userid=:userid,remainingbalance=:remainingbalance, withdrawamount=:withdrawamount, paymentmode=:paymentmode, withdrawstatus=:withdrawstatus,  WHERE (mobile=:mobile OR withdrawid=:mobile)";
          $update_user_stmt = $conn->prepare($update_user);
          $update_user_stmt->bindValue(':mobile',$withdrawid ?? $mobile, PDO::PARAM_STR);
          $update_user_stmt->bindValue(':userid', ($data->userid ?? $withdraw['userid']), PDO::PARAM_STR);
          $update_user_stmt->bindValue(':remainingbalance', ($data->remainingbalance ?? $withdraw['remainingbalance']), PDO::PARAM_STR);
          $update_user_stmt->bindValue(':withdrawamount', ($data->withdrawamount ?? $withdraw['withdrawamount']), PDO::PARAM_STR);
          $update_user_stmt->bindValue(':paymentmode', ($data->paymentmode ?? $withdraw['paymentmode']), PDO::PARAM_STR);
          $update_user_stmt->bindValue(':withdrawstatus', ($data->withdrawstatus ?? $withdraw['withdrawstatus']), PDO::PARAM_STR);
          
          if ($update_user_stmt->execute()):
            $returnData = $error_handler->getResponse(1, 200, 'User Updated Successfully!');
          else:
            $returnData = $error_handler->getResponse(0, 500, 'Something went wrong. Please try again.');
          endif;
        endif;
      else:
        $returnData = $error_handler->getResponse(0, 422, 'No User Found!');
      endif;
    }
    catch(PDOException $e){
      $returnData = $error_handler->getResponse(0, 422,$e->getMessage());
    }
    
  endif;
endif;

echo json_encode($returnData);