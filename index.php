<?php

/**
 * @author MetalMichael
 * @copyright 2012
 */


//Config
require('config.php');



//Header
require(RESOURCE_DIR . 'header.php');
//*********************************************
?>
<div id="table-container" class="box">
<?
//Try and load the table. Cos we're nice. Hacky ajax FTW
$_GET['action'] = 'table';
include(dirname(__file__) . '/ajax.php');
?>
</div>
<?php
//*********************************************
//Footer
require(RESOURCE_DIR . 'footer.php');

?>