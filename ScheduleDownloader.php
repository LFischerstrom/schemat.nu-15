<?php
require_once('MyLog.php');
require_once("Miner.php");
require_once("DatabaseConnection.php");


class ScheduleDownloader{

    const DEFAULT_FILE_PATH = "schedules/";
    const LOCATION_MARKUP = "Sal: ";
    const LOCATION_MARKUP_FILE = "locations.txt";
    const ID_FILE = "idList.txt";

    private $log;
    private $db;

    public function __construct(){
        $this->log = new MyLog();
        $this->db = new DatabaseConnection();
    }

    public function getFilePath($id){
        $id = str_replace("*","@",$id);
        $directory = SELF::DEFAULT_FILE_PATH;
        if (!file_exists($directory)) mkdir($directory, 0777, true);
        return self::DEFAULT_FILE_PATH . $id . ".txt";
    }

    public function getTimeeditUrl($id){
        $startdate = "20150101";
        $enddate = "20300501";
        $timeeditObjectCode = $this->db->getObject($id);

        $timeeditObjectCode  = html_entity_decode($timeeditObjectCode, ENT_QUOTES, "utf-8"); // make it UTF-8
        $type = $this->getScheduleType($timeeditObjectCode);
        // urlencode to fix åäö letters
        $url = "https://se.timeedit.net/web/liu/db1/schema/s/s.html?tab=3&object=" . urlencode($timeeditObjectCode) . "&type=".$type."&startdate=".$startdate."&enddate=".$enddate;
        return $url;
    }


    public function downloadSchedules($offset, $amount){

        $ids = $this->db->getIds($offset,$amount);

        foreach ($ids as $id) {
            $id = $id["code"];
            $this->downloadSchedule($id);

            // Error handling
            $fileSize = round(filesize($this->getFilePath($id))/1000,1);
            if ($fileSize == 0) $this->log->write("ERROR: Downloading " . $id . " | ". $this->getTimeeditUrl($id) . "\n");
            else $this->log->write("Downloaded: " . $id . " | " . $fileSize  . " Kb \n");

            // print log
            print nl2br($this->log->readBackwards(50));

        }
    }

    // Finding the ics link in iCalDialogContent-div, option 3.
    // <div id="iCalDialogContent" class="topmenu topmenuright hidden">
    // <h3></h3>
    // <select class="" id="" name="">
    // <option value="" selected="" data-url="ics link">Rullande 2 veckor</option>
    // <option value=""  data-url="ics link">Rullande 4 veckor</option>
    // <option value=" WANTED ICS LINK ">2015-01-01 - 2015-08-23</option>
    public function findIcsLink($url){
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
        $filePath = $this->getFilePath($id);
        $icsContent = file_get_contents($icsUrl);
        $icsContent = $this->removeLinebreaksInIcsContent($icsContent);
        $icsContent = $this->makeLocationMarkups($icsContent);
        file_put_contents($filePath, $icsContent);
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
            }
            else $newStr[$i] = $line;
        }
        $icsContent = implode(PHP_EOL, $newStr);
        return $icsContent;
    }



    public function downloadSchedule($id){
        $timeeditUrl = $this->getTimeeditUrl($id);
        $icsLink = $this->findIcsLink($timeeditUrl);
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


    //      --TYPE--                  --OBJECT--
    // 1.  studentgroup/subgroup      Group_IT1
    // 2.  subgroup                   Sub_Group_EMM2_EMM2.a
    // 3.  studentgroup               CourseGroup_ÄGY3
    // 4.  studentgroup               CourseGroup_711626.*
    // 5.  subgroup                   CourseSub_CourseGroup_711626.*_711626.01
    // 6.  courseevt                  CM_TAOP86_1534_1544_DAG_NML_100_Valla_1_1
    private function getScheduleType($timeeditCode){
        if (contains($timeeditCode,"*")) $type = "studentgroup";
        else if (contains($timeeditCode,"_")) $type = "subgroup";
        else if (contains($timeeditCode,"CM_")) $type = "courseevt";
        else $type = "studentgroup";
        return $type;
    }
}

function callback($match) {
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
