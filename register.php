<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require __DIR__ . '/classes/db.config.php';
require __DIR__ . '/classes/error.handler.php';

$db_connection = new Database();
$conn = $db_connection->dbConnection();
$error_handler = new ErrorHandler();

$data = json_decode(file_get_contents("php://input"));
$returnData = [];

if ($_SERVER["REQUEST_METHOD"] != "POST"):
    $returnData = $error_handler->getResponse(0, 404, 'Page Not Found!');
elseif (
    !isset($data->name)
    || !isset($data->mobile)
    || !isset($data->password)
    || empty(trim($data->name))
    || empty(trim($data->mobile))
    || empty(trim($data->password))
):
    $fields = ['fields' => ['name', 'mobile', 'password',]];
    $returnData = $error_handler->getResponse(0, 422, 'Please Fill in all Required Fields!', $fields);
else:
    $name = trim($data->name);
    $mobile = trim($data->mobile);
    $password = trim($data->password);
    if (strlen($mobile) > 10):
        $returnData = $error_handler->getResponse(0, 422, 'Invalid Mobile Number!');
    elseif (strlen($name) < 3):
        $returnData = $error_handler->getResponse(0, 422, 'Your name must be at least 3 characters long!');
    else:
        try {
            $check_mobile = "SELECT `mobile` FROM `users` WHERE `mobile`=:mobile";
            $check_mobile_stmt = $conn->prepare($check_mobile);
            $check_mobile_stmt->bindValue(':mobile', $mobile, PDO::PARAM_STR);
            $check_mobile_stmt->execute();
            if ($check_mobile_stmt->rowCount()):
                $returnData = $error_handler->getResponse(0, 422, 'This mobile is already in use!');
            else:
                $insert_query = "INSERT INTO `users`(`name`,`mobile`,`password`, `balance`, `role` `exposer`) VALUES(:name,:mobile,:password, :balance, :role,:exposer)";
                $insert_stmt = $conn->prepare($insert_query);
                $insert_stmt->bindValue(':name', htmlspecialchars(strip_tags($name)), PDO::PARAM_STR);
                $insert_stmt->bindValue(':mobile', $mobile, PDO::PARAM_STR);
                $insert_stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
                $insert_stmt->bindValue(':balance', 0, PDO::PARAM_INT);
                $insert_stmt->bindValue(':role', 'user', PDO::PARAM_STR);
                $insert_stmt->bindValue(':exposer', 0, PDO::PARAM_INT);

                $insert_stmt->execute();
                $returnData = $error_handler->getResponse(1, 201, 'You have successfully registered.');
            endif;
        } catch (PDOException $e) {
            $returnData = $error_handler->getResponse(0, 500, $e->getMessage());
        }
    endif;
endif;

echo json_encode($returnData);