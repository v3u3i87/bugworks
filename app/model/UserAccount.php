<?php

namespace app\model;

use Model;

class UserAccount extends Model{

    protected $_table = 'user_account';

    protected $_primaryKey = 'id';


    /**
     * 查询一条
     * @param $so
     * @return mixed
     */
    public static function one($so)
    {
        return self::where($so)->find();
    }


}