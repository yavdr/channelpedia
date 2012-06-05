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

class singleSourceHTMLReportBase extends HTMLReportBase{

    function __construct( HTMLOutputRenderSource & $obj ){
        $this->parent = $obj;
        parent::__construct( $this->parent->getRelPath() );
    }

    protected function addBodyHeader( $language = ""){
        $this->appendToBody( $this->getSectionTabmenu( $language ) );
        parent::addBodyHeader();
    }

    protected function addToOverviewAndSave( $menuLabel, $filename ){
        parent::addToOverviewAndSave( $menuLabel, $filename, $this->description );
    }

    private  function getSectionTabmenu($language){
        $menu = new HTMLControlTabMenu( $this->parent->getRelPath(), $this->config->getValue("exportfolder"));
        $sourceMenuItem = $this->parent->getVisibleType() . ": " . $this->parent->getPureSource();
        switch ($language){
        case "overview":
            $language = "";
            $menu->addMenuItem( $sourceMenuItem, "index.html", "active", false );
            break;
        case "":
            $language = "";
            $menu->addMenuItem( $sourceMenuItem, "index.html", "", false );
            break;
        default:
            $menu->addMenuItem( $sourceMenuItem, "../index.html", "", false );
            break;
        }
        foreach ($this->parent->getLanguages() as $language_temp){
            if ("" == $language)
                $menu->addMenuItem($language_temp, $language_temp."/index.html", "", true);
            else{
                $class = ($language_temp == $language) ? "active" : "";
                $menu->addMenuItem($language_temp, "../". $language_temp."/index.html", $class, true );
            }
        }
        return $menu->getMarkup();
    }

    protected function getCustomChannelListSQL( $type, $encrypted = false, $customwhere, $columns ){
        if ($type == "TV")
            $type_where = "AND vpid != '0'";
        else if ($type == "Radio")
            $type_where = "AND vpid = '0' AND apid != '0'";
        else if ($type == "Data")
            $type_where = "AND vpid == '0' AND apid == '0'";
        else{
            $type = "";
            $type_where = "";
        }

        if ($encrypted)
            $caidflag = "!=";
        else
            $caidflag = "=";

        return "
                SELECT ".$columns." FROM channels WHERE source = ".$this->db->quote($this->parent->getSource())."
                AND caid $caidflag '0'
                ".$customwhere."
                ".$type_where."
                ORDER BY frequency, parameter, symbolrate, sid
        ";
    }
}
?>