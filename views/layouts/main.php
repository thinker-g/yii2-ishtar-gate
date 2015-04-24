<?php
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use thinker_g\IshtarGate\AppAsset;

/* @var \yii\web\View $this */
/* @var string $content */
/* @var \thinker_g\IshtarGate\Module $module */ 

$_module = $this->context->module;
$blockerRoute = $_module->blockerRoute;
if ($blockerRoute === []) {
    $blockerRoute[0] = '/' . $_module->id . '/gate/index';
} elseif (is_array($_module->blockerRoute)) {
    $blockerRoute[0] = '/' . $blockerRoute[0];
} else {
    $blockerRoute = ['/' . $blockerRoute];
}   
AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>

<?php $this->beginBody() ?>
    <div class="wrap">
        <?php
            NavBar::begin([
                'brandLabel' => 'My Company',
                'brandUrl' => Yii::$app->homeUrl,
                'options' => [
                    'class' => 'navbar-inverse navbar-fixed-top',
                ],
            ]);
            echo Nav::widget([
                'options' => ['class' => 'navbar-nav navbar-right'],
                'items' => [
                    ['label' => 'Preview blocker', 'url' => $blockerRoute],
                    $_module->isTesterAccess ?
                        ['label' => 'Sign out (' . Yii::$app->getSession()->get($_module->sessKey, 'none') . ')',
                        'url' => ['/' . $_module->id . '/gate/signout']
                        ] :
                        ['label' => 'Sign in', 'url' => ['/' . $_module->id . '/gate/signin']],
                ],
            ]);
            NavBar::end();
        ?>

        <div class="container">
            <?= Breadcrumbs::widget([
                'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
            ]) ?>
            <?= $content ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p class="pull-left">&copy; My Company <?= date('Y') ?></p>
            <p class="pull-right"><?= Yii::powered() ?></p>
        </div>
    </footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
