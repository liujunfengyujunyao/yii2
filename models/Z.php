<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "z".
 *
 * @property int $id
 * @property string|null $title
 * @property int|null $number
 */
class Z extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'z';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['number'], 'integer'],
            [['title'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'number' => 'Number',
        ];
    }
}
