<?php

namespace app\models;

use Yii;
use yii\base\UserException;
use app\components\auth\LoginableTrait;
use app\components\auth\LoginableInterface;
/**
 * This is the model class for table "supplier".
 *
 * @property int $id
 * @property string $username 账号
 * @property string $password 密码
 * @property string $salt 密码加盐
 * @property string $code 供应商编码
 * @property string $name 供应商名称
 * @property int $level 供货商级别1、普通2、优质
 * @property string $booth 摊位
 * @property int $cate_id 分类ID
 * @property string|null $cate_name 分类名称
 * @property string $linkman 姓名
 * @property string $mobile1 电话1
 * @property string $mobile2 电话2
 * @property string $address 地址
 * @property string $remark 备注
 * @property int $buyer_id 第一采购
 * @property string|null $arrive_time 到货时间
 * @property string $account_period 账期
 * @property string $jindie_no 金蝶编码
 * @property string $bank_no 银行账号
 * @property string $bank_name 银行名称，开户行
 * @property string $company 公司名称
 * @property int $status 0、禁用1、启用
 * @property string $add_user 添加用户
 * @property int $add_time 添加时间
 * @property string $update_user 修改用户
 * @property int $update_time 修改时间
 */
class Supplier extends \yii\db\ActiveRecord
{
    use LoginableTrait;

    const STATUS_DELETE = 0;
    const STATUS_ACTIVE = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'supplier';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['salt'], 'required'],
            [['level', 'cate_id', 'buyer_id', 'status', 'add_time', 'update_time'], 'integer'],
            [['username'], 'string', 'max' => 11],
            [['password'], 'string', 'max' => 32],
            [['salt', 'code', 'cate_name', 'add_user', 'update_user'], 'string', 'max' => 10],
            [['name', 'bank_name'], 'string', 'max' => 30],
            [['booth', 'linkman', 'mobile1', 'mobile2', 'jindie_no', 'bank_no'], 'string', 'max' => 20],
            [['address', 'remark'], 'string', 'max' => 100],
            [['arrive_time'], 'string', 'max' => 15],
            [['company'], 'string', 'max' => 50],
        ];
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
            'code' => 'Code',
            'name' => 'Name',
            'level' => 'Level',
            'booth' => 'Booth',
            'cate_id' => 'Cate ID',
            'cate_name' => 'Cate Name',
            'linkman' => 'Linkman',
            'mobile1' => 'Mobile1',
            'mobile2' => 'Mobile2',
            'address' => 'Address',
            'remark' => 'Remark',
            'buyer_id' => 'Buyer ID',
            'arrive_time' => 'Arrive Time',
            'account_period' => 'Account Period',
            'jindie_no' => 'Jindie No',
            'bank_no' => 'Bank No',
            'bank_name' => 'Bank Name',
            'company' => 'Company',
            'status' => 'Status',
            'add_user' => 'Add User',
            'add_time' => 'Add Time',
            'update_user' => 'Update User',
            'update_time' => 'Update Time',
        ];
    }

    /*
  *  通过短信登录
  * */
    public static function loginBySms($username,$code,$event)
    {
        $obj = static::findOne(['username'=>$username]);
        if(empty($obj)){
            exit(fail('账号不存在,请核对账号或注册后再登录'));
//            throw new yii\base\UserException("账号不存在，请核对账号或注册后再登录", 400);
        }
        if($obj->validateCode($code,$event)){
            $validateStatus = static::validateStatus($obj);
            $token = $obj->login();

            return $token;
        }
//        throw new UserException('验证码错误', '400');
        exit(fail('验证码错误'));
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
     * 验证账号的状态
     * @author JF <qukaliujun@163.com>
     * @Date 2020-11-03
     */
    public static function validateStatus(Supplier $assistant)
    {
        if ($assistant->status == self::STATUS_DELETE) {
            throw new UserException('该账号已经停用，请联系管理员','-404');
        }
        return true;
    }
    /**
     *  通过账号密码登录
     * @author JF <qukaliujun@163.com>
     * @Date 2020-11-03
     */
    public static function loginByAccount($username,$password)
    {
        $obj = static::findOne(['username'=>$username]);

//        halt($obj);
        if(empty($obj)){
            exit(fail('账号不存在'));
        }
        $salt = $obj->salt;
        if($obj->validatePassword($password,$salt)){
            $validateStatus = static::validateStatus($obj);
            $token = $obj->login();
            return $token;
        }
        exit(fail('账号密码错误'));
    }

    /**
     * 校验账号密码
     */
    protected function validatePassword($password,$salt)
    {
        if(md5(md5($password).$salt)===$this->password){
            return true;
        }else{
            return false;
        }
    }
}
