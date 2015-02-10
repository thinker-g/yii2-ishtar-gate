<?php
    use yii\helpers\Url;
    use yii\helpers\Html;
/* @var $this yii\web\View */
 ?>
<div class="ishtar-default-index">
    <h1><?= $this->context->action->uniqueId ?></h1>
    <?php if ($this->context->module->isAlphaLogin):?>
    <p><?= Html::a('Sign out', Url::toRoute(['/' . $this->context->module->id . '/gate/signout']))?></p>
    <?php endif;?>
    <p>
        This is the view content for action "<?= $this->context->action->id ?>".
        The action belongs to the controller "<?= get_class($this->context) ?>"
        in the "<?= $this->context->module->id ?>" module.
    </p>
    <p>
        You may customize this page by editing the following file:<br>
        <code><?= __FILE__ ?></code><br />
    </p>
    <h3>
        <?=  $this->context->module->customField; ?>
    </h3>
</div>
