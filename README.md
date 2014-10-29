Config
======
Yii2 manage configuration from database

Installation
------------

### One
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```sh
php composer.phar require --prefer-dist sersid/yii2-config "*"
```

or add

```
"sersid/yii2-config": "*"
```

to the require section of your `composer.json` file.



### Two

Add migration

```sh
yii migrate --migrationPath=@vendor/sersid/yii2-config/migrations
```



### Three
```php
$config = [
    ...
    'components' => [
        ...
        'config' => [
            'class' => 'sersid\config\components\Config',
            'coding' => '...', // json of serialize. Default 'json'
            'modelClass' => '...', // ActiveRecord model. Default 'sersid\config\models\Config'
            'modelConfig' => [], //name-value pairs that will be used to initialize the object properties
        ],
    ]
];
```

Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
Yii::$app->config->get('foo'); //null
Yii::$app->config->get('foo', 'default'); //default
Yii::$app->config->set('foo', 'bar');
Yii::$app->config->get('foo', 'default'); //bar
```