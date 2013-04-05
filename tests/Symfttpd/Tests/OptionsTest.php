<?php
/**
 * This file is part of the Symfttpd Project
 *
 * (c) Laurent Bachelier <laurent@bachelier.name>
 * (c) Benjamin Grandfond <benjamin.grandfond@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfttpd\Tests;

use Symfttpd\Options;

/**
 * OptionsTest class
 *
 * @author Benjamin Grandfond <benjaming@theodo.fr>
 */
class OptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Options
     */
    protected $options;

    public function setUp()
    {
        $this->options = new Options();
    }

    /**
     *
     */
    public function testGetIterator()
    {
        $this->assertInstanceOf('ArrayIterator', $this->options->getIterator());
    }

    /**
     * @dataProvider getArrayOptions
     */
    public function testAll($config)
    {
        $this->options->add($config);
        $this->assertEquals($config, $this->options->all());
    }

    /**
     * @dataProvider getArrayOptions
     */
    public function testGet($config)
    {
        $this->options->add($config);
        $this->assertEquals(reset($config), $this->options->get(key($config)));
    }

    public function testHas()
    {
        $this->options->set('foo', 'bar');
        $this->assertTrue($this->options->has('foo'));
        $this->assertFalse($this->options->has('bar'));

        $this->options->set('foo', null);
        $this->assertFalse($this->options->has('foo'));
        $this->assertFalse($this->options->has('bar'));
    }

    /**
     * @dataProvider getOptions
     */
    public function testSet($name, $value)
    {
        $this->options->set($name, $value);

        $this->assertEquals($value, $this->options->get($name));
    }

    /**
     * @dataProvider getArrayOptions
     */
    public function testAdd($config)
    {
        $this->options->add($config);

        // assertArrayHasKey does not work with empty arrays.
        if (!empty($config)) {
            // assert that foo is a key of the config for instance.
            $this->assertArrayHasKey(key($config), $this->options->all());
        }

        $this->assertEquals(count($config), count($this->options->all()));

        // assert that array('foo', 'bar') is the value of $config['bar']
        $this->assertEquals(reset($config), $this->options->get(key($config)));
    }

    public function testMerge()
    {
        $this->options->add(array('foo' => 'bar', 'bar' => 'foo'));
        $this->options->merge(array('foo' => 'foo', 'test' => 'bar'));

        $this->assertEquals(array(
            'foo' => 'foo',
            'bar' => 'foo',
            'test' => 'bar',
        ), $this->options->all());
    }

    public function getOptions()
    {
        return array(
            array('foo', 'bar'),
            array('bar', array('foo', 'bar'))
        );
    }

    public function getArrayOptions()
    {
        return array(
            array(array()),
            array(array('1' => null)),
            array(array('foo' => 'bar')),
            array(array('bar' => array('foo', 'bar')))
        );
    }
}
