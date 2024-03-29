<?php
set_time_limit(0);
require_once('ScheduleDownloader.php');
require_once('Miner.php');
$numberOfIds = getNumberOfIds();
$lastOffsetFile = 'lastOffset.txt';
$lastDownloadedOffset = file_get_contents($lastOffsetFile);
$sd = new ScheduleDownloader();



if(isset($_GET["amount"])) $amount =  htmlspecialchars($_GET["amount"]);

// If offset specified
if(isset($_GET["offset"])) $offset =  htmlspecialchars($_GET["offset"]);

// continue download from last downloaded id if no offset is specified
else $offset = $lastDownloadedOffset;


if ($offset <= $numberOfIds){
    $sd->downloadSchedules($offset, $amount);
    file_put_contents($lastOffsetFile, $offset);
    $nextOffset = $offset + $amount;
}

if ($offset > $numberOfIds){
    if ($amount != 1){
        $amount = ceil($amount/2);
        $nextOffset = $offset + $amount;
    }
    // All schedules downloaded.
    else {
        $amount = 0;
        print "DONE!";
    }
}

if ($amount > 0){
    $location = "downloadAllSchedules.php?amount=". $amount ."&offset=". $nextOffset;
    $script = "<script>window.location.href=\" $location  \"</script>";
    print $script;
}

function getNumberOfIds(){
    require_once("DatabaseConnection.php");
    $db = new DatabaseConnection();
    $size = $db->getNumberOfCoursesAndGroups();
    return $size;

}