<?php

/**
 * @author MetalMichael
 * @copyright 2012
 */
require('config.php');

//Need a login system
$UserID = 1;

//Check everything is well in Smallville
if(!isset($_GET['track']) || !preg_match('/(spotify:(?:track:[a-zA-Z0-9]+))/', $_GET['track'])
    || !isset($_GET['direction']) || !in_array($_GET['direction'], array(0,1))) {
    header('HTTP/1.0 400 Bad Request');
    die('Invalid Spotify URI or Direction');
}

$DB->query("SELECT * FROM voting_list WHERE trackid = '" . $_GET['track'] . "'");
if(!$DB->record_count()) {
    die('notrack');
}

$DB->query("SELECT updown FROM votes WHERE trackid = '" . $_GET['track'] . "' AND userid = '" . $UserID . "'");
if($DB->record_count()) {
    list($vote) = $DB->next_record(MYSQLI_NUM);
    if($vote == $_GET['direction']) die('identical');
    $DB->query("UPDATE votes SET updown = " . $_GET['direction'] . " WHERE trackid = '" . $_GET['track'] . "' AND userid = '" . $UserID . "'");
} else {
    $DB->query("INSERT INTO votes (trackid, userid, updown) VALUES ('" . $_GET['trackid'] . "', '" . $UserID . "', " . $_GET['direction'] . ")");
}

//Return the score, as it may have changed in the mean time, and we want to be as accurate as possible and just cos.
$DB->query("SELECT SUM(IF(updown, 1, -1)) FROM votes WHERE trackid = '" . $_GET['track'] . "'");
list($NewVotes) = $DB->next_record(MYSQLI_NUM);
echo $NewVotes;
?>