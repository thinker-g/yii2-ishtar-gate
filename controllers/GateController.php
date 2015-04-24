<?php
/**
 * @link https://github.com/thinker-g/yii2-ishtar-gate
 * @copyright Copyright (c) Thinker_g (Jiyan.guo@gmail.com)
 * @author Thinker_g
 * @license MIT
 */

namespace thinker_g\IshtarGate\controllers;

use Yii;
use thinker_g\IshtarGate\models\LoginForm;
use yii\web\HttpException;

/**
 * 
 * @author Thinker_g
 * 
 * @property $module \thinker_g\IshtarGate\Module;
 *
 */
class GateController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionSignin()
    {
        if (! $this->module->enabled) {
            throw new HttpException(403, 'The module must be ENABLED to access this action.');
        }
        $model = new LoginForm();
        $model->hashCallable = $this->module->hashCallable;

        if ($model->load(Yii::$app->getRequest()->post()) && $model->validate()) {
            if (array_key_exists($model->username, $this->module->credentials)
                && ($model->password == $this->module->credentials[$model->username])) {
                Yii::$app->getSession()->set($this->module->sessKey, $model->username);
                $this->goHome();
            } else {
                $model->addError('password', 'Invalid username or password.');
            }
        }
        return $this->render('signin', ['model' => $model]);

    }

    public function actionSignout()
    {
        Yii::$app->getSession()->remove($this->module->sessKey);
        $this->goHome();
    }
}
