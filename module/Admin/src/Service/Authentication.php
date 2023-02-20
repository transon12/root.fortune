<?php
namespace Admin\Service;

use RuntimeException;
use Zend\Db\Adapter\Adapter;

class Authentication{
    
    private static $id           = 0;
    private static $companyId    = '';
    private static $firstname    = '';
    private static $lastname     = '';
    private static $username     = '';

    public function __construct($userCurrent){
        self::$id           = isset($userCurrent["id"]) ? $userCurrent["id"] : "";
        self::$companyId    = isset($userCurrent["company_id"]) ? $userCurrent["company_id"] : "";
        self::$firstname    = isset($userCurrent["firstname"]) ? $userCurrent["firstname"] : "";
        self::$lastname     = isset($userCurrent["lastname"]) ? $userCurrent["lastname"] : "";
        self::$username     = isset($userCurrent["username"]) ? $userCurrent["username"] : "";
    }

    public static function getUser(){
        return array(
            "id" => self::$id,
            "company_id" => self::$companyId,
            "firstname" => self::$firstname,
            "lastname" => self::$lastname,
            "username" => self::$username,
        );
    }

    public static function getId(){
        return self::$id;
    }

    public static function getCompanyId(){
        return self::$companyId;
    }

    public static function getFirstname(){
        return self::$firstname;
    }

    public static function getLastname(){
        return self::$lastname;
    }

    public static function getUsername(){
        return self::$username;
    }
}