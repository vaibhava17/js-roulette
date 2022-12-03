<?php
require __DIR__.'/classes/db.config.php';
require __DIR__.'/classes/error.handler.php';

$db_connection = new Database();
$conn = $db_connection->dbConnection();
$error_handler = new ErrorHandler();

$data = json_decode(file_get_contents("php://input"));
$returnData = [];
$mobile = $_GET['mobile'];
$game_id = $_GET['id'];
if ($_SERVER["REQUEST_METHOD"] != "PUT"):
  $returnData = $error_handler->getResponse(0, 404, 'Page Not Found!');
else:
  if (empty($data) && ($mobile || $game_id)):
    $returnData = $error_handler->getResponse(0, 422, 'Invalid Data! Please try again.');
  else:
    try{
      $fetch_user = "SELECT * FROM `game_history` WHERE `mobile`=:mobile OR `game_id`=:mobile";
      $fetch_user_stmt = $conn->prepare($fetch_user);
      $fetch_user_stmt->bindValue(':mobile',$game_id ?? $mobile, PDO::PARAM_STR);
      $fetch_user_stmt->execute();
      if ($fetch_user_stmt->rowCount()):
        $game = $fetch_user_stmt->fetch(PDO::FETCH_ASSOC);
        if (!empty($game)):
          $update_user = "UPDATE `game_history` SET `game_name`=:game_name, `amount`=:amount, `new_balance`=:new_balance, `playon`=:playon, `settle_status`=:settle_status, `rate`=:rate, WHERE (`mobile`=:mobile OR `game_id`=:mobile)";
          $update_user_stmt = $conn->prepare($update_user);
          $update_user_stmt->bindValue(':mobile',$game_id ?? $mobile, PDO::PARAM_STR);
          $update_user_stmt->bindValue(':game_name', ($data->game_name ?? $game['game_name']), PDO::PARAM_STR);
          $update_user_stmt->bindValue(':amount', ($data->amount ?? $game['amount']), PDO::PARAM_STR);
          $update_user_stmt->bindValue(':new_balance', ($data->new_balance ?? $game['new_balance']), PDO::PARAM_STR);
          $update_user_stmt->bindValue(':settle_status', ($data->settle_status ?? $game['settle_status']), PDO::PARAM_STR);
          $update_user_stmt->bindValue(':playon', ($data->playon ?? $game['playon']), PDO::PARAM_STR);
          $update_user_stmt->bindValue(':rate', ($data->rate ?? $game['rate']), PDO::PARAM_STR);
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