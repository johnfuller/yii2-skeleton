<?php

use common\Core;
use common\models\PageData;
use powerkernel\slugify\Slugify;
use powerkernel\tinymce\TinyMce;
use yii\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $model common\models\PageData */
/* @var $page common\models\Page */

$this->title = Yii::t('app', 'Create Page');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Pages'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;


//$js = file_get_contents(__DIR__ . '/_form.min.js');
//$this->registerJs($js);
//$css=file_get_contents(__DIR__.'/create.css');
//$this->registerCss($css);
?>
<div class="page-create">

    <div class="box box-success">
        <div class="box-body">
            <div class="page-data-form">
                <?php $form = ActiveForm::begin(); ?>

                <?= $form->field($model, 'language')->dropDownList(\common\models\Message::getLocaleList()) ?>

                <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

                <?= $form->field($model, 'slug')->widget(Slugify::class,['source'=>'#pagedata-title']) ?>

                <?= $form->field($model, 'description')->textarea(['rows' => 3]) ?>

                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#tab_content" data-toggle="tab" aria-expanded="true"><?= $model->getAttributeLabel('content') ?></a></li>
                        <li class=""><a href="#tab_photo_uploader" data-toggle="tab" aria-expanded="false"><?= Yii::t('app', 'Photo Uploader') ?></a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab_content">
                            <?= $form->field($model, 'content')->widget(TinyMce::class) ?>
                        </div>
                        <!-- /.tab-pane -->
                        <div class="tab-pane" id="tab_photo_uploader">
                            <?= \common\widgets\FlickrUploadWidget::widget() ?>
                        </div>
                        <!-- /.tab-pane -->
                    </div>
                    <!-- /.tab-content -->
                </div>



                <?= $form->field($model, 'keywords')->textInput(['maxlength' => true]) ?>

                <?= $form->field($model, 'thumbnail')->textInput(['maxlength' => false, 'placeholder' => Yii::t('app', 'Must be at least 160x90 pixels and at most 1920x1080 pixels')]) ?>

                <?= $form->field($model, 'status')->dropDownList(PageData::getStatusOption()) ?>

                <?= $form->field($page, 'show_description')->dropDownList(Core::getYesNoOption()) ?>
                <?= $form->field($page, 'show_update_date')->dropDownList(Core::getYesNoOption()) ?>


                <div class="form-group">
                    <?= \common\components\SubmitButton::widget(['text'=>$model->scenario == 'create' ? Yii::t('app', 'Create') : Yii::t('app', 'Update'), 'options'=>['class' => $model->scenario == 'create' ? 'btn btn-success' : 'btn btn-primary']]) ?>
                </div>

                <?php ActiveForm::end(); ?>

            </div>

        </div>
    </div>
</div>


