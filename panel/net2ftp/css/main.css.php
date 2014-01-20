<?php
header("Content-type: text/css");
if (isset($_GET["ltr"]) == false || $_GET["ltr"] != "rtl") { $left = "left"; $right = "right"; }
else                                                       { $left = "right"; $right = "left"; }
if (isset($_GET["image_url"]) == true) { $image_url = preg_replace("/[\\*\\?\\<\\>\\|]/", "", $_GET["image_url"]); }
else                                   { $image_url = ""; }
?>

/* CSS document colors
#003250
#5893ac
#bbd2e0
#F2F2F5
#A7A7A7
#787878
*/

* {
    padding: 0;
    margin: 0;
}

body {
}

#container {
    margin-<?php echo $left; ?>: auto;
    margin-<?php echo $right; ?>: auto;
    margin-bottom: 20px;
    text-align: <?php echo $left; ?>;
}

/* #main {
    background: #F2F2F5;
    padding: 10px;
} */

#poweredby {
    padding-top: 10px;
    text-align: center;
    font-size: 0.75em;
    float: right;
}

ul {
    padding-<?php echo $left; ?>: 15px;
}

select, input {
    padding: 2px;
    margin-top: 1px;
    margin-<?php echo $right; ?>: 0;
    margin-bottom: 1px;
    margin-<?php echo $left; ?>: 0;
    font-size: 1em;
}

textarea {
    padding: 2px;
}

.warning-box {
    background-color: #FFF6BF;
    color: #514721;
    border: 2px;
    border-style: solid;
    border-color: #FFD324; 
    margin-<?php echo $left; ?>: 0px;
    margin-<?php echo $right; ?>: 10px;
}

.warning-text {
    padding-bottom : 5px;
    padding-top : 5px;
    padding-<?php echo $left; ?> : 10px;
    padding-<?php echo $right; ?> : 10px;
}

.browse_cell {
    width: 120px; 
    height: 60px; 
    font-size: 0.8em; 
    text-align: center; 
    overflow: hidden;
    padding: 3px;
}

.browse_rows_actions {
    background-color: #bbd2e0;
    color: #000000;
    font-size: 80%;
    font-weight: normal;
    text-align: <?php echo $left; ?>;
}

.browse_rows_heading td {
    background-color: #006699;
}

.browse_rows_heading a {
    color: #f6f6f6;
    text-decoration: none;
    font-weight: 700;
}

.browse_rows_odd, .browse_rows_even {
    background-color: #f4f4f4;
    font-family: 'Lucida Grande', Verdana, Arial, Sans-Serif;
    font-size: 80%;
    font-weight: normal;
    text-align: <?php echo $left; ?>;
}
.browse_rows_even {
    background:#e5e5e5
}
.browse_rows_odd:hover, .browse_rows_even:hover{
    background-color: #ECFBD4;
}

.browse_rows_separator {
    border: 2px;
    color: #000000;
    font-size: 100%;
    text-align: <?php echo $left; ?>;
}

/*------------------------------------------------------------------------
   Process bar
From the PHP Pear package HTML_Progress
http://pear.laurent-laville.org/HTML_Progress/examples/horizontal/string.php
------------------------------------------------------------------------*/

.p_ba7428 .progressBar, .p_ba7428 .progressBarBorder {
    width: 172px;
    height: 24px;
    position: relative;
    left: 0;
    top: 0;
}

.p_ba7428 .progressBarBorder {
    border-width: 0;
    border-style: solid;
    border-color: #003250;
}

.p_ba7428 .installationProgress {
    width: 350px;
    text-align: left;
    font-family: Verdana, Arial, Helvetica, sans-serif;
    font-size: 12px;
    color: #000000;
}

.p_ba7428 .cellI, .p_ba7428 .cellA {
    width: 15px;
    height: 20px;
    font-family: Courier, Verdana;
    font-size: 0.8em;
    float: left;
}

.p_ba7428 .cellI {
    background-color: #003250;
}

.p_ba7428 .cellA {
    background-color: #003250;
    visibility: hidden;
}
