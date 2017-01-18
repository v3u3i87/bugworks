<?php
namespace app\dao;

use app\model\Demand;
use app\model\DemandLog;

class DemandDao extends BaseDao
{


    public static function up($data = [], $user = [])
    {
        $one = Demand::by($data['demand_id']);
        if ($one)
        {

        } else {
            Demand::add([

            ]);
        }


    }




}