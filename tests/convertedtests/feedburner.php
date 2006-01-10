<?php
require_once 'PHPUnit.php';
require_once 'XML/Feed/Parser.php';
class feedburner_TestCase extends PHPUnit_TestCase {

	function test_feedburner_browserfriendly_1 () { 
		$content = file_get_contents('../feedparsertests/wellformed/feedburner/feedburner_browserfriendly.xml');
		try {
			$feed = new XML_Feed_Parser($content);
		} catch (XML_Feed_Parser_Exception $e) {
			$this->assertTrue(false);
			return;
		}
		$this->assertEquals('This is an XML content feed. It is intended to be viewed in a newsreader or syndicated to another site.', $feed->info);
	}
}

$suite = new PHPUnit_TestSuite('feedburner_TestCase');
$result = PHPUnit::run($suite, '123');
echo $result->toString();

?>
