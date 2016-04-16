<?php

use \Illuminate\Database\Capsule\Manager as Capsule;

class CapsuleServiceProviderTest extends PHPUnit_Framework_TestCase
{
    const ARTICLE_TITLE = 'foo/bar';

    protected $app;

    public function testIsLoaded()
    {
        $this->assertInstanceOf('\Illuminate\Database\Capsule\Manager', $this->app['capsule']);
        $this->assertInstanceOf('\Illuminate\Container\Container', $this->app['capsule.container']);
        $this->assertInstanceOf('Illuminate\Events\Dispatcher', $this->app['capsule.dispatcher']);
        $this->assertInternalType('array', $this->app['capsule.connections']);
        $this->assertInternalType('array', $this->app['capsule.options']);
    }

    public function testIsGlobal()
    {
        $conn = Capsule::connection();
        $this->assertInstanceOf('\Illuminate\Database\Connection', $conn);
    }

    public function testRawQueries()
    {
        // Insert
        $inserted = Capsule::insert('INSERT INTO articles (title) VALUES (:title)', [
            'title' => static::ARTICLE_TITLE,
        ]);
        $this->assertTrue($inserted);

        // Last Insert ID
        $id = Capsule::connection()->getPdo()->lastInsertId();
        $this->assertEquals(1, $id);

        // Select
        $articles = Capsule::select('SELECT * FROM articles WHERE id = ?', [$id]);
        $this->assertCount(1, $articles);
        $this->assertEquals($articles[0]->title, static::ARTICLE_TITLE);

        // Delete
        $deleted = Capsule::delete('DELETE FROM articles WHERE id = ?', [$id]);
        $this->assertEquals(1, $deleted);

        // Count
        $count = Capsule::selectOne('SELECT COUNT(*) as nb FROM articles');
        $this->assertEquals(0, $count->nb);
    }

    public function testQueryBuilder()
    {
        // Insert
        $id = Capsule::table('articles')->insertGetId([
                'title' => static::ARTICLE_TITLE
            ]
        );
        $this->assertEquals($id, 1);

        // Select
        $articles = Capsule::table('articles')->where('id', $id)->get();
        $this->assertCount(1, $articles);
        $this->assertEquals(static::ARTICLE_TITLE, $articles[0]->title);

        // Delete
        $deleted = Capsule::table('articles')->delete($id);
        $this->assertEquals(1, $deleted);

        // Count
        $count = Capsule::table('articles')->count();
        $this->assertEquals(0, $count);
    }

    public function testEloquentORM()
    {
        // Insert
        $article = new Article();
        $article->title = static::ARTICLE_TITLE;
        $saved = $article->save();
        $this->assertTrue($saved);
        $this->assertEquals(1, $article->id);

        $id = $article->id;

        // Select
        $article = Article::find($id);
        $this->assertInstanceOf('Article', $article);
        $this->assertEquals(static::ARTICLE_TITLE, $article->title);

        // Delete
        $deleted = Article::destroy($id);
        $this->assertEquals(1, $deleted);

        // Count
        $count = Article::count();
        $this->assertEquals(0, $count);

    }

    public function setUp()
    {
        // Register Capsule Service Provider
        $this->app = new \Silex\Application();
        $this->app->register(
            new \JG\Silex\Provider\CapsuleServiceProvider(), [
                'capsule.connections' => [
                    'default' => [
                        'driver' => 'sqlite',
                        'database' => ':memory:',
                        'prefix' => '',
                    ]
                ]
            ]
        );

        // Create Database
        $this->app['capsule']->schema()->create('articles', function ($table) {
            $table->increments('id');
            $table->string('title');
            $table->timestamps();
        });

        return parent::setUp();
    }
}

// Bouh
class Article extends \Illuminate\Database\Eloquent\Model {}