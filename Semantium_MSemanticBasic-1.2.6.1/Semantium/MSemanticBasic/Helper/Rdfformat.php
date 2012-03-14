<?php
/**
 * MSemanticBasic Magento Extension
 * @package Semantium_MSemanticBasic
 * @copyright (c) 2010 Semantium, Uwe Stoll <stoll@semantium.de>
 * @author Michael Lambertz <michael@digitallifedesign.net>
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with this program; if not, see <http://www.gnu.org/licenses/>
**/
class Semantium_MSemanticBasic_Helper_Rdfformat extends Semantium_MSemanticBasic_Helper_AbstractFormat
{	
	private $rdfaTag = "rdf:RDF";
	
	public function __construct()
	{
		$this->_defaultReplacements["{rdf}"] = "rdf:";
	}
	
	public function useRdfNamespaces($list)
	{
		$array = explode(",", $list);
		$this->_usedRdfNamespaces = array_merge($this->_usedRdfNamespaces, $array);
	}
	
	/**
	 * starts the RDFa tag
	 */
	public function startRdfa($legalname, $tag = "rdf:RDF")
	{
		$code = '<?xml version="1.0" encoding="UTF-8"?>';
		$code .= "\n";
		/*
		$code .= '<!DOCTYPE rdf:RDF';
		$code .= ' [';
		$code .= "\n";
		forEach($this->_usedRdfNamespaces as $namespace)
		{
			$URI = $this->_RDF_NAMESPACE_URI[$namespace];
			$code .= "\t<!ENTITY $namespace \"$URI\">";
			$code .= "\n";
		}
		$code .= "\t]";
		$code .= ">";
		$code .= "\n";
		*/
		$this->rdfaTag = $tag;
		$code .= '<'.$this->rdfaTag.' ';
		
		forEach($this->_usedRdfNamespaces as $namespace)
		{
			$URI = $this->_RDF_NAMESPACE_URI[$namespace];
			$code .= "xmlns:$namespace=\"$URI\" ";
		}
		
		$code .= '>'."\n\n";
		$code .= '<owl:Ontology rdf:about="">'."\n";
		$code .= '<dc:creator rdf:datatype="http://www.w3.org/2001/XMLSchema#string">MSemanticBasic Magento Extension 0.9.9</dc:creator>'."\n";
		$code .= '<owl:imports rdf:resource="http://purl.org/goodrelations/v1" />'."\n";
		$code .= '<rdfs:label rdf:datatype="http://www.w3.org/2001/XMLSchema#string">RDF/XML data for '.$legalname.', based on http://purl.org/goodrelations/</rdfs:label>'."\n";
		$code .= '<rdfs:seeAlso rdf:resource=""/>'."\n";
		$code .= '</owl:Ontology>'."\n\n";
		
		return $code;
	}
	
	/**
	 * ends the RDFa tag
	 * @return string
	 */
	public function endRdfa()
	{
		$code = '</'.$this->rdfaTag.'>'."\n";
		return $code;
	}
	
	/**
	 * 
	 * @param $statements string
	 * @param $subjectURI string
	 * @param $class string
	 * @return string
	 */
	public function wrapStatements($statements, $subjectURI, $class)
	{
		$wrap = '<'.$class.' rdf:about="'.$subjectURI.'">'."\n|".'</'.$class.'>'."\n";
		$wrapped = $this->wrap($statements, $wrap);
		return $wrapped;
	}
	
	/**
	 * 
	 * @param $attributes
	 * @param $rel
	 * @param $resource
	 * @return string
	 */
	public function wrapRel($attributes, $rel)
	{
		$wrap = '<'.$rel;
		$wrap .= '>'."\n|".'</'.$rel.'>'."\n";
		$wrapped = $this->wrap($attributes, $wrap);
		return $wrapped;
	}
	
	/**
	 * 
	 * @param $resource
	 * @param $rel
	 * @return string
	 */
	public function rel($property, $resource)
	{
		$code = "";
		if (strpos($property," ") === FALSE)
		{
			$code .= '<'.$property.' rdf:resource="' . $resource . '"/>'."\n";
		}
		else
		{
			$keys = explode(" ", $property);
			foreach($keys as $key)
			{
				$code .= $this->rel($key, $resource);
			}
		}
		
		return $code;
	}
	
	public function rels($relsArray)
	{
		$code = "";
		forEach ($relsArray as $property => $resource)
		{
			$code .= $this->rel($key, $resource);
		}
		return $code;
	}
	
	// rev disabled
	/*
	public function rev($property, $resource)
	{
		$code = '<'.$property.' rdf:resource="' . $resource . '"></'.$property.'>'."\n";
		return $code;
	}
	*/
	
	public function revs($property, $resource)
	{
		$code = "";
		forEach ($revsArray as $key => $revconf)
		{
			$code .= $this->rev($revconf['resource'], $revconf["rev"]);
		}
		return $code;
	}
	
	/**
	 * creates a simple RDFa property
	 * @param $property string
	 * @param $content string
	 * @param $datatype string :property value
	 * @param $xmllang string :property value
	 * @return string
	 */
	public function property($property, $content, $datatype=NULL, $xmllang=NULL)
	{
		// start tag
		$code = '<'.$property;
		// conditions
		if (isset($datatype))	$code .= ' rdf:datatype="'. $datatype .'"';
		if (isset($xmllang))	$code .= ' xml:lang="'. $xmllang .'"';
		// end tag
		$code .= '>'.$content.'</'.$property.'>'."\n";
		return $code;
	}
	
	/**
	 * creates a simple RDFa property (with additional tag attributes)
	 * @param $property string
	 * @param $content string
	 * @param $addTagAttributes string :all additional attributes as string
	 * @return string
	 */
	public function propertyFast($property, $content, $addTagAttributes=NULL)
	{
		$code = '<'.$property;
		// additional tag attributes string (starts with whitespace)
		if ($addTagAttributes != '')	$code .= ' ' . $addTagAttributes;
		// end tag
		$code .= '>'.$content.'</'.$property.'>'."\n";
		return $code;
	}
	
	/**
	 * generates multiple properties
	 * @param $array
	 * @param $lang language
	 * @return string
	 */
	public function properties($array, $replacements=NULL )
	{
		$code = "";
		// replace markers in datatypes
		$datatypes = $this->getDatatypes($replacements);
		forEach ($array as $property=>$content)
		{
			if (array_key_exists($property, $datatypes))
			{
				// generate code
				$code .= $this->propertyFast($property, $content, $datatypes[$property]);
			}
			else @webdirx_div::debug("Property $property unknown - no datatype defined");
		}
		return $code;
	}
	
	// property with auto attributes from $this->_datatypes
	public function propertyAuto($property, $content, $replacements=NULL)
	{
		$code = "";
		// replace markers in datatypes
		$datatypes = $this->getDatatypes($replacements);
		if (array_key_exists($property, $datatypes))
		{
			$code = $this->propertyFast($property, $content, $datatypes[$property]);
		}
		else @webdirx_div::debug("Property $property unknown - no datatype defined");
		
		return $code;
	}
	
	/**
	 * wraps the content with the wrap (replaces the wrap symbol in the wrap with the content)
	 * @param $content string
	 * @param $wrap string
	 * @param $wrapSymbol string
	 * @return string
	 * 
	 * @author Michael Lambertz, 2006
	 */
	protected function wrap($content,$wrap, $wrapSymbol = "|")
	{
		$wrappedContent = str_replace($wrapSymbol, $content, $wrap);
		return $wrappedContent;
	}
	
	protected function getDatatypes($replacements=NULL)
	{
		if (!is_array($replacements)) return $this->_datatypes;
		else
		{
			$replacements = array_merge($this->_defaultReplacements, $replacements);
			$markers = array_keys($replacements);
			$replaceValues = array_values($replacements);
			
			$datatypes = array();
			forEach ($this->_datatypes as $dtName => $dtAttr)
			{
				$datatypes[$dtName] = str_replace($markers, $replaceValues, $dtAttr);
			}
			return $datatypes;
		}
	}
}