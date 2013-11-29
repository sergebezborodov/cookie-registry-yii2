<?php


namespace tests;

use sergebezborodov\cookie\CookieRegistry;

/**
 * @package tests
 */
class CookieTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();

        $_COOKIE['first_cookie'] = 'first value';
    }

    protected function createManager()
    {
        $manager = new CookieRegistry();
        $manager->cookies = array(
            'first' => array(
                'name' => 'first_cookie',
            ),
            'second' => array(
                'name'   => 'second_cookie',
                'expire' => '+1 year',
            ),
        );
        $manager->init();
        return $manager;
    }

    public function testValuesAndNull()
    {
        $manager = $this->createManager();

        $this->assertEquals($manager->getCookie('first'), 'first value');
        $this->assertNull($manager->getCookie('second'));
    }

    public function testArrayAccess()
    {
        $manager = $this->createManager();
        $this->assertEquals($manager['first'], 'first value');
        $this->assertNull($manager['second']);
    }

    /**
     * @runInSeparateProcess
     */
    public function testDelete()
    {
        $manager = $this->createManager();

        $manager->removeCookie('first');
        $this->assertEmpty($manager['first']);
    }


    public function testSetCookie()
    {
        $manager = $this->createManager();

        $manager->setCookie('first', 'new value');
        $this->assertEquals($manager['first'], 'new value');
    }

    public function testException()
    {
        $manager = $this->createManager();
        try {
            $manager->getCookie('foo');
            $this->setExpectedException('sergebezborodov\cookie\Exception');
        } catch (\Exception $e) {
            $this->assertInstanceOf('sergebezborodov\cookie\Exception', $e);
        }
        try {
            $manager->setCookie('foo', 'bar');
            $this->setExpectedException('sergebezborodov\cookie\Exception');
        } catch (\Exception $e) {
            $this->assertInstanceOf('sergebezborodov\cookie\Exception', $e);
        }
        try {
            $manager->removeCookie('foo');
            $this->setExpectedException('sergebezborodov\cookie\Exception');
        } catch (\Exception $e) {
            $this->assertInstanceOf('sergebezborodov\cookie\Exception', $e);
        }
    }

    public function testHashNames()
    {
        $manager = $this->createManager();
        $manager->hashName = true;

        $this->assertNull($manager['first']);
        $this->assertNull($manager['second']);

        $manager->hashName = false;

        $this->assertNotNull($manager['first']);
        $this->assertNull($manager['second']);
    }
}