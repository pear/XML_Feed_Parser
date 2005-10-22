<?php

require_once 'XML/Feed/Parser.php';
require_once 'PHPUnit.php';

/**
 * This test is to make sure that we get errors when we should. In
 * particular we check that it throws an exception if we hand in an
 * illegal feed type.
 */
class XML_Feed_Parser_ThrowErrors_TestCase extends PHPUnit_Testcase
{
	
	function __construct($name)
	{
	    $this->PHPUnit_TestCase($name);
        $this->file = "<myfeed><myitem /></myfeed>";
	}
    
    function setUp() {
    }
    
    function tearDown() {
    }
    
    function test_ExceptionThrown()
    {
        try {
            $feed = new XML_Feed_Parser($this->file);
        } catch (Exception $e) {
            $this->assertTrue($e instanceof XML_Feed_Parser_Exception);
        }
    }
}

$suite = new PHPUnit_TestSuite;
$suite->addTestSuite("XML_Feed_Parser_ThrowErrors_TestCase");
$result = PHPUnit::run($suite, "123");
echo $result->toString();

?>