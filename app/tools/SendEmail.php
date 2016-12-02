<?php
namespace bin\tools;

class SendEmail
{
    /**
     * @var string 邮件传输代理用户名
     * @access private
     */
    private $_userName;
    /**
     * @var string 邮件传输代理密码
     * @access private
     */
    private $_password;
    /**
     * @var string 邮件传输代理服务器地址
     * @access protected
     */
    protected $_sendServer;
    /**
     * @var int 邮件传输代理服务器端口
     * @access protected
     */
    protected $_port = 25;

    protected $_fromName = '';
    /**
     * @var string 发件人
     * @access protected
     */
    protected $_from;
    /**
     * @var string 收件人
     * @access protected
     */
    protected $_to;
    /**
     * @var string 抄送
     * @access protected
     */
    protected $_cc;
    /**
     * @var string 秘密抄送
     * @access protected
     */
    protected $_bcc;
    /**
     * @var string 主题
     * @access protected
     */
    protected $_subject;
    /**
     * @var string 邮件正文
     * @access protected
     */
    protected $_body;
    /**
     * @var string 附件
     * @access protected
     */
    protected $_attachment;
    /**
     * @var reource socket资源
     * @access protected
     */
    protected $_socket;
    /**
     * @var string 错误信息
     * @access protected
     */
    protected $_errorMessage;


    public function __construct($config)
    {
        if (!$config['sendServer']) {
            $this->_errorMessage = '未设置发送邮件的smtp服务器';
            return false;
        }

        if (!$config['sendServerEmail']) {
            $this->_errorMessage = '未设置发送邮件的账号';
            return false;
        }

        if (!$config['sendServerEmailPassword']) {
            $this->_errorMessage = '未设置发送邮件的密码';
            return false;
        }

        if (!$config['sendServerPort']) {
            $this->_errorMessage = '未设置发送邮件的端口';
            return false;
        }

        if (!$config['fromEmail']) {
            $this->_errorMessage = '未设置发送邮件的来源邮箱';
            return false;
        }

        $this->setServer($config['sendServer'], $config['sendServerEmail'], $config['sendServerEmailPassword'], $config['sendServerPort']);
        $this->setFrom($config['fromEmail'], $config['fromEmailShowName']);
    }

    /**
     * 设置邮件传输代理，如果是可以匿名发送有邮件的服务器，只需传递代理服务器地址就行
     * @access public
     * @param string $server 代理服务器的ip或者域名
     * @param string $username 认证账号
     * @param string $password 认证密码
     * @param int $port 代理服务器的端口，smtp默认25号端口
     * @return boolean
     */
    public function setServer($server, $username = '', $password = '', $port = 25)
    {
        $this->_sendServer = $server;
        $this->_port = $port;
        if ($username) {
            $this->_userName = base64_encode($username);
        }

        if ($password) {
            $this->_password = base64_encode($password);
        }
        return true;
    }

    /**
     * 设置发件人
     * @access public
     * @param string $from 发件人地址
     * @return boolean
     */
    public function setFrom($from, $from_name = '')
    {
        $this->_fromName = $from_name;
        $this->_from = $from;
        return true;
    }


    /**
     * 设置收件人，多个收件人，连续调用多次.
     * @access public
     * @param string $to 收件人地址
     * @return boolean
     */
    public function to($to = null)
    {
        if ($this->_to) {
            if (is_string($this->_to)) {
                $this->_to = array($this->_to);
                $this->_to[] = $to;
                return true;
            } elseif (is_array($this->_to)) {
                $this->_to[] = $to;
                return true;
            } else {
                return false;
            }
        } else {
            $this->_to = $to;
            return true;
        }
    }


    /**
     * 设置抄送，多个抄送，连续调用多次.
     * @access public
     * @param string $cc 抄送地址
     * @return boolean
     */
    public function setCc($cc)
    {
        if (isset($this->_cc)) {
            if (is_string($this->_cc)) {
                $this->_cc = array($this->_cc);
                $this->_cc[] = $cc;
                return true;
            } elseif (is_array($this->_cc)) {
                $this->_cc[] = $cc;
                return true;
            } else {
                return false;
            }
        } else {
            $this->_cc = $cc;
            return true;
        }
    }

    /**
     * 设置秘密抄送，多个秘密抄送，连续调用多次
     * @access public
     * @param string $bcc 秘密抄送地址
     * @return boolean
     */
    public function setBcc($bcc)
    {
        if (isset($this->_bcc)) {
            if (is_string($this->_bcc)) {
                $this->_bcc = array($this->_bcc);
                $this->_bcc[] = $bcc;
                return true;
            } elseif (is_array($this->_bcc)) {
                $this->_bcc[] = $bcc;
                return true;
            } else {
                return false;
            }
        } else {
            $this->_bcc = $bcc;
            return true;
        }
    }


    /**
     * 设置邮件信息
     * @access public
     * @param string $title 标题
     * @param string $body 邮件主体内容，可以是纯文本，也可是是HTML文本
     * @param string $attachment 附件，文件地址
     * @return boolean
     */
    public function body($title, $body, $attachment = [])
    {
        $this->_subject = $title;
        $this->_body = base64_encode($body);
        if ($attachment) {
            $this->_attachment = $attachment;
        }
        return true;
    }


    /**
     * 发送邮件
     * @access public
     * @return boolean
     */
    public function send()
    {
        $command = $this->getCommand();
        if (false == $this->socket()) {
            return $this->error();
        }

        foreach ($command as $value) {
            if ($this->sendCommand($value[0], $value[1])) {
                continue;
            } else {
                return false;
            }
        }
        $this->close();
        //echo 'Mail OK!';
        return true;
    }

    /**
     * 返回错误信息
     * @return string
     */
    public function error()
    {
        if (!isset($this->_errorMessage)) {
            $this->_errorMessage = "";
        }
        return $this->_errorMessage;
    }

    /**
     * 返回mail命令
     * @access protected
     * @return array
     */
    protected function getCommand()
    {
        $command = array(
            array("HELO sendmail\r\n", 250)
        );

        if (!empty($this->_userName)) {
            $command[] = array("AUTH LOGIN\r\n", 334);
            $command[] = array($this->_userName . "\r\n", 334);
            $command[] = array($this->_password . "\r\n", 235);
        }
        $command[] = array("MAIL FROM:<" . $this->_from . ">\r\n", 250);
        $separator = "----=_Part_" . md5($this->_from . time()) . uniqid(); //分隔符
        //设置发件人
        $header = "FROM: " . $this->_fromName . "<" . $this->_from . ">\r\n";
        //设置收件人
        if (is_array($this->_to)) {
            $count = count($this->_to);
            for ($i = 0; $i < $count; $i++) {
                $command[] = array("RCPT TO: <" . $this->_to[$i] . ">\r\n", 250);
                if ($i == 0) {
                    $header .= "TO: <" . $this->_to[$i] . ">\r\n";
                } elseif ($i + 1 == $count) {
                    $header .= ",<" . $this->_to[$i] . ">\r\n";
                } else {
                    $header .= ",<" . $this->_to[$i] . ">\r\n";
                }
            }
        } else {
            $command[] = array("RCPT TO: <" . $this->_to . ">\r\n", 250);
            $header .= "TO: <" . $this->_to . ">\r\n";
        }


        //设置抄送
        if (isset($this->_cc)) {
            if (is_array($this->_cc)) {
                $count = count($this->_cc);
                for ($i = 0; $i < $count; $i++) {
                    $command[] = array("RCPT TO: <" . $this->_cc[$i] . ">\r\n", 250);
                    if ($i == 0) {
                        $header .= "CC: <" . $this->_cc[$i] . ">\r\n";
                    } elseif ($i + 1 == $count) {
                        $header .= ",<" . $this->_cc[$i] . ">\r\n";
                    } else {
                        $header .= ",<" . $this->_cc[$i] . ">\r\n";
                    }
                }
            } else {
                $command[] = array("RCPT TO: <" . $this->_cc . ">\r\n", 250);
                $header .= "CC: <" . $this->_cc . ">\r\n";
            }
        }

        //设置秘密抄送
        if (isset($this->_bcc)) {
            if (is_array($this->_bcc)) {
                $count = count($this->_bcc);
                for ($i = 0; $i < $count; $i++) {
                    $command[] = array("RCPT TO: <" . $this->_bcc[$i] . ">\r\n", 250);
                    if ($i == 0) {
                        $header .= "BCC: <" . $this->_bcc[$i] . ">\r\n";
                    } elseif ($i + 1 == $count) {
                        $header .= ",<" . $this->_bcc[$i] . ">\r\n";
                    } else {
                        $header .= ",<" . $this->_bcc[$i] . ">\r\n";
                    }
                }
            } else {
                $command[] = array("RCPT TO: <" . $this->_bcc . ">\r\n", 250);
                $header .= "BCC: <" . $this->_bcc . ">\r\n";
            }
        }

        $header .= "Subject: " . $this->_subject . "\r\n";

//        p($header);
        if (!empty($this->_attachment)) {
            //含有附件的邮件头需要声明成这个
            $header .= "Content-Type: multipart/mixed;\r\n";
        } elseif (false) {
            //邮件体含有图片资源的需要声明成这个
            $header .= "Content-Type: multipart/related;\r\n";
        } else {
            //html或者纯文本的邮件声明成这个
            $header .= "Content-Type: multipart/alternative;\r\n";
        }

        //邮件头分隔符
        $header .= "\t" . 'boundary="' . $separator . '"';
        $header .= "\r\nMIME-Version: 1.0\r\n";
        $header .= "\r\n--" . $separator . "\r\n";
        $header .= "Content-Type:text/html; charset=utf-8\r\n";
        $header .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $header .= $this->_body . "\r\n";
        $header .= "--" . $separator . "\r\n";

        /**
         * 加入附件
         */
        if (count($this->_attachment) >= 1) {
            $header .= $this->setAttachment($header, $separator);
        }
        $header .= "\r\n.\r\n";

        $command[] = array("DATA\r\n", 354);
        $command[] = array($header, 250);
        $command[] = array("QUIT\r\n", 221);
        return $command;
    }


    /**
     * 设置附件
     * @param string $header
     * @param $separator
     */
    protected function setAttachment($header = '', $separator)
    {
        foreach ($this->_attachment as $v) {
            $fileName = basename($v);
            $header .= "\r\n--" . $separator . "\r\n";
            $header .= "Content-Type: application/octet-stream " . '; name="' . $fileName . '"' . "\r\n";
            $header .= "Content-Transfer-Encoding: base64\r\n";
            $header .= 'Content-Disposition: attachment; filename="' . $fileName . '"' . "\r\n";
            $header .= "\r\n";
            $header .= $this->readFile($v);
            $header .= "\r\n--" . $separator . "\r\n";
        }
        return $header;
    }


    /**
     * 发送命令
     * @access protected
     * @param string $command 发送到服务器的smtp命令
     * @param int $code 期望服务器返回的响应吗
     * @return boolean
     */
    protected function sendCommand($command, $code)
    {
        //echo 'Send command:' . $command . ',expected code:' . $code . '<br />';  //打印发送内容log信息
        //发送命令给服务器
        try {
            if (socket_write($this->_socket, $command, strlen($command))) {
                //当邮件内容分多次发送时，没有$code，服务器没有返回
                if (empty($code)) {
                    return true;
                }
                //读取服务器返回
                $data = trim(socket_read($this->_socket, 1024));
                //echo 'response:' . $data . '<br /><br />';   //打印邮箱发送的stmp服务器返回的l内容og信息
                if ($data) {
                    $pattern = "/^" . $code . "/";
                    if (preg_match($pattern, $data)) {
                        return true;
                    } else {
                        $this->_errorMessage = "Error:" . $data . "|**| command:";
                        return false;
                    }
                } else {
                    $this->_errorMessage = "Error:" . socket_strerror(socket_last_error());
                    return false;
                }
            } else {
                $this->_errorMessage = "Error:" . socket_strerror(socket_last_error());
                return false;
            }
        } catch (\Exception $e) {
            $this->_errorMessage = "Error:" . $e->getMessage();
        }
    }


    /**
     * 读取附件文件内容，返回base64编码后的文件内容
     * @access protected
     * @return mixed
     */
    protected function readFile($attachment = null)
    {
        if ($attachment) {
            try {
                $file = file_get_contents($attachment);
                if (false !== $file) {
                    return base64_encode($file);
                } else {
                    return false;
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }else{
            return false;
        }

    }


    /**
     * 建立到服务器的网络连接
     * @access private
     * @return boolean
     */
    private function socket()
    {
        if (!function_exists("socket_create")) {
            $this->_errorMessage = "Extension sockets must be enabled";
            return false;
        }
        //创建socket资源
        $this->_socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));
        if (!$this->_socket) {
            $this->_errorMessage = socket_strerror(socket_last_error());
            return false;
        }
        socket_set_block($this->_socket);//设置阻塞模式
        //连接服务器
        if (!socket_connect($this->_socket, $this->_sendServer, $this->_port)) {
            $this->_errorMessage = socket_strerror(socket_last_error());
            return false;
        }
        socket_read($this->_socket, 1024);
        return true;
    }

    /**
     * 关闭socket
     * @access private
     * @return boolean
     */
    private function close()
    {
        if (isset($this->_socket) && is_object($this->_socket)) {
            $this->_socket->close();
            return true;
        }
        $this->_errorMessage = "No resource can to be close";
        return false;
    }

}



/**************************** Test ***********************************/
/**
 * $mail = new SendEmail();
 * $mail->setServer("XXXXX", "XXXXX@XXXXX", "XXXXX");
 * $mail->setFrom("XXXXX@XXXXX");
 * $mail->setReceiver("XXXXX@XXXXX");
 * //$mail->setReceiver("XXXXX@XXXXX");
 * $mail->setCc("XXXXX@XXXXX");
 * $mail->setCc("XXXXX@XXXXX");
 * $mail->setBcc("XXXXX@XXXXX");
 * $mail->setBcc("XXXXX@XXXXX");
 * $mail->setBcc("XXXXX@XXXXX");
 * $mail->setMailInfo("test", "<b>test</b>", "sms.zip");
 * $mail->sendMail();
 **/


/**
 * 邮件发送类
 * 支持发送纯文本邮件和HTML格式的邮件，可以多收件人，多抄送，多秘密抄送，带附件的邮件
 * 需要的php扩展，sockets和Fileinfo。
 * @example
 * $mail = new MySendMail();
 * $mail->setServer("XXXXX", "XXXXX@XXXXX", "XXXXX"); 设置smtp服务器
 * $mail->setFrom("XXXXX"); 设置发件人
 * $mail->setReceiver("XXXXX"); 设置收件人，多个收件人，调用多次
 * $mail->setCc("XXXX"); 设置抄送，多个抄送，调用多次
 * $mail->setBcc("XXXXX"); 设置秘密抄送，多个秘密抄送，调用多次
 * $mail->setMailInfo("test", "<b>test</b>"); 设置邮件主题、内容
 * $mail->sendMail(); 发送
 */