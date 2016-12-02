<?php
namespace bin\tools;

use Config;
use Data;

class Jpush
{

    /**
     * 错误类型
     * @var array
     */
    private static $errorType = [
        1000 => ['code' => 0, 'msg' => '系统内部错误'],
        1001 => ['code' => 0, 'msg' => '只支持 HTTP Post 方法'],
        1002 => array('code' => 0, 'msg' => '缺少了必须的参数'),
        1003 => array('code' => 0, 'msg' => '参数值不合法'),
        1004 => array('code' => 0, 'msg' => '验证失败'),
        1005 => array('code' => 0, 'msg' => '消息体太大'),
        1008 => array('code' => 0, 'msg' => 'app_key参数非法'),
        1011 => array('code' => 0, 'msg' => '没有满足条件的推送目标'),
        1020 => array('code' => 0, 'msg' => '只支持 HTTPS 请求'),
        1030 => array('code' => 0, 'msg' => '内部服务超时')
    ];

    /**
     * 推送的Curl方法
     * @param string $param
     * @return boolean|mixed
     */
    private static function request($param = null)
    {
        if (empty($param)) {
            return false;
        }
        //设置POST接口
        $config = self::getConfig();
        //设置头信息
        $header = array("Authorization:Basic " . self::setBase64(), "Content-Type:application/json");
        //提交参数
        $curlPost = $param;
        //开始执行
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $config['url']);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * 获取配置信息
     * @return mixed
     */
    private static function getConfig()
    {
        return Config::get('tag@jiguang');
    }

    /**
     * 数据编码
     */
    private static function setBase64()
    {
        $config = self::getConfig();
        return base64_encode("{$config['AppKey']}:{$config['MasterSecret']}");
    }


    /**
     * 判断错误码
     * @param string $code
     * @return multitype:number string
     */
    private static function getError($code = null)
    {
        if ($code) {
            return self::$errorType[$code];
        }
    }

    /**
     * 推送
     * @param $title 标题,ios下失效
     * @param null $text 推送内容
     * @param null $user 推送用户 crm_id
     * @param null $param 推送参数:type,val,msg_id 自动插入
     * @return array
     */
    public static function send($title, $text = null, $user = null, $param = null, $key = null)
    {
        $json = self::setData($title, $text, $user, $param, $key);
        $request = self::request($json);
        $msg = json_decode($request, true);
        //判断返回类型
        if (isset($msg['sendno']) && !empty($msg['msg_id'])) {
            //发送类型插入数据记录
            return array('code' => 1, 'msg' => 'ok');
        } else {
            //判断错误类型
            if (isset($msg['error']['code']) && !empty($msg['error']['code']))
            {
                return array('code' => 0, 'msg' => self::getError($msg['error']['code']));
            } else {
                return array('code' => 0, 'msg' => 'server error');
            }
        }
    }


    /**
     * 设置参数
     * @param $title 标题,ios下失效
     * @param null $text 推送内容
     * @param null $user 推送用户 uid
     * @param null $param 推送参数:type,val,msg_id 自动插入
     * @return array
     */
    private static function setData($title = null, $text = null, $user = null, $data = null, $key = null)
    {
        $config = self::getConfig();
        $setData['platform'] = 'all';
        if (empty($user))
        {
            $setData['audience'] = 'all';
        } else {
            $setData['audience'] = ['alias' => $user];
        }

        $notification['alert'] = $title;
        //安卓
        $notification['android'] = [
            //通知标题
            'title' => $title,
            //通知内容
            'alert' => $text,
            'extras' => $data,
        ];

        //苹果
        $notification['ios'] = [
            'alert' => $text,
            'extras' => $data
        ];

        $setData['notification'] = $notification;
        //推送环境 True 表示推送生产环境，False 表示要推送开发环境
        $setData['options']['apns_production'] = $config['is_env'];
        return json_encode($setData);
    }

}