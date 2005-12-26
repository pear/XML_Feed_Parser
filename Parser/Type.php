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
 * @link       http://pear.php.net/package/XML_Feed_Parser/
 */

/**
 * This abstract class provides some general methods that are likely to be
 * implemented exactly the same way for all feed types.
 *
 * @package XML_Feed_Parser
 * @author  James Stewart <james@jystewart.net>
 * @version Release: @package_version@
 */
abstract class XML_Feed_Parser_Type
{
    /**
     * Where we store our DOM object for this feed 
     * @var DOMDocument
     */
    public $model;

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
			$tempcall = array_pop($this->compatMap[$call]);
			if (! empty($this->compatMap)) {
    	    	$arguments = array_merge($arguments, $this->compatMap[$call]);
			}
			$call = $tempcall;
    	}

        /* To be helpful, we allow a case-insensitive search for this method */
    	if (! isset($this->map[$call])) {
    	    foreach (array_keys($this->map) as $key) {
    	        if (strtoupper($key) == strtoupper($call)) {
    	            $call = $key;
    	            break;
    	        }
    	    }
    	}

        if (empty($this->map[$call]))
        {
            return false;
        }

	    $method = 'get' . $this->map[$call][0];
	    if ($method == 'getLink') {
	        $offset = isset($arguments[0][0]) ? $arguments[0][0] : 0;
	        $attribute = isset($arguments[0][1]) ? $arguments[0][1] : 'href';
	        $params = isset($arguments[0][2]) ? $arguments[0][2] : array();
		    return $this->getLink($offset, $attribute, $params);
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
     * We have other methods which will traverse the DOM and work out the different
     * xml:base declarations we need to be aware of. We then need to combine them.
     * If a declaration starts with a protocol then we restart the string. If it 
     * starts with a / then we add on to the domain name. Otherwise we simply tag 
     * it on to the end.
     *
     * @param   string  $base - the base to add the link to
     * @param   string  $link
     */
    function combineBases($base, $link)
    {
        if (preg_match('/^[A-Za-z]+:\/\//', $link)) {
            return $link;
        } else if (preg_match('/^\//', $link)) {
            /* Extract domain and suffix link to that */
            preg_match('/^([A-Za-z]+:\/\/.*)?\/*/', $base, $results);
            $firstLayer = $results[0];
            return $firstLayer . "/" . $link;
        } else if (preg_match('/^\.\.\//', $base)) {
            /* Step up link to find place to be */
            preg_match('/^((\.\.\/)+)(.*)$/', $link, $bases);
            $suffix = $bases[3];
            $count = preg_match_all('/\.\.\//', $bases[1], $steps);
            $url = explode("/", $base);
            for ($i = 0; $i <= $count; $i++) {
                array_pop($url);
            }
            return implode("/", $url) . "/" . $suffix;
        } else {
            /* Just stick it on the end */
            return $base . $link;
        }
        return $combinedBase;
    }

    /**
     * Gets us the xml:base data and then processes that with regard
     * to our current link.
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

        return $this->combineBases($element->baseURI, $link);
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
			    $xmlBase = $entries->item($offset)->baseURI;
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
     * There is no single way of declaring a category in RSS1/1.1 as there is in RSS2 
	 * and  Atom. Instead the usual approach is to use the dublin core namespace to 
	 * declare  categories. For example delicious use both: 
	 * <dc:subject>PEAR</dc:subject> and: <taxo:topics><rdf:Bag>
     * <rdf:li resource="http://del.icio.us/tag/PEAR" /></rdf:Bag></taxo:topics>
     * to declare a categorisation of 'PEAR'.
     *
     * We need to be sensitive to this where possible.
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
     * We need a couple of methods to access XHTML content stored in feeds. 
     * This is because we dereference all xml:base references before returning
     * the element. This method handles the attributes.
     *
     * @param   DOMElement $node
     * @return  string
     */
    function processXHTMLAttributes($node) {
        $return = '';
        foreach ($node->attributes as $attribute) {
            if ($attribute->name == 'src' or $attribute->name == 'href') {
                $attribute->value = $this->addBase($attribute->value, $attribute);
            }
            if ($attribute->name == "base") {
                continue;
            }
            $return .= $attribute->name . '="' . $attribute->value .'" ';
        }
        return " " . trim($return);
    }

    /**
     * We need a couple of methods to access XHTML content stored in feeds. 
     * This is because we dereference all xml:base references before returning
     * the element. This method recurs through the tree descending from the node
     * and builds our string
     *
     * @param   DOMElement $node
     * @return   string
     */
    function traverseNode($node)
    {
        $content = "";

        /* Add the opening of this node to the content */
        if ($node instanceof DOMElement) {
            $content .= "<" . $node->tagName . 
                $this->processAttributes($node) . ">";
        }

        /* Process children */
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $child) {
                $content .= $this->traverseNode($child);
            }
        }

        if ($node instanceof DOMText) {
            $content .= $node->nodeValue;
        }

        /* Add the closing of this node to the content */
        if ($node instanceof DOMElement) {
            $content .= "</" . $node->tagName . ">";
        }

        return $content;
    }

	/**
	 * Checks if this element has a particular child element.
	 *
	 * @todo	implement
	 * @return bool
	 * @author James Stewart
	 **/
	function hasKey()
	{
		return false;
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