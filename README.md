# Cookie Registry Component for Yii2

Main idea of components is all application cookies must be defined in one place.
I saw projects has more than 10-20 cookie, defined in different places
and nobody knows what each cookie does.

## How to install
Via composer:

add to 'repositories'
```
{
    "type": "vcs",
    "url": "https://github.com/sergebezborodov/cookie-registry-yii2.git"
}
```
add to 'require'
```
sergebezborodov/cookie-registry
```

add to config
```php
'components' => [
    'cookieRegistry' => [
        'class' => 'sergebezborodov\cookie\CookieRegistry',
        'cookies' => [
            'cookieAlias' => [
                'name' => 'real_cookie_name',
                'life' => '+1 year',
                'domain' => '.site.ru',
            ],
            'sessionCookie' => [
                'name' => 'session_cookie_name',
            ],
        ],
    ],
],
```

in application:
```php
// get value
$value = \Yii::$app->cookieRegistry['cookieAlias'];

// set value
\Yii::$app->cookieRegistry['cookieAlias'] = 'another value';

// remove cookie
unset(\Yii::$app->cookieRegistry['sessionCookie']);

```