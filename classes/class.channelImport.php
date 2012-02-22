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

class channelImport extends channelFileIterator{

    private
        $reparseOldFile = false,
        $deleteOutdatedChannels = false,
        $metaData,
        $textualSummary,
        $username,
        $htmlOutput,
        $labeller,
        $rawOutput;

    public function __construct( & $metaData, $forceReparsing = false, $deleteOutdated = false ){
        parent::__construct();
        $this->reparseOldFile = $forceReparsing;
        $this->deleteOutdatedChannels = $deleteOutdated;
        $this->metaData = $metaData;
        $this->username = $this->metaData->getUsername();
        $this->textualSummary = "undefined";
        //$this->addToUpdateLog( "-", "Processing users channels.conf.");
    }

    public function addToUpdateLog( $source, $description ){
        $query = $this->db->insert( "upload_log", array(
            "timestamp" => time(), //$this->timestamp,
            "user" => $this->username,
            "source" => $source,
            "description" => $description
        ));
        $this->config->addToDebugLog( $this->username . ": " . $description."\n");

    }

    /*
     * reads channel conf file line by line and
     * adds channel lines that seem correct to the db
     */

    public function insertChannelsConfIntoDB(){
        $sourcepath = $this->config->getValue("userdata")."sources/".$this->username."/";

        $msg_prefix = "";
        $filename = $sourcepath . 'channels.conf';
        if ($this->reparseOldFile) {
            $this->config->addToDebugLog( "Reparsing of old channel file forced.\n");
            if (file_exists($sourcepath . 'lockfile.txt')){
                unlink($sourcepath . 'lockfile.txt');
            }
            if (!file_exists($filename))
                rename($filename . ".old", $filename);
        }
        if (!file_exists($filename)) {
            $this->addToUpdateLog( "-", "No unprocessed channels.conf exists. Nothing to do.");
        }
        elseif (file_exists($sourcepath . 'lockfile.txt')) {
            $this->addToUpdateLog( "-", "Lockfile present. Processing of channels.conf rejected.");
        }
        else{
            //lock this user
            file_put_contents($sourcepath . 'lockfile.txt', "locked");
            //read channels.conf line by line
            $this->openChannelFile($filename);
            $cgroup = "";
            $query = $this->db->exec("BEGIN TRANSACTION");
            while ($this->moveToNextLine() !== false) {
                //$msg_prefix = "try to add channel: ";
                if ($this->isCurrentLineAGroupDelimiter()){
                   $cgroup = $this->getGroupDelimiterFromCurrentLine();
                   //$this->config->addToDebugLog( $msg_prefix."Skipping a group delimiter.\n");
                }
                elseif($this->isCurrentLineEmpty()){
                    //$this->config->addToDebugLog( $msg_prefix . "illegal channel: ignoring empty line.\n");
                }
                else{
                    $currentchannel = new storableChannel( $this->getCurrentLine(), $this->metaData);
                    if ($currentchannel->isValid()){
                        if (false === $currentchannel->insertIntoDB()){
                            //$this->config->addToDebugLog( $msg_prefix . "already exists.\n");
                        }
                        else{
                            //$this->config->addToDebugLog( $msg_prefix . "added successfully.\n");
                        }
                    }
                    else{
                        $this->config->addToDebugLog( $msg_prefix . "illegal channel: ".$this->getCurrentLine().".\n");
                        $this->metaData->increaseIgnoredChannelCount();
                    }
                }
            }
            $query = $this->db->exec("COMMIT TRANSACTION");
            //rename read channels.conf file
            if (file_exists($filename . ".old"))
                unlink($filename . ".old");
            rename($filename, $filename . ".old");
            //now cleanup database content from outdated channels
            //$this->deleteOutdatedChannels();
            //summary
            $this->updateTextualSummary();
            $this->addToUpdateLog( "-", $this->textualSummary);
            unlink($sourcepath . 'lockfile.txt');
            if ($this->deleteOutdatedChannels)
                $this->deleteOutdatedChannelsForPresentSources();
        }
    }

    private function updateTextualSummary(){
        $this->textualSummary = "Summary: ".
            "Checked: " . $this->metaData->getCheckedChannelCount() .
            " / Added: " . $this->metaData->getAddedChannelCount() .
            " / Modified: " . $this->metaData->getChangedChannelCount() .
            " / Ignored: "  . $this->metaData->getIgnoredChannelCount();
    }

    private function deleteOutdatedChannelsForPresentSources(){
        $this->config->addToDebugLog( "deleteOutdatedChannels was called\n");
        foreach ($this->metaData->getPresentSatProviders() as $sat => $dummy){
            $this->deleteOutdatedChannelsForSource( $sat );
        }
        $this->deleteOutdatedChannelsForNonSatProvider( "C" );
        $this->deleteOutdatedChannelsForNonSatProvider( "T" );
        $this->deleteOutdatedChannelsForNonSatProvider( "A" );
    }

    private function deleteOutdatedChannelsForSource( $source ){
        $lastConfirmedTimestamp = dbVariousTools::getInstance()->getLastConfirmedTimestamp( $source );
        $statement = "DELETE FROM channels WHERE source = ".$this->db->quote($source)." AND x_last_confirmed < ".$lastConfirmedTimestamp;
        $this->config->addToDebugLog( "Deleting outdated channels for $source: $statement\n");
        $query = $this->db->exec( $statement );
    }

    private function deleteOutdatedChannelsForNonSatProvider( $type ){
        $rawprovider = $this->metaData->getPresentNonSatProvider( $type );
        if ($rawprovider != "" && $rawprovider != "none"){
            $this->deleteOutdatedChannelsForSource( $rawprovider );
        }
    }

    public function getTextualSummary(){
        return $this->textualSummary;
    }

    /*
     * only those stuff is being updated that really needs to be updated
     * keep the amount of unnecessary updates as small as possible
     */

    public function updateAffectedDataAndFiles(){
        $this->htmlOutput = new HTMLOutputRenderer();
        $this->htmlOutput->writeUploadLog();
        if ( $this->metaData->getAddedChannelCount() + $this->metaData->getChangedChannelCount() > 0){
            $this->labeller = channelGroupingManager::getInstance();
            $this->rawOutput = new rawOutputRenderer();
            foreach ($this->metaData->getPresentSatProviders() as $sat => $dummy){
                $this->config->addToDebugLog( "updateAffectedDataAndFiles: Processing DVB-S: " . $sat . ".\n");
                $languages = $this->config->getLanguageGroupsOfSource( "DVB-S", $sat);
                $this->labeller->updateAllLabelsOfSource($sat);
                $this->rawOutput->writeRawOutputForSingleSource( $sat, $sat, $languages);
                $this->htmlOutput->renderPagesOfSingleSource("S", $sat, $languages);
            }
            $this->updateAffectedDataAndFilesForNonSatProvider("C");
            $this->updateAffectedDataAndFilesForNonSatProvider("T");
            $this->updateAffectedDataAndFilesForNonSatProvider("A");
            $this->htmlOutput->writeGeneralChangelog();
        }
        else{
            $this->config->addToDebugLog( "No need for label update.\n");
        }
    }

    private function updateAffectedDataAndFilesForNonSatProvider( $type ){
        $rawprovider = $this->metaData->getPresentNonSatProvider( $type );
        if ($rawprovider != "" && $rawprovider != "none"){
            $visibletype = ($type == "A") ? "ATSC" : "DVB-". $type;
            $this->config->addToDebugLog( "updateAffectedDataAndFiles: Processing $visibletype: " . $rawprovider . ".\n");
            $languages = $this->config->getLanguageGroupsOfSource( $visibletype, $rawprovider);
            $provider = $type. "[".$rawprovider."]";
            $this->labeller->updateAllLabelsOfSource( $provider );
            $this->rawOutput->writeRawOutputForSingleSource( $type, $provider, $languages);
            $this->htmlOutput->renderPagesOfSingleSource( $type, $rawprovider, $languages);
        }
    }
}
?>