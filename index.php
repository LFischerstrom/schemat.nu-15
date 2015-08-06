<?php
require_once("Stats.php");

// if no cookie -> choose id. Else display schedule page.
if(!isset($_COOKIE['SchematId'])) include("chooseIdPage.php");
else include("schedulePage.php");

$whitelist = array(
    '127.0.0.1',
    '::1'
);

if(!in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
    $st = new Stats();
    if (!isset($id)) $id = "null";
    $st->captureStats($id);
}



