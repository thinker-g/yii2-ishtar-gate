# Ishtar Gate [v1.1.0]



## Description
-----
### What's this?
*Ishtar Gate* is an yii2.0 extension provides enhanced maintenance mode of a project.

It provides a web-based module, which can block public accesses, while allowing permitted users (normally our testers) to access the web site without any restriction. Blocked users will all be displayed a pre-defined maintenance page.

This gives our development team an opportunity to run their test on production environment without "surprising" the public users, and to verify the results without being effected by operations from them.

### How it works?
The blocking control is based on yii request route. Two **blocking modes** mentioned below can be applied:

1. **Positive blocking**: All accesses except specified routes will be blocked, this is basically a whole site blocking.

2. **Passive blocking**: This is opposite to the *Positive blocking*, only specified routes will be blocking. This is useful when we are deploying a new module without taking down the whole site.

### Why this?
Besides blocking public access by providing a settable static page, *Ishtar Gate* provides another level of a session-based authentication, which allows a group of special users (which I'd like to call them the *"Alpha Testers"*) to access the site while normal users are blocked. So our team can run some tests on REAL production environment.

For planned maintenance, a news ticker is integrated in the module, so that we can give users some messages before a planned maintenance takes place.

*PS: Instructions of setting up these enhanced features will be mentioned in later sections.*

### Does it hurt?
The system performance has been well considered while designing this module. Even the module is not frequently used and needs to be mounted to the "bootstrap" phase, it does nothing when it's set to disabled. To load an empty module takes barely no resource for yii framework.



## Quick start
-----

### 1. Install
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).
To install via composer, issuing following command in root directory of your yii2.0 application.
```bash 
    php composer.phar "thinker-g/yii2-ishtar-gate:dev-master"
```
It can also be installed by downloading a copy directly from [Github project page](https://github.com/thinker-g/yii2-ishtar-gate). If you install in this way, you need to setup a path alias for the namespace "thinkerg\IshtarGate". For instance, we place the module folder(yii2-ishtar-gate) in "runtime/tmp-extensions" in application root path. Add following alias definition to attribute "[***aliases***](http://www.yiiframework.com/doc-2.0/yii-base-module.html#$aliases-detail)" of the yii application object.
```php
	return [
		...
		'aliases' => [
			'@thinkerg\IshtarGate' => 'runtime/tmp-extensions/yii2-ishtar-gate'
		]
		...
	]

```


### 2. Mount
Mount the module to the application in the same way to any other modules. In this case, we use "*istharDemo*" as the module ID.
```php
    ...
    'modules' => [
        'ishtarDemo' => [
            'class' => 'thinkerg\IshtarGate\Module'
        ]
    ],
    ...

```
And then add the &lt;module ID&gt; to "bootstrap" of the application.
```php
    ...
    'bootstrap' => ['log', 'ishtarDemo'],
    ...
```

### 3. Enable
Now the module is ready to bring your site into maintenance mode. But you cannot see any change on your site yet, because the module is not enabled yet. To enable it, we need to set the module attribute [***enabled***](#o_enabled) to *true*.
```php
   ...
    'modules' => [
        'ishtarDemo' => [
            'class' => 'thinkerg\IshtarGate\Module',
            'enabled' => true
        ]
    ],
    ...
```

Now you should be seeing that your site always displays Ishtar Gate's default blocker page, no matter which route you are accessing. Enjoy ;-)

We'll introduce basic settings and enhanced features in later sections.



## Basic Usage
-----

**NOTE:** As aforementioned, the blocking control is based on yii request route. The routes in configuration should be full route name WITHOUT the leading slash "/". For example the route in setting "site/about" stands for the real route "/site/about", the leading slash will be then prepended automatically by the module.

### 1. Blocker Route

When *Ishtar Gate* blocks public accesses, it will run a certain route configured in attribute [***blockerRoute***](#o_blockerRoute), instead of invoking the requested one. The default blockerRoute is set to "&lt;module ID&gt;/gate/index". Following setting will use "/site/about" as the blocker page instead of the built-in blocker.

```php
    ...
    'modules' => [
        'ishtarDemo' => [
            'class' => 'thinkerg\IshtarGate\Module',
            'enabled' => true,
            'blockerRoutes' => ['site/about'],
        ],
        ...
    ],
    ...
```

By default, *Ishtar Gate* use its own layout file while rendering the blocker page. This can be customized by setting up the attribute [***layout***](#o_layout). This design is to ensure that the blocker page won't crash when we access database in daily-used layout file. You can build another static layout file for your own application.

### 2. Positive blocking

Positive blocking is the default mode of the module. When the module is ENABLED, all routes will be block except the ones set in [***exceptRoutes***](o_exceptRoutes). 

To let certain route open to the public, you need to add the route to attribute [***exceptRoutes***](#o_exceptRoutes). Following example shows how to let pass the access of route "/site/about", while the maintenance mode is enabled.

```php
    ...
    'modules' => [
        'ishtarDemo' => [
            'class' => 'thinkerg\IshtarGate\Module',
            'enabled' => true,
            'exceptRoutes' => [
                'site/about'
            ],
        ],
        ...
    ],
    ...
```
**NOTE**: Routes in this module, and those set in [***blockerRoute***](#o_blockerRoute), [***errActionRoute***](#o_errActionRoute), [***siteLogoutRoute***](#o_siteLogoutRoute) will never be blocked, as they need to support some lower level functions.


### 3. Passive blocking

When the module is ENABLED and any routes added to attribute [***onlyRoutes***](#o_onlyRoutes), only those routes will be blocked and nothing happens to other parts of the site. The following setting will ONLY block the route '/site/signup':

```php
    ...
    'modules' => [
        'ishtarDemo' => [
            'class' => 'thinkerg\IshtarGate\Module',
            'enabled' => true,
            'onlyRoutes' => [
                'site/signup'
            ],
        ],
        ...
    ],
    ...
```



### 4. Switching between 2 blocking modes

Once any routes are added to [***onlyRoutes***](#o_onlyRoutes), the passive blocking is enabled. So to use positive blocking mode just need to leave the [***onlyRoutes***](#o_onlyRoutes) as an empty array or false. 




## Enhanced Features
-----
### 1. Alpha test access

Test access control allows project team members to access the website with no restriction, while the maintenance mode is enabled (where public users are blocked). To achieve this, we have 2 options, **privileged IPs** and **Alpha Login**.

- **Setup privileged IPs** 

    Add IP addresses or IP ranges to the attribute [***privilegedIPs***](#o_privilegedIPs). This is an array composed by strings of IP address. Requests from IPs listed in the array will never be blocked. Wildcard is supported in here, it follows the same rules of the Gii module.

- **Alpha Login control**

    Ishtar Gate provides a simple session-based authentication to let certain users access the site freely. The authentication is independent from the host application's authentication. This design is for the needs of testing the landing page or log-in/register procedures of the site. You can open the sign-in page via route "*&lt;moduleID&gt;/gate/signin*", where &lt;moduleID&gt; is the ID you configured in your application. And the logout route is "*&lt;moduleID&gt;/gate/signout*".

    2 options are related to the setting of **Alpha Login**:
    - [***credentials***](#o_credentials) is an array, in which the keys are usernames and values are their passwords. The password stored here should be hashed value if any hashing method is specified in [***hashCallable***](#o_hashCallable).
    
    - [***hashCallable***](#o_hashCallable): This should be a callback function/method used to hash the password entered by user. The "signin" action will use this callback to hash the received password and compare the hash code to the one set in [***credentials***](#o_credentials). The function specified should take one parameter for the entered password and return an string which is its hash code.


### 2. News ticker

For a planned maintenance, you may want to inform your users before it takes place. *Ishtar Gate* provides a news ticker to display this kind of messages to public user. To do this you need to setup the option [***news***](#o_news). 


- **Loading news**
    
    The option [***news***](#o_news) is an associate array in where the key is the time stamp when the maintenance starts, and the value is the message that you want to show to public users.
    
    The key of the [***news***](#o_news) array can be any string recognizable to the function *strtotime()*, and only keys "later" than "current time" will be shown to users in a news bar in top of the page. In the message, you can use token "**{ts}**" to display its key value (planned start time). Here's an example of the news option:

    ```php
        ...
        'modules' => [
            'ishtarDemo' => [
                'class' => 'thinkerg\IshtarGate\Module',
                'enabled' => true,
                ...
                'news' => [
                    '2014-10-20 12:15' => 'Site will be down for maintenance at {ts}.',
                    '2014-12-25 00:30:00' => 'Site will be down soon for maintenance.',
                    'Jan 01, 2015' => 'Site will be down for maintenance on {ts}.',
                ],
                ...
            ]
        ],
        ...
    ```

    If current date is 2014-12-01, only 2nd and 3rd messages in the setting above will be loaded to display. And the token "**{ts}**" will be replaced by corresponding keys.


- **Displaying news**
    
    *Ishtar Gate* uses jquery plugin [inewsticker](https://github.com/progpars/inewsticker) to present the news ticker. Any options of that plugin can be set for customizing the news ticker. Example below shows the default options of the plugin (see the key 'pluginOptions'):
    ```php
        ...
        'modules' => [
            'ishtarDemo' => [
                'class' => 'thinkerg\IshtarGate\Module',
                'enabled' => true,
                'news' => [
                    ...
                ],
                'newsTicker' => [
                    'class' => 'thinkerg\IshtarGate\INewsTickerAsset',
                    'pluginOptions' => [
                        'effect' => 'slide',
                        'speed' => 3000,
                        'dir' => 'ltr',
                        'color' => '#fff',
                        'font_size' => 13,
                        'font_family' => 'arial',
                        'delay_after' => 5000
                    ]
                ]
                ...
            ],
        ],
        ...
    ```

**NOTE:** *News ticker will only take effects when attribute [***enabled***](#o_enabled) is set to **false**.*



### 3. Tip version

When a tester is doing internal tests, a version tip can be shown in the bottom right of the page. This can notice our testers/developers that they are now accessing in alpha test mode, while the site is actually blocking other accesses from the public. This feature is enabled by default, and can be turned off by setting option [***tipVersion***](#o_tipVersion) to *false*.

**NOTE:** *The version tip will only show up when [***tipVersion***](#o_tipVersion) is set to **true** AND current request is a test access (authenticated by **Alpha Login** or from a privileged IP).*




## Options Reference
-----

| **Name** | **Type** | **Default** | **Usage** |
|----------|----------|-------------|-----------|
| <label id="o_enabled">enabled</label> | bool | false | Enable the maintenance or not. |
| <label id="o_tipVersion">tipVersion</label> | bool | true | Whether to tip version on page in test accesses. Only take effects when [enabled](#o_enabled) is set to true. |
| <label id="o_layout">layout</label> | string | 'main' | Layout file used to render the blocker page. |
| <label id="o_logoutPublic">logoutPublic</label> | bool | false | Whether to logout the logged users when maintenance mode is enabled. Only takes effects in positive blocking mode. |
| <label for="" id="o_siteLogoutRoute">siteLogoutRoute</label> | array | ['site/logout'] | Route used to logout users when maintenance mode is enabled.<br />This takes effect only when $logoutPublic is set to true.<br />Leave it as "null" or an empty array, system will use Yii::$app->getUser()->logout() to logout current user. Otherwise user will be redirected to the specified route, and the destination route must allow GET request. |
| <label for="" id="o_credentials">credentials</label> | array | ['tester' => 'tester'] | Alpha test user credentials, where key are usernames, and values are hashed passwords. |
| <label for="" id="o_privIPs">privIPs</label> | array | [] | Array of IP addresses, requests from these IPs will never be blocked. |
| <label for="" id="o_sessKey">sessKey</label> | string | 'ishtar' | Session key to support Alpha Login authencation. |
| <label for="" id="o_exceptRoutes">exceptRoutes</label> | array | [] | Routes listed here won't be blocked. |
| <label for="" id="o_onlyRoutes">onlyRoutes</label> | array | [] | Only routes listed here will be blocked.<br />When it has value, [exceptRoutes](#o_exceptRoutes) won't take effects. |
| <label for="" id="o_blockerRoute">blockerRoute</label> | array | &lt;module ID&gt;/gate/index | Blocker page route. |
| <label for="" id="o_useRedirection">useRedirection</label> | bool | false | The way to display the blocker page, set to false to overwrite \yii\web\Application::$catchAll, set to true to always redirect users to the blocker route. |
| <label for="" id="o_errActionRoute">errActionRoute</label> | string | 'site/error' | Error handler of Yii::$app. This should be the value of the "errorAction" of your application. |
| <label for="" id="o_hashCallable">hashCallable</label> | callable | 'thinkerg\IshtarGate\Module::dummyHash' | Callable used to hash the inputted password in Alpha Login authencation.<br />The default method return the password without doing anything. |
| <label for="" id="o_news">news</label> | array | [] | News entries array, where the key is the time string and value is the messages.<br />Only messages whose key is "later" than CURRENT TIME will be displayed. Takes effects only when module is NOT enabled. |
| <label for="" id="o_newsTicker">newsTicker</label> | string or array | 'thinkerg\IshtarGate\INewsTickerAsset' | News ticker configuration (string/array). The class should extends \yii\web\AssetBundle. |
| <label for="" id="o_customField">customField</label> | string | 'System is down for maintenance. We\'ll return in a moment' | Custom message can be invoked in view of blocker page. |
