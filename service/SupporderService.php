<?php
namespace app\service;
use app\models\Cart;
use app\models\Favorite;
use app\models\Member;
use app\models\Goods;
use app\models\Orders;
use app\models\OrdersGoods;
use app\models\PickseatGotime;
use app\models\Supplier;
use app\models\Supporder;
use app\models\SupporderGoods;
use yii\base\UserException;
use app\models\Category;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PHPExcel;
use PHPExcel_IOFactory;

class SupporderService extends BaseService
{
    /**
     * 账单管理列表页
     * @param
     * @author JF
     * @Date 2020-12-15
     * start_time 开始时间
     * end_time 结束时间
     * audit_status 审核状态
     */
    public static function IndexOrderList($supplier_id,$start_time,$end_time,$audit_status)
    {
        if(!is_null($audit_status)){
            $where = ['audit_status'=>$audit_status];
        }else{
            $where = "1=1";
        }
        $supporder = Supporder::find()
            ->where(['supp_id'=>$supplier_id])
            ->andWhere(['status'=>1])
            ->andWhere(['>=','send_date',$start_time])
            ->andWhere(['<=','send_date',$end_time])
            ->andWhere($where)
            ->asArray()
            ->all();
        $result = [];


        foreach($supporder as $key => $value){
            $result[$key]['id'] = $value['id'];
            $result[$key]['all_audit_amount'] = 0;
            $result[$key]['supporder_sn'] = $value['supporder_sn'];//采购单号
            $result[$key]['audit_status'] = $value['audit_status'];//0未确认 1已确认
            $result[$key]['info'] = SupporderGoods::find()
                ->select('pickseat_name,goods_name,spec,unit,needqty,sendqty,receiveqty,supp_price2,audit_price')
                ->where(['supporder_id'=>$value['id']])
                ->andWhere(['status'=>1])
                ->asArray()
                ->all();
            foreach($result[$key]['info'] as $k => &$v){
                $v['audit_amount'] = $v['audit_price'] * $v['receiveqty'];
                $result[$key]['all_audit_amount'] += $v['audit_amount'];
            }
        }
        return success($result);
    }

    /**
     * 确认账单
     * @param
     * @author JF
     * @Date 2020-12-15
     *
     */
    public static function OrderConfirm($id)
    {
        $supporder = SupporderGoods::find()
            ->where(['supporder_id'=>$id])
            ->asArray()
            ->all();
        foreach($supporder as $key => $value){
            if($value['audit_status'] == 0){
                return fail('存在未审核订单');
            }
            if($value['receiveqty'] <= 0){
                return fail('存在未收货订单');
            }
        }

        $table = Supporder::findOne($id);
        $table->audit_status = 1;
        $table->audit_time = time();
        $table->save();
        if(!$table->save()){
            throw new \yii\base\UserException('操作失败,'.join($table->getFirstErrors()), 400);
        }
        return success('状态已更新');
    }


}
