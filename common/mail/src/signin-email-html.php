<?php

/* @var $this yii\web\View */
/* @var $model \common\models\SignIn */


?>
<div itemscope itemtype="http://schema.org/EmailMessage">
    <div itemprop="potentialAction" itemscope itemtype="http://schema.org/ViewAction">
        <meta itemprop="name" content="<?= Yii::t('app', 'Login') ?>"/>
    </div>
    <meta itemprop="description" content="<?= Yii::t('app', 'Login me in {APP}', ['APP' => Yii::$app->name]) ?>"/>
</div>

<table class="body-wrap">
    <tr>
        <td></td>
        <td class="container" width="600">
            <div class="content">
                <table class="main" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="content-wrap">

                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="content-block">
                                        <?= Yii::t('app', 'Hello,') ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="content-block">
                                        <?= Yii::t('app', 'Your login verification code is: {CODE}', ['CODE' => $model->code]) ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

            </div>

        </td>
        <td></td>
    </tr>
</table>
<link href="src/css/mailgun.css" media="all" rel="stylesheet" type="text/css"/>