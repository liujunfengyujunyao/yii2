<?php
namespace app\service;
use app\models\Cart;
use app\models\Favorite;
use app\models\Goods;
use yii\base\UserException;
use app\models\Category;
use yii\data\Pagination;

/**
 * 商品逻辑层.
 * User: Administrator
 * Date: 2020/10/15
 * Time: 9:31
 */
class GoodsService extends BaseService
{
    /**
     * 获取一类商品列表
     * @param 不需要验证token
     * @author JF
     * @Date 2020-10-15
     * sort正序排序
     * status 1启用
     */
    public static function getGoodsList_discard($member_id = 0)
    {
        $result = Category::find()
            ->select('id as cate_id,name as cate_name')
            ->where(['pid'=>0,'status'=>1])
            ->asArray()
            ->orderBy('sort ASC')
            ->all();
        //该同户收藏的商品(购物车数量在另外的接口)
        $favorite_goods_ids = Favorite::find()->select('goods_id')->where(['member_id'=>$member_id])->column();
        $cart_goods_ids = Cart::find()->select('goods_id')->where(['member_id'=>$member_id])->column();
        foreach($result as $key => &$value){
            $value['second_cate'] = Category::find()
                ->select('id as scate_id,name as scate_name')
                ->where(['pid'=>$value['cate_id'],'status'=>1])
                ->with(['goods'=>function($query){
                    $query->select("goods_name,scate_id,id as goods_id,spec,logo,slogo,sale_price,remark")
                        ->where(['status'=>1]);
//                    ->leftjoin(Favorite::tableName() . 'favorite','favorite.goods_id=goods.id')
//                    ->where(['favorite.member_id'=>$user_id])
                }])
                ->asArray()
                ->orderBy('sort ASC')
                ->all();
            //遍历是否收藏
            foreach($value['second_cate'] as $ke => &$va){
                foreach($va['goods'] as $k => &$v){
                    if(in_array($v['goods_id'],$favorite_goods_ids)){
                        $v['is_favorite'] = 1;
                    }else{
                        $v['is_favorite'] = 0;
                    }
                    //遍历是否存在于购物车的数量
                    if(in_array($v['goods_id'],$cart_goods_ids)){
                        $v['cart_number'] = (int) Cart::find()->select('quantity')->where(['member_id'=>$member_id])->scalar();
                    }else{
                        $v['cart_number'] = 0;
                    }
                }

            }
//            foreach($value['second_cate'] as $k => &$v){
//                $v['goods_list'] = Goods::find()
//                    ->select('goods_name,spec,unit,logo,slogo,sale_price,remark')
//                    ->where(['scate_id'=>$v['scate_id'],'status'=>1])
//                    ->asArray()
//                    ->orderBy('sort ASC')
//                    ->all();
//            }
        }
        return $result;
    }

    /**
     * 获取商品详情
     * @param 不需要验证token
     * @author JF
     * @Date 2020-10-15
     * sort正序排序
     * status 1启用
     */
    public static function getGoodsDetail($goods_id,$member_id=0)
    {
        $detail = Goods::find()
            ->select('id as goods_id,goods_name,spec,unit,logo,slogo,sale_price,remark')
            ->where(['id'=>$goods_id])
            ->asArray()
            ->one();

        $detail['cart_number'] = (int) Cart::find()
            ->select('quantity')
            ->where(['member_id'=>$member_id,'goods_id'=>$goods_id])
            ->asArray()
            ->column()[0];
        $detail['is_favorite'] = (int) Favorite::find()
            ->select('quantity')
            ->where(['member_id'=>$member_id,'goods_id'=>$goods_id])
            ->asArray()
            ->column()[0];

        return $detail;
    }

    /**
     * 搜索商品
     * @param 不需要验证token
     * @author JF
     * @Date 2020-10-15
     * sort正序排序
     * status 1启用
     */

    public static function search($key,$member_id=0)
    {
        $goods = Goods::find()
            ->select('id as goods_id,goods_name,spec,unit,logo,slogo,sale_price')
            ->where(['like','goods_name',$key])
            ->andWhere(['status'=>1])
            ->asArray()
            ->orderBy('sort ASC')
            ->all();
        foreach($goods as $key => &$value){
            $value['cart_number'] = (int) Cart::find()
                ->select('quantity')
                ->where(['goods_id'=>$value['goods_id'],'member_id'=>$member_id])
                ->asArray()
                ->column()[0];
            $value['is_favorite'] = (int) Favorite::find()
                ->select('quantity')
                ->where(['goods_id'=>$value['goods_id'],'member_id'=>$member_id])
                ->asArray()
                ->column()[0];
        }
        return $goods;
    }

    /**
     * 添加购物车
     * @param
     * @author JF
     * @Date 2020-10-16
     * $number 操作数量
     * $type 1:增加1 or 2:减少1 or 0:未知(用户编辑商品数量)
     * $goods_id 商品ID
     * $member_id 操作用户ID
     */
    public static function EditShopCart($number=1,$type,$goods_id,$member_id)
    {
        $isset = Cart::find()
            ->where(['goods_id'=>$goods_id,'member_id'=>$member_id])
            ->one();
        $goods_sn = Goods::findOne($goods_id)['goods_sn'];
        $model = new Cart();
        $transaction = \Yii::$app->db->beginTransaction();
//        try {
//
//            $transaction->commit();
//        } catch (\Exception $e) {
//            $transaction->rollBack();
//        }
        switch ($type)
        {
            case 1://+1
                if($isset){
                    try {
                        $isset->quantity++;
                        $isset->save();
                        $transaction->commit();
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                    }
                    return json('已添加到购物车');
                }else{
                    try {
                        $model->member_id = $member_id;
                        $model->goods_id = $goods_id;
                        $model->goods_sn = $goods_sn;
                        $model->quantity = 1;
                        $model->add_time = time();
                        $model->insert();
                        $transaction->commit();
                    }catch (\Exception $e) {
                        $transaction->rollBack();
                    }

                    return json('已新增购物车项目');
                }
                break;
            case 2://-1
                if($isset){
                    $isset->quantity--;
                    if($isset->quantity < 0){
                        return fail('不得小于0');
                    }elseif($isset->quantity == 0){
                        try {
                            $isset->delete();
                            $transaction->commit();
                        } catch (\Exception $e) {
                            $transaction->rollBack();
                        }
                        return json('已删除购物车项目');
                    }else{
                        try {
                            $isset->save();
                            $transaction->commit();
                        } catch (\Exception $e) {
                            $transaction->rollBack();
                        }

                        return json('已减少购物车数量');
                    }
                }
                break;
            case 3://未知(用户编辑商品数量)

                if($isset){
                    if($number == 0){
                        try {
                            $isset->delete();
                            $transaction->commit();
                        } catch (\Exception $e) {
                            $transaction->rollBack();
                        }

                        return json('已清空该商品');
                    }else{
                        try {
                            $isset->quantity = $number;
                            $isset->save();
                            $transaction->commit();
                        } catch (\Exception $e) {
                            $transaction->rollBack();
                        }

                        return json('商品数量更改完成');
                    }

                }else{
                    if($number > 0){
                        try {
                            $model->member_id = $member_id;
                            $model->goods_id = $goods_id;
                            $model->goods_sn = $goods_sn;
                            $model->quantity = $number;
                            $model->add_time = time();
                            $model->insert();
                            $transaction->commit();
                        } catch (\Exception $e) {
                            $transaction->rollBack();
                        }

                    }
                    return json('已新增此商品购物车项目');

                }
                break;
            default:
                return fail('参数错误');
        }
    }

    /**
     * 收藏/取消收藏
     * @param
     * @author JF
     * @Date 2020-10-16
     * $goods_id 商品ID
     * $member_id 操作用户ID
     */
    public static function IsFavorite($goods_id,$member_id)
    {
        $isset = Favorite::find()
            ->where(['goods_id'=>$goods_id,'member_id'=>$member_id])
            ->one();

        $goods_sn = Goods::findOne($goods_id)['goods_sn'];
        $model = new Favorite();
        if($isset){//取消收藏
            $isset->delete();
        }else{
            $model->member_id = $member_id;
            $model->goods_id = $goods_id;
            $model->goods_sn = $goods_sn;
            $model->quantity = 1;
            $model->add_time = time();
            $model->insert();
        }

    }

    /**
     * 重写商品列表 获取商品种类
     * @param
     * @author JF
     * @Date 2020-10-19
     *
     * $member_id 操作用户ID
     */
    public static function getCateGory()
    {
        $first = Category::find()
            ->select('id as cate_id,name as cate_name')
            ->where(['pid'=>0,'status'=>1])
            ->asArray()
            ->orderBy('sort ASC')
            ->all();
        foreach($first as $key => &$value){
            $value['second_cate'] = Category::find()
                ->select('id as scate_id,name as scate_name')
                ->where(['pid'=>$value['cate_id'],'status'=>1])
                ->asArray()
                ->orderBy('sort DESC')
                ->all();
        }
        return success($first);
    }

    /**
     * 重写商品列表 获取商品列表&购物车数量&是否被收藏
     * @param
     * @author JF
     * @Date 2020-10-19
     * $scate_id 商品二级分类ID
     * $member_id 操作用户ID
     */
    public static function getGoodsList($cate_id,$scate_id,$member_id=0,$pages=1)
    {
        if($scate_id){
            $where = [
                'status' => 1,
                'scate_id' => $scate_id,
                'cate_id' => $cate_id,
            ];
        }else{
            $where = [
                'status' => 1,
                'cate_id' => $cate_id,
            ];
        }
        $page = $pages - 1 ? $pages - 1 : 0;

        $query = Goods::find()
            ->select('goods_name,scate_id,id as goods_id,spec,logo,slogo,sale_price,remark,unit,spec')
//            ->where(['status'=>1,'scate_id'=>$scate_id,'cate_id'=>$cate_id])
            ->where($where);
//            ->asArray()
//            ->all();
        $pagination = new Pagination([
            'defaultPageSize' => 10,
            'totalCount' => $query->count(),
            'page' => $page,
        ]);
        $goods = $query
            ->offset($pagination->offset)
            ->limit($pagination->limit)
            ->asArray()
            ->all();

        $favorite_goods_ids = Favorite::find()->select('goods_id')->where(['member_id'=>$member_id])->column();
        $cart_goods_ids = Cart::find()->select('goods_id')->where(['member_id'=>$member_id])->column();

        foreach($goods as $key => &$value){

            if(in_array($value['goods_id'],$favorite_goods_ids)){
                $value['is_favorite'] = 1;
            }else{
                $value['is_favorite'] = 0;
            }
            if(in_array($value['goods_id'],$cart_goods_ids)){
                $value['cart_number'] = (int) Cart::find()->select('quantity')->where(['member_id'=>$member_id,'goods_id'=>$value['goods_id']])->scalar();
            }else{
                $value['cart_number'] = 0;
            }

        }

//        $goods['totalCount'] = $query->count();

        return json_encode(['result' => 0, 'message' => 'Request successful', 'data' => $goods,'totalCount'=>$query->count()],JSON_UNESCAPED_UNICODE);
    }


    /**
     * 编辑购物车备注
     * @param
     * @author JF
     * @Date 2020-10-27
     * $cart_id 购物车ID
     * $remark 购物车备注
     */
    public static function editCartRemark($cart_id,$remark)
    {
        $cart = Cart::findOne($cart_id);
        $cart->remark = $remark;
        $cart->save();
        if(!$cart->save()){
            throw new \yii\base\UserException('添加失败,'.join($cart->getFirstErrors()), 400);
        }
        return success('编辑完成');


    }

}
