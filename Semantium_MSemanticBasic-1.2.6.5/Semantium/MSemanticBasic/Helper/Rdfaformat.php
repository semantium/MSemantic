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
class Semantium_MSemanticBasic_Helper_Rdfaformat extends Semantium_MSemanticBasic_Helper_AbstractFormat
{
	
	
	private $rdfaTag = "div";
	
	

	
	public function useRdfNamespaces($list)
	{
		$array = explode(",", $list);
		$this->_usedRdfNamespaces = array_merge($this->_usedRdfNamespaces, $array);
	}
	
	/**
	 * starts the RDFa tag
	 * @param $rdf boolean
	 * @param $rdfs boolean
	 * @param $eco boolean
	 * @param $gr boolean
	 * @param $owl boolean
	 * @param $xsd boolean
	 * @param $vcard boolean
	 * @param $tag string
	 * @return string boolean
	 */
	public function startRdfa($legalname, $tag = "div")
	{
		
		$this->rdfaTag = $tag;
		$code = "\n".'<'.$this->rdfaTag.' xmlns="http://www.w3.org/1999/xhtml" ';
		
		forEach($this->_usedRdfNamespaces as $namespace)
		{
			$URI = $this->_RDF_NAMESPACE_URI[$namespace];
			$code .= " xmlns:$namespace=\"$URI\"";
		}
		
		// $code .= 'class="rdf2rdfa">'."\n";
		$code .= '>'."\n";
		
		#$code .= '<div typeof="owl:Ontology" about="">'."\n";
		#$code .= '<div property="dc:creator" datatype="xsd:string" content="MSemanticBasic Magento Extension 0.9.9"></div>'."\n";
		#$code .= '<div rel="owl:imports" resource="http://purl.org/goodrelations/v1"></div>'."\n";
		#$code .= '<div property="rdfs:label" datatype="xsd:string" content="RDF/XML data for '.$legalname.', based on http://purl.org/goodrelations/"></div>'."\n";
		#$code .= '<div rel="rdfs:seeAlso" resource=""></div>'."\n";
		#$code .= '</div>'."\n";
		
   		
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
		$wrap = '<div';
		$wrap .= ' typeof="'.$class.'" about="'.$subjectURI.'">'."\n|".'</div>'."\n";
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
		$wrap = '<div rel="'.$rel.'"';
		$wrap .= '>'."\n|".'</div>'."\n";
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
		$code = '<div rel="' . $property . '" resource="' . $resource . '"></div>'."\n";
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
	
	public function rev($property, $resource)
	{
		$code = '<div rev="' . $property . '" resource="' . $resource . '"></div>'."\n";
		return $code;
	}
	
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
		$code = '<div property="'.$property.'" content="'.$content.'"';
		// conditions
		if (isset($datatype))	$code .= ' datatype="'. $datatype .'"';
		if (isset($xmllang))	$code .= ' xml:lang="'. $xmllang .'"';
		// end tag
		$code .= '></div>'."\n";
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
		// start tag
		$code = '<div property="'.$property.'" content="'.$content.'"';
		// additional tag attributes string (starts with whitespace)

// if (isset) 

	
		if ($addTagAttributes != '')	$code .= ' ' . $addTagAttributes;
		// end tag
		$code .= '></div>'."\n";
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
				$datatypes[$dtName] = str_replace($markers, $replaceValues, str_replace('http://www.w3.org/2001/XMLSchema#', 'xsd:', $dtAttr));
			    
			}
			
			return $datatypes;
		}
	}
}