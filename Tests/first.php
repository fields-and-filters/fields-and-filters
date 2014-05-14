<?php
class FirstTest extends PHPUnit_Framework_TestCase
{
    public function testIsEquals()
    {
        $this->assertEquals(1,1);
    }

    public function testIsNotEquals()
    {
        $this->assertNotEquals(1, 2);
    }
}