<?php
namespace app\dao;


use app\model\UserAccount as account;
use app\model\UserInfo;
use app\model\UserLog;
use app\model\UserToken;
use app\tools\GuidBuilder;
use app\tools\Help;


class UserDao extends BaseDao
{

    /**
     * 验证TOKEN
     * @param null $token
     */
    public static function is_token($token = null)
    {
        $v = account::one(['access_token' => $token]);
        if ($v) {
            return self::success($v);
        }
        return self::error('TOKEN错误..');
    }


    /**
     * 创建账号
     * @param array $data
     * @return bool
     */
    public static function create($data = [])
    {

        if (account::where(['email' => $data['email']])->find()) {
            return self::error('邮箱已存在');
        }
        $addData['guid'] = GuidBuilder::getGuid();
        if (account::where(['guid' => $addData['guid']])->find()) {
            return self::error('该账户唯一码已存在');
        }

        $addData['passwd_random'] = Help::random();
        $addData['passwd'] = password_hash($data['passwd'] . $addData['passwd_random'], PASSWORD_DEFAULT);
        $addData['email'] = $data['email'];
        $addData['roles_id'] = $data['roles_id'];
        $addData['guid_md5'] = md5($addData['guid']);
        $addData['status'] = 1;
        $addData['update_time'] = time();
        $addData['create_time'] = time();
        if (account::add($addData)) {
            return self::success();
        }
        return self::error();
    }


    /**
     * login
     * @param array $data
     * @return array
     */
    public static function login($data = [])
    {
        $find = account::one(['email' => $data['email']]);

        if (empty($find)) {
            return self::error('抱歉,没有该用户');
        }

        if ($find['status'] == 2) {
            return self::error('账户已禁用');
        }

        $key = $data['passwd'] . $find['passwd_random'];
        $verify = password_verify($key, $find['passwd']);
        if ($verify == false || $verify == FALSE) {
            return self::error('密码错误');
        }

        $time = time();
        $getClient_id = getClient_id();
        $token_key = md5($getClient_id . $key . $time . mt_rand(9, 99999) . $find['guid']);
        $token = sha1($token_key . $time);

        $upData = [
            'access_token' => $token,
            'access_token_key' => $token_key,
            'login_ip' => $getClient_id,
            'login_count' => ($find['login_count'] + 1),
            'login_time' => $time,
        ];

        $upAcc = account::update($upData, ['uid' => $find['uid']]);
        UserToken::add([
            'uid' => $find['uid'],
            'account' => $data['email'],
            'token' => $token,
            'token_key' => $token_key,
            'login_time' => $time,
            'client' => 1,
            'login_ip'=>$getClient_id
        ]);

        if ($upAcc) {
            return self::success([
                'token' => $token,
                'login_time' => $time
            ]);
        }
        return self::error('登陆失败');
    }

    /**
     * 退出
     * @param array $data
     * @return array
     */
    public static function logout($data = [])
    {
        $v = account::one(['access_token' => $data['token']]);
        if ($v) {
            $up = account::update(['access_token' => null, 'access_token_key' => null], ['uid' => $v['uid']]);
            if ($up) {
                return self::success(['flag' => 1]);
            }
            return self::error('服务器繁忙退出失败..');
        }
        return self::error('token错误..');
    }

    /**
     * 修改密码
     * @param array $data
     * @return array
     */
    public static function changePassword($data = [], $userData = [])
    {
        $newPasswd = $data['new_passwd'];
        $passwd = $data['passwd'];
        if ($newPasswd == $passwd) {
            return self::error('抱歉,新旧密码一致.');
        }

        $upData['passwd_random'] = Help::random();
        $upData['passwd'] = password_hash($newPasswd . $upData['passwd_random'], PASSWORD_DEFAULT);
        if (account::update($upData, ['uid' => $userData['uid']])) {
            return self::success(['flag' => 1]);
        } else {
            return self::error('修改失败,服务器繁忙.');
        }

    }

}