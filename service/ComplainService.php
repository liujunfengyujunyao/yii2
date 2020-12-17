<?php
namespace app\service;
use app\models\Reward;
use app\models\OrdersComplain;
use yii\base\UserException;
use app\models\Category;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PHPExcel;
use PHPExcel_IOFactory;

class ComplainService extends BaseService
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
    public static function OrderComplainList($supplier_id,$start_time,$end_time)
    {
        $order_complain = OrdersComplain::find()
            ->alias('t1')
            ->select('t1.*,t2.goods_name,t2.receiveqty,t2.unit,t3.send_date')
            ->leftJoin('supporder_goods t2','t2.order_id=t1.order_id')
            ->leftJoin('supporder t3','t2.supporder_id=t3.id')
            ->where(['t3.supp_id'=>$supplier_id])
            ->andWhere(['>=','t1.add_time',$start_time])
            ->andWhere(['<=','t1.add_time',$end_time])
            ->asArray()
            ->all();
        $result = [];
        foreach($order_complain as $key => $value){
            $result[$key]['id'] = $value['id'];
            $result[$key]['add_time'] = date('Y-m-d',$value['add_time']);
            $result[$key]['send_date'] = $value['send_date'];
            $result[$key]['name'] = $value['name'];
            $result[$key]['goods'] = $value['goods_name'] . "(" . $value['receiveqty'] . $value['unit'] . ")";
            $result[$key]['reason'] = $value['reason'];

        }
        return success($result);
    }


}
