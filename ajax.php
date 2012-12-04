<?php

/**
 * @author MetalMichael
 * @copyright 2012
 */

require_once('config.php');

//Need a login system
$UserID = 1;

if(!isset($_GET['action']) || empty($_GET['action'])) invalid();

switch($_GET['action']) {
    case 'add':
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
            if(!$data) invalid();
            
            $data = json_decode($data);
            
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
        
        break;
    case 'vote':
        if(!isset($_GET['track']) || !preg_match('/(spotify:(?:track:[a-zA-Z0-9]+))/', $_GET['track'])
            || !isset($_GET['direction']) || !in_array($_GET['direction'], array(0,1))) {
            invalid();
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
        break;
    case 'table':
        //Load the active voting list
        $DB->query("SELECT 
                        vl.trackid,
                        SUM(IF(v.updown, 1, -1)) as Score,
                        ti.Title,
                        ti.Artist,
                        ti.Album,
                        ti.Duration,
                        ti.Popularity
                    FROM voting_list AS vl
                    JOIN track_info AS ti 
                        ON vl.trackid = ti.trackid 
                    LEFT JOIN votes AS v
                        ON vl.trackid = v.trackid
                    GROUP BY vl.trackid
                    ORDER BY Score DESC");
        $VotingTracks = $DB->to_array(false, MYSQL_ASSOC);
?>
            <table id="voting-table">
                <thead>
                    <tr>
                        <th class="col1">Track</th>
                        <th class="col2">Artist</th>
                        <th class="col3">Time</th>
                        <th class="col4">Popularity</th>
                        <th class="col5">Album</th>
                        <th class="col6"></th>
                    </tr>
                </thead>
                <tbody>
<?php
        $a = 'even';
        $counter = 0;
        if(count($VotingTracks)) {
            foreach($VotingTracks as $VT) {
                $counter++;
                $a = ($a == 'even') ? 'odd' : 'even';
?>
                    <tr id="row-<?=sanitizeID($VT['trackid'])?>" style="top: <?=70+$counter*25?>px" class="<?=$a?>">
                        <td class="col1"><?=display_str($VT['Title'])?></td>
                        <td class="col2"><?=display_str($VT['Artist'])?></td>
                        <td class="col3"><?=get_time($VT['Duration'])?></td>
                        <td class="col4"><span class="popularity"><span class="popularity-value" style="width: <?=$VT['Popularity']*100?>%;"></span></span></td>
                        <td class="col5"><?=display_str($VT['Album'])?></td>
                        <td class="col6 votebox">
                            <a href="#" onclick="vote(0,'<?=sanitizeID($VT['trackid'])?>')">
                                <button class="votedown votebtn"></button>
                            </a>
                            <span id="score-<?=sanitizeID($VT['trackid'])?>" class="score"><?=$VT['Score']?></span>
                            <a href="#" onclick="vote(1,'<?=sanitizeID($VT['trackid'])?>')">
                                <button class="voteup votebtn"></button>
                            </a>
                        </td>
                    </tr>
<?php
            }
        } else {
?>
                    <tr class="<?=$a?>">
                        <td colspan="6">No Current Tracks</td>
                    </tr>
<?php
        }
?>
                </tbody>
            </table>
<?php
        break;
    default:
        invalid();
}
?>