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
  empty($data)
  || !isset($data->userid)
  || !isset($data->withdrawamount)
  || !isset($data->mobile)
  || !isset($data->accountnumber)
  || !isset($data->accountname)
  || !isset($data->bankname)
  || !isset($data->ifsc)
  || !isset($data->accounttype)
  || empty(trim($data->userid))
  || empty(trim($data->withdrawamount))
  || empty(trim($data->mobile))
  || empty(trim($data->accountnumber))
  || empty(trim($data->accountname))
  || empty(trim($data->bankname))
  || empty(trim($data->ifsc))
  || empty(trim($data->accounttype))
):
  $fields = ['fields' => ['mobile', 'withdrawamount', 'accountnumber', 'accountname', 'bankname', 'ifsc', 'accounttype']];
  $returnData = $error_handler->getResponse(0, 422, 'Please Fill in all Required Fields!', $fields);
else:
  $userid = trim($data->userid);
  $withdrawamount = trim($data->withdrawamount);
  $mobile = trim($data->mobile);
  $accountnumber = trim($data->accountnumber);
  $accountname = trim($data->accountname);
  $bankname = trim($data->bankname);
  $ifsc = trim($data->ifsc);
  $accounttype = trim($data->accounttype);
  $pattern = '/^[6-9]\d{9}$/';
  if (preg_match($pattern, $mobile) == 0):
    $returnData = $error_handler->getResponse(0, 422, 'Invalid Mobile Number!');
  else:
    try {
      $fetch_user_by_mobile = "SELECT * FROM `users` WHERE `mobile`=:mobile";
      $query_stmt = $conn->prepare($fetch_user_by_mobile);
      $query_stmt->bindValue(':mobile', $userid, PDO::PARAM_STR);
      if ($query_stmt->execute()):
        $row = $query_stmt->fetch(PDO::FETCH_ASSOC);
        $balance = $row['balance'];
        if ($balance < $withdrawamount):
          $returnData = $error_handler->getResponse(0, 403, 'Insufficient Balance!');
        else:
          $add_withdraw_request = "INSERT INTO `withdrawal` (userid,remainingbalance,withdrawamount,withdrawstatus,mobile, accountnumber, accountname, bankname, ifsc, accounttype) VALUES(:userid,:remainingbalance,:withdrawamount,:withdrawstatus,:mobile, :accountnumber, :accountname, :bankname, :ifsc, :accounttype)";
          $insert_stmt = $conn->prepare($add_withdraw_request);
          $insert_stmt->bindValue(':userid', $userid, PDO::PARAM_STR);
          $insert_stmt->bindValue(':remainingbalance', $balance - $withdrawamount, PDO::PARAM_STR);
          $insert_stmt->bindValue(':withdrawamount', $withdrawamount, PDO::PARAM_STR);
          $insert_stmt->bindValue(':withdrawstatus', 'pending', PDO::PARAM_STR);
          $insert_stmt->bindValue(':mobile', $mobile, PDO::PARAM_STR);
          $insert_stmt->bindValue(':accountnumber', $accountnumber, PDO::PARAM_STR);
          $insert_stmt->bindValue(':accountname', $accountname, PDO::PARAM_STR);
          $insert_stmt->bindValue(':bankname', $bankname, PDO::PARAM_STR);
          $insert_stmt->bindValue(':ifsc', $ifsc, PDO::PARAM_STR);
          $insert_stmt->bindValue(':accounttype', $accounttype, PDO::PARAM_STR);
          if ($insert_stmt->execute()):
            $returnData = $error_handler->getResponse(1, 200, 'Withdrawal Request Sent Successfully!');
          else:
            $returnData = $error_handler->getResponse(0, 500, 'Something Went Wrong!');
          endif;
        endif;
      else:
        $returnData = $error_handler->getResponse(0, 403, 'Invalid User!');
      endif;
    } catch (PDOException $e) {
      $returnData = $error_handler->getResponse(0, 500, $e->getMessage());
    }
  endif;
endif;

echo json_encode($returnData);