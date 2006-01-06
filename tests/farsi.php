<?php

require_once 'XML/Feed/Parser.php';
require_once 'PHPUnit.php';

class XML_Feed_Parser_Farsi_TestCase extends PHPUnit_TestCase
{
    function __construct($name)
    {
        $this->PHPUnit_TestCase($name);
        $this->file = file_get_contents("../samples/hoder.xml");
        $this->feed = new XML_Feed_Parser($this->file);
        $this->entry = $this->feed->getEntryByOffset(0);
    }

    function test_itemTitleFarsi()
    {
        $value = "لينکدونی‌ | جلسه‌ی امریکن انترپرایز برای تقسیم قومی ایران";
        $this->assertEquals($value, $this->entry->title);
    }
}

$suite = new PHPUnit_TestSuite("XML_Feed_Parser_Farsi_TestCase");
$result = PHPUnit::run($suite, "123");
echo $result->toString();

?>