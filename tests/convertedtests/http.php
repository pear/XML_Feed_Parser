<?php
require_once 'PHPUnit.php';
require_once 'XML/Feed/Parser.php';
class http_TestCase extends PHPUnit_TestCase {
}

$suite = new PHPUnit_TestSuite('http_TestCase');
$result = PHPUnit::run($suite, '123');
echo $result->toString();

?>
