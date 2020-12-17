<?php

namespace app\service;

use Yii;
use yii\base\BaseObject;
use yii\base\UserException;

/**
 * 服务层基类
 * @author JF
 * @date 2020-10-14
 */
class BaseService extends  BaseObject
{
    /**
     * ---------------------------------------
     * 修改数据表一条记录的一条值
     * @param \yii\db\ActiveRecord $model 模型名称
     * @param array $data 数据
     * @return \yii\db\ActiveRecord|boolean
     * ---------------------------------------
     */
    public static  function saveRow($model, $data)
    {
        if (empty($data)) {
            return false;
        }
        if ($model->load($data, '') && $model->validate()) {
            /* 添加到数据库中,save()会自动验证rule */
            if ($model->save()) {
                return $model;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    /**
     * ---------------------------------------
     * 由表主键删除数据表中的多条记录
     * @param \yii\db\ActiveRecord $model 模型名称,供M函数使用的参数
     * @param string $pk 修改的数据
     * @return boolean
     * ---------------------------------------
     */
    public static function delRow($model, $pk = 'id')
    {
        $id = Yii::$app->request->get($pk, 0);
        if (empty($id)) {
            return false;
        }
        if ($model::findOne($id)) {
            if ($model::findOne($id)->delete()) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * 获取对应模型实例,如果没有找到就抛出异常
     * @author JF
     * @date 2020-10-14
     * @param string $class 模型类名
     * @param string | array 查询条件
     * @param string $name 模型名字
     */
    public static function getModel($class, $condition, $name = '对象')
    {
        $model = $class::findOne($condition);
        if (!$model) {
            throw new \yii\base\UserException("未获取到对应" . $name, 404);
        }
        return $model;
    }


}
