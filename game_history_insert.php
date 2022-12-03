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
elseif (empty($data) || !isset($data->game_name) || empty(trim($data->game_name)) || !isset($data->mobile) || empty(trim($data->mobile))):
  $fields = ['fields' => ['game_name', 'mobile']];
  $returnData = $error_handler->getResponse(0, 422, 'Please Fill in all Required Fields!', $fields);
else:
  $game_name = trim($data->game_name);
  $mobile = trim($data->mobile);
  $amount = trim($data->amount);
  $new_balance = trim($data->new_balance);
  $playon = trim($data->playon);
  $settle_status = trim($data->settle_status);
  $rate = trim($data->rate);
  
  if (strlen($mobile) > 10):
    $returnData = $error_handler->getResponse(0, 422, 'Invalid Mobile Number!');
  else:
    try {
      $check_mobile = "SELECT `mobile` FROM `game_history` WHERE `mobile`=:mobile";
      $check_mobile_stmt = $conn->prepare($check_mobile);
      $check_mobile_stmt->bindValue(':mobile', $mobile, PDO::PARAM_STR);
      $check_mobile_stmt->execute();
      if ($check_mobile_stmt->rowCount()):
        $returnData = $error_handler->getResponse(0, 422, 'This mobile is already in use!');
      else:
        
        $insert_query = "INSERT INTO `game_history` (`game_name`,`amount`,`new_balance`,`playon`,`settle_status`,`rate`,`mobile`) VALUES(:game_name,:amount,:new_balance,:playon,:settle_status,:rate,:mobile)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bindValue(':game_name', htmlspecialchars(strip_tags($game_name)), PDO::PARAM_STR);
        $insert_stmt->bindValue(':amount', $amount, PDO::PARAM_STR);
        $insert_stmt->bindValue(':new_balance', $new_balance, PDO::PARAM_STR);
        $insert_stmt->bindValue(':playon', $playon, PDO::PARAM_STR);
        $insert_stmt->bindValue(':settle_status', $settle_status, PDO::PARAM_STR);
        $insert_stmt->bindValue(':rate', $rate, PDO::PARAM_STR);
        
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