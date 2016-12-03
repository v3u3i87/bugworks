<?php
namespace app\action;

use Data;
use Config;
use app\dao\UserDao;

class PublicAction extends BaseAction
{

    public $verifyToken = false;


    /**
     * 登陆
     * @return array
     */
    public function login()
    {
//        $this->verifyToken = false;

        $result = $this->checkParam([
            'email'=>'登陆邮箱不得为空',
            'passwd'=>'密码不得为空',
//            'code'=>'验证不能为空',
        ]);

        if($result['bool'] == true)
        {
            $bool = UserDao::login($result['data']);
            if ($bool['bool'] == true)
            {
                return $this->msg(200, '登陆成功',$bool['data']);
            }
            return $this->msg(201,$bool['data']);
        }else{
            return $this->msg(206,$result['data']);
        }
    }









}