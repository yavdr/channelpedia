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

class channelImportMetaData{

    private
        $timestamp,
        $usersAnnouncedProviders = array(), //names of the valid providers that are defined in the user config
        $usersPresentProviders, //provider names for channels actually present in the channels.conf including all satellite positions found
        $numChanChecked = 0,
        $numChanAdded = 0,
        $numChanChanged = 0,
        $numChanIgnored = 0,
        $username,
        $currentUserConfig,
        $lastProviderName;


    public function __construct( $username ){
        $this->resetPresentProviders();
        $this->timestamp = time();
        $this->numChanChecked = 0;
        $this->numChanAdded   = 0;
        $this->numChanChanged = 0;
        $this->numChanIgnored = 0;
        $this->username = $username;

        global $global_user_config;
        if ( array_key_exists($username, $global_user_config) ){
            $this->currentUserConfig = $global_user_config[ $username ];

            //prepare infos about providers
            $this->usersAnnouncedProviders = array(
                "C" => $this->checkAnnouncedNonSatProviderForType("C"),
                "T" => $this->checkAnnouncedNonSatProviderForType("T"),
                "A" => $this->checkAnnouncedNonSatProviderForType("A"),
                "S" => $this->checkAnnouncedSatProviders()
            );
        }
        else
            $this->currentUserConfig = false;

    }

    public function userNameExists(){
        return (!$this->currentUserConfig === false);
    }

    public function isAuthenticated( $password ){
        return ($this->currentUserConfig["password"] === $password);
    }

    public function getUsername(){
        return $this->username;
    }

    public function isValidNonSatSource( $type ){
        if ( array_key_exists( $type, $this->usersAnnouncedProviders )){
            $this->lastProviderName = $this->currentUserConfig["announcedProviders"][$type];
            return $this->usersAnnouncedProviders[$type];
        }
        else{
            return false;
        }
    }

    public function getProviderNameForLastCheckedNonSatSource(){
        return $this->lastProviderName;
    }

    public function isValidSatSource( $name ){
        return in_array($name, $this->usersAnnouncedProviders["S"]);
    }

    private function checkAnnouncedNonSatProviderForType( $type ){
        $feedback = false;
        if ( array_key_exists( $type, $this->currentUserConfig["announcedProviders"] ) &&
           is_string( $this->currentUserConfig["announcedProviders"][$type] ) &&
           $this->currentUserConfig["announcedProviders"][$type] !== "none" &&
           $this->currentUserConfig["announcedProviders"][$type] !== ""
        )
            $feedback = true;
            return $feedback;
    }

    private function checkAnnouncedSatProviders(){
        $feedback = array();
        if (array_key_exists( "S", $this->currentUserConfig["announcedProviders"] ) &&
            is_array( $this->currentUserConfig["announcedProviders"]["S"] )
        )
            $feedback = $this->currentUserConfig["announcedProviders"]["S"];
        return $feedback;
    }

    public function getTimestamp(){
        return $this->timestamp;
    }

    public function resetPresentProviders(){
        $this->usersPresentProviders = array();
        $this->usersPresentProviders["S"] = array();
    }

    public function addPresentNonSatProvider( $type, $provider ){
        $this->usersPresentProviders[$type] = $provider;
    }

    public function getPresentNonSatProvider( $type ){
        $result = "";
        if (in_array($type, $this->usersPresentProviders))
            $result = $this->usersPresentProviders[$type];
        return $result;
    }

    public function addPresentSatProvider( $satposition ){
        $this->usersPresentProviders["S"][$satposition] = true;
    }

    public function getPresentSatProviders(){
        return $this->usersPresentProviders["S"];
    }

    public function increaseIgnoredChannelCount(){
        $this->numChanIgnored++;
    }

    public function increaseChangedChannelCount(){
        $this->numChanChanged++;
    }

    public function increaseAddedChannelCount(){
        $this->numChanAdded++;
    }

    public function increaseCheckedChannelCount(){
        $this->numChanChecked++;
    }

    public function getIgnoredChannelCount(){
        return $this->numChanIgnored;
    }

    public function getChangedChannelCount(){
        return $this->numChanChanged;
    }

    public function getAddedChannelCount(){
        return $this->numChanAdded;
    }

    public function getCheckedChannelCount(){
        return $this->numChanChecked;
    }
}