<?php

class HTMLFragments{

    const
        stylesheet = "../templates/styles.css",
        htmlHeaderTemplate = "../templates/html_header.html",
        htmlFooterTemplate = "../templates/html_footer.html",
        htmlCustomFooterTemplate = "../templates/html_custom_footer.html";

    private
        static $instance = null,
        $exportpath = "",
        $html_header_template = "",
        $html_footer_template = "";

    protected function __construct(){
        $this->exportpath = config::getInstance()->getValue("exportfolder")."/";

        //prepare html header template + stylesheet include
        $stylefile = "styles_". md5( file_get_contents( HTMLFragments::stylesheet ) ). ".css";
        $this->html_header_template =
            preg_replace( "/\[STYLESHEET\]/", "[CHANNELPEDIA_REL_PATH]" . $stylefile, file_get_contents( HTMLFragments::htmlHeaderTemplate));
        //TODO: delete old stylesheet files before copying new one
        if (!file_exists( $this->exportpath . $stylefile))
            copy( HTMLFragments::stylesheet, $this->exportpath . $stylefile );
        $this->html_header_template =
            preg_replace( "/\[INDEX\]/", "[CHANNELPEDIA_REL_PATH]" . "index.html", $this->html_header_template );
            //preg_replace( "/\[INDEX\]/", $this->getCrispFilename( "[CHANNELPEDIA_REL_PATH]" . "index.html" ), $this->html_header_template );

        //footer
        $customfooter = "";
        if ( file_exists( HTMLFragments::htmlCustomFooterTemplate ) ){
            $customfooter = file_get_contents( HTMLFragments::htmlCustomFooterTemplate);
        }
        $this->html_footer_template =
            preg_replace( "/\[CUSTOM_FOOTER\]/", $customfooter, file_get_contents( HTMLFragments::htmlFooterTemplate) );
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
            $checkpath = "../res/icons/flags/".$label.".png";
            if (file_exists( $checkpath ))
                $image = "<img src=\"".$relPath."../res/icons/flags/".$label.".png\" class=\"flag_icon\" />";
            //else
                //die("image $checkpath does not exist! Stopping\n");
        }
        return $image;
    }

}
?>