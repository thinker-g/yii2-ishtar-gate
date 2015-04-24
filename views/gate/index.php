<?php
    use yii\helpers\Url;
    use yii\helpers\Html;
/* @var $this yii\web\View */
 ?>

<div class="ishtar-default-index" style="position: relative;">
    <h1><?= $this->context->module->name ?> <?= $this->context->module->version ?></h1>
    <?php if ($this->context->module->isAlphaLogin):?>
    <p><?= Html::a('Sign out from ' . $this->context->module->name, Url::toRoute(['signout']), ['class' => 'btn btn-primary btn-sm'])?></p>
    <?php endif;?>
    <p>
        <?= $this->context->module->name?> is an yii2.0 extension provides enhanced maintenance mode with restricted access for internal tests.
    </p>
    <p>
        You may customize the blocker page by setting up the option <code>blockRoute</code> to another route. E.g. <code>'blockerRoute' => ['site/about']</code>. <br>
        For further helps, please refer to the project page on <?= Html::a('Github', 'https://github.com/thinker-g/yii2-ishtar-gate', ['target' => '_blank']) ?>.
    </p>
    <p>
        <strong>Custom message:</strong> <?=  $this->context->module->customField; ?>
    </p>
</div>
