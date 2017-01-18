<?php
namespace app\action;

use app\model\Project;
use Data;
use Config;
use app\dao\ProjectDao;

class ProjectAction extends BaseAction
{

    public function up()
    {
        $result = $this->checkParam([
            'name' => '项目名称不得为空..',
            'info' => '一个项目应该需要一个好的描述..',
            'project_id' => '项目ID参数必须提交..',
        ]);

        if ($result['bool'] == true) {
            $bool = ProjectDao::up($result['data'], $this->userData);
            if ($bool['bool'] == true) {
                if (isset($bool['data']['flag']) == 1) {
                    return $this->msg(200, '更新成功', $bool['data']);
                } else {
                    return $this->msg(200, '创建成功', $bool['data']);
                }
            }
            return $this->msg(201, $bool['data']);
        }
        return $this->msg(206, $result['data']);
    }


    /**
     * 获取项目成员
     * @return array|string
     */
    public function getUserList()
    {
        $result = $this->checkParam([
            'project_id' => '项目ID参数必须提交..',
        ]);
        $project_id = $result['data']['project_id'];
        $status = ProjectDao::byUser($project_id, $this->userData['uid']);
        if ($status === false)
        {
            return $this->msg(203, '抱歉,您不是该项目成员.');
        }

        if ($result['bool'] == true)
        {
            $bool = ProjectDao::getUserList($project_id);
            if ($bool['bool'] == true) {
                return $this->msg(200, 'ok', $bool['data']);
            }
            return $this->msg(201, $bool['data']);
        }
        return $this->msg(206, $result['data']);
    }


    public function addUser()
    {
        $result = $this->checkParam([
            'project_id' => '项目ID参数必须提交..',
            'guid'=>'必须提交用户guid',
        ]);
        $project_id = $result['data']['project_id'];
        $status = ProjectDao::byUser($project_id, $this->userData['uid']);
        if ($status === false)
        {
            return $this->msg(203, '抱歉,您不是该项目成员.');
        }

        if ($result['bool'] == true)
        {
            $bool = ProjectDao::getUserList($project_id);
            if ($bool['bool'] == true) {
                return $this->msg(200, 'ok', $bool['data']);
            }
            return $this->msg(201, $bool['data']);
        }
        return $this->msg(206, $result['data']);
    }


}