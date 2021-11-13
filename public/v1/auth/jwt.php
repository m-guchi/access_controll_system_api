<?php

namespace Auth;

include_once(__DIR__."/cookie.php");

use ApiReturn;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Ramsey\Uuid\Uuid;
use DateTimeImmutable;
use DB\DB;
use PDOException;

class JWT
{
    public $jwt;
    private bool $is_valid_jwt = false;
    private string $key = "OseSuQaAznPSzaLL09etQXC5Awq2tj0s";

    public function __construct()
    {
        if(!is_null($this->get_token())){
            $this->is_valid_jwt = true;
        }
        $this->jwt = $this->get_token();
    }

    public function is_valid():bool
    {
        return $this->is_valid_jwt;
    }

    public function get($key)
    {
        if(!$this->is_valid()) return null;
        return $this->jwt->claims()->get($key);
    }

    //JWTがない場合はnullを返す
    private function get_token()
    {
        $cookie = new Cookie();
        $jwt = $cookie->get();
        if(is_null($jwt)) return null;
        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::base64Encoded($this->key),
        );
        return $config->parser()->parse($jwt);
    }

    private $exp_time = "+24 hour";

    //JWTを発行
    //return [jwt_token, token_id, token]
    public function create_token($user_id):array
    {
        $token_id = Uuid::uuid4();
        $token = Uuid::uuid4();
        $now   = new DateTimeImmutable();

        $config = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::base64Encoded($this->key),
        );
        $hashed_token = password_hash($token,PASSWORD_DEFAULT);
        $url = get_url();
        $jwt_token = $config->builder()
                            ->issuedBy($url)
                            ->issuedAt($now)
                            ->expiresAt($now->modify($this->exp_time))
                            ->withClaim('id', $user_id)
                            ->withClaim('token', $hashed_token)
                            ->withClaim('token_id', $token_id)
                            ->getToken($config->signer(), $config->signingKey());

        return [
            "jwt_token"=>$jwt_token,
            "token_id"=>$token_id,
            "token"=>$token
        ];
    }

    //CSRFトークンをDB登録＋cookie設定
    public ?array $error_msg = null;
    public function insert_token($jwt,$login_user_id):bool
    {
        $return = new ApiReturn();
        $db = new DB();
        $now = new DateTimeImmutable();
        try{
            $sql = "INSERT INTO login_tokens (login_user_id, token, token_id, valid_date) VALUES (:login_user_id, :token, :token_id, :valid_date)";
            $sth = $db->pdo()->prepare($sql);
            $sth->bindValue(":token_id",$jwt["token_id"]);
            $sth->bindValue(":token",$jwt["token"]);
            $sth->bindValue(":login_user_id",$login_user_id);
            $sth->bindValue(":valid_date",$now->modify($this->exp_time)->format('Y-m-d H:i:s'));
            $sth->execute();
        }catch(PDOException $e){
            $this->error_msg = $return->set_db_error($e);
            return false;
        }

        $cookie = new Cookie();
        $cookie->set($jwt["jwt_token"]->toString());

        return true;
    }
}