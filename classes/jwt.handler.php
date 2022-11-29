<?php
require './vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
use Firebase\JWT\JWT;

class JwtHandler
{
    protected $jwt_secrect;
    protected $token;
    protected $issuedAt;
    protected $expire;
    protected $jwt;

    public function __construct()
    {
        date_default_timezone_set('Asia/Kolkata');
        $this->issuedAt = time();

        $this->expire = $this->issuedAt + 3600 * 24 * 7; // 7 days

        $this->jwt_secrect = $_ENV['JWT_SECRECT'];
    }

    public function jwtEncodeData($data)
    {

        $this->token = array(
            "iat" => $this->issuedAt,
            "exp" => $this->expire,
            "data" => $data
        );

        $this->jwt = JWT::encode($this->token, $this->jwt_secrect, 'HS256');
        return $this->jwt;
    }

    public function jwtDecodeData($jwt_token)
    {
        try {
            $decode = JWT::decode($jwt_token, $this->jwt_secrect, array('HS256'));
            return [
                "data" => $decode->data
            ];
        } catch (Exception $e) {
            return [
                "message" => $e->getMessage()
            ];
        }
    }
}