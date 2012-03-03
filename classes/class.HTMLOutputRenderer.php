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

define( 'PATH_TO_GLOBAL_REPORT_CLASSES', dirname(__FILE__) );
require_once PATH_TO_GLOBAL_REPORT_CLASSES . '/HTMLReportBase.php';
require_once PATH_TO_GLOBAL_REPORT_CLASSES . '/globalHTMLReportBase.php';
require_once PATH_TO_GLOBAL_REPORT_CLASSES . '/GlobalHTMLReports/globalChangelog.php';
require_once PATH_TO_GLOBAL_REPORT_CLASSES . '/GlobalHTMLReports/deComparison.php';
require_once PATH_TO_GLOBAL_REPORT_CLASSES . '/GlobalHTMLReports/uniqueIDs.php';
require_once PATH_TO_GLOBAL_REPORT_CLASSES . '/GlobalHTMLReports/uploadLog.php';
require_once PATH_TO_GLOBAL_REPORT_CLASSES . '/GlobalHTMLReports/globalIndexPage.php';

class HTMLOutputRenderer{

    private
        $db,
        $config,
        $homepageLinkList = array();

    function __construct(){
        $this->db = dbConnection::getInstance();
        $this->config = config::getInstance();
    }

    public function getCraftedPath(){
        return "";
    }

    public function getRelPath(){
        return "";
    }

    public function getHomepageLinkList(){
        return $this->homepageLinkList;
    }

    public function renderAllHTMLPages(){
        $this->addDividerTitle("DVB sources");
        $this->addDVBType( "S", "sat_positions",   "Satellite positions");
        $this->addDVBType( "C", "cable_providers", "Cable providers");
        $this->addDVBType( "T", "terr_providers",  "Terrestrial providers");
        $this->closeHierarchy();
        $this->addDividerTitle("Global reports");
        $this->renderGlobalReports();
        $this->closeHierarchy();
        $x = new globalIndexPage( $this);
        $x->popuplatePageBody();
    }

    private function addDVBType( $key, $configValue, $title){
        if (count($this->config->getValue($configValue)) > 0 ){
            $this->addDividerTitle($title);
            foreach ($this->config->getValue($configValue) as $provider => $languages){
                $this->renderPagesOfSingleSource( $key, $provider, $languages );
            }
            $this->closeHierarchy();
        }
    }

    public function renderPagesOfSingleSource( $type, $puresource, $languages ){
        $x = new HTMLOutputRenderSource( $type, $puresource, $languages );
        $this->homepageLinkList[] = $x->render();
    }

    public function renderGlobalReports(){
        $x = new globalChangelog( $this);
        $x->popuplatePageBody();
        $this->homepageLinkList[] = $x->getParentPageLink();
        $x = new uploadLog( $this);
        $x->popuplatePageBody();
        $this->homepageLinkList[] = $x->getParentPageLink();
        $x = new deComparison( $this);
        $x->popuplatePageBody();
        $this->homepageLinkList[] = $x->getParentPageLink();
        $x = new uniqueIDs( $this);
        $x->popuplatePageBody();
        $this->homepageLinkList[] = $x->getParentPageLink();

    }

    private function addDividerTitle( $title ){
        $this->homepageLinkList[] = array( $title, "");
    }

    private function closeHierarchy(){
        $this->homepageLinkList[] = array( "", "close");
    }

}
?>