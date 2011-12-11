<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Henning Pingel
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

class channelListWriter extends channelIterator{

    private
        $filehandle = null,
        $filename = "",
        $delimiters = false,
        $config;

    function __construct($label = "_complete", $type, $puresource, $orderby = "UPPER(name) ASC"){
        $this->config = config::getInstance();
        $xlabel = $label;
        if ($label === "_complete"){
            $this->delimiters = "transponders";
            if ($type == "S")
              $orderby = "substr(parameter,1,1), frequency, sid ASC"; //horiz/vertic
            else
              $orderby = "frequency, parameter, sid ASC";
        }
        elseif ($label === "_complete_sorted_by_groups"){
            $this->delimiters = "groups";
            $orderby = "x_label ASC, UPPER(name) ASC";
        }
        parent::__construct();
        $visibletype = ($type == "A") ? "ATSC" : "DVB-". $type;
        if ($type !== "S")
            $source = $type . "[" . $puresource . "]";
        else
            $source = $puresource;
        $this->init1($xlabel, $source, $orderby);
        $label = (substr($label, 0,1) == "_") ? $label : "_".$label;
        $this->filename = $visibletype ."/". strtr(strtr( trim($puresource," _"), "/", ""),"_","/"). "/"  . $source. $label . '.channels.conf';
    }

    public function writeFile(){
        while ($this->moveToNextChannel() !== false){
            if ($this->delimiters !== false){
                if ($this->delimiters === "transponders" && $this->transponderChanged())
                    $this->write2File( ":".$this->getCurrentTransponderInfo()."\n" );
                elseif ($this->delimiters === "groups" && $this->groupChanged())
                    $this->write2File( ":".$this->getCurrentGroupInfo()."\n" );
            }
            $this->write2File( $this->getCurrentChannelObject()->getChannelString()."\n" );
        }
        $this->closeFile();
    }

    private function write2File( $buffer){
        if ($this->filehandle == null){
            $this->openFile();
        }
        fputs( $this->filehandle, $buffer);
    }

    private function openFile(){
        $this->config->addToDebugLog( "channelListWriter: writing to file $this->filename\n");
        $path = $this->config->getValue("exportfolder") . "/" . substr( $this->filename, 0, strrpos ( $this->filename , "/" ) );
        if (!is_dir($path))
            mkdir($path, 0777, true);
        else
            @unlink($gpath .  $this->filename);
        $this->filehandle = fopen ( $this->config->getValue("exportfolder") . "/" .  $this->filename, "w");
    }

    private function closeFile(){
        if ($this->filehandle != null){
            if (fclose($this->filehandle) === false)
                die("Error on file close.");
        }
        else
            $this->config->addToDebugLog( "channelListWriter: $this->filename was not written - it is empty!\n" );
    }
}
?>