<?php
namespace app\action;

use Data;
use Config;

class PublicAction extends BaseAction
{


    /**
     * 登陆
     * @return array
     */
    public function login()
    {
        $data = $this->checkParam([
            'email'=>'登陆邮箱不得为空',
            'passwd'=>'密码不得为空',
            'code'=>'验证不能为空',
        ]);

        if($data['bool'] == true)
        {

        }else{
            return $this->msg(206,$data['data']);
        }
    }








}