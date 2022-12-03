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
elseif (empty($data) || (!isset($data->mobile) || empty(trim($data->mobile)))|| (!isset($data->game_id) || empty(trim($data->game_id)))):
  $returnData = $error_handler->getResponse(0, 422, 'Mobile number is required!');
else:
  try {
    $fetch_user = "SELECT * FROM `game_history` WHERE `mobile`=:mobile";
    $fetch_user_stmt = $conn->prepare($fetch_user);
    $fetch_user_stmt->bindValue(':mobile', $data->mobile, PDO::PARAM_INT);
    $fetch_user_stmt->execute();
    if ($fetch_user_stmt->rowCount()):
      $game_history = $fetch_user_stmt->fetch(PDO::FETCH_ASSOC);
      $returnData = $error_handler->getResponse(1, 200, 'Record found!', $game_history);
    else:
      $returnData = $error_handler->getResponse(0, 422, 'No Data found!');
    endif;
  } catch (PDOException $e) {
    $returnData = $error_handler->getResponse(0, 500, $e->getMessage());
  }
endif;

echo json_encode($returnData);