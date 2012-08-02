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
$installer = $this;

$installer->installEntities();



// perform HTTP GET on endpoint with given URL
function _semantiumMsemanticInstallHttpGet($url)
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
                                "User-Agent: GoodRelations Extension for Joomla v2\r\n".
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