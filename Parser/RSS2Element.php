<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class representing entries in an RSS2 feed.
 *
 * PHP versions 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   XML
 * @package    XML_Feed_Parser
 * @author     James Stewart <james@jystewart.net>
 * @copyright  2005 James Stewart <james@jystewart.net>
 * @license    http://www.gnu.org/copyleft/lesser.html  GNU LGPL 2.1
 * @version    CVS: $Id$
 * @link       http://dev.jystewart.net/XML_Feed_Parser/
 */

/**
 * This class provides support for RSS 2.0 entries. It will usually be 
 * called by XML_Feed_Parser_RSS2 with which it shares many methods.
 *
 * @author	James Stewart <james@jystewart.net>
 * @version	0.2.2 22nd September 2005
 * @package XML_Feed_Parser
 */
class XML_Feed_Parser_RSS2Element extends XML_Feed_Parser_RSS2
{
    /**
     * This will be a reference to the parent object for when we want
     * to use a 'fallback' rule
     * @var XML_Feed_Parser_RSS2
     */
    protected $parent;

    /**
     * Our specific element map 
     * @var array
     */
    protected $map = array(
    	'title' => array('Text'),
    	'guid' => array('Guid'),
    	'description' => array('Text'),
    	'author' => array('Text'),
    	'comments' => array('Text'),
    	'enclosure' => array('Enclosure'),
    	'pubDate' => array('Date'),
    	'source' => array('Source'),
    	'link' => array('Text'));

	/**
	 * Here we map some elements to their atom equivalents. This is going to be
	 * quite tricky to pull off effectively (and some users' methods may vary)
	 * but is worth trying. The key is the atom version, the value is RSS2.
	 * @var array
	 */
	protected $compatMap = array(
	    'id' => array('guid'),
	    'content' => array('description'),
	    'updated' => array('lastBuildDate'),
	    'published' => array('pubdate'));

    /**
     * Store useful information for later.
     *
     * @param   DOMElement  $element - this item as a DOM element
     * @param   XML_Feed_Parser_RSS2    $parent - the feed of which this is a member
     */
    function __construct(DOMElement $element, $parent, $xmlBase = '')
    {
    	$this->model = $element;
    	$this->parent = $parent;
    }

    /**
     * guid is the closest RSS2 has to atom's ID. It is usually but not always a URI.
     * The one attribute that RSS2 can posess is 'ispermalink' which specifies whether
     * the guid is itself dereferencable. Use of guid is not obligatory, but is
     * advisable.
     *
     * @todo    Implement ispermalink support
     * @return  string  the guid
     */
    function getGuid()
    {
        if ($this->model->getElementsByTagName('guid')->length > 0) {
            return $this->model->getElementsByTagName('guid')->item(0)->nodeValue;
        }
        return false;
    }

    /**
     * The RSS2 spec is ambiguous as to whether an enclosure element must be
     * unique in a given entry. For now we will assume it needn't, and allow
     * for an offset.
     *
     * @param   int offset
     * @return  array|false
     */
    function getEnclosure($offset = 0)
    {
        $encs = $this->model->getElementsByTagName('enclosure');
        if ($encs->length >= $offset) {
            try {
                $attrs = $encs->item($offset)->attributes;
                return array(
                    'url' => $attrs->getNamedItem('url')->value,
                    'length' => $attrs->getNamedItem('length')->value,
                    'type' => $attrs->getNamedItem('type')->value);
            } catch (Exception $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * source is an optional sub-element of item. Like atom:source it tells
     * us about where the entry came from (eg. if it's been copied from another
     * feed). It is not a rich source of metadata in the same way as atom:source
     * and while it would be good to maintain compatibility by returning an
     * XML_Feed_Parser_RSS2 element, it makes a lot more sense to return an array.
     *
     * @return array|false
     */
    function getSource()
    {
        $get = $this->model->getElementsByTagName('source');
        if ($get->length) {
            $source = $get->item(0);
            $array = array(
                'content' => $source->nodeValue);
            foreach ($source->attributes as $attribute) {
                $array[$attribute->name] = $attribute->value;
            }
            return $array;
        }
        return false;
    }
}

?>