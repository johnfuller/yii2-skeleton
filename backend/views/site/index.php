<?php

/* @var $this yii\web\View */
use powerkernel\fontawesome\Icon;

/* @var $urls [] */
/* @var $favicon [] */
/* @var $v [] */
/* @var $newVersion boolean */


$this->title = Yii::t('app', 'Dashboard');
//$this->params['subtitle']='v1.0';
//$this->params['breadcrumbs'][] = ['label' => 'Home', 'url' => '#home'];
?>
<div class="site-index">
    <div class="row">
        <div class="col-sm-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('app', 'Application Information') ?></h3>
                </div>
                <div class="box-body no-padding">
                    <div class="table-responsive">
                        <table class="table no-margin">
                            <tr>
                                <th>Name</th>
                                <td><?= Yii::$app->name ?></td>
                            </tr>
                            <tr>
                                <th>Version</th>
                                <td>
                                    Yii2 Skeleton v<?= $v['version'] ?> build <?= $v['build'] ?>
                                    <?php if($newVersion):?>
                                        <br />
                                        <a href="https://github.com/powerkernel/yii2-skeleton" target="_blank" class="label label-warning"><?= Yii::t('app', 'New version available') ?></a>
                                    <?php endif;?>
                                </td>
                            </tr>
                            <tr>
                                <th>Release date</th>
                                <td><?= Yii::$app->formatter->asDate($v['date']) ?></td>
                            </tr>


                            <tr>
                                <th>Developer</th>
                                <td>Harry Tang (harry@powerkernel.com)</td>
                            </tr>
                            <tr>
                                <th>Powered by</th>
                                <td>powerkernel.com</td>
                            </tr>
                            <tr>
                                <th>Support</th>
                                <td>powerkernel.com</td>
                            </tr>


                        </table>
                    </div>

                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><?= Yii::t('app', 'System Information') ?></h3>
                </div>
                <div class="box-body no-padding">
                    <div class="table-responsive">
                        <table class="table no-margin">
                            <tr>
                                <th>Operating System</th>
                                <td><?= php_uname() ?></td>
                            </tr>
                            <tr>
                                <th>Software</th>
                                <td><?= $_SERVER['SERVER_SOFTWARE'] ?></td>
                            </tr>
                            <tr>
                                <th>Database</th>
                                <td>
                                    <?php if(Yii::$app->has('db')):?>
                                        <?= Yii::$app->db->driverName=='mysql'?'MySQL':Yii::$app->db->driverName ?>
                                    <?php endif;?>
                                    <?php if(Yii::$app->has('mongodb')):?>
                                        MongoDB
                                    <?php endif;?>

                                </td>
                            </tr>
                            
                            <tr>
                                <th>PHP Version</th>
                                <td><?= \yii\helpers\Html::a(PHP_VERSION, Yii::$app->urlManager->createUrl(['site/php']), ['target' => '_blank']) ?></td>
                            </tr>                            

                            <tr>
                                <th>Yii Version</th>
                                <td><?= Yii::getVersion() ?></td>
                            </tr>


                            <tr>
                                <th>Environment</th>
                                <td><?= YII_ENV ?></td>
                            </tr>
                            <tr>
                                <th>Debug Mode</th>
                                <td><?= Yii::$app->formatter->asBoolean(YII_DEBUG) ?></td>
                            </tr>


                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Yii::t('app', 'Images files verification') ?></h3>
        </div>
        <div class="box-body padding">
            <?php foreach ($favicon as $icon): ?>
                <div>
                    <?php if($icon['exist']):?>
                        <code class="text-green"><?= $icon['file'] ?></code>
                        <?= Icon::widget(['prefix'=>'fas', 'name'=>'check', 'options'=>['class'=>'text-green']]) ?>
                    <?php else:?>
                        <code class="text-red"><?= $icon['file'] ?></code>
                        <?= Icon::widget(['prefix'=>'fas', 'name'=>'exclamation-triangle', 'options'=>['class'=>'text-red']]) ?>
                    <?php endif;?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
