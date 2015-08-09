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
        stylesheet = "styles_gen.css",
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
                die(
                    "File not present: ".config::getInstance()->getValue("exportfolder") . $stylefile.
                    "</br>copy failed: " . $this->exportpath . HTMLFragments::stylesheet . " -> " . config::getInstance()->getValue("exportfolder") . $stylefile . "\n"
                );
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

    public function getHTMLHeader($pagetitle = "", $relPath = "", $keywords = "", $description = ""){
        if ($keywords === "")
            $keywords = "";
        if ($description === "")
            $description = "Pre-sorted channel list collections for VDR 1.7.4+ and ReelBox";
        return preg_replace(
            array(
                "/\[PAGE_TITLE\]/",
                "/\[CHANNELPEDIA_REL_PATH\]/",
                "/\[KEYWORDS\]/",
                "/\[DESCRIPTION\]/",
            ),
            array(
                htmlspecialchars( $pagetitle ),
                htmlspecialchars( $relPath ),
                htmlspecialchars( $keywords ),
                htmlspecialchars( $description )
            ),
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
                $retVal = "./";
        }
        return $retVal;
    }

    public function getFlagIcon($label, $relPath){
        $image = "";
        if ($label != "uncategorized"){
            if ($label == "uk"){
                $filename = "gb";
            }
            else if ($label == "sky_de"){
                $filename = "de";
            }
            else
                $filename = $label;
            $checkpath = config::getInstance()->getValue("data_path")."/res/icons/flags/".$filename.".png";
            if (file_exists( $checkpath ))
                $image = "<img src=\"".$relPath."../res/icons/flags/".$filename.".png\" title=\"".$label."\" class=\"flag_icon\" />";
            //else
                //die("image $checkpath does not exist! Stopping\n");
        }
        return $image;
    }

    public function getScrambledIcon( $caid, $relPath ){
        //lock icon taken from http://www.openwebgraphics.com/resources/data/1629/lock.png
        return (($caid !== "0")? '<img src="'.$relPath.'../res/icons/lock.png" class="lock_icon" title="'.htmlspecialchars($caid).'" />':'');
    }

    public function getWikipediaIcon($label, $relPath){
        return '<img class="wikipedia_icon" title="' .$label.'" src="'.$relPath.'../res/icons/wikipedia.ico" />';
    }

}

class HTMLControlTabMenu{

    private
        $relPath,
        $controlMarkup,
        $exportfolder;

    function __construct($relPath, $exportfolder){
        $this->relPath = $relPath;
        $this->controlMarkup = "";
        $this->exportfolder = $exportfolder;
    }

    public function addMenuItem( $label, $filename, $class = "", $showflagicon = false){
        $classAttr = ($class === "") ? "" : ' class="'.$class.'"';
        $path = $this->exportfolder . substr( $filename, 0, strrpos ( $filename , "/" ) );
        //$this->config->addToDebugLog( "HTMLOutputRenderer/getMenuItem: file '".$filename."', link: '$link'\n" );
        $label = ($showflagicon ? HTMLFragments::getFlagIcon($label, $this->relPath) : "") . $label;
        if ($class !== "active")
            $label = '<a href="'.HTMLFragments::getCrispFilename($filename).'">'. $label .'</a>';
        else
            $label = '<span>'. $label .'</span>';
        $this->controlMarkup .= '<li'.$classAttr.'>' . $label . '</li>'."\n";
    }

    public function getMarkup(){
        return "<ul class=\"section_menu\">\n" . $this->controlMarkup . "<br clear=\"all\" /></ul>\n";

    }
}

?>