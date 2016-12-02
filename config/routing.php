<?php

Routes::get('/',function() {
    return jump('/web/index.html');
});


Routes::post('/api/v1/login','app\action\PublicAction@login');


Routes::group(array('prefix' => '/api/v1/'), function ()
{

    //退出
    Routes::any('/logout', 'app\action\UserAction@logout');

    /////项目

    /////需求

    /////版本

    /////文件

    /////文档

    /////用户




});
