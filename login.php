<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require __DIR__ . '/classes/db.config.php';
require __DIR__ . '/classes/jwt.handler.php';
require __DIR__ . '/classes/error.handler.php';

$db_connection = new Database();
$conn = $db_connection->dbConnection();
$error_handler = new ErrorHandler();

$data = json_decode(file_get_contents("php://input"));
$returnData = [];

if ($_SERVER["REQUEST_METHOD"] != "POST"):
    $returnData = $error_handler->getResponse(0, 404, 'Page Not Found!');

elseif (
    !isset($data->email)
    || !isset($data->password)
    || empty(trim($data->email))
    || empty(trim($data->password))
):

    $fields = ['fields' => ['email', 'password']];
    $returnData = $error_handler->getResponse(0, 422, 'Please Fill in all Required Fields!', $fields);

else:
    $email = trim($data->email);
    $password = trim($data->password);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)):
        $returnData = $error_handler->getResponse(0, 422, 'Invalid Email Address!');

    elseif (strlen($password) < 8):
        $returnData = $error_handler->getResponse(0, 422, 'Your password must be at least 8 characters long!');

    else:
        try {

            $fetch_user_by_email = "SELECT * FROM `users` WHERE `email`=:email";
            $query_stmt = $conn->prepare($fetch_user_by_email);
            $query_stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $query_stmt->execute();

            if ($query_stmt->rowCount()):
                $row = $query_stmt->fetch(PDO::FETCH_ASSOC);
                $check_password = password_verify($password, $row['password']);

                if ($check_password):

                    $jwt = new JwtHandler();
                    $token = $jwt->jwtEncodeData(
                        array(
                            "user_id" => $row['id'],
                            "user_email" => $row['email'],
                            "user_role" => $row['role']
                        )

                    );

                    $returnData = [
                        'success' => 1,
                        'message' => 'You have successfully logged in.',
                        'token' => $token
                    ];

                else:
                    $returnData = $error_handler->getResponse(0, 422, 'Invalid Password!');
                endif;

            else:
                $returnData = $error_handler->getResponse(0, 422, 'Invalid Email Address!');
            endif;
        } catch (PDOException $e) {
            $returnData = $error_handler->getResponse(0, 500, $e->getMessage());
        }

    endif;

endif;

echo json_encode($returnData);