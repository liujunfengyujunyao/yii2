<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "supporder_goods".
 *
 * @property int $id
 * @property int|null $supporder_id 采购单号
 * @property int|null $order_id 订单ID
 * @property int|null $member_id 客户ID
 * @property string|null $member_name 客户名称
 * @property int|null $member_level 客户级别
 * @property int|null $pickseat_id 分拣位ID
 * @property string|null $pickseat_name 分拣位

 * @property int|null $goods_id 商品ID
 * @property string|null $goods_sn 商品编号
 * @property string|null $goods_name 商品名称
 * @property string|null $unit 单位
 * @property string|null $spec 规格
 * @property int|null $type 商品类型1、自定义2、后台显示商品3、预购商品4、普通商品
 * @property string|null $remark 个性化需求
 * @property float|null $cost_price 成本价

 * @property float|null $sale_price 售价
 * @property float|null $needqty 下单数量
 * @property float|null $sendqty 发货数量
 * @property float|null $receiveqty 接收数量
 * @property string|null $pick_user 分拣者
 * @property int|null $pick_time 分拣时间
 * @property float|null $supp_price1 供货商系统报价
 * @property float|null $supp_price2 供货商细分报价
 * @property float|null $system_price1 后台系统报价
 * @property float|null $system_price2 后台细分报价
 * @property int|null $supp_price_status 0、未报价1、已报价
 * @property int|null $supp_price_time 供货商报价时间
 * @property int|null $supp_pick_status 0、未备货1、已备货
 * @property int|null $supp_pick_time 供货商备货时间
 * @property int|null $audit_status 0、未审核1、已审核
 * @property float|null $buyer_price1 采购系统报价
 * @property float|null $buyer_price2 采购报价
 * @property int|null $buyer_audit 采购审核状态 0、未审核1、已审核
 * @property string|null $buyer_add_user 采购核价提交人
 * @property int|null $buyer_add_time 采购核价提交时间
 * @property string|null $buyer_audit_user 采购核价审核人
 * @property int|null $buyer_audit_time 采购核价审核时间
 * @property int|null $label 1、正常分配的2、调整供货商转入3、调整供货商转出4、取消订单
 * @property int|null $status 0、无效1、有效
 */
class SupporderGoods extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'supporder_goods';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['supporder_id', 'order_id', 'member_id', 'member_level', 'pickseat_id', 'goods_id', 'type', 'pick_time', 'supp_price_status', 'supp_price_time', 'supp_pick_status', 'supp_pick_time', 'audit_status', 'buyer_audit', 'buyer_add_time', 'buyer_audit_time', 'label', 'status'], 'integer'],
            [['cost_price', 'sale_price', 'needqty', 'sendqty', 'receiveqty', 'supp_price1', 'supp_price2', 'system_price1', 'system_price2', 'buyer_price1', 'buyer_price2'], 'number'],
            [['member_name'], 'string', 'max' => 50],
            [['pickseat_name', 'pick_user', 'buyer_add_user', 'buyer_audit_user'], 'string', 'max' => 10],
//            [['pickseat_gotime'], 'string', 'max' => 5],
            [['goods_sn'], 'string', 'max' => 6],
            [['goods_name', 'unit', 'spec'], 'string', 'max' => 30],
            [['remark'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'supporder_id' => 'Supporder ID',
            'order_id' => 'Order ID',
            'member_id' => 'Member ID',
            'member_name' => 'Member Name',
            'member_level' => 'Member Level',
            'pickseat_id' => 'Pickseat ID',
            'pickseat_name' => 'Pickseat Name',
//            'pickseat_gotime' => 'Pickseat Gotime',
            'goods_id' => 'Goods ID',
            'goods_sn' => 'Goods Sn',
            'goods_name' => 'Goods Name',
            'unit' => 'Unit',
            'spec' => 'Spec',
            'type' => 'Type',
            'remark' => 'Remark',
            'cost_price' => 'Cost Price',
//            'audit_price' => 'Audit Price',
            'sale_price' => 'Sale Price',
            'needqty' => 'Needqty',
            'sendqty' => 'Sendqty',
            'receiveqty' => 'Receiveqty',
            'pick_user' => 'Pick User',
            'pick_time' => 'Pick Time',
            'supp_price1' => 'Supp Price1',
            'supp_price2' => 'Supp Price2',
            'system_price1' => 'System Price1',
            'system_price2' => 'System Price2',
            'supp_price_status' => 'Supp Price Status',
            'supp_price_time' => 'Supp Price Time',
            'supp_pick_status' => 'Supp Pick Status',
            'supp_pick_time' => 'Supp Pick Time',
            'audit_status' => 'Audit Status',
            'buyer_price1' => 'Buyer Price1',
            'buyer_price2' => 'Buyer Price2',
            'buyer_audit' => 'Buyer Audit',
            'buyer_add_user' => 'Buyer Add User',
            'buyer_add_time' => 'Buyer Add Time',
            'buyer_audit_user' => 'Buyer Audit User',
            'buyer_audit_time' => 'Buyer Audit Time',
            'label' => 'Label',
            'status' => 'Status',
        ];
    }
}
