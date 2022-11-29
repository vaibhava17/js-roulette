<?php
require './classes/jwt.handler.php';



class Auth extends JwtHandler 
{
    protected $db;
    protected $headers;

    protected $error_handler;

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

            if (
                isset($data['data']->user_id) &&
                isset($data['data']->user_mobile) &&
                isset($data['data']->user_role)
            ) {
                return $data['data'];
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getUserId()
    {
        return $this->isValid()->user_id;
    }
}
