<?php
namespace sersid\config\tests;

use Yii;
use yii\codeception\TestCase;

class ConfigTest extends TestCase
{
    // tests
    public function testMe()
    {
        /**
         * @var $config \sersid\config\components\Config
         */
        $config = \Yii::$app->config;
        $config->deleteAll();

        // Testing getAll()
        $this->assertTrue(is_array($config->getAll()));
        $this->assertEquals($config->getAll(), []);

        // Testing default
        $this->assertEquals($config->get('foo'), null);
        $this->assertEquals($config->get('foo', 123), 123);
        $this->assertEquals($config->get('foo', 'default'), 'default');
        $this->assertEquals($config->get('foo', ['bar']), ['bar']);

        // Testing get
        $config->set('foo', 'bar');
        $this->assertEquals($config->get('foo'), 'bar');
        $this->assertEquals($config->get('foo', 'default'), 'bar');
        $config->set('foo', ['bar', 'baz']);
        $this->assertEquals($config->get('foo'), ['bar', 'baz']);

        // Testing set array
        $config->deleteAll();
        $this->assertEquals($config->get(['foo','baz']), [
            'foo' => null,
            'baz' => null
        ]);
        $this->assertEquals($config->get([
            'foo',
            'baz' => 'default',
            'zyx' => ['default']
        ]), [
            'foo' => null,
            'baz' => 'default',
            'zyx' => ['default']
        ]);
        $config->set([
            'foo' => 'bar',
            'baz' => ['zyx']
        ]);
        $this->assertEquals($config->get('foo'), 'bar');
        $this->assertEquals($config->get('baz'), ['zyx']);
        $this->assertEquals($config->get([
            'baz',
            'foo' => 'default',
            'empty' => 'default'
        ]), [
            'baz' => ['zyx'],
            'foo' => 'bar',
            'empty' => 'default'
        ]);

        // Deleting
        $config->deleteAll();
        $this->assertTrue(is_array($config->getAll()));
        $this->assertEquals($config->getAll(), []);
    }
}