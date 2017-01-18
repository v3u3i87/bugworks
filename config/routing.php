<?php

Routes::get('/',function() {
    return jump('/web/src/index.html');
});


Routes::post('/api/v1/login','app\action\PublicAction@login');

Routes::group(array('prefix' => '/api/v1'), function ()
{

    /////项目 Project
    Routes::post('/project/up', 'app\action\ProjectAction@up');
    //项目成员列表
    Routes::post('/project/user/list', 'app\action\ProjectAction@getUserList');
    /////需求 Demand
    Routes::post('/project/demand/edit', 'app\action\DemandAction@edit');


    /////版本 version_id

    /////文件 file_if

    /////文档 docer

    /////用户
    //退出
    Routes::any('/account/logout', 'app\action\UserAction@logout');
    //创建账号
    Routes::post('/account/create', 'app\action\UserAction@create');
    //更新密码
    Routes::post('/account/change/password', 'app\action\UserAction@change_password');

});
