<?php

require_once 'XML/Feed/Parser.php';
require_once 'PHPUnit.php';

class XML_Feed_Parser_Atom_valueValidity_TestCase extends PHPUnit_TestCase
{
    function __construct($name)
    {
        $this->PHPUnit_TestCase($name);
        $this->file = file_get_contents("../samples/atom10-example2.xml");
        $this->feed = new XML_Feed_Parser($this->file);
        $this->entry = $this->feed->getEntryByOffset(0);
    }

    function test_feedNumberItems()
    {
        $value = 1;
        $this->assertEquals($value, $this->feed->numberEntries);
    }

    function test_FeedTitle()
    {
        $value = "dive into mark";
        $this->assertEquals($value, $this->feed->title);
    }
    
    function test_feedSubtitle()
    {
        $value = "A <em>lot</em> of effort
  went into making this effortless";
        $content = trim($this->feed->subtitle);
        $content = preg_replace("/\t/", " ", $content);
        $content = preg_replace("/(  )+/", " ", $content);
        $this->assertEquals($content, $value);
    }
    
    function test_feedUpdated()
    {
        $value = strtotime("2005-07-31T12:29:29Z");
        $this->assertEquals($this->feed->updated, $value);
    }
    
    function test_feedId()
    {
        $value = "tag:example.org,2003:3";
        $this->assertEquals($this->feed->id, $value);
    }
    
    function test_feedRights()
    {
        $value = "Copyright (c) 2003, Mark Pilgrim";
        $this->assertEquals($this->feed->rights, $value);
    }
    
    function test_feedLinkPlain()
    {
        $value = "http://example.org/";
        $this->assertEquals($this->feed->link, $value);
    }

    function test_feedLinkAttributes()
    {
        $value = "self";
        $link = $this->feed->link(0, "rel", array('type' => "application/atom+xml"));
        $this->assertEquals($link, $value);
    }
    
    function test_feedGenerator()
    {
        $value = "Example Toolkit";
        $this->assertEquals($value, trim($this->feed->generator));
    }
    
    function test_entryTitle()
    {
        $value = "Atom draft-07 snapshot";
        $this->assertEquals($value, trim($this->entry->title));
    }
    
    function test_entryLink()
    {
        $value = "http://example.org/2005/04/02/atom";
        $this->assertEquals($value, trim($this->entry->link));
    }
    
    function test_entryId()
    {
        $value = "tag:example.org,2003:3.2397";
        $this->assertEquals($value, trim($this->entry->id));
    }
    function test_entryUpdated()
    {
        $value = strtotime("2005-07-31T12:29:29Z");
        $this->assertEquals($value, $this->entry->updated);
    }
    
    function test_entryPublished()
    {
        $value = strtotime("2003-12-13T08:29:29-04:00");
        $this->assertEquals($value, $this->entry->published);
    }
    
    function test_entryContent()
    {
        $value = "<p><i>[Update: The Atom draft is finished.]</i></p>";
        $content = trim($this->entry->content);
        $content = preg_replace("/\t/", " ", $content);
        $content = preg_replace("/(  )+/", " ", $content);
        $this->assertEquals($value, $content);
    }
    
    function test_entryAuthorURL()
    {
        $value = "http://example.org/";
        $name = $this->entry->author(false, array('param' => "uri"));
        $this->assertEquals($value, $name);
    }
    
    function test_entryAuthorName()
    {
        $value = "Mark Pilgrim";
        $this->assertEquals($value, $this->entry->author);
    }
    
    function test_entryContributor()
    {
        $value = "Sam Ruby";
        $this->assertEquals($value, $this->entry->contributor);
    }
    
    function test_entryContributorOffset()
    {
        $value = "Joe Gregorio";
        $this->assertEquals($value, $this->entry->contributor(1));
    }
}

$suite = new PHPUnit_TestSuite("XML_Feed_Parser_Atom_valueValidity_TestCase");
$result = PHPUnit::run($suite, "123");
echo $result->toString();

?>