<?php
namespace app\action;

use Data;
use Config;

use app\dao\ProjectDao;
use app\dao\DemandDao;


class DemandAction extends BaseAction
{


    /**
     * 编辑数据
     */
    public function edit()
    {
        $result = $this->checkParam([
            'project_id' => '项目ID参数必须提交..',
            'title' => '标题必须要填写.',
        ]);
        if ($result['bool'] == false)
        {
            return $this->msg(206, $result['data']);
        }
        $project_id = $result['data']['project_id'];
        $status = ProjectDao::byUser($project_id, $this->userData['uid']);
        if ($status === false)
        {
            return $this->msg(203, '抱歉,您不是该项目成员.');
        }
        DemandDao::up($result['data'],$this->userData);
    }


}