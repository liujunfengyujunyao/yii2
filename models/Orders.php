<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "orders".
 *
 * @property int $id 序列号
 * @property string $order_sn 订单编号
 * @property int $order_type 1、平台下单2、手工订单、3、业务自采4、司机自采
 * @property int $member_id 客户ID
 * @property string $member_name 客户名称
 * @property string $member_linkman 收货人姓名
 * @property string $member_mobile 电话
 * @property string $member_address 地址
 * @property int $driver_id 送货司机ID
 * @property string $driver_name 送货司机姓名
 * @property string $driver_mobile 送货司机电话
 *
 * @property int $pickseat_id 分拣位ID
 * @property string|null $pickseat_no 分拣位编号
 * @property int $sale_id 销售ID
 * @property string $sale_name 销售姓名
 * @property string $send_date 送货日期
 * @property int $disorder_id 分配单号
 * @property int $status 0、作废10、取消20、下单30、配货4、发货50、收货60、结算
 * @property int $stock_status 0、不备货1、备货
 * @property float $total_price 下单总价
 * @property string $ip 下单ip
 * @property int $utm_source 来源 1、PC2、微信3、后台补货
 * @property string $remark 备注
 * @property string $add_user 下单用户
 * @property int $add_time 下单时间

 * @property int|null $audit_status 0、未审核1、已审核

 * @property int|null $audit_time 审核时间
 * @property string|null $receive_start_time 审核时间
 * @property string|null $receive_end_time 审核时间
 */
class Orders extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'orders';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_sn', 'member_id', 'send_date'], 'required'],
            [['order_type', 'member_id', 'driver_id', 'pickseat_id', 'sale_id', 'disorder_id', 'status', 'stock_status', 'utm_source', 'add_time', 'audit_status', 'audit_time'], 'integer'],
            [['send_date'], 'safe'],
            [['total_price'], 'number'],
            [['order_sn'], 'string', 'max' => 25],
            [['member_name', 'member_address'], 'string', 'max' => 100],
            [['member_linkman', 'driver_name', 'pickseat_no', 'sale_name', 'add_user'], 'string', 'max' => 10],
            [['member_mobile', 'driver_mobile'], 'string', 'max' => 20],
            [['ip'], 'string', 'max' => 15],
            [['remark'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_sn' => 'Order Sn',
            'order_type' => 'Order Type',
            'member_id' => 'Member ID',
            'member_name' => 'Member Name',
            'member_linkman' => 'Member Linkman',
            'member_mobile' => 'Member Mobile',
            'member_address' => 'Member Address',
            'driver_id' => 'Driver ID',
            'driver_name' => 'Driver Name',
            'driver_mobile' => 'Driver Mobile',

            'pickseat_id' => 'Pickseat ID',
            'pickseat_no' => 'Pickseat No',
            'sale_id' => 'Sale ID',
            'sale_name' => 'Sale Name',
            'send_date' => 'Send Date',
            'disorder_id' => 'Disorder ID',
            'status' => 'Status',
            'stock_status' => 'Stock Status',
            'total_price' => 'Total Price',
            'ip' => 'Ip',
            'utm_source' => 'Utm Source',
            'remark' => 'Remark',
            'add_user' => 'Add User',
            'add_time' => 'Add Time',
//            'merge_user' => 'Merge User',
//            'merge_time' => 'Merge Time',
            'audit_status' => 'Audit Status',
//            'audit_name' => 'Audit Name',
            'audit_time' => 'Audit Time',
        ];
    }
}
