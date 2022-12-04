<?php
require __DIR__ . '/classes/db.config.php';
require __DIR__ . '/classes/error.handler.php';

$db_connection = new Database();
$conn = $db_connection->dbConnection();
$error_handler = new ErrorHandler();

$data = json_decode(file_get_contents("php://input"));
$returnData = [];
$withdrawid = isset($_GET['withdrawid']) ? $_GET['withdrawid'] : null;
$mobile = isset($_GET['mobile']) ? $_GET['mobile'] : null;

if ($_SERVER["REQUEST_METHOD"] != "PUT"):
  $returnData = $error_handler->getResponse(0, 404, 'Page Not Found!');
else:
  if (empty($data) && ($mobile || $withdrawid)):
    $returnData = $error_handler->getResponse(0, 422, 'Invalid Data! Please try again.');
  else:
    try {
      $withdrawl_data = "SELECT * FROM withdrawal WHERE withdrawid=:value OR mobile=:value";
      $withdrawl_stmt = $conn->prepare($withdrawl_data);
      $withdrawl_stmt->bindValue(':value', $withdrawid ?? $mobile, PDO::PARAM_STR);
      $withdrawl_stmt->execute();
      if ($withdrawl_stmt->rowCount() > 0):
        $withdrawl = $withdrawl_stmt->fetch(PDO::FETCH_ASSOC);
        $sql = "UPDATE withdrawal SET userid=:userid,remainingbalance=:remainingbalance, withdrawamount=:withdrawamount, paymentmode=:paymentmode, withdrawstatus=:withdrawstatus WHERE `withdrawid`=:value OR `mobile`=:value";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':value', $withdrawid ?? $mobile, PDO::PARAM_STR);
        $stmt->bindValue(':userid', $data->userid ?? $withdrawl['userid'], PDO::PARAM_STR);
        $stmt->bindValue(':remainingbalance', $data->remainingbalance ?? $withdrawl['remainingbalance'], PDO::PARAM_STR);
        $stmt->bindValue(':withdrawamount', $data->withdrawamount ?? $withdrawl['withdrawamount'], PDO::PARAM_STR);
        $stmt->bindValue(':paymentmode', $data->paymentmode ?? $withdrawl['paymentmode'], PDO::PARAM_STR);
        $stmt->bindValue(':withdrawstatus', $data->withdrawstatus ?? $withdrawl['withdrawstatus'], PDO::PARAM_STR);
        $stmt->execute();
        $returnData = $error_handler->getResponse(1, 200, 'Withdrawal Updated Successfully!');
      else:
        $returnData = $error_handler->getResponse(0, 404, 'No Data Found!');
      endif;
    } catch (PDOException $e) {
      $returnData = $error_handler->getResponse(0, 422, $e->getMessage());
    }
  endif;
endif;

echo json_encode($returnData);