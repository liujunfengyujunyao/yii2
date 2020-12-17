<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "sms".
 *
 * @property int $id
 * @property string $event 短信内容说明
 * @property string $mobile 手机号
 * @property string $code 验证码
 * @property int $times 验证次数
 * @property string $ip IP
 * @property int $createtime 创建时间
 */
class Sms extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sms';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['event', 'mobile', 'code', 'ip', 'createtime'], 'required'],
            [['times', 'createtime'], 'integer'],
            [['event'], 'string', 'max' => 64],
            [['mobile'], 'string', 'max' => 20],
            [['code'], 'string', 'max' => 10],
            [['ip'], 'string', 'max' => 30],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'event' => 'Event',
            'mobile' => 'Mobile',
            'code' => 'Code',
            'times' => 'Times',
            'ip' => 'Ip',
            'createtime' => 'Createtime',
        ];
    }
}
