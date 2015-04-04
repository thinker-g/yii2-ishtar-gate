<?php
/**
 * @link https://github.com/thinker-g/yii2-ishtar-gate
 * @copyright Copyright (c) Thinker_g (Jiyan.guo@gmail.com)
 * @author Thinker_g
 * @license MIT
 * 
 * This file return a sample configuration array with all supported options and their default values.
 * You may use this as a template to customize your module in application's config array.
 * 
 */

return [
    'class' => 'thinker_g\IshtarGate\Module',

    'enabled' => false,

    'blockerRoute' => [],
    'useRedirection' => false,
    'layout' => 'main',

    'exceptRoutes' => [],
    'onlyRoutes' => [],
    'tipVersion' => true,

    'privIPs' => [],
    'credentials' => ['tester' => 'tester'],
    'hashCallable' => 'thinker_g\IshtarGate\Module::dummyHash',

    'logoutPublic' => false,
    'siteLogoutRoute' => ['site/logout'],

    'newsTicker' => 'thinker_g\IshtarGate\INewsTickerAsset',
    'news' => [],

    'errActionRoute' => 'site/error',
    'sessKey' => 'ishtar',

    'customField' => 'System is down for maintenance. We\'ll return in a moment'
];