<?php

namespace app\models;

use Prophecy\Prediction\CallbackPrediction;
use Yii;
use yii\base\UserException;
use app\components\auth\LoginableTrait;
use app\components\auth\LoginableInterface;
/**
 * This is the model class for table "member".
 *
 * @property int $id ID
 * @property string $username 客户账号
 * @property string $password 密码
 * @property string|null $salt 密码加盐
 * @property string|null $name 客户名称
 * @property string $linkman 联系人
 * @property string $mobile 客户电话
 * @property string $address 客户地址
 * @property string|null $reveive_start_time 收货时间
 * @property string|null $receive_end_time 收货时间
 * @property int|null $receive_sms 是否接受短信0、不接收1、接收
 * @property string|null $receive_mobile 接受短信的手机号
 * @property int $sale_id 销售ID
 * @property int $driver_id 默认配送司机
 * @property int|null $pickseat_id 默认分拣位
 * @property int|null $level 客户等级1、A2、B3、C4、D
 * @property int $type 客户类型 1、社会-普通2、社会-连锁3、社会-企事业4、招标-部队5、招标-机关6、招标-企事业
 * @property int $price_type 报价类型1、普通用户2、单品3、分类4、新发地5、北京6、岳各庄,7、创价网8、301医院9、中国餐饮网
 * @property int $price_option 报价设置0、昨天1、上周一......7、上周日
 * @property int $price_level 1、最低价2、平均价3、最高价
 * @property float $gross_rate 毛利率
 * @property int $scale 售价保留小数位数0、取整1、1位小数2、2位小数
 * @property int|null $print_remark 是否打印备注0、不打印1、打印
 * @property int|null $print_sendqty 是否打印发货数量为0、不打印1、打印
 * @property int $print_type 打印类型1、商品分类2、下单顺序
 * @property int|null $print_page 打印页数
 * @property int|null $print_style 打印样式
 * @property string|null $settle_method 结算方式
 * @property string $settle_period 结算周期
 * @property string|null $enterprise_type 企业类型 01：企业 02：机关执业单位 03：个人04：其他
 * @property string|null $jindie_no 金蝶编码
 * @property string|null $account_no 开户行及账号
 * @property string|null $taxpayer_no 纳税人识别号
 * @property string|null $invoice_title 发票抬头
 * @property string|null $invoice_address 发票地址
 * @property int|null $utm_source 用户来源 1、PC2、微信3、后台
 * @property string $remark 备注
 * @property int $status 会员状态1、开启2、锁定(不能登录)3、冻结(能登录不能下单)
 * @property int|null $stock_status 是否备货：0、不备货1、备货
 * @property int|null $is_vip 是否VIP客户：0、否1、是
 * @property int|null $is_merge 是否合并订单：0、否1、是
 * @property int|null $member_group_id 集团id
 * @property int|null $login_time 最后登录时间
 * @property int|null $add_time 添加时间
 * @property string|null $company 公司名称
 */
class Member extends \yii\db\ActiveRecord
{
    use LoginableTrait;

    const STATUS_DELETE = 2;
    const STATUS_ACTIVE = 1;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'member';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['receive_sms', 'sale_id', 'driver_id', 'pickseat_id', 'level', 'type', 'price_type', 'price_option', 'price_level', 'scale', 'print_remark', 'print_sendqty', 'print_type', 'print_page', 'print_style', 'utm_source', 'status', 'stock_status', 'is_vip', 'is_merge', 'member_group_id', 'login_time', 'add_time'], 'integer'],
            [['gross_rate'], 'number'],
            [['username', 'mobile', 'jindie_no'], 'string', 'max' => 20],
            [['password'], 'string', 'max' => 64],
            [['salt', 'linkman'], 'string', 'max' => 10],
            [['name'], 'string', 'max' => 50],
            [['address', 'invoice_address', 'remark'], 'string', 'max' => 100],
            [['receive_start_time', 'receive_end_time'], 'string', 'max' => 5],
            [['receive_mobile'], 'string', 'max' => 11],
            [['enterprise_type'], 'string', 'max' => 2],
            [['taxpayer_no', 'invoice_title'], 'string', 'max' => 30],
        ];
    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'password' => 'Password',
            'salt' => 'Salt',
            'name' => 'Name',
            'linkman' => 'Linkman',
            'mobile' => 'Mobile',
            'address' => 'Address',
            'reveive_start_time' => 'Reveive Start Time',
            'receive_end_time' => 'Receive End Time',
            'receive_sms' => 'Receive Sms',
            'receive_mobile' => 'Receive Mobile',
            'sale_id' => 'Sale ID',
            'driver_id' => 'Driver ID',
            'pickseat_id' => 'Pickseat ID',
            'level' => 'Level',
            'type' => 'Type',
            'price_type' => 'Price Type',
            'price_option' => 'Price Option',
            'price_level' => 'Price Level',
            'gross_rate' => 'Gross Rate',
            'scale' => 'Scale',
            'print_remark' => 'Print Remark',
            'print_sendqty' => 'Print Sendqty',
            'print_type' => 'Print Type',
            'print_page' => 'Print Page',
            'print_style' => 'Print Style',
            'settle_method' => 'Settle Method',
            'settle_period' => 'Settle Period',
            'enterprise_type' => 'Enterprise Type',
            'jindie_no' => 'Jindie No',
            'account_no' => 'Account No',
            'taxpayer_no' => 'Taxpayer No',
            'invoice_title' => 'Invoice Title',
            'invoice_address' => 'Invoice Address',
            'utm_source' => 'Utm Source',
            'remark' => 'Remark',
            'status' => 'Status',
            'stock_status' => 'Stock Status',
            'is_vip' => 'Is Vip',
            'is_merge' => 'Is Merge',
            'member_group_id' => 'Member Group ID',
            'login_time' => 'Login Time',
            'add_time' => 'Add Time',
            'company' => '公司名称'
        ];
    }



    /*
   *  通过短信登录
   * */
    public static function loginBySms($username,$code,$event)
    {
        $obj = static::findOne(['username'=>$username]);
        if(empty($obj)){
            throw new yii\base\UserException("账号不存在，请核对账号或注册后再登录", 400);
        }
        if($obj->validateCode($code,$event)){
            $validateStatus = static::validateStatus($obj);
            $token = $obj->login();

            return $token;
        }
        throw new UserException('验证码错误', '400');
    }

    /*
     * 忘记密码(用户已经登陆进系统)
     *
     * */
    public static function forgetPassword($member_id,$code,$event,$new_pass,$rnew_pass)
    {
        $obj = static::findOne($member_id);
        if($obj->validateCode($code,$event)){
            $validateStatus = static::validateStatus($obj);
            //修改密码
            $obj->password = md5($new_pass);
            $obj->save();
            return success('修改密码成功');
        }
        return fail('验证码错误');
    }

    protected function validateCode($code,$event)
    {

        $lasttime = time()-60*5;
//        $sms = Sms::find()->select('code')->where(['times'=>0])->andWhere(['createtime','gte',$lasttime])->orderBy('createtime DESC')->limit(1)->all();
        $sms = Sms::find()->select('code,event')
            ->where(['times'=>0])
            ->andWhere(['>','createtime',$lasttime])
            ->andWhere(['code'=>$code])
            ->andWhere(['event'=>$event])
            ->orderBy('createtime DESC')
            ->limit(1)->one();

        return !empty($sms)?true:false;
    }

    /**
     *  通过账号密码登录
     */
    public static function loginByAccount($username,$password)
    {

        $obj = static::findOne(['username'=>$username]);
//        halt($obj);
        if(empty($obj)){
            throw new yii\base\UserException("账号不存在，请核对账号或注册后再登录", 400);
        }
        if($obj->validatePassword($password)){
            $validateStatus = static::validateStatus($obj);
            $token = $obj->login();

            return $token;
        }
        throw new UserException('账号密码错误', '400');
    }

    /*
     *  注册
     * */
    public static function register($username,$password,$code,$linkman,$company,$event)
    {
        $obj = static::findOne(['username',$username]);
        $member = new Member();
        $isset = Member::find()->where(['username'=>$username])->one();
        if($isset){
            throw new \yii\base\UserException("账号已存在,请直接登录", 400);
        }

        if($member->validateCode($code,$event)){

            $member->username = $username;
            $member->password = md5($password);
            $member->linkman = $linkman;
            $member->name = $company;
            $result = $member->save();

            if($result === false){
                return $member->errors;
            }else{
                return success();
            }
        }
        throw new UserException('验证码错误', '400');
    }

    /**
     * 校验账号密码
     */
    protected function validatePassword($password)
    {
        if(md5($password)===$this->password){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 验证账号的状态
     * @author JF <qukaliujun@163.com>
     * @Date 2020-06-22
     */
    public static function validateStatus(Member $assistant)
    {
        if ($assistant->status == self::STATUS_DELETE) {
            throw new UserException('该账号已经停用，请联系管理员','-404');
        }
        return true;
    }
}
