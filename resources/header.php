<?php

/**
 * @author MetalMichael
 * @copyright 2012
 */

header('Cache-Control: no-cache, must-revalidate, post-check=0, pre-check=0');
header('Pragma: no-cache');

?>
<!DOCTYPE HTML>
<head>
	<meta http-equiv="content-type" content="text/html" />
	<meta name="author" content="MetalMichael" />

	<title>Spotify Player</title>
    <link href='http://fonts.googleapis.com/css?family=Ubuntu' rel='stylesheet' type='text/css'>
    <link href="<?=RESOURCE_DIR?>style.css" rel="stylesheet" type="text/css" />
    
    <script type="text/javascript" src="<?=RESOURCE_DIR?>jquery.min.js"></script>
    <script type="text/javascript" src="<?=RESOURCE_DIR?>radio.js"></script>
    <script type="text/javascript" src="<?=RESOURCE_DIR?>jquery.dataTables.js"></script>
    <script type="text/javascript" src="<?=RESOURCE_DIR?>jquery-ui-1.9.1.custom.min.js"></script>
    
</head>
<body>
    <div id="header">       
        <h2>Spotify Search And Add And Vote And Shit</h2>
        <form id="searchbox" method="get" onsubmit="return false;">
            <span class="label">Search: </span><input type="text" id="searchinput" onclick="updateSearch(this.value)" onkeyup="updateSearch(this.value);" />
        </form>
        <div id="search">
            <div id="search-results"></div>
        </div>
    </div>  
    <div id="content">
