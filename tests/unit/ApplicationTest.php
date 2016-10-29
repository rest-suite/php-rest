<?php

use bc\rest\Application;

class ApplicationTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testCreateApp()
    {
        $this->tester->amGoingTo("Create app instance");
        
        $app = new Application();
        $this->tester->assertInstanceOf(Application::class, $app);        
    }
}