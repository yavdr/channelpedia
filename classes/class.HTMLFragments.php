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

class HTMLFragments{

    const
        stylesheet = "styles.css",
        htmlHeaderTemplate = "html_header.html",
        htmlFooterTemplate = "html_footer.html",
        htmlCustomFooterTemplate = "html_custom_footer.html";

    private
        static $instance = null,
        $exportpath = "",
        $html_header_template = "",
        $html_footer_template = "";

    protected function __construct(){
        $this->exportpath = config::getInstance()->getValue("engine_path")."templates/";

        //prepare html header template + stylesheet include
        $stylefile = "styles_". md5( file_get_contents( $this->exportpath . HTMLFragments::stylesheet ) ). ".css";
        $this->html_header_template =
            preg_replace( "/\[STYLESHEET\]/", "[CHANNELPEDIA_REL_PATH]" . $stylefile, file_get_contents( $this->exportpath . HTMLFragments::htmlHeaderTemplate));
        //TODO: delete old stylesheet files before copying new one
        if (!file_exists( config::getInstance()->getValue("exportfolder") . $stylefile)){
            if (!copy( $this->exportpath . HTMLFragments::stylesheet, config::getInstance()->getValue("exportfolder") . $stylefile )){
                die("copy failed: " .  $this->exportpath . HTMLFragments::stylesheet . " -> " . config::getInstance()->getValue("exportfolder") . $stylefile . "\n");
            }
        }
        $this->html_header_template =
            preg_replace( "/\[INDEX\]/", "[CHANNELPEDIA_REL_PATH]" . "index.html", $this->html_header_template );
            //preg_replace( "/\[INDEX\]/", $this->getCrispFilename( "[CHANNELPEDIA_REL_PATH]" . "index.html" ), $this->html_header_template );

        //footer
        $customfooter = "";
        if ( file_exists( $this->exportpath . HTMLFragments::htmlCustomFooterTemplate ) ){
            $customfooter = file_get_contents( $this->exportpath . HTMLFragments::htmlCustomFooterTemplate);
        }
        $this->html_footer_template =
            preg_replace( "/\[CUSTOM_FOOTER\]/", $customfooter, file_get_contents( $this->exportpath . HTMLFragments::htmlFooterTemplate) );
    }

    private function __clone(){}

    public static function getInstance(){
        if ( self::$instance == null){
            self::$instance = new HTMLFragments();
        }
        return self::$instance;
    }

    public function getHTMLHeader($pagetitle, $relPath){
        return preg_replace(
            array( "/\[PAGE_TITLE\]/", "/\[CHANNELPEDIA_REL_PATH\]/" ),
            array( htmlspecialchars( $pagetitle ), $relPath ),
            $this->html_header_template
        );
    }

    public function getHTMLFooter(){
        return $this->html_footer_template;
    }

    public function getCrispFilename( $filename){
        $retVal = $filename;
        if (CUT_OFF_INDEX_HTML){
            $retVal = str_replace("index.html","", $filename);
            if ($retVal == "")
                $retVal = "/";
        }
        return $retVal;
    }

    public function getFlagIcon($label, $relPath){
        $image = "";
        if ($label != "uncategorized"){
            if ($label == "uk"){
                $label = "gb";
            }
            $checkpath = config::getInstance()->getValue("data_path")."/res/icons/flags/".$label.".png";
            if (file_exists( $checkpath ))
                $image = "<img src=\"".$relPath."../res/icons/flags/".$label.".png\" class=\"flag_icon\" />";
            //else
                //die("image $checkpath does not exist! Stopping\n");
        }
        return $image;
    }

}
?>