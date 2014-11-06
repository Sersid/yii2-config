Yii2 Config
======
Manage configuration from database

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

Applying migrations

```
yii migrate --migrationPath=@vendor/sersid/config/migrations
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
            'idConnection' => 'db', // The ID of the connection component
            'tableName' => '{{%config}}', //Config table name
            'idCache' => 'cache', // The ID of the cache component. Default null (no caching)
            'cacheKey' => 'config.component', // The key identifying the value to be cached
        ],
    ]
];
```

Usage
-----

Once the extension is installed, simply use it in your code by  :

#### Set
```php
Yii::$app->config->set('foo', 'bar');
Yii::$app->config->set('foo', ['bar', 'baz']);
Yii::$app->config->set(['foo' => 'bar']);
```

#### Get
```php
Yii::$app->config->get('zyx'); // null
Yii::$app->config->get('zyx', 'default'); // 'default'
Yii::$app->config->get('foo', 'default'); // 'bar'
Yii::$app->config->get(['foo' => 'default']);
```

#### Delete
```php
Yii::$app->config->delete('foo');
Yii::$app->config->deleteAll(); // delete all config
```
