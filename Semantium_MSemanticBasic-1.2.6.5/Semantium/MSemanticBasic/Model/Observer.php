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

class Semantium_MSemanticBasic_Model_Observer
{
	
	public function submitSemanticWebData()
	{
		$this->debug("Cronjob gestartet");
		$this->notifySWSE();
		$this->debug("Cronjob beendet");
		return $this;
	}

	 
	
	protected function notifySWSE($submission_url="http://gr-notify.appspot.com/submit?uri=") {
		$email = Mage::getStoreConfig('trans_email/ident_general/email');
		$base_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
		$sitemap_url = $submission_url.$base_url."sitemap.xml"."&contact=".$email."&agent=msemantic-1.2.6.5";
		$this->_httpGet($sitemap_url);	
		}
	
	
	/**
	 * for PTSW
	 */
	protected function jsEscape($str) {
		$r = array(
			' '	=> '%20',
			'!'	=> '%21',
			'@'	=> '@',
			'#'	=> '%23',
			'$'	=> '%24',
			'%'	=> '%25',
			'^'	=> '%5E',
			'&'	=> '%26',
			'*'	=> '*',
			'('	=> '%28',
			')'	=> '%29',
			'-'	=> '-',
			'_'	=> '_',
			'='	=> '%3D',
			'+'	=> '+',
			':'	=> '%3A',
			';'	=> '%3B',
			'.'	=> '.',
			'"'	=> '%22',
			"'"	=> '%27',
			'\\'	=> '%5C',
			'/'	=> '/',
			'?'	=> '%3F',
			'<'	=> '%3C',
			'>'	=> '%3E',
			'~'	=> '%7E',
			'['	=> '%5B',
			']'	=> '%5D',
			'{'	=> '%7B',
			'}'	=> '%7D',
			'`'	=> '%60',
			'€'	=> '%u20AC'
		);
		$needles = array_keys($r);
		$replacements = array_values($r);
		$output = $str;
		$output = str_replace($needles, $replacements, $output);
		return $output;
	}
	
	protected function debug($content)
	{
		 if (!$_GET['debug']) return;
		
		$type = gettype($content);
		echo '<div style="background-color: #FFF; border: solid 1px #000; padding: 5px 10px; font: 11px/15px Arial,sans-serif; display: block; color: #444; text-align: left; width: 960px; margin: 0px auto;">';
		echo "<b> | </b>";
		echo "<i>($type)</i>";
		
		switch (gettype($content)) {
			case 'array':
				echo "<pre>";
    			print_r($content);
    			echo "</pre>";
				break;
			case 'object':
				echo "<pre>";
    			print_r($content);
    			echo "</pre>";
				break;
			case 'boolean':
				if ($content == TRUE) echo "<b> TRUE </b>";
				else echo "<b> FALSE </b>";
				break;
			default:
				echo "<pre>$content</pre>";
				break;
		}
		echo "<b> | </b></div>";
	}


protected function _httpGet($url)
	{
		// file operations are allowed
		if (ini_get('allow_url_fopen') == '1') {
			$str = file_get_contents($url);
			if($str === false) {
				$http_status_code = "";
			    for($i=0; $i<count($http_response_header); $i++)
			    {
			        if(strncasecmp("HTTP", $http_response_header[$i], 4)==0)
			        {
						// determine HTTP response code
						$http_status_code = preg_replace("/^.{0,9}([0-9]{3})/i", "$1", $http_response_header[$i]);
			            break;
			        }
			    }
				echo "<p class=\"error\">Submission failed: ".$http_status_code."</p>";
			}
			return $str;
		}
		// file operations are disallowed, try it like curl
		else {
			$url = parse_url($url);
			$port = isset($url['port'])?$url['port']:80;

			$fp = fsockopen($url['host'], $port);

			if(!$fp) {
				echo "<p class=\"error\">Cannot retrieve $url</p>";
				return false;
			}
			else {
				// send the necessary headers to get the file
				fwrite($fp, "GET ".$url['path']."?".$url['query']." HTTP/1.0\r\n".
					"Host:". $url['host']."\r\n".
					"Accept: text/html\r\n".
					"User-Agent: MSemantic v2\r\n".
					"Connection: close\r\n\r\n");

				// retrieve response from server
				$buffer = "";
				$status_code_found = false;
				$is_error = false;
				while($line = fread($fp, 4096))
				{
					$buffer .= $line;
					if(!$status_code_found && ($pos=strpos($line, "HTTP"))>=0) {
						// extract HTTP response code
						$response = explode("\n", substr($line, $pos));
						$http_status_code = preg_replace("/^.{0,9}([0-9]{3})/i", "$1", $response[0]);
						$is_error = !preg_match("/(200|406)/i", $http_status_code); // accepted status codes not resulting in error are 200 and 406
						$status_code_found = true;
					}
				}
				fclose($fp);
				
				$pos = strpos($buffer,"\r\n\r\n");
				if($is_error)
					echo "<p class=\"error\">Submission failed: ".$http_status_code."</p>";
				return substr($buffer,$pos);
			}
		}
	}


}

 
 // $a = new Semantium_MSemanticBasic_Model_Observer;
//  $a->submitSemanticWebData();
// echo var_dump(Mage::getBaseUrl());
 
 
 

// perform HTTP GET on endpoint with given URL
// thanks go to Alex Stolz // goodrelations-for-joomla


/* Deprecated
	
	protected function pingTheSemanticWeb()
	{
		// Shop Models
		/
		$this->Business = Mage::getModel('msemanticbasic/Business');
		$client = new Zend_XmlRpc_Client('http://rpc.pingthesemanticweb.com');
		$params = array(
			"name"	=> $this->Business->getLegalName(),
			"url"	=> Mage::getUrl('')."semanticweb.rdf"
			);
		$result = $client->call('weblogUpdates.ping', $params);
		$this->debug($result);
		
		// ganz normal per GET-Methode
		//$this->debug(Mage::getUrl() . 'semanticweb.rdf');
		$url = Mage::getBaseUrl() . "semanticweb.rdf";
		$url = str_replace('index.php/', '', $url);	// important to remove index.php/ fom the base_url
		//$this->debug($url);
		$ptswlink = 'http://pingthesemanticweb.com/ping.php?url=' . $this->jsEscape($url);
		$this->debug(implode("\n", file($ptswlink)));
		return;
	}
	
	protected function pingSindice()
	{

		// Shop Models
		$this->Business = Mage::getModel('msemanticbasic/Business');
		$client = new Zend_XmlRpc_Client('http://sindice.com/xmlrpc/api');
		$params = array(
			"name"	=> $this->Business->getLegalName(),
			"url"	=> Mage::getUrl('')."semanticweb.rdf"
			);
		$result = $client->call('weblogUpdates.ping', $params);
		$this->debug($result);
	}
	*/

?>
	
