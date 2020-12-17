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
/**
 * 供货商配货单逻辑层.
 * User: Administrator
 * Date: 2020/11/03
 * Time: 9:31
 */
class SupplierService extends BaseService
{
    /**
     * 供应商首页显示内容
     * @param
     * @author JF
     * @Date 2020-11-03
     * $supplier_id 供应商ID
     * $result['send_date'] : 送货日期
     * $result['order_count'] : 今日订单总数
     * DistributionSchedule 配货进度
     */
    public static function IndexOrderList($supplier_id,$send_date)
    {
        /*   用于测试数据  上线删除   */
        $supplier_id = 115;
        $send_date = '2020-12-02';
        /*   用于测试数据  上线删除   */
        $supplier = Supplier::findOne($supplier_id);
        $result['send_date'] = $send_date;
        $supporder = Supporder::find()
            ->where(['supp_id'=>$supplier_id])
            ->andWhere(['send_date'=>$send_date])
            ->andWhere(['status'=>1])
            ->asArray()
            ->all();
        $supporder_ids = array_column($supporder, 'id');
        $result['pick'] = static::DistributionSchedule($supporder_ids);
        $result['price'] = static::PriceSchedule($supporder_ids);
        $result['order_count'] = count($supporder);
        return success($result);
    }

    /**
     * 订单配货列表
     * @param
     * @author JF
     * @Date 2020-11-04
     * $supplier_id 供应商ID
     * $result['send_date'] : 送货日期
     */
    public static function OrderPickList($supplier_id,$send_date)
    {
        /*   用于测试数据  上线删除   */
        $supplier_id = 115;
        $send_date = '2020-12-02';
        /*   用于测试数据  上线删除   */
        $supporder = Supporder::find()
            ->where(['supp_id'=>$supplier_id])
            ->andWhere(['send_date'=>$send_date])
            ->andWhere(['status'=>1])
            ->andWhere(['audit_status'=>0])//未审核才能配货
            ->asArray()
            ->all();
        foreach($supporder as $key =>$value){
            $result[$key]['id'] = $value['id'];
            $result[$key]['order_sn'] = $value['supporder_sn'];
            $result[$key]['goods_count'] = SupporderGoods::find()
                ->where(['status'=>1])
                ->andWhere(['supporder_id'=>$value['id']])
                ->sum('needqty');
            $result[$key]['send_time'] = $value['send_date'];
            $result[$key]['gotime'] = $value['gotime'];
        }
        return success($result);
    }

    /**
     * 配货商品列表
     * @param
     * @author JF
     * @Date 2020-11-04
     * $id supporder_id
     * @supp_pick_status:0、未配货1、已配货2、缺货
     */
    public static function SupporderGoodsPick($id)
    {
       $result = SupporderGoods::find()
           ->select('id as supporder_goods_id,pickseat_name,goods_name,spec,needqty,unit,supp_pick_status')
           ->where(['status'=>1])
           ->where(['supporder_id'=>$id])
           ->asArray()
           ->all();
        foreach($result as $key =>&$value){
            $value['needqty'] = $value['needqty'] . $value['unit'];
            unset($value['unit']);
        }
        return success($result);
    }

    /**
     * 修改配货状态
     * @param
     * @author JF
     * @Date 2020-11-04
     * $supporder_goods_id supporder_goods主键
     *
     */
    public static function UpdateSupporderGoods($supporder_goods_id,$status)
    {
        if($status == 1){
            $supp_pick_time = time();
        }else{
            $supp_pick_time = null;
        }
        $table = SupporderGoods::findOne($supporder_goods_id);
        $table->supp_pick_status = $status;
        $table->supp_pick_time = $supp_pick_time;
        $table->save();
        if(!$table->save()){
            throw new \yii\base\UserException('添加失败,'.join($table->getFirstErrors()), 400);
        }
        return success('状态已更新');
    }

    /**
     * 配货统计列表页
     * @param
     * @author JF
     * @Date 2020-11-05
     * $send_time 送货时间
     *
     */
    public static function SuppOrderStatistics($supplier_id,$send_date,$gotime_id='')
    {
        if(!empty($gotime_id)){
            $where = ['gotime_id'=>$gotime_id];
        }else{
            $where = "1=1";
        }
        $supporder = Supporder::find()
            ->where(['supp_id'=>$supplier_id])
            ->andWhere(['send_date'=>$send_date])
            ->andWhere(['status'=>1])
            ->andWhere($where)
            ->asArray()
            ->all();

        $supporder_ids = array_column($supporder, 'id');
        $result = SupporderGoods::find()
            ->select('goods_name,spec,unit,sum(needqty) as order_count')
            ->where(['in','supporder_id',$supporder_ids])
            ->andWhere(['status'=>1])
            ->groupBy('goods_id')
            ->asArray()
            ->all();


        $gotime_list = PickseatGotime::find()
            ->select('id as gotime_id,gotime')
            ->asArray()
            ->all();
        return json_encode([
            'result' => 0,
            'message' => "Request successful",
            'data' => $result,
            'gotime' => $gotime_list
        ],JSON_UNESCAPED_UNICODE);
    }

    /**
     * 报价明细列表
     * @param
     * @author JF
     * @Date 2020-11-05
     * $send_time 送货时间
     * audit_status 未审核
     */
    public static function SupporderGoodsPrice($supporder_id)
    {
        /*   用于测试数据  上线删除   */
        $supporder_id = 15;
        $send_date = '2020-12-02';
        /*   用于测试数据  上线删除   */
        $result = SupporderGoods::find()
            ->select('sum(needqty) as needqty,id as supporder_goods_id,pickseat_name,goods_name,spec,unit,supp_price1')
            ->where(['status'=>1])
            ->andWhere(['supporder_id'=>$supporder_id])
            ->andWhere(['audit_status'=>0])
            ->groupBy('goods_id')
            ->asArray()
            ->all();

        foreach($result as $key =>&$value){
            $value['needqty'] = $value['needqty'] . $value['unit'];
            unset($value['unit']);
        }
        return success($result);
    }

    /**
     * 报价明细->修改
     * @param
     * @author JF
     * @Date 2020-11-05

     */
    public static function UpdateSuppGoodsPrice($id,$supplier_id,$price)
    {

        $supporder_goods = SupporderGoods::findOne($id);
        $goods_id = $supporder_goods['goods_id'];//商品ID
        $supporder_id = $supporder_goods['supporder_id'];//采购单号
        $send_date = Supporder::findOne($supporder_id)['send_date'];//配送日期
        //supporder_goods主键集合
        $supporder_ids = Supporder::find()
            ->select('id')
            ->where(['supp_id'=>$supplier_id])
            ->andWhere(['send_date'=>$send_date])
            ->andWhere(['audit_status'=>0])
            ->andWhere(['status'=>1])
            ->column();

        $save = SupporderGoods::find()
            ->where(['in','supporder_id',$supporder_ids])
            ->andWhere(['goods_id'=>$goods_id])
            ->andWhere(['audit_status'=>0])
            ->andWhere(['status'=>1])
            ->all();
//        halt($save);
        foreach($save as $key => &$value){
            $value->supp_price_status = 1;
            $value->supp_price_time = time();
            $value->supp_price1 = $price;
            $value->supp_price2 = $price;
            $value->system_price1 = $price;
            $value->system_price2 = $price;
            $result = $value->save();
        }

        if($result !== false){
            return success('报价完成');
        }else{
            return fail(join($save->getFirstErrors()));
        }
    }

    /**
     * 细分报价列表
     * @param
     * @author JF
     * @Date 2020-11-06
     * $supporder_goods_id supporder_goods主键
     */
    public static function SupporderGoodsPrice2($supporder_goods_id)
    {
        $supporder_goods = SupporderGoods::findOne($supporder_goods_id);
        $supporder = Supporder::findOne($supporder_goods['supporder_id']);
//       halt($supporder['supp_id']);
        $supporder_ids = Supporder::find()
            ->select('id')
            ->where(['supp_id' => $supporder['supp_id']])
            ->andWhere(['send_date' => $supporder['send_date']])
            ->andWhere(['status' => 1])
            ->andWhere(['audit_status' => 0])
            ->column();
//        halt($supporder_ids);
        $result = SupporderGoods::find()
            ->select('pickseat_name,goods_name,spec,needqty,supp_price2,id,unit,remark')
            ->where(['in', 'supporder_id', $supporder_ids])
            ->andWhere(['goods_id' => $supporder_goods['goods_id']])
            ->andWhere(['audit_status' => 0])
            ->andWhere(['status' => 1])
            ->asArray()
            ->all();
        foreach($result as $key => &$value){
            $value['needqty'] = $value['needqty'] . $value['unit'];
            unset($value['unit']);
        }
        return success($result);
    }

    /**
     * 细分报价列表->修改细分报价(细分报价)
     * @param
     * @author JF
     * @Date 2020-11-06

     */
    public static function UpdateSuppGoodsPrice2($id,$price)
    {

        $save = SupporderGoods::findOne($id);
        if($save['status'] == 0 || $save['audit_status'] == 1){
            return fail('此发货单无效');
        }
        $save->supp_price2 = $price;
        $save->system_price2 = $price;
        $result = $save->save();

        if($result !== false){
            return success('细分报价完成');
        }else{
            return fail(join($save->getFirstErrors()));
        }
    }

        /**
     * 返回当日配货进度
     * @param
     * @author JF
     * @Date 2020-11-04
     * all_pick 全部订单
     * is_pick 完成配货
     */
    public static function DistributionSchedule($supporder_ids)
    {
        $result['all_pick'] = SupporderGoods::find()
            ->where(['in','supporder_id',$supporder_ids])
            ->andWhere(['status'=>1])
            ->asArray()
            ->count();
        $result['is_pick'] = SupporderGoods::find()
            ->where(['in','supporder_id',$supporder_ids])
            ->andWhere(['supp_pick_status'=>1])
            ->andWhere(['status'=>1])
            ->asArray()
            ->count();
        return $result['is_pick'] . "/" . $result['all_pick'];

    }

    /**
     * 返回当日报价进度
     * @param
     * @author JF
     * @Date 2020-11-04
     * all_pick 汇总商品
     * is_pick 完成报价
     */
    public static function PriceSchedule($supporder_ids)
    {
        $result['all_price'] = SupporderGoods::find()
            ->where(['in','supporder_id',$supporder_ids])
            ->andWhere(['status'=>1])
            ->groupBy('goods_id')
            ->asArray()
            ->count();
        $result['is_price'] = SupporderGoods::find()
            ->where(['in','supporder_id',$supporder_ids])
            ->andWhere(['supp_price_status'=>1])
            ->andWhere(['status'=>1])
            ->groupBy('goods_id')
            ->asArray()
            ->count();
        return $result['is_price'] . "/" . $result['all_price'];
    }
    /**
     * 修改密码
     * @param
     * @author JF
     * @Date 2020-12-16
     * supplier_id 供应商ID
     * newpassword 新密码
     */
    public static function UpdatePass($supplier_id,$newpassword)
    {
        $supplier = Supplier::findOne($supplier_id);

        $supplier->password  = md5(md5($newpassword).$supplier->salt);
        $result = $supplier->save();

        if($result !== false){
            return success('修改完成');
        }else{
            return fail(join($supplier->getFirstErrors()));
        }
    }
}
