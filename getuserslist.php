<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require __DIR__.'/classes/db.config.php';
require __DIR__.'/classes/error.handler.php';
require __DIR__.'/middlewares/admin.middleware.php';

$allHeaders = getallheaders();
$db_connection = new Database();
$conn = $db_connection->dbConnection();
$auth = new AdminAuth($conn, $allHeaders);
$error_handler = new ErrorHandler();

if ($_SERVER["REQUEST_METHOD"] != "GET"):
  $returnData = $error_handler->getResponse(0, 404, 'Page Not Found!');
elseif (!$auth->isValid()):
  $returnData = $error_handler->getResponse(0, 401, 'Unauthorized!');
else:
  try {
    $fetch_users = "SELECT * FROM `users`";
    $fetch_users_stmt = $conn->prepare($fetch_users);
    $fetch_users_stmt->execute();
    if ($fetch_users_stmt->rowCount()):
      $users = $fetch_users_stmt->fetchAll(PDO::FETCH_ASSOC);
      $returnData = [
        'success' => 1,
        'users' => $users
      ];
    else:
      $returnData = $error_handler->getResponse(0, 422, 'No Users Found!');
    endif;
  } catch (PDOException $e) {
    $returnData = $error_handler->getResponse(0, 500, $e->getMessage());
  }
endif;

echo json_encode($returnData);