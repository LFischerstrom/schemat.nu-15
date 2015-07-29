<?php
require_once('MyLog.php');
require_once("Miner.php");


class ScheduleDownloader{

    const DEFAULT_FILE_PATH = "schedules/";
    const LOCATION_MARKUP = "Sal: ";
    const LOCATION_MARKUP_FILE = "locations.txt";
    const ID_FILE = "idList.txt";

    private $log;

    public function __construct(){
        $this->log = new MyLog();
    }

    public function getFilePath($id){
        $id = str_replace("*","@",$id);
        return self::DEFAULT_FILE_PATH . $id . ".txt";
    }

    private function makeTimeeditUrl($object, $type){
        $startdate = "20150101";
        $enddate = "20300501";
        return "https://se.timeedit.net/web/liu/db1/schema/s/s.html?tab=3&object=".$object."&type=".$type."&startdate=".$startdate."&enddate=".$enddate;
    }

    private function isScheduleFound($url){
        if (strpos(file_get_contents($url),"Schemat kunde inte skapas") !== false){
            return false;
        }
        else{
            return true;
        }
    }

    // Finding the ics link in iCalDialogContent-div, option 3.
    // <div id="iCalDialogContent" class="topmenu topmenuright hidden">
    // <h3></h3>
    // <select class="" id="" name="">
    // <option value="" selected="" data-url="ics link">Rullande 2 veckor</option>
    // <option value=""  data-url="ics link">Rullande 4 veckor</option>
    // <option value=" WANTED ICS LINK ">2015-01-01 - 2015-08-23</option>
    private function findIcsLink($url){
        $timeeditPage = file_get_contents($url);
        $iCalDialogContentPos = strpos($timeeditPage ,"id=\"iCalDialogContent");
        $option1Pos = strpos($timeeditPage , "<option",$iCalDialogContentPos) +1;
        $option2Pos = strpos($timeeditPage , "<option",$option1Pos) +1 ;
        $option3Pos = strpos($timeeditPage , "<option",$option2Pos) +1;
        $option3ValueStart = strpos($timeeditPage , "\"",$option3Pos) +1;
        $option3ValueEnd = strpos($timeeditPage , "\"",$option3ValueStart);
        $iscLink = substr($timeeditPage, $option3ValueStart, $option3ValueEnd - $option3ValueStart);
        return $iscLink;
    }

    private function saveIcsFile($icsUrl, $id){
        if(contains($id,"*")) $id = str_replace("*","@", $id);
        $newFile = $id.".txt";
        $directory = SELF::DEFAULT_FILE_PATH;
        if (!file_exists($directory)) mkdir($directory, 0777, true);

        $icsContent = file_get_contents($icsUrl);

        $icsContent = $this->removeLinebreaksInIcsContent($icsContent);
        $icsContent = $this->makeLocationMarkups($icsContent);

        file_put_contents($directory . $newFile, $icsContent);
        $filePath = $directory .$newFile;
        return $filePath;
    }

    // removes unwantad linebreaks in data, summary etc.
    private function removeLinebreaksInIcsContent($icsContent){
        $newStr = [];
        $i = 0;
        foreach(preg_split("/((\r?\n)|(\r\n?))/", $icsContent) as $line) {
            $i++;

            if ($line != null && $line[0] == chr(32)) {
                $line = substr($line, 1);
                if (isset($newStr[$i-1])) $newStr[$i-1].= $line;
                else $newStr[$i-1] = $line;
            } else {
                $newStr[$i] = $line;
            }

        }
        $icsContent = implode(PHP_EOL, $newStr);
        return $icsContent;
    }

    public function downloadSchedules($offset, $amount){

        $miner = new Miner();
        $allIds = $miner->getGroupsAndCourses();
        $counter = 0;
        $downloadedSchedules = 0;

        foreach ($allIds as $id => $timeeditCode) {
            if($counter++ < $offset) continue;
            if($downloadedSchedules >= $amount) return;

            if (contains($timeeditCode,"*")) $type = "studentgroup";
            else if (contains($timeeditCode,"_")) $type = "subgroup";
            else if (contains($timeeditCode,"CM_")) $type = "courseevt";
            else $type = "studentgroup";

            $timeeditUrl = $this->makeTimeeditUrl($timeeditCode, $type);
            $this->downloadSchedule($id, $timeeditUrl);
            $fileSize = round(filesize("schedules/". str_replace("*","@",$id) . ".txt")/1000,1);

            if ($fileSize == 0) $this->log->write("ERROR: Downloading " . $id . " | ". $timeeditUrl . " | " . $timeeditCode ."\n");
            else $this->log->write("Downloaded: " . $id . " | " . $fileSize  . " Kb \n");

            print nl2br($this->log->readBackwards(50));

            $downloadedSchedules++;
        }
    }

    public function downloadSchedule($id, $timeeditCode){
        $icsLink = $this->findIcsLink($timeeditCode);
        $icsFilePath = $this->saveIcsFile($icsLink, $id);
        return $icsFilePath;
    }

    // if finding any location from locations.txt, markup will be put.
    public function makeLocationMarkups($icsContent){
        $locations = file_get_contents(self::LOCATION_MARKUP_FILE);
        $regex = "#(";
        $regex .= preg_replace("/\r\n|\n|\r/" , "\\\\\\\\". "|( |:)",$locations);
        $regex .= ")#";
        $icsContent = preg_replace_callback($regex,"callback",$icsContent);
        return $icsContent;
    }
}


function callback ($match) {
    $colon = "";
    if (substr($match[0],0,1) == ':'){
        $colon = ": ";
        $match[0][0] = "";
    }
    return $colon . "Sal: " . preg_replace('/(\s+)+/', '', $match[0]);
}


function contains($string, $find){
    if (strpos($string,$find) !== false) return true;
    else return false;
}

$s = new ScheduleDownloader();
$s->downloadSchedule("IT1", "https://se.timeedit.net/web/liu/db1/schema/ri157XQQ799Z50Qv87080gZ6y5Y7555Q6Y90Y7.html");
//$s->downloadSchedule("KB1", "https://se.timeedit.net/web/liu/db1/schema/ri157XQQ799Z50Qv37070gZ6y5Y7552Q6Y90Y7.html");