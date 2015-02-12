<?php

namespace thinkerg\IshtarGate\controllers;

use Yii;
use thinkerg\IshtarGate\models\LoginForm;
use yii\web\HttpException;

/**
 * 
 * @author Thinker_g
 * 
 * @property $module \thinkerg\IshtarGate\Module;
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
