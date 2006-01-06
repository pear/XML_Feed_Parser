<?php

require_once '../Parser.php';
require_once 'PHPUnit.php';

class XML_Feed_Parser_Tidy_TestCase extends PHPUnit_Testcase
{
    
    function __construct($name)
    {
        $this->PHPUnit_TestCase($name);
    }
    
    function setUp() {
    }
    
    function tearDown() {
    }

    /**
     * Try to work with this ill-formed feed. If the tidy extension is not installed,
     * it expects parsing to fail. If tidy is installed and parsing fails, the test
     * fails. If tidy is installed and it parses, then the test passes.
     */ 
    function test_Tidy() {
        $file = file_get_contents("../samples/illformed_atom10.xml");
        try {
            $feed = new XML_Feed_Parser($file, false, true, true);    
        } catch (XML_Feed_Parser_Exception $e) {
            if (extension_loaded('tidy')) {
                $this->assertTrue(false);
            } else {
                $this->assertTrue(true);
            }
            return;
        }
        $entry = $feed->getEntryByOffset(0);
        $this->assertEquals($entry->author, 'Example author');
    }
}

$suite = new PHPUnit_TestSuite;
$suite->addTestSuite("XML_Feed_Parser_Tidy_TestCase");
$result = PHPUnit::run($suite, "123");
echo $result->toString();

?>