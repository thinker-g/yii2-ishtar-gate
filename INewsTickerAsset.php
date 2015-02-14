<?php
namespace thinkerg\IshtarGate;

use Yii;
use yii\web\AssetBundle;
use yii\web\View;

class INewsTickerAsset extends AssetBundle
{
    public $sourcePath = '@thinkerg/IshtarGate/assets';

    public $js = [
        'inewsticker.js'
    ];

    public $css = [
        'inewsticker.css'
    ];

    public $depends = [
        'yii\web\JqueryAsset',
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset'
    ];

    public $containerCssClass = 'inewsticker-container container';

    public $tickerId = 'ishtar-inewsticker';

    public $tickerCssClass = 'inewsticker text-center';

    public $pluginOptions = [
        'effect' => 'typing',
        'speed' => 100,
        'dir' => 'ltr',
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
     * 
     * @param \yii\web\View $view
     * @see \yii\web\AssetBundle::register()
     */
    protected function getJs()
    {
        $options = json_encode($this->pluginOptions);
        $html = $this->getHtml();
        return "
            var tickerContainer = $('<div>', {
                class: '{$this->containerCssClass} alert fade in'
            }).hide();
            var tickerNode = $('<ul>', {
                id: '{$this->tickerId}',
                class: '{$this->tickerCssClass}'
            }).html('$html');

            $('<button>',{
                'class': 'close',
                'data-dismiss': 'alert'
            }).html('<span>&times;</span>').appendTo(tickerContainer);
            tickerContainer.on('close.bs.alert', function(){
                //
            });
            tickerContainer.append(tickerNode);
            $('body').prepend(tickerContainer);

            $('#{$this->tickerId}').inewsticker({$options});
            tickerContainer.show();
        ";
    }

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