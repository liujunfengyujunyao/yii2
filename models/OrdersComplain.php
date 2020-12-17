<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "orders_complain".
 *
 * @property int $id
 * @property int|null $order_id 订单Id
 * @property int|null $goods_id 菜品Id
 * @property string|null $type 投诉的类型
 * @property string|null $reason 投诉的原因
 * @property string|null $suggestion 处理意见
 * @property int|null $solve 是否解决0未解决1已解决
 * @property int|null $inform_supp 是否通知供货商0、不通知1、通知
 * @property string|null $mobile 来电号码
 * @property string|null $name 来电人
 * @property string|null $remark 备注
 * @property string|null $add_user
 * @property int|null $add_time 添加时间
 */
class OrdersComplain extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'orders_complain';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['order_id', 'goods_id', 'solve', 'inform_supp', 'add_time'], 'integer'],
            [['type', 'reason'], 'string', 'max' => 15],
            [['suggestion', 'remark'], 'string', 'max' => 30],
            [['mobile'], 'string', 'max' => 20],
            [['name', 'add_user'], 'string', 'max' => 10],
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
            'type' => 'Type',
            'reason' => 'Reason',
            'suggestion' => 'Suggestion',
            'solve' => 'Solve',
            'inform_supp' => 'Inform Supp',
            'mobile' => 'Mobile',
            'name' => 'Name',
            'remark' => 'Remark',
            'add_user' => 'Add User',
            'add_time' => 'Add Time',
        ];
    }
}
