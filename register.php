<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require __DIR__ . '/classes/db.config.php';
$db_connection = new Database();
$conn = $db_connection->dbConnection();

function msg($success, $status, $message, $extra = [])
{
    return array_merge([
        'success' => $success,
        'status' => $status,
        'message' => $message
    ], $extra);
}

// DATA FORM REQUEST
$data = json_decode(file_get_contents("php://input"));
$returnData = [];

if ($_SERVER["REQUEST_METHOD"] != "POST"):

    $returnData = msg(0, 404, 'Page Not Found!');
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
    $returnData = msg(0, 422, 'Please Fill in all Required Fields!', $fields);

    // IF THERE ARE NO EMPTY FIELDS THEN-
else:
    if (trim($data->password) != trim($data->confirm_password)):
        $returnData = msg(0, 422, 'Password and Confirm Password does not match!');
    else:
        $name = trim($data->name);
        $email = trim($data->email);
        $mobile = trim($data->mobile);
        $password = trim($data->password);
        if (strlen($mobile)<10 || strlen($mobile)>10):
            $returnData = msg(0, 422, 'Invalid Mobile Number!');
        elseif (strlen($password) < 8):
            $returnData = msg(0, 422, 'Your password must be at least 8 characters long!');
        elseif (strlen($name) < 3):
            $returnData = msg(0, 422, 'Your name must be at least 3 characters long!');
        else:
            try {

                $check_mobile = "SELECT `mobile` FROM `users` WHERE `mobile`=:mobile";
                $check_mobile_stmt = $conn->prepare($check_mobile);
                $check_mobile_stmt->bindValue(':mobile', $mobile, PDO::PARAM_STR);
                $check_mobile_stmt->execute();

                if ($check_mobile_stmt->rowCount()):
                    $returnData = msg(0, 422, 'This Mobile Number already Registered!');
                else:
                    $insert_query = "INSERT INTO `users`(`name`,`email`,`mobile`,`password`, `balance`, `role`) VALUES(:name,:email,:mobile,:password, 0, 'user')";

                    $insert_stmt = $conn->prepare($insert_query);

                    // DATA BINDING
                    $insert_stmt->bindValue(':name', htmlspecialchars(strip_tags($name)), PDO::PARAM_STR);
                    $insert_stmt->bindValue(':email', $email, PDO::PARAM_STR);
                    $insert_stmt->bindValue(':mobile', $mobile, PDO::PARAM_STR);
                    $insert_stmt->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);

                    $insert_stmt->execute();

                    $returnData = msg(1, 201, 'You have successfully registered.');

                endif;
            } catch (PDOException $e) {
                $returnData = msg(0, 500, $e->getMessage());
            }
        endif;
    endif;
endif;

echo json_encode($returnData);