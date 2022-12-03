<?php
require __DIR__.'/classes/db.config.php';
require __DIR__.'/classes/error.handler.php';

$db_connection = new Database();
$conn = $db_connection->dbConnection();
$error_handler = new ErrorHandler();

$data = json_decode(file_get_contents("php://input"));
$returnData = [];

if ($_SERVER["REQUEST_METHOD"] != "GET"):
  $returnData = $error_handler->getResponse(0, 404, 'Page Not Found!');
  elseif (empty($data) && ((!isset($data->game_id) || empty(trim($data->game_id))) || (!isset($data->mobile) || empty(trim($data->mobile))))):
  $returnData = $error_handler->getResponse(0, 422, 'Game-ID or Number is required!');
else:
  try {
    if((isset($data->game_id) ) || (isset($data->mobile) )):
      $fetch_user = "SELECT * FROM `game_history` WHERE `mobile`=:game_id OR `game_id`=:game_id";
    $fetch_user_stmt = $conn->prepare($fetch_user);
    $fetch_user_stmt->bindValue(':game_id', $data->mobile ?? $data->game_id, PDO::PARAM_INT);
    // if (isset($data->mobile)):
      
    // $fetch_user_stmt->bindValue(':mobile', $data->mobile, PDO::PARAM_INT);
    // elseif(isset($data->game_id)):
    
    // endif;
    $fetch_user_stmt->execute();
    if ($fetch_user_stmt->rowCount()):
      $game_history = $fetch_user_stmt->fetch(PDO::FETCH_ASSOC);
      $returnData = $error_handler->getResponse(1, 200, 'Record found!', $game_history);
    else:
      $returnData = $error_handler->getResponse(0, 422, 'No Data found!');
    endif;
  else:
    $returnData = $error_handler->getResponse(0,403,'unauthorised');
  endif;
  } catch (PDOException $e) {
    $returnData = $error_handler->getResponse(0, 500, $e->getMessage());
  }
endif;

echo json_encode($returnData);