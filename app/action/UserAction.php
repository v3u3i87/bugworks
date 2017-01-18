<?php
namespace app\action;

use Data;
use Config;

use app\dao\UserDao;

class UserAction extends BaseAction
{

    /**
     * 退出
     * @return array
     */
    public function logout()
    {
        $result = $this->checkParam([
            'token' => 'token不能为空..',
        ]);

        if ($result['bool'] == true) {
            $bool = UserDao::logout($result['data']);
            if ($bool['bool'] == true) {
                return $this->msg(200, '退出成功', $bool['data']);
            }
            return $this->msg(201, $bool['data']);
        }
        return $this->msg(206, $result['data']);
    }

    /**
     * 创建账号
     * @return array
     */
    public function create()
    {
        $result = $this->checkParam([
            'email' => '登陆邮箱不得为空',
            'passwd' => '密码不得为空',
            'roles_id' => '角色不能为空',
        ]);

        if ($result['bool'] == true) {
            $bool = UserDao::create($result['data']);
            if ($bool['bool'] == true) {
                return $this->msg(200, '创建成功');
            }
            return $this->msg(201, $bool['data']);
        }
        return $this->msg(206, $result['data']);
    }


    /**
     * 更新密码
     * @return array
     */
    public function change_password()
    {
        $result = $this->checkParam([
            'new_passwd' => '新密码不得为空',
            'passwd' => '久密码不得为空',
        ]);

        if ($result['bool'] == true) {
            $bool = UserDao::changePassword($result['data'],$this->userData);
            if ($bool['bool'] == true) {
                return $this->msg(200, '修改成功', $bool['data']);
            }
            return $this->msg(201, $bool['data']);
        }
        return $this->msg(206, $result['data']);
    }


}