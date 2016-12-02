<?php
namespace bin\tools;

class JsonM
{
    /**
     *  使用特定function对数组中所有元素做处理 递归
     * @param unknown $array
     * @param unknown $function
     * @param string $apply_to_keys_also
     */
    private static function arrJson(&$array, $apply_to_keys_also = false)
    {
        static $recursive_counter = 0;
        foreach ($array as $key => $value)
        {
            if (is_array($value))
            {
                static::arrJson($array[$key], $apply_to_keys_also);
            } else {
                $array[$key] = urlencode($value);
            }

            if ($apply_to_keys_also && is_string($key))
            {
                $new_key = urlencode($key);
                if ($new_key != $key)
                {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
        $recursive_counter--;
    }

    /**
     * 将数组转换为JSON字符串（in gbk2312-gbk-utf-8）
     * @param    array $array 要转换的数组
     * @return   string        转换得到的json字符串
     * @return   json to string in array
     */
    public static function setJson($array)
    {
        self::arrJson($array, true);
        $json = json_encode($array);
        return urldecode($json);
    }

}