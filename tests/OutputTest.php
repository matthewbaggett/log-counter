<?php

class OutputTest extends \PHPUnit_Framework_TestCase
{

    /** @var LogCounter\LogCounter */
    private $lc;

    public function setUp(){
        $this->lc = new \LogCounter\LogCounter();
    }

    public function testOutput(){
        ob_start();
        $this->lc->setShowProgress(false);
        $this->lc->run("tests/test.log", 10);
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals(file_get_contents("tests/expected_output.txt"), $output);
    }
}
