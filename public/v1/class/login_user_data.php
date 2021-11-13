<?php

use DB\DB;

class UserData
{
    public array $return;
    public int $code = 200;
    public bool $is_error = false;
    
    private string $user_id;
    private $db;

    function __construct($user_id)
    {
        $this->user_id = $user_id;
        $this->db = new DB();
        if(!$this->get_user_data()) return false;
        if(!$this->add_user_authority()) return false;
        if(!$this->add_user_gate_place()) return false;
        $this->return = $this->user_data;
        return true;
    }

    private array $user_data;
    public function data()
    {
        return $this->user_data;
    }

    public function get_login_user($user_id = $this->user_id)
    {
        try{
            $sql = "SELECT lu.login_user_id,lu.login_user_name,la.auth_name,lg.gate_id FROM login_users as lu LEFT JOIN login_auth_group as la USING(auth_group) LEFT JOIN login_range_gate as lg USING(login_user_id) WHERE user_id = :user_id";
            $sth = $this->db->pdo->prepare($sql);
            $sth->bindValue(":user_id",$user_id);
            $sth->execute();
        }catch(PDOException $e){
            $this->code = 500;
            $this->return = fatal_error($e);
            return false;
        }

        $user_data = $sth->fetch(PDO::FETCH_ASSOC);
        if($user_data===false){
            $this->code = 400;
            $this->return = ["ok"=>false,"re"=>false,"error" => [
                "type" => "not_in_user",
                "msg" => "not exist user in this id"
            ]];
            return false;
        }else{
            $this->user_data = $user_data;
            return true;
        }
    }

    private function add_user_authority()
    {
        try{
            $sql = "SELECT authority_name FROM login_authority WHERE authority_group = :authority_group";
            $sth = $this->db->pdo->prepare($sql);
            $sth->bindValue(":authority_group",$this->user_data["authority_group"]);
            $sth->execute();
        }catch(PDOException $e){
            $this->code = 500;
            $this->return = fatal_error($e);
            return false;
        }
        $this->user_data["authority"] = array_reduce($sth->fetchALl(PDO::FETCH_ASSOC), function($carry, $item){
            $carry[] = $item["authority_name"];
            return $carry;
        }, []);
        return true;
    }

    private function add_user_gate_place()
    {
        try{
            $sql = "SELECT gate_id FROM login_gate_place WHERE user_id = :user_id";
            $sth = $this->db->pdo->prepare($sql);
            $sth->bindValue(":user_id",$this->user_data["user_id"]);
            $sth->execute();
        }catch(PDOException $e){
            $this->code = 500;
            $this->return = fatal_error($e);
            return false;
        }
        $this->user_data["place"] = array_reduce($sth->fetchALl(PDO::FETCH_ASSOC), function($carry, $item){
            $carry[] = $item["gate_id"];
            return $carry;
        }, []);
        return true;
    }

}