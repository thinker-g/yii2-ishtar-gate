<?php
/**
 * @link https://github.com/thinker-g/yii2-ishtar-gate
 * @copyright Copyright (c) Thinker_g (Jiyan.guo@gmail.com)
 * @author Thinker_g
 * @license MIT
 */

namespace thinkerg\IshtarGate;

use Yii;
use yii\web\AssetBundle;
use yii\web\View;
/**
 * Asset bundle of jquery plugin "Inews ticker".
 * @author Thinker_g
 * @see https://github.com/progpars/inewsticker
 *
 */
class INewsTickerAsset extends AssetBundle
{
    /**
     * @var string overriding parament property.
     * @see \yii\web\AssetBundle::$sourcePath
     */
    public $sourcePath = '@thinkerg/IshtarGate/assets';

    /**
     * @var array overriding parent property.
     * @see \yii\web\AssetBundle::$js
     */
    public $js = [
        'inewsticker.js'
    ];

    /**
     * @var array overriding parent property.
     * @see \yii\web\AssetBundle::$css
     */
    public $css = [
        'inewsticker.css'
    ];

    /**
     * @var array overriding parent property.
     * @see \yii\web\AssetBundle::$css
     */
    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset'
    ];

    /**
     * @var string css classes added to newsbar container.
     */
    public $containerCssClass = 'inewsticker-container container';

    /**
     * @var string newsbar id of the HTML element.
     */
    public $tickerId = 'ishtar-inewsticker';

    /**
     * @var string  css classes added to news ticker element.
     */
    public $tickerCssClass = 'inewsticker text-center';

    /**
     * @var array options passed to the jquery plugin "inewsticker"
     * Any option supported by inewsticker can be set in this array.
     * @see https://github.com/progpars/inewsticker
     */
    public $pluginOptions = [
        'effect' => 'slide',
        'speed' => 3000,
        'dir' => 'ltr',
        'color' => '#fff',
        'font_size' => 13,
        'font_family' => 'arial',
        'delay_after' => 5000
    ];

    /* (non-PHPdoc)
     * @see \yii\web\AssetBundle::init()
     */
    public function init()
    {
        parent::init();
        Yii::$app->getView()->registerJs($this->getJs(), View::POS_READY);
    }

    /**
     * Js codes will be registered to view component.
     * @param \yii\web\View $view
     * @see \yii\web\AssetBundle::register()
     */
    protected function getJs()
    {
        $options = json_encode($this->pluginOptions);
        $html = $this->getHtml();
        return "
            ishtarNewsticker = {};

            ishtarNewsticker.holder = $('<div>').css({
                position: 'absolute',
                width: '100%',
                top: '0px',
                left: '0px'
            });

            ishtarNewsticker.container = $('<div>', {
                class: '{$this->containerCssClass} alert fade in'
            }).hide();

            ishtarNewsticker.tickerNode = $('<ul>', {
                id: '{$this->tickerId}',
                class: '{$this->tickerCssClass}'
            }).html('$html');

            $('<button>',{
                'class': 'close',
                'data-dismiss': 'alert'
            }).html('<span>&times;</span>').appendTo(ishtarNewsticker.container);

            ishtarNewsticker.container.on('close.bs.alert', function(){
                console.info('close ishtar news ticker');//
            });

            ishtarNewsticker.container.append(ishtarNewsticker.tickerNode);
            ishtarNewsticker.holder.append(ishtarNewsticker.container);
            $('body').prepend(ishtarNewsticker.holder);

            ishtarNewsticker.tickerNode.inewsticker({$options});
            setTimeout('ishtarNewsticker.container.fadeIn();',500);

        ";
    }

    /**
     * @return string html codes needed by the plugin.
     */
    protected function getHtml()
    {
        $html = '';
        foreach (Yii::$app->getView()->params['news'] as $ts => $news) {
            $html .= '<li>' . str_replace('{ts}', $ts, $news) . '</li>';
        }
        return $html;
    }

}

?>