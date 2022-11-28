<?php
require './classes/jwt.handler.php';
class AdminAuth extends JwtHandler
{

  protected $db;
  protected $headers;
  protected $token;

  public function __construct($db, $headers)
  {
    parent::__construct();
    $this->db = $db;
    $this->headers = $headers;
  }

  public function isValid()
  {

    if (array_key_exists('Authorization', $this->headers) && preg_match('/Bearer\s(\S+)/', $this->headers['Authorization'], $matches)) {

      $data = $this->jwtDecodeData($matches[1]);
      if (isset($data['data']->user_role) && $data['data']->user_role == "admin" && $users = $this->fetchUsers()) :
        return [
          "success" => 1,
          "items" => $users
        ];
      else:
        return [
          "success" => 0,
          "message" => $data['message'],
        ];
      endif;
    } else {
      return [
        "success" => 0,
        "message" => "Token not found in request"
      ];
    }
  }


  protected function fetchUsers()
  {
    try {
      $fetch_users = "SELECT `id`,`name`,`email`,`balance`,`mobile` FROM `users`";
      $query_stmt = $this->db->prepare($fetch_users);
      $query_stmt->execute();

      if ($query_stmt->rowCount()) :
        return $query_stmt->fetchAll(PDO::FETCH_ASSOC);
      else :
        return false;
      endif;
    } catch (PDOException $e) {
      return null;
    }
  }
}