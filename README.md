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

### Does is hurt?
The system performance has been well considered while designing this module. Even the module is not frequently used and needs to be mounted to the "bootstrap" phase, it does nothing when it's disabled. To load an empty module takes barely no resource for yii framework.



## Quick start
-----

### 1. Installation


### 2. Loading

### 3. Enabling


## Enhanced Features
-----
### I. Alpha test access
#### 1. Alpha login
```php
# The authentication and its credentials are independent from the host application, and be held by the module itself. This design is for the needs of testing the landing pages or log-in/register procedures of the site.
```
#### 2. Privileged IPs

### II. News ticker

``` 
# need mention original site url of inewsticker, and the overriding of plugin options.
```


## Options
-----
| **Name** | **Type** | **Default** | **Usage** |
|----------|----------|-------------|-----------|
| enabled  | bool     | false       | Enable the maintenance or not.|