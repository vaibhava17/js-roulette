<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: PUT");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
require __DIR__ . '/classes/db.config.php';
require __DIR__ . '/classes/error.handler.php';

$db_connection = new Database();
$conn = $db_connection->dbConnection();
$error_handler = new ErrorHandler();

$data = json_decode(file_get_contents("php://input"));
$returnData = [];

if ($_SERVER["REQUEST_METHOD"] != "PUT"):
  $returnData = $error_handler->getResponse(0, 404, 'Page Not Found!');
elseif (empty($data) || !isset($data->mobile) || empty(trim($data->mobile))):
  $returnData = $error_handler->getResponse(0, 422, 'Mobile number is required!');
else:
  try {
    $fetch_user = "SELECT balance FROM `users` WHERE `mobile`=:mobile";
    $fetch_user_stmt = $conn->prepare($fetch_user);
    $fetch_user_stmt->bindValue(':mobile', $data->mobile, PDO::PARAM_INT);
    $fetch_user_stmt->execute();
    if ($fetch_user_stmt->rowCount()):
      $user = $fetch_user_stmt->fetch(PDO::FETCH_ASSOC);
      $returnData = $error_handler->getResponse(1, 200, 'Mobile found!', $user);
      
        $balance = $user['balance'] + $data->balance;
        $update = "UPDATE `users` SET `balance`=:balance WHERE `mobile`=:mobile";
        $update_stmt = $conn->prepare($update);
        $update_stmt->bindValue(':mobile', $data->mobile, PDO::PARAM_INT);
        $update_stmt->bindValue(':balance', $balance, PDO::PARAM_STR);
        if ($update_stmt->execute()):
          $returnData = $error_handler->getResponse(1, 200, 'Balance updated successfully!');
        else:
          $returnData = $error_handler->getResponse(0, 500, 'Something went wrong. Please try again.');
        endif;
      
       
   
    else:
      $returnData = $error_handler->getResponse(0, 422, 'No Data found!');
    endif;
  } catch (PDOException $e) {
    $returnData = $error_handler->getResponse(0, 500, $e->getMessage());
  }
endif;

echo json_encode($returnData);