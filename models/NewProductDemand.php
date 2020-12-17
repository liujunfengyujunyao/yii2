<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "new_product_demand".
 *
 * @property int $id
 * @property string $goods_name 商品名称
 * @property string $spec 规格
 * @property string $brand 产地/品牌
 * @property int|null $projectedqty 预计采购量/天
 * @property string|null $rate 1:临时,2:短期,3:长期 采购频次
 * @property string|null $procurement_source 原采购地
 * @property float|null $procurement_price 原采购价
 * @property string|null $remark 详细描述
 * @property string $img 商品图片
 * @property int $create_time 创建时间
 * @property int $member_id 创建人员
 */
class NewProductDemand extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'new_product_demand';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_name', 'spec', 'brand', 'img', 'create_time', 'member_id'], 'required'],
            [['projectedqty', 'create_time', 'member_id'], 'integer'],
            [['rate', 'remark'], 'string'],
            [['procurement_price'], 'number'],
            [['goods_name', 'procurement_source'], 'string', 'max' => 64],
            [['spec'], 'string', 'max' => 15],
            [['brand'], 'string', 'max' => 20],
            [['img'], 'string', 'max' => 125],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_name' => 'Goods Name',
            'spec' => 'Spec',
            'brand' => 'Brand',
            'projectedqty' => 'Projectedqty',
            'rate' => 'Rate',
            'procurement_source' => 'Procurement Source',
            'procurement_price' => 'Procurement Price',
            'remark' => 'Remark',
            'img' => 'Img',
            'create_time' => 'Create Time',
            'member_id' => 'Member ID',
        ];
    }

    public static function Detail($id)
    {
        $detail = static::find()->where(['id'=>$id])->asArray()->one();
        return success($detail);
    }

    public static function getList($member_id,$key)
    {
        if(!empty($key)){
            $where = ['like','goods_name',$key];
        }else{
            $where = "1=1";
        }
        $goods = static::find()
            ->where($where)
            ->andWhere(['member_id'=>$member_id])
            ->asArray()
            ->orderBy('create_time DESC')
            ->all();
        return success($goods);
    }
}
