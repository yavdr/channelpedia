<?php 
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Henning Pingel
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

require_once 'class.cpbasics.php';

//init db: delete db and re-create it with empty tables
//require_once 'class.cpinitdb.php';
//$x = new cpDBInit("/home/hp/Desktop/channels/");

//input: reads channel.conf from path and put channels into db
require_once 'class.cpinput.php';
$x = new cpInput("/home/hp/Desktop/channels/", "Germany_KabelBW", "none");

?>