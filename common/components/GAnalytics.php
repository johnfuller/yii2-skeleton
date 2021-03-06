<?php
/**
 * @author Harry Tang <harry@powerkernel.com>
 * @link https://powerkernel.com
 * @copyright Copyright (c) 2016 Power Kernel
 */
namespace common\components;


use common\models\Setting;
use Yii;
use yii\base\BaseObject;
use yii\web\View;

/**
 * Class GAnalytics
 * @package common\widgets
 */
class GAnalytics extends BaseObject
{

    public static function register()
    {
        $id = Setting::getValue('googleAnalytics');
        if (!empty($id)) {
            $js=<<<EOB
(function(i,s,o,g,r,a,m){i["GoogleAnalyticsObject"]=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,"script","https://www.google-analytics.com/analytics.js","ga");
ga("create", "{$id}", "auto");
ga("send", "pageview");
EOB;

            
            $view = Yii::$app->getView();
            $view->registerJs($js, View::POS_HEAD);
        }
    }
}