<?php
require __DIR__ . '/classes/db.config.php';
require __DIR__ . '/classes/error.handler.php';

$db_connection = new Database();
$conn = $db_connection->dbConnection();
$error_handler = new ErrorHandler();

$data = json_decode(file_get_contents("php://input"));
$returnData = [];
$game_id = isset($_GET['game_id']) ? $_GET['game_id'] : null;
$mobile = isset($_GET['mobile']) ? $_GET['mobile'] : null;

if ($_SERVER["REQUEST_METHOD"] != "PUT"):
  $returnData = $error_handler->getResponse(0, 404, 'Page Not Found!');
else:
  if (empty($data) && (empty($mobile) || empty($game_id))):
    $returnData = $error_handler->getResponse(0, 422, 'Invalid Data! Please try again.');
  else:
    try {
      $game_data = "SELECT * FROM game_history WHERE mobile = :value OR game_id = :value";
      $get_stmt = $conn->prepare($game_data);
      $get_stmt->bindValue(':value', $mobile ?? $game_id, PDO::PARAM_STR);
      $get_stmt->execute();
      if ($get_stmt->rowCount() > 0):
        $game = $get_stmt->fetch(PDO::FETCH_ASSOC);
        $sql = "UPDATE game_history SET game_name=:game_name, playon=:playon, amount=:amount, new_balance=:new_balance, settle_status=:settle_status, rate=:rate  WHERE mobile=:value OR game_id=:value";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':value', $game_id ?? $mobile, PDO::PARAM_STR);
        $stmt->bindValue(':game_name', $data->game_name ?? $game['game_name'], PDO::PARAM_STR);
        $stmt->bindValue(':playon', $data->playon ?? $game['playon'], PDO::PARAM_STR);
        $stmt->bindValue(':amount', $data->amount ?? $game['amount'], PDO::PARAM_STR);
        $stmt->bindValue(':new_balance', $data->new_balance ?? $game['new_balance'], PDO::PARAM_STR);
        $stmt->bindValue(':settle_status', $data->settle_status ?? $game['settle_status'], PDO::PARAM_STR);
        $stmt->bindValue(':rate', $data->rate ?? $game['rate'], PDO::PARAM_STR);
        $stmt->execute();
        $returnData = $error_handler->getResponse(1, 200, 'Game History Updated Successfully!');
      else:
        $returnData = $error_handler->getResponse(0, 404, 'No Data Found!');
      endif;
    } catch (PDOException $e) {
      $returnData = $error_handler->getResponse(0, 422, $e->getMessage());
    }
  endif;
endif;

echo json_encode($returnData);