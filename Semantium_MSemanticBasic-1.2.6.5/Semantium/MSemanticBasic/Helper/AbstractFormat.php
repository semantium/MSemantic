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


abstract class Semantium_MSemanticBasic_Helper_AbstractFormat extends Mage_Core_Helper_Abstract
{
	

	protected $_RDF_NAMESPACE_URI = array(
		"rdf"		=> "http://www.w3.org/1999/02/22-rdf-syntax-ns#",
		"rdfs"		=> "http://www.w3.org/2000/01/rdf-schema#",
		"xsd"		=> "http://www.w3.org/2001/XMLSchema#", 
		"vcard"		=> "http://www.w3.org/2006/vcard/ns#",
		"foaf"		=> "http://xmlns.com/foaf/0.1/",
		"gr"		=> "http://purl.org/goodrelations/v1#",
		"eco"		=> "http://www.ebusiness-unibw.org/ontologies/eclass/5.1.4/#",
		"owl"		=> "http://www.w3.org/2002/07/owl#",
		"product"	=> "http://search.yahoo.com/searchmonkey/product/",
		"dc"		=> "http://purl.org/dc/elements/1.1/",
		"media"		=> "http://search.yahoo.com/searchmonkey/media/", 
		"v"         => "http://rdf.data-vocabulary.org/#"
		
	);
	protected $_usedRdfNamespaces = array();
	// default marker replacements for this->datatypes
	protected $_defaultReplacements	= array(
		"{lang}"	=> "en",
		"{rdf}"		=> "",		// can be "rdf:" or empty (for rdfa)
		"{xml}"		=> "xml:"
	);
	
	
	// http://www.w3.org/2001/XMLSchema#fl
	
	// additional attributes
	protected $_datatypes = array (
		"gr:legalName"				=> '{xml}lang="{lang}"',
		"gr:validFrom"				=> '{rdf}datatype="http://www.w3.org/2001/XMLSchema#dateTime"',
		"gr:validThrough"			=> '{rdf}datatype="http://www.w3.org/2001/XMLSchema#dateTime"',
		"gr:eligibleRegions"		=> '{rdf}datatype="http://www.w3.org/2001/XMLSchema#string"',
		"gr:hasCurrencyValue"		=> '{rdf}datatype="http://www.w3.org/2001/XMLSchema#float"',
		"gr:hasMaxCurrencyValue"	=> '{rdf}datatype="http://www.w3.org/2001/XMLSchema#float"',
		"gr:hasMinCurrencyValue"	=> '{rdf}datatype="http://www.w3.org/2001/XMLSchema#float"',
		"gr:hasCurrency"			=> '{rdf}datatype="http://www.w3.org/2001/XMLSchema#string"',
		"gr:hasUnitOfMeasurement"	=> '{rdf}datatype="http://www.w3.org/2001/XMLSchema#string"',
		"gr:valueAddedTaxIncluded"	=> '{rdf}datatype="http://www.w3.org/2001/XMLSchema#boolean"',
		"gr:amountOfThisGood"		=> '{rdf}datatype="http://www.w3.org/2001/XMLSchema#float"',
		"gr:hasEAN_UCC-13"			=> '{rdf}datatype="http://www.w3.org/2001/XMLSchema#string"',
		"gr:hasGTIN-8"			=> '{rdf}datatype="http://www.w3.org/2001/XMLSchema#string"',
		"gr:hasGTIN-14"			=> '{rdf}datatype="http://www.w3.org/2001/XMLSchema#string"',
		
		"vcard:country-name"		=> '{xml}lang="{lang}"',
		"vcard:email"				=> NULL,
		"vcard:locality"			=> NULL,
		"vcard:postal-code"			=> NULL,
		"vcard:street-address"		=> NULL,
		"vcard:tel"					=> NULL,
		"vcard:url"					=> NULL,
		"vcard:fn"					=>'{xml}lang="{lang}"',
		
		"rdfs:isDefinedBy"			=> NULL,
		"rdfs:label"				=> '{xml}lang="{lang}"',
		
		// multiple properties
		"rdfs:label vcard:fn gr:legalName" => '{xml}lang="{lang}"'
	);
}
