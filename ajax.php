<?php

/**
 * @author MetalMichael
 * @copyright 2012
 */

require('config.php');

//Need a login system
$UserID = 1;

//Check everything is well in Smallville
if(!isset($_GET['track']) || !preg_match('/(spotify:(?:track:[a-zA-Z0-9]+))/', $_GET['track'])) {
    header('HTTP/1.0 400 Bad Request');
    die('Invalid Spotify URI');
}

$DB->query("SELECT * FROM voting_list WHERE trackid = '" . $_GET['track'] . "'");
if($DB->record_count()) {
    die('exists');
}

$DB->query("SELECT * FROM track_info WHERE trackid = '" . $_GET['track'] . "'");
if(!$DB->record_count()) {
    
    //Get info on the track and add it to the database
    /*$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://ws.spotify.com/lookup/1/?uri=' . $_GET['track']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($ch);
    curl_close($ch);
    */
    $data = file_get_contents('http://ws.spotify.com/lookup/1/.json?uri=' . $_GET['track']);
    
    //Track doesn't exist in Spotify
    if(!$data) {
        header('HTTP/1.0 400 Bad Request');
        die('Track Not Found');
    }
    $data = json_decode($data);
    
    var_dump($data);
    
    $Track = array(
        'Title' => $data->track->name,
        'Artist' => $data->track->artists[0]->name,
        'Album' => $data->track->album->name,
        'Time' => $data->track->length,
        'Popularity' => $data->track->popularity
    );
    
    //Add info to the track catalogue
    $DB->query("INSERT IGNORE INTO track_info (trackid, Title, Artist, Album, Duration, Popularity) VALUES(
        '" . $_GET['track'] . "',
        '" . db_string($Track['Title']) . "',
        '" . db_string($Track['Artist']) . "',
        '" . db_string($Track['Album']) . "',
        '" . db_string($Track['Time']) . "',
        '" . db_string($Track['Popularity']) . "')");
}

//Add it to the voting list
$DB->query("INSERT INTO voting_list (trackid) VALUES ('" . $_GET['track'] . "')");

//Add a vote
$DB->query("INSERT INTO votes (trackid, userid, updown) VALUES ('" . $_GET['track'] . "', '"  . $UserID . "', 1)");
?>