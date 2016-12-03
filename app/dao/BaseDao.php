<?php
namespace app\dao;

use Config;

class BaseDao
{

    /**
     * 检查必须参数
     * @param $checkContent = []
     * @param $data = []
     * @return array
     */
    public function checkParam($checkContent = [])
    {
        return $this->setException(function () use($checkContent)
        {
            $result = $this->getData();
            if ($result['bool'] == false)
            {
                return $this->errorParam($result['data']);
            }
            $request = $result['data'];
            $list = [];
            foreach ($checkContent as $k => $v)
            {
                if(isset($request[$k])) {
                    $list[$k] = $request[$k];
                    if ($list[$k] == null) {
                        return $this->errorParam($v);
                    }
                }else{
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
    public static function error($msg = '')
    {
        return ['bool' => false, 'data' => $msg];
    }


    /**
     * 返回成功
     * @param array $data
     * @return array
     */
    public static function success($data = [])
    {
        return ['bool' => true, 'data' => $data];
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