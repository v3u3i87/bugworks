<?php

namespace app\model;

use Model;

class Project extends Model{

    protected $_table = 'project';

    protected $_primaryKey = 'id';


    /**
     * 查询一条
     * @param null $so
     * @return mixed
     */
    public static function one($so=null)
    {
        return self::where($so)->find();
    }

}