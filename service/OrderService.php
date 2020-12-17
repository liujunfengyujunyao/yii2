<?php
namespace app\service;
use app\models\Cart;
use app\models\Favorite;
use app\models\Member;
use app\models\Goods;
use app\models\Orders;
use app\models\OrdersGoods;
use yii\base\UserException;
use app\models\Category;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PHPExcel;
use PHPExcel_IOFactory;
/**
 * 用户逻辑层.
 * User: Administrator
 * Date: 2020/10/15
 * Time: 9:31
 */
class OrderService extends BaseService
{
    /**
     * 计算购物车金额
     * @param
     * @author JF
     * @Date 2020-10-19
     * $member_id 用户ID
     * $ids 购物车表ID
     */
    public static function CalculationAmount($ids,$member_id)
    {
        $ids = explode(',',$ids);
        $price = 0;
        foreach($ids as $key => $value) {
            $good = Cart::find()
                ->select('goods_id,quantity')
                ->where(['member_id' => $member_id, 'id' => $value])
                ->asArray()
                ->one();
            $price += Goods::findOne($good['goods_id'])['sale_price'] * $good['quantity'];//商品价格 * 购物车中的数量
        }

        return sprintf("%.2f", $price);
    }

    /**
     * 检查金额以及提交订单,插入orders及orders_goods表
     * @param
     * @author JF
     * @Date 2020-10-20
     * $member_id 用户ID
     * $ids 购物车表ID
     * $receive_start_time 最早送达时间
     * $receive_end_time 最晚送达时间
     * $remark 订单备注
     */
    public static function CheckOrderSubmit($totalPrice,$ids,$member_id,$receive_start_time,$receive_end_time,$delivery_date,$remark)
    {


        if($totalPrice != (float) self::CalculationAmount($ids,$member_id)){
            return fail('金额有误');
        }
        //插入订单表
        $member = Member::findOne($member_id);

        $orders = new Orders();
        $orders->order_sn = date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $orders->order_type = 1;//1:平台下单
        $orders->member_id = $member_id;
        $orders->member_linkman = $member['linkman'];
        $orders->member_name = $member['name'];
        $orders->member_mobile = $member['username'];
        $orders->member_address = $member['address'];
        $orders->send_date = $delivery_date;//送货日期
        $orders->total_price = $totalPrice;
        $orders->ip = $_SERVER['REMOTE_ADDR'];
        $orders->utm_source = 2;//2:微信
        $orders->remark = $remark;
        $orders->add_time = time();
        $orders->receive_start_time = $receive_start_time;
        $orders->receive_end_time = $receive_end_time;

        $orders->save();
        if(!$orders->save()){
            throw new \yii\base\UserException('添加失败,'.join($orders->getFirstErrors()), 400);
        }
        $order_id = $orders->id;
        $trans = \Yii::$app->db->beginTransaction();
        try{
            //插入订单商品表
            $cart_ids = explode(',',$ids);
            $goods = Goods::find()
                ->alias('t1')
                ->select('t1.id as goods_id,t1.goods_sn,t1.goods_name,t1.spec,t1.unit,t1.type,t1.attr,t1.cate_id,t1.cate_name,t1.scate_id,t1.scate_name,t1.cost_price,t1.sale_price,t2.quantity,t2.remark')
                ->where(['in','t2.id',$cart_ids])
                ->andWhere(['t2.member_id'=>$member_id])
                ->rightJoin('cart t2','t2.goods_id=t1.id')
                ->asArray()
                ->all();
            //remark needqty下单数量
            foreach($goods as $key => $value){
                $orders_goods = new OrdersGoods();
                $orders_goods->order_id = $order_id;
                $orders_goods->goods_id = $value['goods_id'];
                $orders_goods->goods_sn = $value['goods_sn'];
                $orders_goods->goods_name = $value['goods_name'];
                $orders_goods->spec = $value['spec'];
                $orders_goods->unit = $value['unit'];
                $orders_goods->type = $value['type'];
                $orders_goods->attr = $value['attr'];
                $orders_goods->cate_id = $value['cate_id'];
                $orders_goods->cate_name = $value['cate_name'];
                $orders_goods->scate_id = $value['scate_id'];
                $orders_goods->scate_name = $value['scate_name'];
                $orders_goods->needqty = $value['quantity'];
                $orders_goods->cost_price = $value['cost_price'];
                $orders_goods->sale_price = $value['sale_price'];
                $orders_goods->remark = $value['remark'];
                $orders_goods->modify_time = time();
                $orders_goods->save();
            }
            //删除购物车
            Cart::deleteAll(['and','member_id = :member_id',['in','id',$cart_ids]],[':member_id'=>$member_id]);
            $trans->commit();
            return success('提交完成');
        }catch(\Exception $e) {
            $trans->rollBack();
            throw $e;//抛出异常
        }
    }

    /**
     * 返回订单列表
     * @param
     * @author JF
     * @Date 2020-10-20
     * $member_id 用户ID
     */
    public static function getOrderList($start_time,$end_time,$member_id)
    {
        $model = Orders::find()

            ->select('id,member_id,member_address,order_sn,remark,send_date,status,total_price,remark,add_time,receive_start_time,receive_end_time')
            ->where(['between','add_time',$start_time,$end_time])
            ->andWhere(['member_id'=>$member_id])
            ->andWhere(['!=','status',0])
            ->asArray()
            ->all();


        foreach($model as $key => &$value){

            $value['info'] = OrdersGoods::find()
                ->select('goods_id,order_id,goods_name,spec,unit,sale_price,needqty,sendqty,backqty')
                ->where(['order_id'=>$value['id']])
                ->asArray()
                ->all();
            foreach($value['info'] as $k => &$v){
                $v['logo'] = Goods::find()
                    ->select('logo')
                    ->where(['id'=>$v['goods_id']])
                    ->scalar();
            }
        }
        return success($model);



    }

    /*
     * 根据order_ids查询对应orders_goods信息
     *
     * */
    public static function getOrderGoods($order_ids)
    {
        $order_ids = explode(',',$order_ids);
        $info = [];//商品信息
        $title = [];//订单信息
        $statistics = [];//统计金额
        foreach($order_ids as $key => $value) {

            $title[$key] = Orders::find()
                ->select('member_address,member_mobile,member_name,driver_name,driver_mobile,send_date,order_sn,add_time,receive_start_time,receive_end_time,remark')
                ->where(['id'=>$value])
                ->asArray()
                ->all();
            $info[$key] = OrdersGoods::find()
                ->alias('t1')
                ->select('t1.cate_name,t1.scate_name,t1.goods_name,t1.spec,t1.unit,t1.sale_price,t1.needqty,t1.sendqty,t1.backqty')
                ->where(['order_id'=>$value])
                ->leftJoin('orders t2', 't1.order_id=t2.id')
                ->asArray()
                ->all();
//            halt($info[$key]);
            $statistics[$key]['need_price'] = 0;
            $statistics[$key]['send_price'] = 0;
            $statistics[$key]['back_price'] = 0;
            $excel = [];
            //重新排序
            foreach($info[$key] as $k => &$v){
               $excel = [
                   'idx' => $k+1,
                   'cate_name' => $v['cate_name'],
                   'scate_name' => $v['scate_name'],
                   'goods_name' => $v['goods_name'],
                   'spec' => $v['spec'],
                   'unit' => $v['unit'],
                   'sale_price' => $v['sale_price'],
                   'needqty' => $v['needqty'],
                   'need_price' => $v['needqty'] * $v['sale_price'] ,
                   'sendqty' => $v['sendqty'],
                   'send_price' => $v['sendqty'] * $v['sale_price'],
                   'backqty' => $v['backqty'],
                   'back_price' => $v['backqty'] * $v['sale_price'],
               ];
                $v = $excel;
                $statistics[$key]['need_price'] += $v['needqty'] * $v['sale_price'];
                $statistics[$key]['send_price'] += $v['sendqty'] * $v['sale_price'];
                $statistics[$key]['back_price'] += $v['backqty'] * $v['sale_price'];
            }
        }
        $result['info'] = $info;
        $result['title'] = $title;
        $result['statistics'] = $statistics;
        return $result;

    }
    /**
     * excel格式
     * @param
     * @author JF
     * @Date 2020-10-23
     * $data 商品信息
     * $head 商品标题信息
     * $title 订单标题信息
     * $statistics 总计
     */
    public static function export2Excel(array $data,$head,$title,$statistics)
    {
//        halt($title[1-1][0]['member_name']);
        header("Content-type:application/vnd.ms-excel");
        header("Content-Disposition:filename="."订单导出".".xls");
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['客户订单'],null,'G1');
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();

        $drawing->setName('N1');
        $drawing->setDescription('N1');
        $drawing->setPath('./upload/images/20201022/20201022115200_36540.jpg'); //http://192.168.1.118/qBAF/Server/media/demo.png
        $drawing->setHeight(36);
        $drawing->setCoordinates('N16');
//        $drawing->setOffsetX(10);
//        $drawing->setOffsetY(10);


      $drawing->setName('tupian');
        $drawing->setDescription('tupian');
        $drawing->setPath('./upload/images/20201022/20201022134136_55429.jpg'); //http://192.168.1.118/qBAF/Server/media/demo.png
        $drawing->setHeight(100);
        $drawing->setCoordinates('A18');
//        $drawing->setOffsetX(10);
//        $drawing->setOffsetY(10);
//        $drawing->setWorksheet($spreadsheet->getActiveSheet());
        $drawing->setWorksheet($spreadsheet->getActiveSheet());
        $top = 2;//第一行为1  +  G1
        $next = 0;//上边距初始值
        for($a=1;$a<=count($data);$a++){
            $top_margin = count($data[$a-1]);//上间距
            if($a == 1){
                $sheet->fromArray(['收货单位:'.$title[$a-1][0]['member_name']],null,'A2');
                $sheet->fromArray(['司机:'.$title[$a-1][0]['driver_name']],null,'F2');
                $sheet->fromArray(['订单编号:'.$title[$a-1][0]['order_sn']],null,'I2');
                $sheet->fromArray(['联系电话:'.$title[$a-1][0]['member_mobile']],null,'A3');
                $sheet->fromArray(['司机电话:'.$title[$a-1][0]['driver_mobile']],null,'F3');
                $sheet->fromArray(['送达时间:'.($title[$a-1][0]['receive_start_time'].'-'.$title[$a-1][0]['receive_end_time'])],null,'I3');
                $sheet->fromArray(['地址:'.$title[$a-1][0]['member_address']],null,'A4');
                $sheet->fromArray(['下单日期:'.date("Y-m-d H:i:s",$title[$a-1][0]['add_time'])],null,'F4');
                $sheet->fromArray(['送货日期:'.$title[$a-1][0]['send_date']],null,'I4');
                $sheet->fromArray(['订单备注:'.$title[$a-1][0]['remark']],null,'A5');
                $sheet->fromArray($head,null,'A6');
                $sheet->fromArray($data[$a-1], null, 'A7');

                $next = 6+$top_margin+1+1+2;//11 +2是为了美观  与逻辑无关
                $sheet->fromArray(['总计:'.$statistics[$a-1]['need_price']],null,'I'.(7+$top_margin));
                $sheet->fromArray(['总计:'.$statistics[$a-1]['send_price']],null,'K'.(7+$top_margin));
                $sheet->fromArray(['总计:'.$statistics[$a-1]['back_price']],null,'M'.(7+$top_margin));
            }else{
                $sheet->fromArray(['收货单位:'.$title[$a-1][0]['member_name']],null,'A'.$next);
                $sheet->fromArray(['司机:'.$title[$a-1][0]['driver_name']],null,'F'.$next);
                $sheet->fromArray(['订单编号:'.$title[$a-1][0]['order_sn']],null,'I'.$next);
                $sheet->fromArray(['联系电话:'.$title[$a-1][0]['member_mobile']],null,'A'.($next+1));
                $sheet->fromArray(['司机电话:'.$title[$a-1][0]['driver_mobile']],null,'F'.($next+1));
                $sheet->fromArray(['送达时间:'.($title[$a-1][0]['receive_start_time'].'-'.$title[$a-1][0]['receive_end_time'])],null,'I'.($next+1));
                $sheet->fromArray(['地址:'.$title[$a-1][0]['member_address']],null,'A'.($next+2));
                $sheet->fromArray(['下单日期:'.date("Y-m-d H:i:s",$title[$a-1][0]['add_time'])],null,'F'.($next+2));
                $sheet->fromArray(['送货日期:'.$title[$a-1][0]['send_date']],null,'I'.($next+2));
                $sheet->fromArray(['订单备注:'.$title[$a-1][0]['remark']],null,'A'.($next+3));
                $sheet->fromArray($head,null,'A'.($next+4));
                $sheet->fromArray($data[$a-1], null, 'A'.($next+5));

                $sheet->fromArray(['总计:'.$statistics[$a-1]['need_price']],null,'I'.($next+4+$top_margin+1));
                $sheet->fromArray(['总计:'.$statistics[$a-1]['send_price']],null,'K'.($next+4+$top_margin+1));
                $sheet->fromArray(['总计:'.$statistics[$a-1]['back_price']],null,'M'.($next+4+$top_margin+1));
                $next += 4+$top_margin+1+1+2;
            }
        }

//        $sheet->fromArray($head,null,'A6');
//        $sheet->fromArray($data, null, 'A7');
        $spreadsheet->getActiveSheet()->getStyle('A6:M6')
            ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);

        $writer = IOFactory::createWriter($spreadsheet, "Xls");
        ob_end_clean();//解决乱码
        $writer->save("php://output");
    }

    /**
     * 取消订单
     * @param
     * @author JF
     * @Date 2020-10-22
     * $member_id 用户ID
     */
    public static function cancelOrder($order_id)
    {
        $result = Orders::find()
            ->where(['id'=>$order_id])
            ->one();
        if($result->status != 20){
            return fail('订单状态非待确认');
        }else{
            $result->status = 10;
            $result->save();
            return success('订单已取消');
        }
    }


}
