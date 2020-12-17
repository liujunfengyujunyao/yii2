<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pickseat_gotime".
 *
 * @property int $id
 * @property string|null $gotime 发车时间
 */
class PickseatGotime extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'pickseat_gotime';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['gotime'], 'string', 'max' => 15],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'gotime' => 'Gotime',
        ];
    }
}
