<?php
/**
 * @author Thinker_g
 * @version 1.0
 */
namespace thinkerg\IshtarGate;

use Yii;
use yii\base\Application;
use yii\base\Event;

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
 * @author Thinker_g
 *
 * @property bool $isAlphaLogin
 * @property bool $isPrivIP
 * @property bool $isTesterAccess
 *
 */
class Module extends \yii\base\Module
{

    /**
     * Event triggered after initialization, provides a point to re-setup attributes after the configuration array.
     * For example, while tester users need to be load other place than the config array. Handler bound on this event
     * can replace the attribute $credentials with another array of customized data.
     * @var unknown
     */
    const EVENT_POST_INIT = 'ishtarPostInit';

    /**
     * Event triggered before proform the actual block action.
     * The blocking can be by by either a redirection or the route set in \yii\web\Application::$catchAll.
     * @var string
     */
    const EVENT_BEFORE_BLOCK = 'ishtarBeforeBlock';

    /**
     *
     * @var string
     * @see \yii\base\Module::$controllerNamespace
     */
    public $controllerNamespace = 'thinkerg\IshtarGate\controllers';

    /**
     * @var string Module name.
     */
    private $name = 'Ishtar Gate';

    /**
     * @var string Module version.
     */
    private $version = 'v1.0';

    /**
     * If the site is in maintenance mode. Default to false.
     * @var bool
     */
    public $enabled = false;

    /**
     * Set to true to display the module version in bottom right of the screen,
     * while restricted user are logged in. <br />
     * So they can know they are in alpha test mode.
     * @var bool
     */
    public $tipVersion = true;

    /**
     * @var string Layout file to be used.
     */
    public $layout = '/main';

    /**
     * When alpha test mode is enabled, whether to logout public users. <br />
     * Default to false. <br />
     * If set to true, the attribute $siteLogoutRoute will be used as the route to logout users.
     * ATTENTION: the route \thinkerg\IshtarGate\Module::$siteLogoutRoute must accept GET request
     * to support this attribute, otherwise an exception might be thrown.
     *
     * @see \thinkerg\IshtarGate\Module::$siteLogoutRoute
     * @var bool
     */
    public $logoutPublic = false;

    /**
     * Route to logout public users. <br />
     * This will take effect when $logoutPublics is set to true. <br />
     * Overwrite this attribute if the site is not using the default logout path of yii framework.
     * @var array
     * @see \thinkerg\IshtarGate\Module::$logoutPublic
     */
    public $siteLogoutRoute = ['site/logout'];

    /**
     * Alpha test user credentials. Let these users pass.
     * @var array
    */
    public $credentials = ['tester' => 'f5d1278e8109edd94e1e4197e04873b9'];

    /**
     * Pivileged IPs, white list.
     * Requests from this IP will always get pass, regardless the user login.
     * @var array
     */
    public $privIps = [];

    /**
     * @var string
    */
    public $userStateKey = 'ishtar';

    /**
     * Don't block access on these routes.
     * Elements in the array should be STRING indicates the route of the module/controller/action.
     * No parameter is supported.
     * @var array
     */
    public $exceptRoutes = [];

    /**
     * If this array has elements, only listed actions will be blocked, $logoutPublic won't take effects.
     * Leave it empty and use $exceptRoutes if a whole site maintenance is taking place.
     * Elements in the array should be STRING indicates the route of the module/controller/action.
     * No parameter is supported.
     * @var array
     */
    public $onlyRoutes = [];

    /**
     * The route to the block controller and action.
     * Used as first parameter in \yii\web\helpers\Url::to();
     * @var array
     * @see \yii\web\Controller::redirect()
     */
    public $blockerRoute = [];

    /**
     * Set to true to redirect user to a static route.
     * Default to true, if set to false the \yii\web\Application::$catchAll will be used for processing all requests.
     * @var bool
     */
    public $useRedirection = false;

    /**
     * Error handler of Yii::$app. Will be added to except routes while initializing the module.
     * @var string
     */
    public $errHandlerRoute = 'site/error';

    /**
     * An array contains the messages for informing maintenance,
     * where the key is the deadline of displaying a message,
     * and its message is the news displayed.
     * @var array
     *
     * @tutorial
     *      Only the items in the array, whose key is "later" then current time
     *      will be displayed. If there's no upcoming messages, nothing happens.
     *      When there's messages to display, a news ticker will be displayed
     *      on the top of the page. <br />
     *
     *      When the module's isMaintaining is true. This will not run.
     *
     * @example
     *      [
     *          '2014-06-02 15:00:00' => 'Maintenance start at 2014-06-02 15:00:00',
     *      ];
     *
    */
    public $news = [];

    /**
     * Define the object, which will generate the news bar on the page. <br />
     * The "class" must be a subclass of UIComponent, and must be placed under directory "components". <br />
     * The run() method will be called to generate news bar. <br />
     * Set to <em>false</em> to disable it. <br />
     * @var array | bool
    */
    public $newsBar = ['class' => 'NewsBar'];

    /**
     * Custom attribute to store custom messages or some other things.
     * Can be used in the blocker route action's view.
     * @var mixed
    */
    public $customField = 'We\'ll return in a moment';

    /**
     * (non-PHPdoc)
     * @see \yii\base\Module::init()
     */
    public function init()
    {
        parent::init();
        if ($this->enabled) {
            // Initialize attributes
            empty($this->blockerRoute) && $this->blockerRoute = [$this->id];
            if (empty($this->onlyRoutes)) {
                // Positive blocking
                $errHandler = is_array($this->errHandlerRoute) ? $this->errHandlerRoute[0] : $this->errHandlerRoute;
                $blocker = is_array($this->blockerRoute) ? $this->blockerRoute[0] : $this->blockerRoute;
                $siteLogout = is_array($this->siteLogoutRoute) ? $this->siteLogoutRoute[0] : $this->siteLogoutRoute;
                array_push($this->exceptRoutes, $errHandler);
                array_push($this->exceptRoutes, $blocker);
                array_push($this->exceptRoutes, $siteLogout);

                $route = Yii::$app->getRequest()->resolve()[0];
                if (preg_match("#/?{$this->id}/?#", $route)) {
                    array_push($this->exceptRoutes, $route);
                }

                Yii::$app->on(Application::EVENT_BEFORE_REQUEST, [$this, 'positiveBlocking']);
            } else {
                Yii::$app->on(Application::EVENT_BEFORE_REQUEST, [$this, 'passiveBlocking']);
            }

            $this->trigger(self::EVENT_POST_INIT, new Event());

        } else {
            // news bar initialization
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

    public function getIsPrivIP()
    {
        $ip = Yii::$app->getRequest()->getUserIP();
        foreach ($this->privIps as $filter) {
            if ($filter === '*' || $filter === $ip || (($pos = strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos))) {
                return true;
            }
        }
        return false;
    }

    public function getIsAlphaLogin()
    {
        return false;
    }

    public function getIsTesterAccess()
    {
        return $this->isPrivIP || $this->isAlphaLogin;
    }

    public function positiveBlocking(Event $event)
    {
        if ($this->isTesterAccess)
            return;

        if (! in_array(Yii::$app->getRequest()->resolve()[0], $this->exceptRoutes)) {
            if ($this->logoutPublic && !Yii::$app->getUser()->isGuest) {
                Yii::$app->getResponse()->redirect($this->siteLogoutRoute);
                Yii::$app->end();
            }
            if ($this->useRedirection) {
                Yii::$app->getResponse()->redirect($this->blockerRoute);
            } else {
                Yii::$app->catchAll = $this->blockerRoute;
            }

        }

    }

    public function passiveBlocking(Event $event)
    {
        if ($this->isTesterAccess)
            return;
        if (in_array(Yii::$app->getRequest()->resolve()[0], $this->onlyRoutes)) {
            $this->blockAccess();
        }
    }

    protected function blockAccess()
    {
        $this->trigger(self::EVENT_BEFORE_BLOCK, new Event());
        if ($this->useRedirection) {
            Yii::$app->getResponse()->redirect($this->blockerRoute);
        } else {
            Yii::$app->catchAll = $this->blockerRoute;
        }
    }

}
