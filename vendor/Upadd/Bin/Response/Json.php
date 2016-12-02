<?php
namespace Upadd\Bin\Response;

class Json extends Run
{

    public function execute()
    {
        return (json_encode($this->content,JSON_UNESCAPED_UNICODE));
    }


}