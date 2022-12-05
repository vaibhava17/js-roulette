<?php
require __DIR__ . '/classes/db.config.php';
require __DIR__ . '/classes/error.handler.php';

$db_connection = new Database();
$conn = $db_connection->dbConnection();
$error_handler = new ErrorHandler();

$data = json_decode(file_get_contents("php://input"));
$returnData = [];

if ($_SERVER["REQUEST_METHOD"] != "PUT"):
  $returnData = $error_handler->getResponse(0, 404, 'Page Not Found!');
else:
  if (empty($data) || !isset($data->mobile) || empty(trim($data->mobile))):
    $returnData = $error_handler->getResponse(0, 422, 'Mobile number is required!');
  else:
    try {
      $update = "UPDATE `users` SET `exposer`=:exposer WHERE `mobile`=:mobile";
      $update_stmt = $conn->prepare($update);
      $update_stmt->bindValue(':mobile', $data->mobile, PDO::PARAM_INT);
      $update_stmt->bindValue(':exposer', $data->exposer, PDO::PARAM_STR);
      if ($update_stmt->execute()):
        $returnData = $error_handler->getResponse(1, 200, 'exposer updated successfully!');
      else:
        $returnData = $error_handler->getResponse(0, 500, 'Something went wrong. Please try again.');
      endif;
    } catch (PDOException $e) {
      $returnData = $error_handler->getResponse(0, 500, $e->getMessage());
    }
  endif;
endif;

echo json_encode($returnData);