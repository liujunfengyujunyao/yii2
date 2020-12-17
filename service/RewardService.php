<?php
namespace app\service;
use app\models\Reward;
use yii\base\UserException;
use app\models\Category;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PHPExcel;
use PHPExcel_IOFactory;

class RewardService extends BaseService
{
    /**
     * 账单管理列表页
     * @param
     * @author JF
     * @Date 2020-12-17
     * start_time 开始时间
     * end_time 结束时间
     * status 状态
     */
    public static function IndexRewardList($supplier_id,$start_time,$end_time,$status)
    {
        if(!is_null($status)){
            $where = ['status'=>$status];
        }else{
            $where = ['!=','status',0];
        }

        $reward = Reward::find()
            ->where(['supp_id'=>$supplier_id])
            ->andWhere(['>=','add_time',$start_time])
            ->andWhere(['<=','add_time',$end_time])
            ->andWhere($where)
            ->asArray()
            ->all();
        $result = [];
        foreach($reward as $key => $value){
            $result[$key]['id'] = $value['id'];
            $result[$key]['supporder_sn'] = $value['supporder_sn'];
            $result[$key]['supp_name'] = $value['supp_name'];
            if($value['type'] == 1){//奖惩类型
                $result[$key]['type'] = "奖励";
            }else{
                $result[$key]['type'] = "惩罚";
            }
            $result[$key]['status'] = $value['status'];//状态0、已取消1、待确认2、已确认3、已完成
            $result[$key]['money'] = $value['money'];
            $result[$key]['name'] = $value['name'];//奖惩项目
            $result[$key]['reason'] = $value['reason'];//奖惩理由
            $result[$key]['add_time'] = date('Y-m-d',$value['add_time']);//奖惩时间
        }
        return success($result);
    }


    /**
     * 账单管理列表页
     * @param
     * @author JF
     * @Date 2020-12-17
     * id reward主键
     *
     */
    public static function RewardConfirm($id)
    {
        $reward = Reward::findOne($id);

        if($reward['status'] != 1){
            return fail('状态错误');
        }
        $reward->status = 2;
        if($reward->save()){
            return success('确认完成');
        }else{
            return error('网络错误');
        }
    }

}
