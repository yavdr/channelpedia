<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 - 2012 Henning Pingel
*  All rights reserved
*
*  This script is part of the yaVDR project. yaVDR is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*/

class Uncategorized extends ruleBase{

    private
        $db,
        $priorityCount,
        $source;

    function __construct(){
        $this->source = "";
        $this->db = dbConnection::getInstance();
    }

    function getConfig(){
        return array (
            "country" => "uncategorized",
            "lang" => "uncategorized", //make sure that all dynamically generated rules have languageOverrule set to ""
            "validForSatellites" => "all",
            "validForCableProviders" => "all",
            "validForTerrProviders" => "all",
        );
    }

    public function setSource($source){
        $this->source = $source;
    }

    function getGroups(){
        $this->priorityCount = 1;
        $groups = array();
        $result = $this->db->query(
            "SELECT provider, COUNT(*) AS providercount FROM channels ".
            "WHERE source = ".$this->db->quote($this->source).
            " AND x_label = '' GROUP BY provider ORDER by providercount DESC"
        );
        foreach ($result as $row) {
            $groups2 = $this->getGroupSkeletonForProvider( $row["provider"] );
            array_splice( $groups, count($groups), 0, $groups2);
        }
        //print_r($groups);
        //die();
        return $groups;
    }

    private function getGroupSkeletonForProvider( $provider ){
        return array (
            array(
                "title" => $provider,
                "outputSortPriority" => $this->priorityCount++,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeHDTV,
                "languageOverrule" => "",
                "customwhere" => " AND provider = " . $this->db->quote( $provider )
            ),
            array(
                "title" => $provider,
                "outputSortPriority" => $this->priorityCount++,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeHDTV,
                "languageOverrule" => "",
                "customwhere" => " AND provider = " . $this->db->quote( $provider )
            ),
            array(
                "title" => $provider,
                "outputSortPriority" => $this->priorityCount++,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeSDTV,
                "languageOverrule" => "",
                "customwhere" => " AND provider = " . $this->db->quote( $provider )
            ),
            array(
                "title" => $provider,
                "outputSortPriority" => $this->priorityCount++,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeSDTV,
                "languageOverrule" => "",
                "customwhere" => " AND provider = " . $this->db->quote( $provider )
            ),
            array(
                "title" => $provider,
                "outputSortPriority" => $this->priorityCount++,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeRadio,
                "languageOverrule" => "",
                "customwhere" => " AND provider = " . $this->db->quote( $provider )
            ),
            array(
                "title" => $provider,
                "outputSortPriority" => $this->priorityCount++,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeRadio,
                "languageOverrule" => "",
                "customwhere" => " AND provider = " . $this->db->quote( $provider )
            ),
            array(
                "title" => $provider,
                "outputSortPriority" => $this->priorityCount++,
                "caidMode" => self::caidModeFTA,
                "mediaType" => self::mediaTypeData,
                "languageOverrule" => "",
                "customwhere" => " AND provider = " . $this->db->quote( $provider )
            ),
            array(
                "title" => $provider,
                "outputSortPriority" => $this->priorityCount++,
                "caidMode" => self::caidModeScrambled,
                "mediaType" => self::mediaTypeData,
                "languageOverrule" => "",
                "customwhere" => " AND provider = " . $this->db->quote( $provider )
            ),
        );
    }

}

?>