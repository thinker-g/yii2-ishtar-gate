<?php
namespace thinkerg\IshtarGate;

use Yii;
use yii\web\AssetBundle;

class INewsTickerAsset extends AssetBundle
{
    public $sourcePath = '@thinkerg/IshtarGate/assets';
    
    public $depends = [
        'yii\web\JqueryAsset'
    ];
    
    public function init()
    {
        parent::init();
        Yii::$app->getView()->registerJs('$(function(){});');
    }
}

?>