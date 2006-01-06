<?php

require_once 'XML/Feed/Parser.php';
require_once 'PHPUnit.php';

class XML_Feed_Parser_Iteration_TestCase extends PHPUnit_Testcase
{
    function __construct($name)
    {
        $this->PHPUnit_TestCase($name);
    }
    
    function setUp() {
    }
    
    function tearDown() {
    }
    
    function test_Atom() {
        $feed = new XML_Feed_Parser(file_get_contents("../samples/grwifi-atom.xml"));
        $entries = array();
        foreach ($feed as $entry) {
            array_push($entries, $entry);
        }
        $this->assertNotSame($entries[0], $entries[1]);
    }

    function test_RSS1() {
        $feed = new XML_Feed_Parser(file_get_contents("../samples/delicious.feed"));
        $entries = array();
        foreach ($feed as $entry) {
            array_push($entries, $entry);
        }
        $this->assertNotSame($entries[0], $entries[1]);
    }
    
    function test_RSS2() {
        $feed = new XML_Feed_Parser(file_get_contents("../samples/rss2sample.xml"));
        $entries = array();
        foreach ($feed as $entry) {
            array_push($entries, $entry);
        }
        $this->assertNotSame($entries[0], $entries[1]);
    }
}

$suite = new PHPUnit_TestSuite;
$suite->addTestSuite("XML_Feed_Parser_Iteration_TestCase");
$result = PHPUnit::run($suite, "123");
echo $result->toString();

?>