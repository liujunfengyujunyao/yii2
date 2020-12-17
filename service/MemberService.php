<?php
namespace app\service;
use app\models\Cart;
use app\models\Favorite;
use app\models\Member;
use app\models\Goods;
use yii\base\UserException;
use app\models\Category;

/**
 * 用户逻辑层.
 * User: Administrator
 * Date: 2020/10/15
 * Time: 9:31
 */
class MemberService extends BaseService
{
    /**
     * 获取用户信息
     * @param
     * @author JF
     * @Date 2020-10-16
     */
    public static function getMemberInfo($member_id)
    {
        $info = Member::find()
            ->select('id,username,name,address,linkman,receive_start_time,receive_end_time,mobile,head')
            ->where(['id'=>$member_id])
            ->asArray()
            ->one();
        //购物车banner显示条数  (优化:需要判断商品商品是否被下架  banner要-被下架SUM)
        $info['cart_banner'] = (int) Cart::find()
            ->select("SUM(quantity) as count")
            ->where(['member_id'=>$member_id])
            ->asArray()
            ->all()[0]['count'];
        return $info;
    }

    /**
     * 收藏夹列表
     * @param
     * @author JF
     * @Date 2020-10-16
     */
    public static function getFavoriteList($member_id)
    {
        $favorite = Favorite::find()
            ->select('goods_id')
            ->where(['member_id'=>$member_id])
            ->asArray()
            ->all();
        $result = [];
        foreach($favorite as $key => $value){
            $result[$key] = Goods::find()
                ->select('id as goods_id,goods_name,spec,unit,logo,slogo,sale_price,remark')
                ->where(['id'=>$value['goods_id'],'status'=>1])
                ->asArray()
                ->one();
            $result[$key]['cart_number'] = Cart::find()
                ->select('quantity')
                ->where(['member_id'=>$member_id,'goods_id'=>$value['goods_id']])
                ->asArray()
                ->one()['quantity'];
        }
        return $result;
    }

    /**
     * 购物车列表
     * @param
     * @author JF
     * @Date 2020-10-16
     * 优化方向  在model层封装输入goods_id查询出详情,是否被收藏,购物车中该商品有多少的function
     */

    public static function getShopCartList($member_id)
    {
        $shop_cart = Cart::find()
            ->select('id,goods_id,quantity,remark')
            ->where(['member_id'=>$member_id])
            ->asArray()
            ->all();
        $result = [];
        foreach($shop_cart as $key => $value){
            $result[$key] = Goods::find()
                ->select('id as goods_id,goods_name,spec,unit,logo,slogo,sale_price,remark')
                ->where(['id'=>$value['goods_id'],'status'=>1])
                ->asArray()
                ->one();
            $result[$key]['is_favorite'] = Favorite::find()
                ->select('id')
                ->where(['member_id'=>$member_id,'goods_id'=>$value['goods_id']])
                ->asArray()
                ->one()?1:0;
            $result[$key]['id'] = $value['id'];//用于删除购物车
            $result[$key]['cart_number'] = (int) $value['quantity'];
            $result[$key]['remark'] = $value['remark'];
        }
        return $result;
    }

    /**
     * 批量删除购物车列表
     * @param
     * @author JF
     * @Date 2020-10-16
     *
     */
    public static function delShopCart($ids,$member_id)
    {
        $ids = explode(',',$ids);
//        foreach($ids as $key => $value){
//            Cart::findOne($value)->delete();
//        }
        Cart::deleteAll(['and','member_id = :member_id',['in','id',$ids]],[':member_id'=>$member_id]);
    }

    /**
     * 修改用户信息
     * @param
     * @author JF
     * @Date 2020-10-22
     *
     */
    public static function UpdateUserInfo($member_id,$linkman,$mobile,$receive_start_time,$receive_end_time)
    {
        $info = Member::find()
            ->where(['id'=>$member_id])
            ->one();
//        halt($info);
        $info->linkman = $linkman;
        $info->mobile = $mobile;
        $info->receive_start_time = $receive_start_time;
        $info->receive_end_time = $receive_end_time;
        $info->save();
        return success('编辑完成');


    }

    /**
     * 修改密码
     * @param
     * @author JF
     * @Date 2020-10-22
     *
     */
    public static function UpdatePassword($member_id,$old_pass,$new_pass,$rnew_pass)
    {
        if($new_pass != $rnew_pass){
            return fail('两次新密码不一致');
        }
        $member = Member::find()
            ->where(['id'=>$member_id])
            ->one();
        if(md5($old_pass) != $member->password){
            return fail('密码错误');
        }else{
            $member->password = md5($new_pass);
            $member->save();
            return success('修改完成');
        }

    }

}
