<?php
namespace Upadd\Bin;

use Upadd\Bin\Config\Configuration;
use Upadd\Bin\Tool\Log;
use Upadd\Bin\Response\Run as ResponseRun;
use Upadd\Bin\Package\Data;

class Application
{

    /**
     * 配置文件
     * @var array
     */
    public static $_config = [];

    /**
     * 初始化组件对象
     * @var array
     */
    public $_work = [];

    /**
     * 响应数据
     * @var
     */
    public $_responseData;


    /**
     * 请求数据
     * @var array
     */
    public $requestData = [];


    /**
     * 运行
     */
    public function run($callable, $argv = [])
    {
        $this->requestData = $this->requestData();

        //日志
        $this->setRequestLog();

        // 设置时区
        date_default_timezone_set('Asia/Shanghai');

        /**
         * 实例化对象
         */
        $this->request()->getInit($this->_work, $argv,$this->requestData);

        /**
         * 判断运行环境
         */
        if (is_run_evn()) {
            if (is_callable($callable)) {
                call_user_func_array($callable, func_get_args());
            }

            $this->_responseData = $this->request()->run_cgi();
        } else {
            $this->_responseData = $this->request()->run_cli();
        }

        /**
         * 发送响应数据
         */
        $_responseRun = new ResponseRun($this->_responseData, $this->request()->_responseType);
        $_responseRun->send();
    }


    /**
     * 请求日志
     * @pamer
     */
    private function setRequestLog()
    {
        $_requestData = $this->requestData;
        $body = 'Run Start' . "\n";
        $body .= 'Host:' . $_requestData['host'] . "\n";
        $body .= 'Url:' . $_requestData['url'] . "\n";
        $body .= 'Ip:' . $_requestData['client_ip'] . "\n";
        $body .= 'Method:' . $_requestData['method'] . "\n";
        $body .= 'Header:' . $_requestData['header'] . "\n";
        $body .= 'Parameter:' . $_requestData['request'] . "\n";
        Log::run($body);
    }

    /**
     * 请求数据
     */
    protected function requestData()
    {
        $url = 'cli';
        if (is_run_evn()) {
            if (isset($_SERVER ['REQUEST_URI'])) {
                $url = $_SERVER ['REQUEST_URI'];
            }
        }
        $requestData = Data::all();
        if (is_array($requestData)) {
            $requestData = json($requestData);
        }

        $head = getHeader();
        if ($head) {
            $head = json($head);
        }

        return [
            'header' => $head,
            'host' => (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''),
            'url' => $url,
            'client_ip' => getClient_id(),
            'method' => (isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : 'cli'),
            'request' => $requestData,
            'request_time' => time(),
        ];
    }

    /**
     * 获取请求对象
     * @return mixed
     */
    public function request()
    {
        return $this->_work['Request'];
    }


    /**
     * 实例化全局工作模块
     * @param $work
     */
    public function getWorkModule()
    {
        return ($this->_work = [
            'GetConfiguration' => new \Upadd\Bin\Config\GetConfiguration,
            'Request' => new \Upadd\Bin\Http\Request,
            'Route' => new \Upadd\Bin\Http\Route,
            'getSession' => \Upadd\Bin\Session\getSession::init(),
            'Log' => new \Upadd\Bin\Tool\Log,
            'Data' => new \Upadd\Bin\Http\Data,
            'Cache' => new \Upadd\Bin\Cache,
//            'Async' => new \Upadd\Bin\Async,
        ]);
    }


    /**
     * 获取配置文件
     */
    public function getConfig()
    {
        return (static::$_config = $this->getConfiguration()->getConfigLoad());
    }

    /**
     * 实例化全局配置文件
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return ($this->_work['Configuration'] = new Configuration());
    }

    /**
     * 获取别名
     * @return \Upadd\Bin\Alias
     * @throws \Upadd\Bin\UpaddException
     */
    public function getAlias()
    {
        return (new Alias(static::$_config));
    }


    /**
     * 获取Session配置状态
     * @return mixed
     */
    private function getSessionStatus()
    {
        return static::$_config['sys']['is_session'];
    }

    /**
     * 设置 session
     * @return bool
     */
    public function setSession()
    {
        if (is_run_evn()) {
            if ($this->getSessionStatus()) {
                $config = static::$_config['sys']['session'];
                if ($config['domain']) {
                    ini_set('session.cookie_domain', $config['domain']);
                }
                if ($config['expire']) {
                    ini_set('session.gc_maxlifetime', $config['expire']);
                    ini_set('session.cookie_lifetime', $config['expire']);
                }
                if ($config['use_cookies']) {
                    ini_set('session.use_cookies', $config['use_cookies'] ? 1 : 0);
                }
                if ($config['cache_limiter']) {
                    session_cache_limiter($config['cache_limiter']);
                }
                if ($config['cache_expire']) {
                    session_cache_expire($config['cache_expire']);
                }

                $seeion = new \Upadd\Bin\Session\SessionFile();
                session_set_save_handler(
                    array($seeion, 'open'),
                    array($seeion, 'close'),
                    array($seeion, 'read'),
                    array($seeion, 'write'),
                    array($seeion, 'destroy'),
                    array($seeion, 'gc')
                );
                session_start();
            }
        }
    }

}