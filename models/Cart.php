<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cart".
 *
 * @property int $id
 * @property int $member_id 客户id
 * @property int|null $goods_id 商品id
 * @property string $goods_sn 商品编号
 * @property float $quantity 数量
 * @property string $remark 个性化设置
 * @property int $add_time 添加时间
 */
class Cart extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cart';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'goods_sn', 'quantity'], 'required'],
            [['member_id', 'goods_id', 'add_time'], 'integer'],
            [['quantity'], 'number'],
            [['goods_sn'], 'string', 'max' => 6],
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
            'member_id' => 'Member ID',
            'goods_id' => 'Goods ID',
            'goods_sn' => 'Goods Sn',
            'quantity' => 'Quantity',
            'remark' => 'Remark',
            'add_time' => 'Add Time',
        ];
    }
}
