<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require __DIR__ . '/classes/db.config.php';
require __DIR__ . '/classes/jwt.handler.php';

function msg($success, $status, $message, $extra = [])
{
    return array_merge([
        'success' => $success,
        'status' => $status,
        'message' => $message
    ], $extra);
}

$db_connection = new Database();
$conn = $db_connection->dbConnection();

$data = json_decode(file_get_contents("php://input"));
$returnData = [];

// IF REQUEST METHOD IS NOT EQUAL TO POST
if ($_SERVER["REQUEST_METHOD"] != "POST"):
    $returnData = msg(0, 404, 'Page Not Found!');

    // CHECKING EMPTY FIELDS
elseif (
    !isset($data->mobile)
    
    || empty(trim($data->mobile))
  
):

    $fields = ['fields' => ['mobile']];
    $returnData = msg(0, 422, 'Please Send Mobile Number!', $fields);

    // IF THERE ARE NO EMPTY FIELDS THEN-
else:
    $mobile = trim($data->mobile);
 

    // CHECKING THE MOLBILE FORMAT (IF INVALID FORMAT)
    if (strlen($mobile)>10):
        $returnData = msg(0, 422, 'Invalid Mobile Number!');

        // THE USER IS ABLE TO PERFORM THE Balnce ACTION
    else:
        try {

            $fetch_user_by_mobile = "SELECT 'balance'  FROM `users` WHERE `mobile`=:mobile";
            $query_stmt = $conn->prepare($fetch_user_by_mobile);
            $query_stmt->bindValue(':mobile', $mobile, PDO::PARAM_STR);
            $query_stmt->execute();

            // IF THE USER IS FOUNDED BY MOBILE
            if ($query_stmt->rowCount()):
                $row = $query_stmt->fetch(PDO::FETCH_ASSOC);
                $check_password = password_verify($password, $row['password']);

                // VERIFYING THE PASSWORD (IS CORRECT OR NOT?)
                // IF PASSWORD IS CORRECT THEN SEND THE LOGIN TOKEN
                if ($check_password):

                    $jwt = new JwtHandler();
                    $token = $jwt->jwtEncodeData(
                        array(
                            "user_id" => $row['id'],
                            "user_mobile" => $row['mobile'],
                            "user_role" => $row['role']
                        )

                    );

                    $returnData = [
                        'success' => 1,
                        'message' => 'You have successfully logged in.',
                        'balance' => $token
                    ];

                    // IF INVALID PASSWORD
                else:
                    $returnData = msg(0, 422, 'Invalid Password!');
                endif;

                // IF THE USER IS NOT FOUNDED BY MOBILE THEN SHOW THE FOLLOWING ERROR
            else:
                $returnData = msg(0, 422, 'Invalid Mobile Numberrrrrrr!');
            endif;
        } catch (PDOException $e) {
            $returnData = msg(0, 500, $e->getMessage());
        }

    endif;

endif;

echo json_encode($returnData);