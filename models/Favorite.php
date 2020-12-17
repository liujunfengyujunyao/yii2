<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "favorite".
 *
 * @property int $id
 * @property int $member_id 客户id
 * @property int $goods_id 商品id
 * @property string $goods_sn 商品编号
 *
 * @property string $remark 个性化设置
 * @property int $add_time 添加时间
 */
class Favorite extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'favorite';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['member_id', 'goods_id', 'goods_sn', 'add_time'], 'required'],
            [['member_id', 'goods_id', 'add_time'], 'integer'],
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
            'remark' => 'Remark',
            'add_time' => 'Add Time',
        ];
    }
}
