<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require __DIR__ . '/classes/db.config.php';
require __DIR__ . '/classes/error.handler.php';
require __DIR__ . '/middlewares/admin.middleware.php';

$allHeaders = getallheaders();
$db_connection = new Database();
$conn = $db_connection->dbConnection();
$error_handler = new ErrorHandler();
$auth = new AdminAuth($conn, $allHeaders);

$data = json_decode(file_get_contents("php://input"));
$returnData = [];

if ($_SERVER["REQUEST_METHOD"] != "PUT"):
  $returnData = $error_handler->getResponse(0, 404, 'Page Not Found!');
elseif (!$auth->isValid()):
  $returnData = $error_handler->getResponse(0, 401, 'Unauthorized!');
else:
  if (empty($data) || !isset($data->mobile) || empty(trim($data->mobile))):
    $returnData = $error_handler->getResponse(0, 422, 'Invalid Data! Please try again.');
  elseif (isset($data->password) && strlen($data->password) < 8):
      $returnData = $error_handler->getResponse(0, 422, 'Your password must be at least 8 characters long!');
  else:
    $fetch_user = "SELECT * FROM `users` WHERE `mobile`=:mobile";
    $fetch_user_stmt = $conn->prepare($fetch_user);
    $fetch_user_stmt->bindValue(':mobile', $data->mobile, PDO::PARAM_INT);
    $fetch_user_stmt->execute();
    if ($fetch_user_stmt->rowCount()):
      $user = $fetch_user_stmt->fetch(PDO::FETCH_ASSOC);
      if (!empty($user)):
        $update_user = "UPDATE `users` SET `name`=:name, `email`=:email, `balance`=:balance, `role`=:role, `password`=:password WHERE `mobile`=:mobile";
        $update_user_stmt = $conn->prepare($update_user);
        $update_user_stmt->bindValue(':mobile', ($data->mobile ?? $user['mobile']), PDO::PARAM_STR);
        $update_user_stmt->bindValue(':name', ($data->name ?? $user['name']), PDO::PARAM_STR);
        $update_user_stmt->bindValue(':email', ($data->email ?? $user['email']), PDO::PARAM_STR);
        $update_user_stmt->bindValue(':balance', ($data->balance ?? $user['balance']), PDO::PARAM_STR);
        $update_user_stmt->bindValue(':role', ($data->role ?? $user['role']), PDO::PARAM_STR);
        $update_user_stmt->bindValue(':password', (password_hash($data->password, PASSWORD_DEFAULT) ?? $user['password']), PDO::PARAM_STR);
        if ($update_user_stmt->execute()):
          $returnData = $error_handler->getResponse(1, 200, 'User Updated Successfully!');
        else:
          $returnData = $error_handler->getResponse(0, 500, 'Something went wrong. Please try again.');
        endif;
      endif;
    else:
      $returnData = $error_handler->getResponse(0, 422, 'No User Found!');
    endif;
  endif;
endif;

echo json_encode($returnData);