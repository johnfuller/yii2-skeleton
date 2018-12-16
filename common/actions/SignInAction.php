<?php
/**
 * @author Harry Tang <harry@powerkernel.com>
 * @link https://powerkernel.com
 * @copyright Copyright (c) 2018 Power Kernel
 */

namespace common\actions;


use Yii;
use yii\base\Action;

/**
 * Class SignInAction
 * @package common\actions
 */
class SignInAction extends Action
{
    /**
     * run action
     */
    public function run()
    {
        if (Yii::$app->request->isAjax) {
            $model = new \common\models\CodeVerification();
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                if ($model->save()) {
                    return json_encode([
                        'vid' => (string)$model->id,
                        'message'=> Yii::t('app', 'A message with a 6-digit verification code was just sent to {IDENTIFIER}', ['IDENTIFIER'=>$model->identifier])
                    ]);
                }
            }
        }
    }
}
