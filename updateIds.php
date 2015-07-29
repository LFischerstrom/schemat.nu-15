<?php
require_once 'Miner.php';
require_once 'StudentPortalenDownloader.php';

$spd = new StudentPortalenDownloader();
$spd->downloadCoursesAndGroupsHtmlFiles();

$miner = new Miner();
$miner->mineGroups();
$miner->mineCourses();