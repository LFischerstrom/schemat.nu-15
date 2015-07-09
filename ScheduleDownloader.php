<?php


class ScheduleDownloader{

    const DEFAULT_FILE_PATH = "schedules/";
    const LOCATION_MARKUP = "Sal: ";
    const LOCATION_MARKUP_FILE = "locations.txt";


    public function getFilePath($id){
        return self::DEFAULT_FILE_PATH . $id . ".txt";
    }

    private function makeTimeeditUrl($object, $type){
        $startdate = "20150101";
        $enddate = "20300501";
        return "https://se.timeedit.net/web/liu/db1/schema/s/s.html?tab=3&object=".$object."&type=".$type."&startdate=".$startdate."&enddate=".$enddate;
    }

    private function isScheduleFound($url){
        if (strpos(file_get_contents($url),"Schemat kunde inte skapas") !== false) return false;
        else return true;
    }

    private function getTimeeditScheduleUrl(){
        $id = $_COOKIE['SchematId'];

        // 5 different possible types and object in url:
        //      --EXAMPLE--    --TYPE--         --OBJECT--
        // 1.   Group_IT1      studentgroup     Group_IT1
        // 2.   EMM2.a         subgroup         Sub_Group_EMM2_EMM2.a
        // 3.   ÄGY3           studentgroup     CourseGroup_ÄGY3
        // 4.   711626.*       studentgroup     CourseGroup_711626.*
        // 5.   711626.01      subgroup         CourseSub_CourseGroup_711626.*_711626.01

        // 4. Contains *
        if (contains($id,"*")){
            $type = "studentgroup";
            $object = "CourseGroup_" . $id;
            $url = $this->makeTimeeditUrl($object,$type);
        }

        // 2. Contains dot, force try 2.
        else if (contains($id,".")){
            $type = "subgroup";
            $pieces = explode('.', $id);
            $object = "Sub_Group_" . $pieces[0] . "_" . $id;
            $url = $this->makeTimeeditUrl($object,$type);

            // If not a correct schedule; it is a 5.
            if (!$this->isScheduleFound($url)) {
                $object = "CourseSub_CourseGroup_" . $pieces[0] . ".*_" . $id;
                $url = $this->makeTimeeditUrl($object,$type);
            }

        }

        // 3. No dot. Force try 3.
        else {
            $type = "studentgroup";
            $object = "CourseGroup_" . $id;
            $url = $this->makeTimeeditUrl($object,$type);

            // If not a correct schedule; it is a 1.
            if (!$this->isScheduleFound($url)){
                $object = "Group_" . $id;
                $url = $this->makeTimeeditUrl($object,$type);
            }
        }

        return $url;
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
        $icsContent = file_get_contents($icsUrl);
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        $icsContent = $this->makeLocationMarkups($icsContent);

        file_put_contents($directory . $newFile, $icsContent);
        $filePath = $directory .$newFile;
        return $filePath;
    }

    /* TODO: Not working */
    function updateAllSchedules(){
        $handle = fopen("idList.txt", "r");
        if ($handle) {


            while (($line = fgets($handle)) !== false) {

                print $line;
                $timeeditScheduleUrl =  $this->getTimeeditScheduleUrl();
                print  $timeeditScheduleUrl ."<br />";
                $icsLink = findIcsLink($timeeditScheduleUrl);
                print  $icsLink ."<br />";
                saveIcsFile($icsLink, $line);
                break;

            }

            fclose($handle);
        } else {
            // error opening the file.
            print "ERROR?!?!?!";
        }
    }

    public function downloadSchedule($id){
        $timeeditScheduleUrl =  $this->getTimeeditScheduleUrl();
        $icsLink = $this->findIcsLink($timeeditScheduleUrl);
        $icsFilePath = $this->saveIcsFile($icsLink, $id);
        return $icsFilePath;
    }



    // if finding any location from locations.txt, markup will be put.
    public function makeLocationMarkups($icsContent){
        $locations = file_get_contents(self::LOCATION_MARKUP_FILE);
        $regex = "#(";
        $regex .= preg_replace("/\r\n|\n|\r/","| ",$locations);
        $regex .= ")#";
        $icsContent = preg_replace_callback($regex,"callback",$icsContent);
        return $icsContent;
    }



}



function contains($string, $find){
    if (strpos($string,$find) !== false) return true;
    else return false;
}

function callback ($match) {
    return "Sal: " . $match[0];
}