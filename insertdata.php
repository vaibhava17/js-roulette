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
elseif (empty($data) || !isset($data->name) || empty(trim($data->name)) || !isset($data->mobile) || empty(trim($data->mobile))):
  $fields = ['fields' => ['name', 'mobile']];
  $returnData = $error_handler->getResponse(0, 422, 'Please Fill in all Required Fields!', $fields);
else:
  $name = trim($data->name);
  $mobile = trim($data->mobile);
  if (strlen($mobile) > 10):
    $returnData = $error_handler->getResponse(0, 422, 'Invalid Mobile Number!');
  else:
    try {
      $check_mobile = "SELECT `mobile` FROM `users` WHERE `mobile`=:mobile";
      $check_mobile_stmt = $conn->prepare($check_mobile);
      $check_mobile_stmt->bindValue(':mobile', $mobile, PDO::PARAM_STR);
      $check_mobile_stmt->execute();
      if ($check_mobile_stmt->rowCount()):
        $returnData = $error_handler->getResponse(0, 422, 'This mobile is already in use!');
      else:
        $insert_query = "INSERT INTO `users`(`name`,`mobile`,`balance`, `role`,`exposer`,`password`,`email`) VALUES(:name,:mobile,:balance,:role,:exposer,:password,:email)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bindValue(':name', htmlspecialchars(strip_tags($name)), PDO::PARAM_STR);
        $insert_stmt->bindValue(':mobile', $mobile, PDO::PARAM_STR);
        $insert_stmt->bindValue(':balance',0, PDO::PARAM_STR);
        $insert_stmt->bindValue(':role', 'user', PDO::PARAM_STR);
        $insert_stmt->bindValue(':exposer',0, PDO::PARAM_STR);
        $insert_stmt->bindValue(':password',$data->password, PDO::PARAM_STR);
        $insert_stmt->bindValue(':email', $data->email, PDO::PARAM_STR);
        $insert_stmt->execute();
        $returnData = $error_handler->getResponse(1, 201, 'Data added.');
      endif;
    } catch (PDOException $e) {
      $returnData = $error_handler->getResponse(0, 500, $e->getMessage());
    }
  endif;
endif;

echo json_encode($returnData);