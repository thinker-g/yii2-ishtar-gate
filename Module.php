<?php
/**
 * @link https://github.com/thinker-g/yii2-ishtar-gate
 * @copyright Copyright (c) Thinker_g (Jiyan.guo@gmail.com)
 * @author Thinker_g
 * @license MIT
 * @since v1.0.0
 */
namespace thinkerg\IshtarGate;

use Yii;
use yii\web\Application;
use yii\base\Event;
use yii\web\View;

/**
 * Ishtar Gate is a new version of Alpha Portal Module developed based on Yii 2.0.
 * It provides maintenance mode (aka. alpha test) on site level or route level.
 * The mode can be switched on or off by setting up a single boolean property.
 * While maintenance mode is enabled, all public access will be blocked and be redirected
 * to a static page. Only permitted users (generally would be developers or testers of the site), can login and
 * finish their test or deployment tasks. A login action will be provided for alpha test login.
 * While it's not be enabled, one or more messages can be setup and displayed on all site pages,
 * to give a tip for an upcoming planned maintenance.
 * 
 * Blocking modes:
 * 2 blocking modes are supported: "positive blocking" and "passive blocking".
 * In "Positive blocking" mode, all accesses except certain ones are blocked;
 * In "Passive blocking" mode, only certain routes will be blocked, all other places are accessable for anybody.
 * To use these 2 mode, please see $exceptRoute , $onlyRoutes for instructions.
 *
 * @since v1.0.0
 *
 * @property bool $isAlphaLogin
 * @property bool $isPrivIP
 * @property bool $isTesterAccess
 *
 */
class Module extends \yii\base\Module
{

    /**
     * @var string Event triggered before proform the actual block action.
     * The blocking can be performed by either a redirection or the route set in \yii\web\Application::$catchAll.
     */
    const EVENT_BEFORE_BLOCK = 'ishtarBeforeBlock';

    /**
     *
     * @var string
     * @see \yii\base\Module::$controllerNamespace
     */
    public $controllerNamespace = 'thinkerg\IshtarGate\controllers';

    /**
     * @see \yii\base\Module::$defaultRoute.
     * @var array
     */
    public $defaultRoute = 'gate/index';

    /**
     * @var string Module name.
     */
    private $name = 'Ishtar Gate';

    /**
     * @var string Module version.
     */
    private $version = 'v1.0.1';

    /**
     * @var bool If the site is in maintenance mode. Default to false.
     */
    public $enabled = false;

    /**
     * @var bool set to true to display the module name and version in bottom right of the screen,
     * while restricted user are logged in. So they can know they are in alpha test mode.
     * This will only take effect while module is "enabled".
     */
    public $tipVersion = true;

    /**
     * @var string Layout file to be used.
     * @see \yii\base\Module::$layout
     */
    public $layout = 'main';

    /**
     * @var bool When the module is enabled, whether to logout public users.
     * Default to false, \Yii::$app->user->logout() will be invoked to logout current user.
     * If set to true, and $siteLogoutRoute is null,
     * the attribute $siteLogoutRoute will be used as the route to logout users.
     * ATTENTION: the route \thinkerg\IshtarGate\Module::$siteLogoutRoute must accept GET request
     * to support this attribute, otherwise an exception might be thrown.
     *
     * @see \thinkerg\IshtarGate\Module::$siteLogoutRoute
     * 
     */
    public $logoutPublic = false;

    /**
     * @var array Route to logout public users.
     * This takes effect only when $logoutPublic is set to true.
     * Leave it as "null" or an empty array, system will use Yii::$app->getUser()->logout() to logout current user.
     * Otherwise user will be redirected to the specified route, and the destination route must allow GET request.
     * @see \thinkerg\IshtarGate\Module::$logoutPublic
     */
    public $siteLogoutRoute = ['site/logout'];

    /**
     * @var array Alpha test user credentials. Let these users pass. 
     * The array keys are username, and values are corresponding HASHED password.
     * The hash method is set in $hashCallable.
     */
    public $credentials = ['tester' => 'tester'];

    /**
     * @var array Pivileged IPs, white list, widecast such as '192.168.1.*' are supported to allow IP ranges.
     * Requests from this IP will always get pass, regardless the alpha test user credentials.
     */
    public $privIPs = [];

    /**
     * @var string Session key used to store user identity in session.
     * This will be used to test if current user is logged in as an alpha test user.
     */
    public $sessKey = 'ishtar';

    /**
     * @var array Don't block access on these routes.
     * Elements in the array should be STRINGs, without the leading slash "/",
     * indicate the routes of the module/controller/action.
     * No parameter is supported.
     */
    public $exceptRoutes = [];

    /**
     * @var array If this array has elements, only listed actions will be blocked, and $logoutPublic won't take effects.
     * Leave it empty and use $exceptRoutes if a positive blocking is taking place.
     * Elements in the array should be STRINGs, without the leading slash "/",
     * indicate the routes of the module/controller/action.
     * No parameter is supported.
     */
    public $onlyRoutes = [];

    /**
     * @var array The route to the block controller and action.
     * Used as first parameter in \yii\web\helpers\Url::to();
     * @see \yii\web\Controller::redirect()
     */
    public $blockerRoute = [];

    /**
     * @var bool Set to true to redirect user to a static route.
     * Default to false, the \yii\web\Application::$catchAll will be used for processing all requests;
     * if set to true, the user will be redirected to the route set in \thinkerg\IshtarGate\Module::$blockerRoute.
     */
    public $useRedirection = false;

    /**
     * @var string Error handler of Yii::$app. Will be added to except routes while initializing the module.
     * This parameter only need to be setup while the default error handler of the application is changed.
     */
    public $errActionRoute = 'site/error';

    /**
     * @var mixed Callback to hash the password while authencating users.
     * Default to thinkerg\IshtarGate\Module::dummyHash
     * The signature of the called function should take 1 parameter to receive the inputted password,
     * and return the hashed string. The returned string will then be used to compare to "values" set in $credentials.
     */
    public $hashCallable = 'thinkerg\IshtarGate\Module::dummyHash';

    /**
     * @var array An array contains the messages for informing maintenance,
     * where the key is the deadline of displaying a message,
     * and its value is the news displayed.
     * The "keys" can be any values accepted by strtotime().
     * In the message, you could use token "{ts}" (without the ") to display its key (time).
     * This array can be retrieved by calling attribute \Yii::$app->getView()->param['news'] in other places
     * for further customizaitons.
     *
     * @tutorial
     *      Only the items in the array, whose key is "later" than current time
     *      will be displayed. If there's no upcoming messages, nothing happens.
     *      When there's messages to display, a news ticker will be displayed
     *      on the top of the page. <br />
     *
     *      When the module is enabled. This will not run.
     *
     * @example
     *     setting:
     *     [
     *         '2014-06-02 15:00:00' => 'Maintenance start at {ts}',
     *     ];
     *     invoking in view:
     *     $ishtarNews = \Yii::$app->getView()->param['news'];
     *     var_dump($ishtarNews);
     *
     */
    public $news = [];

    /**
     * @var string The AssetBundle of the newsticker class.
     * The class must be subclass of \yii\web\AssetBundle.
     * The module will register this automatically to current view component.
     */
    public $newsTicker = 'thinkerg\IshtarGate\INewsTickerAsset';

    /**
     * @var mixed Custom attribute to store custom messages or some other things.
     * Can be used in the blocker route action's view.
     */
    public $customField = 'System is down for maintenance. We\'ll return in a moment';

    /**
     * (non-PHPdoc)
     * @see \yii\base\Module::init()
     */
    public function init()
    {
        parent::init();

        if ($this->enabled) {
            // Initialize attributes
            empty($this->blockerRoute) && ($this->blockerRoute = [$this->id . '/' . $this->defaultRoute]);

            if (empty($this->onlyRoutes)) {
                // Positive blocking
                // Except current route if user is accessing this module
                $route = Yii::$app->getRequest()->resolve();
                if (preg_match("#/?{$this->id}/?#", $route[0])) {
                    array_push($this->exceptRoutes, $route[0]);
                }

                is_string($this->blockerRoute) && ($this->blockerRoute = [$this->blockerRoute]);
                is_string($this->siteLogoutRoute) && ($this->siteLogoutRoute = [$this->siteLogoutRoute]);

                array_push($this->exceptRoutes, $this->errActionRoute);
                array_push($this->exceptRoutes, $this->blockerRoute[0]);
                array_push($this->siteLogoutRoute, $this->siteLogoutRoute[0]);

                Yii::$app->on(Application::EVENT_BEFORE_REQUEST, [$this, 'positiveBlocking']);
            } else {
                Yii::$app->on(Application::EVENT_BEFORE_REQUEST, [$this, 'passiveBlocking']);
            }

            $this->tipVersion && $this->isTesterAccess && $this->tipVersion();

        } else {
            // news bar initialization
            empty($this->news) || $this->loadNewsTicker();
        }

    }

    /**
     * @return string Module name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string Module version.
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get whether the accessing IP is from a premitted address range, where all accesses will be allowed.
     * @return boolean
     */
    public function getIsPrivIP()
    {
        $ip = Yii::$app->getRequest()->getUserIP();
        foreach ($this->privIPs as $filter) {
            if ($filter === '*' || $filter === $ip || (($pos = strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get whether current user is logged in via ishtar sign-in portal.
     * @return boolean
     */
    public function getIsAlphaLogin()
    {
        return Yii::$app->getSession()->has($this->sessKey);
    }

    /**
     * Get whether user is accessing from a privileged IP address, or logged in as an internal tester.
     * It's a quick way to check if current access should be blocked or not.
     * @return boolean
     */
    public function getIsTesterAccess()
    {
        return $this->isPrivIP || $this->isAlphaLogin;
    }

    /**
     * Positive blocker, which blocks all accesses except certain routes.
     * This function is an event handler bound to the event \yii\base\Application::EVENT_BEFORE_REQUEST.
     * @param Event $event \yii\base\Application::EVENT_BEFORE_REQUEST
     */
    public function positiveBlocking(Event $event)
    {
        if ($this->isTesterAccess)
            return;

        if (! in_array(Yii::$app->getRequest()->resolve()[0], $this->exceptRoutes)) {
            if ($this->logoutPublic && !Yii::$app->getUser()->isGuest) {
                if (empty($this->siteLogoutRoute)) {
                    Yii::$app->getUser()->logout();
                    Yii::$app->getResponse()->redirect(Yii::$app->getHomeUrl());
                } else {
                    Yii::$app->getResponse()->redirect($this->siteLogoutRoute);
                }
                Yii::$app->end();
            }
            $this->blockAccess();
        }

    }

    /**
     * Passive blocker, which blocks accesses to certain routes.
     * @param Event $event \yii\base\Application::EVENT_BEFORE_REQUEST
     */
    public function passiveBlocking(Event $event)
    {
        if ($this->isTesterAccess)
            return;
        if (in_array(Yii::$app->getRequest()->resolve()[0], $this->onlyRoutes)) {
            $this->blockAccess();
        }
    }

    /**
     * The actual blocking operation.
     * This function is invoked by Module::passiveBlocking() or Module::positiveBlocking(), to perform the block
     * by redirection or by changing the request route (depends on setting of attribute $useRedirection).
     * Event EVENT_BEFORE_BLOCK will be triggered.
     */
    protected function blockAccess()
    {
        $this->trigger(self::EVENT_BEFORE_BLOCK);
        if ($this->useRedirection) {
            Yii::$app->getResponse()->redirect($this->blockerRoute);
        } else {
            Yii::$app->catchAll = $this->blockerRoute;
        }
    }

    /**
     * Load news keeper to current View object.
     * The loaded "newsticker" object must be inherited from yii\web\AssetBundle.
     * And the "newsticker"'s register() method will be invoked right after the object is initialized.
     * The news array of this Module object will be firstly cleaned up,
     * and then be saved in view's "params" attribute, with key "news".
     */
    protected function loadNewsTicker()
    {
        $now = time();
        // Remove expired news
        foreach ($this->news as $ts => $news) {
            if(strtotime($ts) < $now) {
                unset($this->news[$ts]);
            }
        }
        if (!empty($this->news)) {
            Yii::$app->getView()->params['news'] = $this->news;
            if (is_array($this->newsTicker)) {
                $class = $this->newsTicker['class'];
                // if the bundle options has been explicitly set in bundles of assetManager, dont copy. 
                if (!isset(Yii::$app->getAssetManager()->bundles[$class])) {
                    unset($this->newsTicker['class']);
                    Yii::$app->getAssetManager()->bundles[$class] = $this->newsTicker;
                }
                call_user_func($class . "::register", Yii::$app->getView());
            } else {
                call_user_func($this->newsTicker . "::register", Yii::$app->getView());
            }

        } // else { // all news have expired}

    }

    /**
     * Prompt version number on web page.
     */
    protected function tipVersion()
    {
        $view = Yii::$app->getView();
        $verInfo = $this->name . ' ' . $this->version;
        $view->registerCss('
            .ishtar-version-tip {
                position: fixed;
                bottom: 0;
                width: 100%;
                box-sizing: border-box;
                padding: 0 0.5rem;
                text-align: right;
            }
        ');
        $view->registerJs("
            ishtarVerNode = document.createElement('div');
            ishtarVerNode.innerHTML = '{$verInfo}';
            ishtarVerNode.className = 'ishtar-version-tip';
            document.body.appendChild(ishtarVerNode);
        ", View::POS_READY);
    }

    /**
     * Example password hashing method, returns the password without doing anything.
     * @param string $password
     * @return string
     */
    public static function dummyHash($password)
    {
        return $password;
    }

}
