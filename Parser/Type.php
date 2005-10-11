<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Abstract class providing common methods for XML_Feed_Parser feeds.
 *
 * PHP versions 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @todo        Produce tests with non-UTF8 encoded feeds
 * @category   XML
 * @package    XML_Feed_Parser
 * @author     James Stewart <james@jystewart.net>
 * @copyright  2005 James Stewart <james@jystewart.net>
 * @license    http://www.gnu.org/copyleft/lesser.html  GNU LGPL 2.1
 * @version    CVS: $Id$
 * @link       http://dev.jystewart.net/XML_Feed_Parser/
 */

/**
 * This abstract class provides some general methods that are likely to be
 * implemented exactly the same way for all feed types.
 *
 * @package XML_Feed_Parser
 * @author  James Stewart <james@jystewart.net>
 * @version 0.2.2 22nd September 2005
 */
abstract class XML_Feed_Parser_Type
{
    /**
     * Where we store our DOM object for this feed 
     * @var DOMDocument
     */
    public $model;

    /**
     * We don't particularly need to use this for this class, but it's helpful
     * to make inheritance work.
     * @var string
     */
    protected $xmlBase;

    /**
     * For iteration we'll want a count of the number of entries 
     * @var int
     */
    public $numberEntries;

    /**
     * Where we store our entry objects once instantiated 
     * @var array
     */
    public $entries = array();

    /**
     * We are not going to provide methods for every entry type so this
     * function will allow for a lot of mapping. We rely pretty heavily
     * on this to handle our mappings between other feed types and atom.
     *
     * @param   string  $call - the method attempted
     * @param   array   $arguments - arguments to that method
     * @return  mixed
     */
    function __call($call, $arguments = array())
    {
        if (! is_array($arguments)) {
            $arguments = array();
        }

        if (isset($this->compatMap[$call])) {
    	    $arguments = array_merge($arguments, $this->compatMap[$call]);
    	    $call = $this->compatMap[$call][0];
    	}

    	if (isset($this->map[$call])) {
    	    $method = 'get' . $this->map[$call][0];
    	    if ($method == 'getLink') {
    	        $offset = isset($arguments[0][0]) ? $arguments[0][0] : 0;
    	        $attribute = isset($arguments[0][1]) ? $arguments[0][1] : 'href';
    	        $params = isset($arguments[0][2]) ? $arguments[0][2] : array();
    		    return $this->getLink($offset, $attribute, $params);
    	    }
    	} else {
    	    return false;
    	}

        if (method_exists($this, $method)) {
    	    return $this->$method($call, $arguments);
    	}

    	return false;
    }

    /**
     * For many elements variable-style access will be desirable. This function
     * provides for that.
     *
     * @param   string  $value - the variable required
     * @return  mixed
     */
    function __get($value)
    {
        return $this->$value();
    }

    /**
     * We will often need to extract the xml:base values that apply to a
     * link. This method iterates through the heirarchy and extracts the
     * relevant attributes, and then combines them.
     *
     * @param   DOMElement  The starting node
     * @return  string
     */
    function getBase($thisNode)
    {
        /* We'll need some containers and settings */
        $bases = array();
        $combinedBase = $this->xmlBase;
        preg_match('/^([A-Za-z]+:\/\/.*?)\//', $combinedBase, $results);
        isset($results[1]) ? $firstLayer = $results[1] : $firstLayer = '';

        $nameSpace = 'http://www.w3.org/XML/1998/namespace';

        /* Iterate up the tree and grab all parent xml:bases */
        while ($thisNode instanceof DOMElement) {
            if ($thisNode->hasAttributes()) {
                $test = $thisNode->attributes->getNamedItemNS($nameSpace, 'base');
                if ($test) {
                    array_push($bases, $test->nodeValue);
                }
            }
            $thisNode = $thisNode->parentNode;
        }

        /* if starts with a protocol then restart the string. if starts with a / then 
         * add on to the domain name. otherwise tag on to the end */
        $bases = array_reverse($bases);

        foreach ($bases as $base) {
            if (preg_match('/^[A-Za-z]+:\/\//', $base)) {
                $combinedBase = $base;
                preg_match('/^([A-Za-z]+:\/\/.*?)\//', $base, $results);
                $firstLayer = $results[1];
            } else if (preg_match('/^\//', $base)) {
                $combinedBase = $firstLayer . $base;
            } else {
                $combinedBase .= $base;
            }

        }
        return $combinedBase;
    }

    /**
     * getBase gets us the xml:base data. We then need to process that with regard
     * to our current link. This function does that and returns the link in as
     * complete a form as possible.
     *
     * @param   string
     * @param   DOMElement
     * @return  string
     */
    function addBase($link, $element)
    {
        if (preg_match('/^[A-Za-z]+:\/\//', $link)) {
            return $link;
        }

        $base = $this->getBase($element);

        if (preg_match('/^\//', $link)) {
            preg_match('/^([A-Za-z]+:\/\/.*?)\//', $base, $results);
            $root = $results[1];
            return $root . $link;
        } else {
            return $base . $link;
        }
    }

	/**
	 * Pretty fundamental!
	 * 
	 * @param   int $offset
	 * @return  XML_Feed_Parser_RSS1Element
	 */
	function getEntryByOffset($offset)
	{
    	if (! isset($this->entries[$offset])) {
    		$entries = $this->model->getElementsByTagName($this->itemElement);
    		if ($entries->length > 0) {
			    $xmlBase = $this->getBase($entries->item($offset));
				$this->entries[$offset] = new $this->itemClass(
				    $entries->item($offset), $this, $xmlBase);
    		} else {
    		    throw new XML_Feed_Parser_Exception('No entries found');
    		}
    	}

    	return $this->entries[$offset];
	}

	/**
	 * Get a date construct. We use PHP's strtotime to return it as a unix datetime
	 * 
	 * @param	string	$method	    The name of the date construct we want
	 * @param	array 	$arguments	Included for compatibility with our __call usage
	 * @return	int|false datetime
	 */
	protected function getDate($method, $arguments)
	{
		$time = $this->model->getElementsByTagName($method);
		if ($time->length == 0) {
		    return false;
		}
		return strtotime($time->item(0)->nodeValue);
	}

	/**
	 * Get a text construct. 
	 *
	 * @param	string	$method	The name of the text construct we want
	 * @param	array 	$arguments	Included for compatibility with our __call usage
	 * @return	string
	 */
	protected function getText($method, $arguments = array())
	{
		$tags = $this->model->getElementsByTagName($method);
		if ($tags->length > 0) {
			$value = $tags->item(0)->nodeValue;
			return $value;
		}
		return false;
	}

    /**
     * There is no single way of declaring a category in RSS1 or Atom as there is
     * in RSS2. 
     * Instead the usual approach is to use the dublin core namespace to declare 
     * categories. For example delicious use both: <dc:subject>PEAR</dc:subject>
     * and: <taxo:topics><rdf:Bag>
     * <rdf:li resource="http://del.icio.us/tag/PEAR" /></rdf:Bag></taxo:topics>
     * to declare a categorisation of 'PEAR'.
     *
     * We need to be sensitive to this where possible. For the initial implementation
     * we will simply extract all dc:subject entries as that is common across Atom and
     * RSS1.
     *
	 * @param	string	$call	for compatibility with our overloading
	 * @param   array $arguments - arg 0 is the offset, arg 1 is whether to return as array
	 * @return  string|array|false
	 */
    protected function getCategory($call, $arguments)
    {
        $categories = $this->model->getElementsByTagName('subject');
        $offset = empty($arguments[0]) ? 0 : $arguments[0];
        $array = empty($arguments[1]) ? false : true;
        if ($categories->length < $offset or $categories->length == 0) {
            return false;
        }
        if ($array) {
            $list = array();
            foreach ($categories as $category) {
                array_push($list, $category->nodeValue);
            }
            return $list;
        }
        return $categories->item($offset)->nodeValue;
    }

    /**
     * This function will tell us how many times the element $type
     * appears at this level of the feed.
     * 
     * @param	string	$type	the element we want to get a count of
     * @return	int
     */
    protected function count($type)
    {
    	if ($tags = $this->model->getElementsByTagName($type)) {
    		return $tags->length;
    	}
    	return 0;
    }

	/**
	 * Return an XML serialization of the feed, should it be required. Most 
	 * users however, will already have a serialization that they used when 
	 * instantiating the object.
	 *
	 * @return    string    XML serialization of element
	 */    
	function __toString()
	{
	    $simple = simplexml_import_dom($this->model);
	    return $simple->asXML();
	}
}

?>