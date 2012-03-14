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
class Semantium_MSemanticBasic_Helper_Div extends Mage_Core_Helper_Abstract
{
	
	/**
     * converts string to foldername (ends with a slash)
     * @param $string
     * @return string
     */
	public function toFolder($string)
	{
		return $this->noEndSlash( $string ) ."/";
	}
	/**
	 * trims name and removes Slashes at the end of a string (e.g. symlink-files)
	 * @param $string
	 * @return string
	 */
	public function noEndSlash($string)
	{
		$target = trim($string);
		if (substr($target, -1) == "/") $target = substr($target, 0, (strlen($target)-1) );
		return $target;
	}
	
	public function dateToIso8601($timestring )
	{
		$timestamp = strtotime($timestring);
		
		$timezoneOffsetSeconds =  Mage::getModel('core/date')->getGmtOffset();
		if ($timezoneOffsetSeconds == 0) $timezoneString = "Z";
		else $timezoneString = $this->secondsToHoursMinutes($timezoneOffsetSeconds);
		
		$date = date("Y-m-d\TH:i:s",$timestamp) . $timezoneString;
		return $date;
	}
	
	public function secondsToHoursMinutes($seconds)
	{
		if ($seconds<0)
		{
			$seconds = -$seconds;
			$prefix = "-";
		}
		else
		{
			$prefix = "+";
		}
		$fullHours = $this->nulls (floor($seconds/3600));
		$minutes = $seconds % 3600;
		$fullMinutes = $this->nulls (round($minutes/60));
		return "$prefix$fullHours:$fullMinutes";
	}
	
	public function nulls($number, $digits=2)
	{
		while(strlen($number) < $digits)
		$number = "0".$number;
		return $number;
	}
	
	
	/**
	 * removes newlines and html tags e.g. for xml attributes
	 * new: strips html special chars
	 * @param $subject
	 * @return unknown_type
	 */
	public function removeTagsAndNls($subject)
	{
		$subject = strip_tags($subject);
	    
	    // $subject = htmlspecialchars($subject, ENT_QUOTES, "UTF-8", false);
		$subject = trim( preg_replace("/[\n\r]/"," ",$subject) );
		$subject = html_entity_decode($subject, ENT_QUOTES, "UTF-8");
		return $subject;
	}
	
}