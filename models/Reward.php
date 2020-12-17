<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "reward_list".
 *
 * @property int $id
 * @property int $supporder_id 采购单id
 * @property string $supporder_sn 采购单编号
 * @property int $supp_id 供货商id
 * @property string $supp_name 供货商名称
 * @property int|null $rule_id 奖惩id
 * @property string|null $name 奖惩项目
 * @property int $type 奖惩类型1、奖励2、处罚
 * @property float $money 奖惩金额
 * @property int $score 奖惩积分
 * @property string $reason 奖惩理由
 * @property string|null $logo 图片
 * @property int $status 状态0、已取消1、待确认2、已确认3、已完成
 * @property string $add_user 添加用户
 * @property int $add_time 添加时间
 * @property string $update_user 更新用户
 * @property int $update_time 更新时间
 */
class Reward extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reward_list';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['supporder_id'], 'required'],
            [['supporder_id', 'supp_id', 'rule_id', 'type', 'score', 'status', 'add_time', 'update_time'], 'integer'],
            [['money'], 'number'],
            [['supporder_sn'], 'string', 'max' => 20],
            [['supp_name', 'name', 'reason'], 'string', 'max' => 30],
            [['logo'], 'string', 'max' => 50],
            [['add_user', 'update_user'], 'string', 'max' => 10],
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
            'supporder_sn' => 'Supporder Sn',
            'supp_id' => 'Supp ID',
            'supp_name' => 'Supp Name',
            'rule_id' => 'Rule ID',
            'name' => 'Name',
            'type' => 'Type',
            'money' => 'Money',
            'score' => 'Score',
            'reason' => 'Reason',
            'logo' => 'Logo',
            'status' => 'Status',
            'add_user' => 'Add User',
            'add_time' => 'Add Time',
            'update_user' => 'Update User',
            'update_time' => 'Update Time',
        ];
    }
}
