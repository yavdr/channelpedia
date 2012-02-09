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

CREATE TABLE channels(
    name TEXT,
    provider TEXT,
    frequency INTEGER,
    parameter TEXT,
    source TEXT,
    symbolrate INTEGER,
    vpid INTEGER,
    apid INTEGER,
    tpid TEXT,
    caid TEXT,
    sid INTEGER,
    nid INTEGER,
    tid TEXT,
    rid INTEGER,
    x_label TEXT,
    x_xmltv_id TEXT,
    x_namespace INTEGER,
    x_timestamp_added TIMESTAMP,
    x_last_changed TIMESTAMP,
    x_last_confirmed TIMESTAMP,
    x_utf8 BOOLEAN,
    PRIMARY KEY ( source, nid, tid, sid)
);

/* FIXME: x_namespace should become a PRIMARY_KEY as soon as all selects, inserts and updates are aware of x_namespace!*/

CREATE TABLE channel_update_log(
    timestamp TIMESTAMP,
    importance INTEGER,
    name TEXT,
    combined_id TEXT,
    update_description TEXT
);

CREATE TABLE upload_log(
    timestamp TIMESTAMP,
    user TEXT,
    source TEXT,
    description TEXT
);