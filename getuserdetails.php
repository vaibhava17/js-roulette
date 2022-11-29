<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require __DIR__ . '/classes/db.config.php';
require __DIR__ . '/middlewares/auth.middleware.php';
require __DIR__ . '/classes/error.handler.php';

$allHeaders = getallheaders();
$db_connection = new Database();
$conn = $db_connection->dbConnection();
$auth = new Auth($conn, $allHeaders);
$error_handler = new ErrorHandler();

if ($_SERVER["REQUEST_METHOD"] != "GET"):
  $returnData = $error_handler->getResponse(0, 404, 'Page Not Found!');
elseif (!$auth->isValid()):
  $returnData = $error_handler->getResponse(0, 401, 'Unauthorized!');
else:
  try {
    $user_id = $auth->getUserId();
    $fetch_user = "SELECT * FROM `users` WHERE `id`=:id";
    $fetch_user_stmt = $conn->prepare($fetch_user);
    $fetch_user_stmt->bindValue(':id', $user_id, PDO::PARAM_INT);
    $fetch_user_stmt->execute();
    if ($fetch_user_stmt->rowCount()):
      $user = $fetch_user_stmt->fetch(PDO::FETCH_ASSOC);
      $returnData = [
        'success' => 1,
        'user' => $user
      ];
    else:
      $returnData = $error_handler->getResponse(0, 422, 'No User Found!');
    endif;
  } catch (PDOException $e) {
    $returnData = $error_handler->getResponse(0, 500, $e->getMessage());
  }
endif;

echo json_encode($returnData);