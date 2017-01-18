<?php
namespace app\dao;

use Data;
use Config;

use app\model\Project;
use app\model\ProjectModule;
use app\model\ProjectUser;

class ProjectDao extends BaseDao
{


    public static function up($data = [], $user = [], $project_id = null)
    {
        $data['update_time'] = time();
        if ($data['project_id'] === 'y') {
            unset($data['project_id']);
            $show = Project::one(['name' => $data['name']]);
            if (empty($show)) {
                $data['type'] = 2;
                $data['token'] = sha1((lode(',', $data) . time() . mt_rand(9, 999)));
                $data['uid'] = $user['uid'];
                $data['status'] = 1;
                $data['create_time'] = time();
                $project_id = Project::add($data);
                if ($project_id) {
                    self::addUser($project_id, $user['uid']);
                    return self::success(['project_id' => $project_id]);
                }
            } else {
                return self::error('项目名称已存在..');
            }
            return self::error('项目创建失败');
        } else {
            $project_id = (int)$data['project_id'];
            if (Project::one(['project_id' => $project_id, 'uid' => $user['uid']])) {
                $up = Project::update([
                    'name' => $data['name'],
                    'info' => $data['info'],
                    'icon' => $data['icon'],
                ], ['project_id' => $project_id]);
                if ($up) {
                    return self::success(['flag' => 1]);
                }
            } else {
                return self::error('抱歉,您不是项目创建人..');
            }
        }
        return self::error('编辑失败');
    }


    /**
     * 添加用户到项目
     * @param $project_id
     * @param $uid
     * @return mixed
     */
    public static function addUser($project_id, $uid)
    {
        return ProjectUser::add([
            'uid' => $uid,
            'project_id' => $project_id,
            'update_time' => time(),
            'create_time' => time()
        ]);
    }


    /**
     * 获取项目成员列表
     * @param $project_id
     * @return array
     */
    public static function getUserList($project_id = null)
    {
        $show = Project::one(['id' => $project_id, 'status' => 1]);
        if ($show) {
            $data = ProjectUser::where(['project_id' => $show['id'], 'status' => 1])->get();
            if ($data)
            {
                $tmp['info'] = $show;
                $tmp['list'] = $data;
                return self::success($tmp);
            } else {
                return self::error('该项目,还没有任何成员..');
            }
        }
        return self::error('没有该项目..');
    }


    /**
     * 判断用户是否为该项目
     * @param null $project_id
     * @param null $uid
     * @return bool
     */
    public static function byUser($project_id = null, $uid = null)
    {
        if (ProjectUser::one(['project_id' => $project_id, 'uid' => $uid]))
        {
            return true;
        } else {
            return false;
        }
    }


}