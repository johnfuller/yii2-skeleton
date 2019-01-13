<?php
/**
 * @author Harry Tang <harry@powerkernel.com>
 * @link https://powerkernel.com
 * @copyright Copyright (c) 2018 Power Kernel
 */

namespace common\models;

use common\behaviors\UTCDateTimeBehavior;
use common\Core;
use powerkernel\sms\components\AwsSMS;
use Yii;

/**
 * class CodeVerification
 *
 * @property string $identifier
 * @property string $code
 * @property integer $attempts
 * @property string $status
 * @property \MongoDB\BSON\UTCDateTime $created_at
 * @property \MongoDB\BSON\UTCDateTime $updated_at
 */
class CodeVerification extends \yii\mongodb\ActiveRecord
{
    const STATUS_NEW = 'STATUS_NEW';//10;
    const STATUS_USED = 'STATUS_USED';//20;

    public $captcha;

    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return 'core_code_verification';
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [
            '_id',
            'identifier',
            'code',
            'attempts',
            'status',
            'created_at',
            'updated_at',
        ];
    }

    /**
     * get id
     * @return \MongoDB\BSON\ObjectID|string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            UTCDateTimeBehavior::class,
        ];
    }

    /**
     * @return int timestamp
     */
    public function getUpdatedAt()
    {
        return $this->updated_at->toDateTime()->format('U');
    }

    /**
     * @return int timestamp
     */
    public function getCreatedAt()
    {
        return $this->created_at->toDateTime()->format('U');
    }


    /**
     * get status list
     * @param null $e
     * @return array
     */
    public static function getStatusOption($e = null)
    {
        $option = [
            self::STATUS_NEW => Yii::t('app', 'New'),
            self::STATUS_USED => Yii::t('app', 'Used'),
        ];
        if (is_array($e))
            foreach ($e as $i)
                unset($option[$i]);
        return $option;
    }

    /**
     * get status text
     * @return string
     */
    public function getStatusText()
    {
        $status = $this->status;
        $list = self::getStatusOption();
        if (!empty($status) && in_array($status, array_keys($list))) {
            return $list[$status];
        }
        return Yii::t('app', 'Unknown');
    }

    /**
     * get status color text
     * @return string
     */
    public function getStatusColorText()
    {
        $status = $this->status;
        $list = self::getStatusOption();

        $color = 'default';
        if ($status == self::STATUS_NEW) {
            $color = 'primary';
        }
        if ($status == self::STATUS_USED) {
            $color = 'danger';
        }

        if (!empty($status) && in_array($status, array_keys($list))) {
            return '<span class="label label-' . $color . '">' . $list[$status] . '</span>';
        }
        return '<span class="label label-' . $color . '">' . Yii::t('app', 'Unknown') . '</span>';
    }


    /**
     * @inheritdoc
     */
    public function rules()
    {
        /* identifier */
        if (!empty(\powerkernel\sms\models\Setting::getValue('aws_access_key') && !empty(\powerkernel\sms\models\Setting::getValue('aws_secret_key')))) {
            $identifier = [
                [['identifier'], 'match', 'pattern' => '/^(\+[1-9][0-9]{9,14})|([a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?)$/', 'message' => Yii::t('app', 'Email or phone number is invalid. Note that phone number should begin with a country prefix code.')],
            ];

        } else {
            $identifier = [
                [['identifier'], 'email'],
            ];
        }


        $default = [
            [['attempts'], 'default', 'value' => 0],

            [['identifier'], 'required'],
            [['code'], 'string', 'length' => 6],

            [['identifier'], 'trim'],
            [['code'], 'match', 'pattern' => '/^[0-9]{6}$/'],

            [['status'], 'string', 'max' => 20],
            [['created_at', 'updated_at'], 'yii\mongodb\validators\MongoDateValidator'],
            //['captcha', ReCaptchaValidator::class, 'message' => Yii::t('app', 'Prove you are NOT a robot')]
        ];

        return array_merge($default, $identifier);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {

        $default = [
            'code' => \Yii::t('app', 'Verification code'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];

        if (!empty(\powerkernel\sms\models\Setting::getValue('aws_access_key') && !empty(\powerkernel\sms\models\Setting::getValue('aws_secret_key')))) {
            $identifier = [
                'identifier' => \Yii::t('app', 'Email or phone number'),
            ];
        } else {
            $identifier = [
                'identifier' => \Yii::t('app', 'Email'),
            ];
        }

        return array_merge($default, $identifier);
    }

    /**
     * @inheritdoc
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->status = self::STATUS_NEW;
            /* demo account */
            if($this->identifier==Yii::$app->params['demo_account']){
                $this->code=Yii::$app->params['demo_pass'];
                return true;
            }
            /* not demo */
            $this->code = (string)rand(100000, 999999);
            if(Core::isLocalhost()){
                $this->code = '999999';
            }
            /* send code */
            if ($this->getType() == 'phone') {
                return $this->sendSMS();
            }
            if ($this->getType() == 'email') {
                return $this->sendEmail();
            }
            /* cannot send code ? */
            return false;
        }
        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }

    /**
     * @inheritdoc
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes); // TODO: Change the autogenerated stub
        if ($this->attempts >= 3) {
            $this->delete();
        }
    }

    /**
     * get identifier type
     * @return bool|string
     */
    public function getType()
    {
        $patterns = [
            'phone' => '/^\+[1-9][0-9]{9,14}$/',
            'email' => '/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/'
        ];
        foreach ($patterns as $type => $pattern) {
            if (preg_match($pattern, $this->identifier)) {
                return $type;
            }
        }
        return false;
    }

    /**
     * send SMS code
     * @return bool
     */
    protected function sendSMS()
    {
        return (new AwsSMS())->send(
            $this->identifier,
            Yii::t('app', '{APP}: Your verification code is {CODE}', ['CODE' => $this->code, 'APP' => Yii::$app->name]
            ));
    }

    /**
     * send email code
     * @return bool
     */
    protected function sendEmail()
    {
        $subject = Yii::t('app', 'Verification code for {APP}', ['APP' => Yii::$app->name]);
        return Yii::$app->mailer
            ->compose(
                ['html' => '@common/mail/code-verification-html', 'text' => '@common/mail/code-verification-text'],
                ['model' => $this]
            )
            ->setFrom([Setting::getValue('outgoingMail') => Yii::$app->name])
            ->setTo($this->identifier)
            ->setSubject($subject)
            ->send();
    }
}
