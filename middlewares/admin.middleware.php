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
      if (isset($data['data']->user_role) && $data['data']->user_role == "admin") {
        return true;
      } else {
        return false;
      }
    } else {
      return false;
    }
  }
}