<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "category".
 *
 * @property int $id
 * @property string $name 分类名称
 * @property int $pid 上级id
 * @property int $sort 排序
 * @property int $status 0、无效1、有效
 * @property string|null $add_user 添加用户
 * @property int $add_time 添加时间
 * @property string|null $update_user 更新用户
 * @property int|null $update_time 更新时间
 */
class Category extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['pid', 'sort', 'status', 'add_time', 'update_time'], 'integer'],
            [['name', 'add_user', 'update_user'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'pid' => 'Pid',
            'sort' => 'Sort',
            'status' => 'Status',
            'add_user' => 'Add User',
            'add_time' => 'Add Time',
            'update_user' => 'Update User',
            'update_time' => 'Update Time',
        ];
    }

    public function getGoods()
    {
        return $this->hasMany(Goods::className(),['scate_id'=>'scate_id']);
    }
}
