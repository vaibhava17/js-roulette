<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require __DIR__ . '/classes/db.config.php';
require __DIR__.'/classes/error.handler.php';

$db_connection = new Database();
$conn = $db_connection->dbConnection();
$error_handler = new ErrorHandler();

$data = json_decode(file_get_contents("php://input"));
$returnData = [];

if ($_SERVER["REQUEST_METHOD"] != "POST"):

    $returnData = $error_handler->getResponse(0, 404, 'Page Not Found!');
elseif (
    !isset($data->name)
    || !isset($data->email)
    || !isset($data->mobile)
    || !isset($data->password)
    || !isset($data->confirm_password)
    || empty(trim($data->name))
    || empty(trim($data->email))
    || empty(trim($data->mobile))
    || empty(trim($data->password))
    || empty(trim($data->confirm_password))
):

    $fields = ['fields' => ['name', 'email', 'mobile', 'password', 'confirm_password']];
    $returnData = $error_handler->getResponse(0, 422, 'Please Fill in all Required Fields!', $fields);

else:
    if (trim($data->password) != trim($data->confirm_password)):
        $returnData = $error_handler->getResponse(0, 422, 'Password and Confirm Password does not match!');
    else:
        $name = trim($data->name);
        $email = trim($data->email);
        $mobile = trim($data->mobile);
        $password = trim($data->password);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)):
            $returnData = $error_handler->getResponse(0, 422, 'Invalid Email Address!');
        elseif (strlen($password) < 8):
            $returnData = $error_handler->getResponse(0, 422, 'Your password must be at least 8 characters long!');
        elseif (strlen($name) < 3):
            $returnData = $error_handler->getResponse(0, 422, 'Your name must be at least 3 characters long!');
        else:
            try {

                $check_email = "SELECT `email` FROM `users` WHERE `email`=:email";
                $check_email_stmt = $conn->prepare($check_email);
                $check_email_stmt->bindValue(':email', $email, PDO::PARAM_STR);
                $check_email_stmt->execute();

                if ($check_email_stmt->rowCount()):
                    $returnData = $error_handler->getResponse(0, 422, 'This E-mail already in use!');
                else:
                    $insert_query = "INSERT INTO `users`(`name`,`email`,`mobile`,`password`, `balance`, `role`) VALUES(:name,:email,:mobile,:password, :balance, :role)";

                    $insert_stmt = $conn->prepare($insert_query);

                    $insert_stmt->bindValue(':name', htmlspecialchars(strip_tags($name)), PDO::PARAM_STR);
                    $insert_stmt->bindValue(':email', $email, PDO::PARAM_STR);
                    $insert_stmt->bindValue(':mobile', $mobile, PDO::PARAM_STR);
                    $insert_stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);
                    $insert_stmt->bindValue(':balance', 0, PDO::PARAM_INT);
                    $insert_stmt->bindValue(':role', 'user', PDO::PARAM_STR);

                    $insert_stmt->execute();

                    $returnData = $error_handler->getResponse(1, 201, 'You have successfully registered.');

                endif;
            } catch (PDOException $e) {
                $returnData = $error_handler->getResponse(0, 500, $e->getMessage());
            }
        endif;
    endif;
endif;

echo json_encode($returnData);