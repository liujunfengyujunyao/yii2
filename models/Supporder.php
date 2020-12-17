<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "supporder".
 *
 * @property int $id
 * @property int $disorder_id 分配单Id
 * @property string $supporder_sn 采购单编号
 * @property int $supp_id 供货商Id
 * @property string|null $supp_name 供货商名称
 * @property string|null $arrive_time 到货时间
 * @property int|null $buyer_id 采购Id
 * @property string|null $buyer_name 采购名称
 * @property string $send_date 配送日期
 * @property int|null $status 0、未审核1、已审核
 * @property string|null $audit_user 审核人
 * @property int|null $audit_time 审核时间
 * @property string|null $add_user 添加人
 * @property int $add_time 添加时间
 */
class Supporder extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'supporder';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['disorder_id', 'supporder_sn', 'supp_id', 'send_date', 'add_time'], 'required'],
            [['disorder_id', 'supp_id', 'buyer_id', 'status', 'audit_time', 'add_time'], 'integer'],
            [['send_date'], 'safe'],
            [['supporder_sn'], 'string', 'max' => 13],
            [['supp_name'], 'string', 'max' => 30],
            [['arrive_time'], 'string', 'max' => 15],
            [['buyer_name', 'audit_user', 'add_user'], 'string', 'max' => 10],
            [['supporder_sn'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'disorder_id' => 'Disorder ID',
            'supporder_sn' => 'Supporder Sn',
            'supp_id' => 'Supp ID',
            'supp_name' => 'Supp Name',
            'arrive_time' => 'Arrive Time',
            'buyer_id' => 'Buyer ID',
            'buyer_name' => 'Buyer Name',
            'send_date' => 'Send Date',
            'status' => 'Status',
            'audit_user' => 'Audit User',
            'audit_time' => 'Audit Time',
            'add_user' => 'Add User',
            'add_time' => 'Add Time',
        ];
    }
}
