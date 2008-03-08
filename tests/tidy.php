<?php

require_once 'XML_Feed_Parser_TestCase.php';

class tidy extends XML_Feed_Parser_TestCase
{
    /**
     * Try to work with this ill-formed feed. If the tidy extension is not installed,
     * it expects parsing to fail. If tidy is installed and parsing fails, the test
     * fails. If tidy is installed and it parses, then the test passes.
     */ 
    function test_Tidy() {
        $sample_dir = XML_Feed_Parser_TestCase::getSampleDir();
        $file = file_get_contents($sample_dir . DIRECTORY_SEPARATOR . "illformed_atom10.xml");
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

?>