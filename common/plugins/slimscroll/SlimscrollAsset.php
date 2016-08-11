<?php
/**
 * @author Harry Tang <harry@modernkernel.com>
 * @link https://modernkernel.com
 * @copyright Copyright (c) 2016 Modern Kernel
 */

namespace common\plugins\slimscroll;


use yii\web\AssetBundle;

/**
 * Class SlimscrollAsset
 * @package common\plugins\slimscroll
 */
class SlimscrollAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/assets';

    public $js = [
        'jquery.slimscroll.min.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}