<?php
namespace bin\verify;

class Help{


    /**
     * 验证邮箱
     * @param $email
     * @return bool
     */
    public static function is_email($email)
    {
        $reg = '/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/';
        if(preg_match($reg,$email))
        {
            return true;
        }
        return false;
    }

    /**
     * 验证密码长度
     * @param $passwd
     * @return bool
     */
    public static function is_passwd_strlen($passwd)
    {
        if(strlen($passwd) >= 6 && strlen($passwd) <= 26)
        {
            return true;
        }
        return false;
    }


    /**
     * 验证手机号码
     * @param null $mobile
     * @return bool
     */
    public static function is_mobile($mobile=null)
    {
        if (!is_numeric($mobile))
        {
            return false;
        }
        return (preg_match('#^13[\d]{9}$|^14[4,5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false);
    }





}