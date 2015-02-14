<?php

namespace thinkerg\IshtarGate\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $hashCallable;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
        ];
    }

    /**
     * (non-PHPdoc)
     * @see \yii\base\Model::afterValidate()
     */
    public function afterValidate()
    {
        parent::afterValidate();
        $this->password = call_user_func($this->hashCallable, $this->password);
    }

}
