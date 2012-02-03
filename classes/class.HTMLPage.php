<?php

class HTMLPage {

    private
        $relPath = "",
        $pageBody = "",
        $pagetitle = "undefined pagetitle",
        $pageFragments;


    function __construct( $relPath ){
        $this->relPath = $relPath;
        $this->pageFragments = HTMLFragments::getInstance();
    }

    public function setPageTitle( $title ){
        $this->pagetitle = $title;
    }

    public function appendToBody( $content ){
        $this->pageBody .= $content;
    }

    public function getContents(){
        return $this->getHTMLHeader(). $this->pageBody . $this->getHTMLFooter();
    }

    private function getHTMLHeader(){
        return $this->pageFragments->getHTMLHeader($this->pagetitle, $this->relPath);
    }

    private function getHTMLFooter(){
        return $this->pageFragments->getHTMLFooter();
    }
}

?>