# Capsule (and Eloquent) Service Provider for Silex 2
[![Travis](https://img.shields.io/travis/jguyomard/silex-capsule-eloquent.svg?maxAge=1800&style=flat-square)](https://travis-ci.org/jguyomard/silex-capsule-eloquent)
[![StyleCI](https://styleci.io/repos/56372806/shield)](https://styleci.io/repos/56372806)
[![Packagist Pre Release](https://img.shields.io/packagist/vpre/jguyomard/silex-capsule-eloquent.svg?maxAge=1800&style=flat-square)](https://packagist.org/packages/jguyomard/silex-capsule-eloquent)
[![Licence](https://img.shields.io/packagist/l/jguyomard/silex-capsule-eloquent.svg?maxAge=1800&style=flat-square)](https://github.com/jguyomard/silex-capsule-eloquent/blob/master/LICENCE)

This is a Service Provider for [Silex](http://silex.sensiolabs.org/) 2.0.x-dev that integrates Laravel's [Fluent Query Builder](https://laravel.com/docs/5.2/queries) and [Eloquent ORM](https://laravel.com/docs/5.2/eloquent) via [Capsule](https://github.com/illuminate/database).

## Installation

Note: This Service Provider requires `silex/silex ~2.0@dev`.

```
composer require jguyomard/silex-capsule-eloquent 2.0.x-dev
```

## Usage

This is a basic configuration with MySQL (Currently, Laravel supports MySQL, Postgres, SQLite and SQL Server):


```php
$app = new Silex\Application();

$app->register(
    new \JG\Silex\Provider\CapsuleServiceProvider(),
    [
        'capsule.connections' => [
            'default' => [
                'driver'    => 'mysql',
                'host'      => 'localhost',
                'database'  => 'mydatabase',
                'username'  => 'root',
                'password'  => 'root',
            ]
        ]
    ]
);
```

This is a basic usage, using [Query Builder](https://laravel.com/docs/5.2/queries) or [Raw SQL Queries](https://laravel.com/docs/5.2/database#running-queries):

```php
$app->get('/article/{id}', function(Application $app, $id)
{
    $article = Capsule::table('article')->where('id', $id)->get();

    // Rest of your code...
});

$app->get('/raw/{id}', function(Application $app, $id)
{
    $article = Capsule::select('SELECT * FROM article WHERE id = :id', [
        'id' => $id,
    ]);

    // Rest of your code...
});

$app->run();
```

You can also use [Eloquent Models](https://laravel.com/docs/5.2/eloquent):

```php
class ArticleModel extends Model
{
    protected $table = 'article';

    protected $primaryKey = 'id';

    protected $fillable = [
        'title'
    ];

    // Rest of your code...
}

$app->get('/article/{id}', function(Application $app, $id)
{
    $article = ArticleModel::find($id);

    // Rest of your code...
});

$app->post('/article', function(Application $app)
{
    $article = ArticleModel::create([
        'title' => 'Foo'
    ]);

    // Rest of your code...
});

$app->run();
```

For further documentation on using the various database facilities this library provides, consult the [Laravel framework database documentation](https://laravel.com/docs/5.2/database).

## Configuration

This is a complete configuration example, with multiple connections:
```php
$app = new Silex\Application();

$app->register(
    new \JG\Silex\Provider\CapsuleServiceProvider(),
    [
        'capsule.connections' => [
            'default' => [
                'driver'    => 'mysql',
                'host'      => 'localhost',
                'port'      => 3306,
                'database'  => 'mydatabase',
                'username'  => 'root',
                'password'  => 'root',
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => '',
                'strict'    => false,
                'engine'    => null,
            ],
            'pgsql' => [
                'driver' => 'pgsql',
                'host'      => 'localhost',
                'port'      => 5432,
                'database'  => 'mydatabase',
                'username'  => 'root',
                'password'  => 'root',
                'charset'   => 'utf8',
                'prefix'    => '',
                'schema'    => 'public',
            ],
            'sqlite' => [
                'driver' => 'sqlite',
                'database'  => 'mydatabase',
                'prefix' => '',
            ],
        ],
        'capsule.options' => [
            'setAsGlobal'    => true,
            'bootEloquent'   => true,
            'enableQueryLog' => true,
        ],
    ]
);
```


## Testing

To run the test suite, you need [PHPUnit](https://phpunit.de/):

```
phpunit
```

## Credits

Inspired by [illuminate-database-silex-service-provider](https://github.com/mattkirwan/illuminate-database-silex-service-provider/) (for Silex 1.*)
and [saxulum-doctrine-mongodb-odm-provider@dev](https://github.com/saxulum/saxulum-doctrine-mongodb-odm-provider/tree/master) (Mongodb ODM for Silex 2.0.x-dev).


## Issues

If you have any problems with or questions about this Service Provider, please contact me through a [GitHub issue](https://github.com/jguyomard/silex-capsule-eloquent/issues).
If the issue is related to Capsule itself please leave an issue on [Laravel official repository](https://github.com/laravel/framework/tree/5.2/src/Illuminate/Database).


## Contributing

You are invited to contribute new features, fixes or updates to this container, through a [Github Pull Request](https://github.com/jguyomard/silex-capsule-eloquent/pulls).
