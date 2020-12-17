<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "goods".
 *
 * @property int $id
 * @property string $goods_sn 商品编号
 * @property string $goods_name 商品名称
 * @property string $spec 商品规格
 * @property string $unit 单位
 * @property string $logo 大图片
 * @property string $slogo 小图片
 * @property int $cate_id 一级分类id
 * @property string $cate_name 一级分类名称
 * @property int $scate_id 二级分类id
 * @property string $scate_name 二级分类名称
 * @property float $cost_price 成本价
 * @property float $virtual_price 虚拟价
 * @property int|null $price_type 定价类型
 * @property float|null $price_per 百分比定价
 * @property float|null $price_fix 固定价格
 * @property float $sale_price 售价
 * @property int $type 商品类型1、自定义2、后台显示3、预约商品4、普通商品
 * @property int $attr 商品属性1、非标品2、标品3、特种品
 * @property string $remark 商品描述
 * @property float $tax_rate 税率
 * @property int $fina_id 财务分类id
 * @property string $fina_tax_code 财务分类编码
 * @property float $fina_tax_rate 财务分类税率
 * @property float $supp_price 供货商当日报价
 * @property string|null $supp_date 供货商报价日期
 * @property int $is_recommend 首页推荐 0、默认1、推荐
 * @property int $status 状态0、下架1、上架
 * @property int $sort 排序
 * @property int $add_time 添加时间
 * @property int $update_time 更新时间
 */
class Goods extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'goods';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['goods_sn', 'goods_name', 'remark'], 'required'],
            [['cate_id', 'scate_id', 'price_type', 'type', 'attr', 'fina_id', 'is_recommend', 'status', 'sort', 'add_time', 'update_time'], 'integer'],
            [['cost_price', 'virtual_price', 'price_per', 'price_fix', 'sale_price', 'tax_rate', 'fina_tax_rate', 'supp_price'], 'number'],
            [['remark'], 'string'],
            [['supp_date'], 'safe'],
            [['goods_sn'], 'string', 'max' => 6],
            [['goods_name', 'spec'], 'string', 'max' => 30],
            [['unit'], 'string', 'max' => 5],
            [['logo', 'slogo'], 'string', 'max' => 50],
            [['cate_name', 'scate_name'], 'string', 'max' => 10],
            [['fina_tax_code'], 'string', 'max' => 20],
            [['goods_sn'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'goods_sn' => 'Goods Sn',
            'goods_name' => 'Goods Name',
            'spec' => 'Spec',
            'unit' => 'Unit',
            'logo' => 'Logo',
            'slogo' => 'Slogo',
            'cate_id' => 'Cate ID',
            'cate_name' => 'Cate Name',
            'scate_id' => 'Scate ID',
            'scate_name' => 'Scate Name',
            'cost_price' => 'Cost Price',
            'virtual_price' => 'Virtual Price',
            'price_type' => 'Price Type',
            'price_per' => 'Price Per',
            'price_fix' => 'Price Fix',
            'sale_price' => 'Sale Price',
            'type' => 'Type',
            'attr' => 'Attr',
            'remark' => 'Remark',
            'tax_rate' => 'Tax Rate',
            'fina_id' => 'Fina ID',
            'fina_tax_code' => 'Fina Tax Code',
            'fina_tax_rate' => 'Fina Tax Rate',
            'supp_price' => 'Supp Price',
            'supp_date' => 'Supp Date',
            'is_recommend' => 'Is Recommend',
            'status' => 'Status',
            'sort' => 'Sort',
            'add_time' => 'Add Time',
            'update_time' => 'Update Time',
        ];
    }
}
