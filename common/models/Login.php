<?php
/**
 * @author Harry Tang <harry@modernkernel.com>
 * @link https://modernkernel.com
 * @copyright Copyright (c) 2017 Modern Kernel
 */

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%core_login}}".
 *
 * @property string $email
 * @property string $token
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class Login extends ActiveRecord
{


    const STATUS_NEW = 10;
    const STATUS_USED = 20;

    public $remember = false;
    public $admin = false;


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
    public static function tableName()
    {
        return '{{%core_login}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email'], 'required'],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['email', 'token'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email' => Yii::t('app', 'Email'),
            'token' => Yii::t('app', 'Token'),
            'status' => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->token = Yii::$app->security->generateRandomString() . '_' . time();
            /* admin ?*/
            if ($this->admin) {
                $account = Account::findByEmail($this->email);
                if (!$account or !Yii::$app->authManager->checkAccess($account->id, 'admin')) {
                    return false;
                }

            }
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
        if ($insert) {
            /* find name */
            $name = $this->email;
            $account = Account::findByEmail($this->email);
            if ($account) {
                $name = $account->fullname;
                Yii::$app->language=$account->language;
            }

            /* send email */
            $expire = (int)Setting::getValue('tokenExpiryTime');
            $login['name'] = $name;
            $login['link'] = Yii::$app->urlManager->createAbsoluteUrl(['/site/login', 'email' => $this->email, 'token' => $this->token, 'remember' => $this->remember]);
            $login['min'] = $expire / 60;

            /* send email */
            $subject = Yii::t('app', 'Log in to {APP}', ['APP' => Yii::$app->name]);
            Yii::$app->mailer
                ->compose(
                    ['html' => 'login-email-html', 'text' => 'login-email-text'],
                    ['title' => $subject, 'login' => $login]
                )
                ->setFrom([Setting::getValue('outgoingMail') => Yii::$app->name])
                ->setTo($this->email)
                ->setSubject($subject)
                ->send();
        }
    }
}
