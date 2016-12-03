<?php

Routes::get('/',function() {
    return jump('/web/index.html');
});


Routes::post('/api/v1/login','app\action\PublicAction@login');


Routes::group(array('prefix' => '/api/v1'), function ()
{

    //退出
    Routes::any('/account/logout', 'app\action\UserAction@logout');
    //创建账号
    Routes::post('/account/create', 'app\action\UserAction@create');
    //更新密码
    Routes::any('/account/change/password', 'app\action\UserAction@change_password');


    /////项目

    /////需求

    /////版本

    /////文件

    /////文档

    /////用户




});
