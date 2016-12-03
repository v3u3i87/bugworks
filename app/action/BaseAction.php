<?php
namespace app\action;

use Data;
use Config;
use Upadd\Bin\UpaddException;
use app\dao\UserDao;


class BaseAction extends \Upadd\Frame\Action
{

    /**
     * 判断是否验证token
     * @var bool
     */
    public $verifyToken = true;

    /**
     * 用户资料
     * @var array
     */
    public $userData = [];

    public function __construct()
    {
        if ($this->verifyToken === true)
        {
            $verify = $this->verifyToken();
            if ($verify !== true)
            {
                throw new UpaddException(json($verify));
            }
        }
    }


    /**
     * 验证TOKEN
     * @return array
     */
    public function verifyToken()
    {
        $token = Data::get('token');
        if ($token) {
            $bool = UserDao::is_token($token);
            if ($bool['bool'] == false) {
                return $this->msg(208, 'token失效..');
            } else {
                $this->userData = $bool['data'];
            }
            return true;
        } else {
            return $this->msg(204, '非法请求..');
        }
    }

    /**
     * 返回信息
     * @param int $code
     * @param string $msg
     * @param array $data
     */
    public function msg($code = 204, $msg = 'efault error', $data = array())
    {
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    /**
     * 检查必须参数
     * @param $checkContent = []
     * @param $data = []
     * @return array
     */
    public function checkParam($checkContent = [])
    {
        return $this->setException(function () use ($checkContent) {
            $result = $this->getData();
            if ($result['bool'] == false) {
                return $this->errorParam($result['data']);
            }
            $request = $result['data'];
            $list = [];
            foreach ($checkContent as $k => $v) {
                if (isset($request[$k])) {
                    $list[$k] = $request[$k];
                    if ($list[$k] == null) {
                        return $this->errorParam($v);
                    }
                } else {
                    return $this->errorParam($v);
                }
            }
            return $this->successParam($list);
        });
    }

    /**
     * 判断错误
     * @param string $msg
     * @return array
     */
    public function errorParam($msg = '')
    {
        return ['bool' => false, 'data' => $msg];
    }


    /**
     * 返回成功
     * @param array $data
     * @return array
     */
    public function successParam($data = [])
    {
        return ['bool' => true, 'data' => $data];
    }

    /**
     * retrieve data
     * @return bool|\Exception
     */
    protected function getData()
    {
        return $this->setException(function ()
        {
            $data = Data::get('stream');
            if ($data) {
                //Whether complex decryption ?
                $data = json(base64_decode($data));
                //if the is json in error
                $jsonError = json_error();
                if ($jsonError['bool'] == true) {
                    return ['bool' => true, 'data' => $data];
                } else {
                    return ['bool' => false, 'data' => $jsonError['msg']];
                }
            }
            return ['bool' => false, 'data' => 'Data must not be empty'];
        });
    }

    /**
     * 异常处理
     * @param null $fun
     * @param string $type
     * @return bool|\Exception
     */
    protected function setException($fun = null, $type = '')
    {
        try {
            if (is_callable($fun)) {
                return $fun();
            } else {
                return false;
            }
        } catch (\Exception $e) {
            return $e;
        }
    }


}