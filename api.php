<?php

/**
 * @author MetalMichael
 * @copyright 2012
 */

require('config.php');

$DB->query("SELECT trackid FROM votes ORDER BY SUM(IF(updown, 1, -1)) LIMIT 1");
if(!$DB->record_count()) die('empty');
list($ID) = $DB->next_record(MYSQLI_NUM);
echo $ID;

$DB->query("DELETE FROM votes WHERE trackid = '" . $ID . "'");
$DB->query("DELETE FROM track_info WHERE trackid = '" . $ID . "'");
$DB->query("DELETE FROM voting_list WHERE trackid = '" . $ID . "'");

?>