<?php

/**
 * @author MetalMichael
 * @copyright 2012
 */


//Config
require('config.php');

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

//Header
require(RESOURCE_DIR . 'header.php');
//*********************************************
?>

<div class="box">
    <table id="example">
        <thead>
            <tr>
                <th>Track Name</th>
                <th>Artist</th>
                <th>Time</th>
                <th>Popularity</th>
                <th>Album</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
<?php
$a = 'even';
if(count($VotingTracks)) {
    foreach($VotingTracks as $VT) {
        $a = ($a == 'even') ? 'odd' : 'even';
?>
            <tr class="<?=$a?>">
                <td><?=display_str($VT['Title'])?></td>
                <td><?=display_str($VT['Artist'])?></td>
                <td><?=get_time($VT['Duration'])?></td>
                <td><span class="popularity"><span class="popularity-value" style="width: <?=$VT['Popularity']*100?>%;"></span></span></td>
                <td><?=display_str($VT['Album'])?></td>
                <td class="votebox">
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
</div>


<?php
//*********************************************
//Footer
require(RESOURCE_DIR . 'footer.php');

?>