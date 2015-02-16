<?php
    use yii\helpers\Url;
    use yii\helpers\Html;
/* @var $this yii\web\View */
 ?>
<div class="ishtar-default-index">
    <h1><?= $this->context->module->name ?> <?= $this->context->module->version ?></h1>
    <?php if ($this->context->module->isAlphaLogin):?>
    <p><?= Html::a('Sign out ' . $this->context->module->name, Url::toRoute(['/' . $this->context->module->id . '/gate/signout']))?></p>
    <?php endif;?>
    <p>
        <?= $this->context->module->name?> is an yii2.0 extension provides enhanced maintenance mode with restricted access for internal tests.
    </p>
    <p>
        You may customize this page by editing the following file:<br>
        <code><?= __FILE__ ?></code><br />
    </p>
    <p>
        <strong>Custom message:</strong> <?=  $this->context->module->customField; ?>
    </p>
</div>
