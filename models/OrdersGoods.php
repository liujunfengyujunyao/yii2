<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "orders_goods".
 *
 * @property int $id
 * @property int $order_id 订单id
 * @property int $goods_id 商品id
 * @property string $goods_sn 商品编号
 * @property string $goods_name 商品名称
 * @property string $spec 规格
 * @property string $unit 计量单位
 * @property int $type 商品类型1、自定义2、后台显示3、预约商品4、普通商品
 * @property int $attr 商品属性1、非标品2、标品3、特种品
 * @property int $cate_id 一级分类
 * @property string $cate_name 一级分类名称
 * @property int $scate_id 二级分类
 * @property string $scate_name 二级分类名称
 * @property float $needqty 下单数量
 * @property float $sendqty 发货数量
 * @property float $backqty 退货数量
 * @property float $cost_price 成本价


 * @property float $original_price 原始售价
 * @property float $sale_price 售价
 * @property string $remark 客户备注
 * @property string $sys_remark 系统备注
 * @property string $pick_user 分拣司机
 * @property int $pick_time 分拣时间
 * @property int $hand 手输  0默认，称重为1 手输入为2 累加为3
 * @property int $supp_id 供货商ID
 * @property string $supp_name 供货商名称
 * @property string $supp_code 供货商编码
 * @property int $supp_cate_id 供货商类别

 * @property string $modify_user 修改用户
 * @property int $modify_time 修改时间

 */
class OrdersGoods extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'orders_goods';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'goods_id', 'goods_sn', 'unit'], 'required'],
            [['order_id', 'goods_id', 'type', 'attr', 'cate_id', 'scate_id', 'pick_time', 'hand', 'supp_id', 'supp_cate_id', 'modify_time'], 'integer'],
            [['needqty', 'sendqty', 'backqty', 'cost_price', 'original_price', 'sale_price'], 'number'],
            [['goods_sn'], 'string', 'max' => 6],
            [['goods_name', 'spec', 'remark', 'sys_remark'], 'string', 'max' => 100],
            [['unit', 'supp_code'], 'string', 'max' => 20],
            [['cate_name', 'scate_name', 'pick_user', 'modify_user'], 'string', 'max' => 10],
            [['supp_name'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'goods_id' => 'Goods ID',
            'goods_sn' => 'Goods Sn',
            'goods_name' => 'Goods Name',
            'spec' => 'Spec',
            'unit' => 'Unit',
            'type' => 'Type',
            'attr' => 'Attr',
            'cate_id' => 'Cate ID',
            'cate_name' => 'Cate Name',
            'scate_id' => 'Scate ID',
            'scate_name' => 'Scate Name',
            'needqty' => 'Needqty',
            'sendqty' => 'Sendqty',
            'backqty' => 'Backqty',
            'cost_price' => 'Cost Price',
//            'confirm_cost_price' => 'Confirm Cost Price',
//            'confirm_cost_time' => 'Confirm Cost Time',
            'original_price' => 'Original Price',
            'sale_price' => 'Sale Price',
            'remark' => 'Remark',
            'sys_remark' => 'Sys Remark',
            'pick_user' => 'Pick User',
            'pick_time' => 'Pick Time',
            'hand' => 'Hand',
            'supp_id' => 'Supp ID',
            'supp_name' => 'Supp Name',
            'supp_code' => 'Supp Code',
            'supp_cate_id' => 'Supp Cate ID',

            'modify_user' => 'Modify User',
            'modify_time' => 'Modify Time',

        ];
    }
}
