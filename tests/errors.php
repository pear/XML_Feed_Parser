<?php

require_once '../Parser.php';
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
    }
    
    function setUp() {
    }
    
    function tearDown() {
    }
    
    function test_fakeFeedType()
    {
        $file = "<myfeed><myitem /></myfeed>";
        try {
            $feed = new XML_Feed_Parser($file, false, true);
        } catch (Exception $e) {
            $this->assertTrue($e instanceof XML_Feed_Parser_Exception);
        }
    }
    
    function test_badRSSVersion()
    {
        $file = "<?xml version=\"1.0\"?>
        <rss version=\"0.8\">
           <channel></channel></rss>";
       try {
           $feed = new XML_Feed_Parser($file, false, true);
       } catch (Exception $e) {
           $this->assertTrue($e instanceof XML_Feed_Parser_Exception);
       }
    }
    
    function test_emptyInput()
    {
        $file = null;
        try {
            $feed = new XML_Feed_Parser($file, false, true);
        } catch (Exception $e) {
            $this->assertTrue($e instanceof XML_Feed_Parser_Exception);
        }
    }

    function test_nonXMLInput()
    {
        $file = "My string";
        try {
            $feed = new XML_Feed_Parser($file, false, true);
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