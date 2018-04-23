<?php
/**
 * @author Harry Tang <harry@powerkernel.com>
 * @link https://powerkernel.com
 * @copyright Copyright (c) 2017 Power Kernel
 */


namespace common\components;


use Yii;
use yii\bootstrap\Html;
use yii\bootstrap\Widget;

/**
 * Class SubmitButton
 * @package common\components
 */
class SubmitButton extends Widget
{

    public $text='Submit';
    public $hiddenClass='hidden';
    public $options;

    public function init()
    {
        parent::init();
        if(!empty($this->options['class'])){
            $this->options['class']='pds '.$this->options['class'];
        }
        else {
            $this->options['class']='pds';
        }

        $this->registerJs();

    }

    /**
     * run
     */
    public function run()
    {
        parent::run(); // TODO: Change the autogenerated stub
        echo Html::submitButton(
            \powerkernel\fontawesome\Icon::widget(['prefix'=>'fas', 'name'=>'sync-alt', 'styling'=>'fa-spin', 'options'=>['class'=>$this->hiddenClass]]).'<span>'.$this->text.'</span>',
            $this->options
        );
    }

    /**
     * register js
     */
    protected function registerJs(){
        $js=<<<EOB
var form = $("button.pds").parents("form:first");form.on("beforeSubmit", function(event){if(jQuery(this).data("submitting")) {event.preventDefault();return false;} jQuery(this).data("submitting", true); $(this).find(":submit").find("i,svg").removeClass("{$this->hiddenClass}"); $(this).find(":submit").find("span").addClass("{$this->hiddenClass}");$(this).find(":submit").attr("disabled", "disabled");return true;});
EOB;


        $view = Yii::$app->getView();
        $view->registerJs($js);
    }
}