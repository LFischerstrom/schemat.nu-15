<?php
require_once('ScheduleDownloader.php');
require_once('Day.php');
require_once('Week.php');
require_once('Event.php');
require_once('ics-parser/class.iCalReader.php');


class Schedule {

    const ICS_FILE_DIRECTORY = "schedules/";

    private $events;
    private $firstEventStartTimeUnix;
    private $lastEventEndTimeUnix;
    private $startWeek;
    private $endWeek;
    private $startYear;
    private $endYear;
    private $errorMessage;
    private $error = false;

    function __construct($id){

        // for testing
        //$this->icsFilePath = "ics-parser/MyCal.ics";

        $path = self::ICS_FILE_DIRECTORY . str_replace("*","@",$id) . ".txt";

        // If path doesnt exist  - check if it is a user and get its courses
        if (!file_exists($path)){
            require_once("DatabaseConnection.php");
            $db = new DatabaseConnection();
            if($db->isUser($id)){
                $schedules = $db->getSchedulesForUser($id);
                $icalEvents = array();
                foreach ($schedules as $schedule){
                    $ical = new ICal(self::ICS_FILE_DIRECTORY . str_replace("*","@",$schedule["code"]) . ".txt");
                    if (sizeof($ical->events()) > 0) $icalEvents = array_merge($icalEvents, $ical->events());
                }
            }
            // ERROR: File not found and no user
            else {
                $this->error = true;
                $this->errorMessage = " ERROR: File not found and no user: $id";
            }
        }
        // Path exist (id is a group or cours -> get events from file
        else{
            $ical = new ICal(self::ICS_FILE_DIRECTORY . str_replace("*","@",$id) . ".txt");
            $icalEvents = $ical->events();
        }

        if (!isset($icalEvents) || $icalEvents == null || $icalEvents == "") {
            $this->error = true;
            $this->errorMessage = "Inga bokningar hittade för $id";
        }
        else {
            $this->events = $this->generateEvents($icalEvents);

            $this->firstEventStartTimeUnix = $this->getEventTimeExtremeValue($this->events,"min");
            $this->lastEventEndTimeUnix = $this->getEventTimeExtremeValue($this->events,"max");

            $this->setStartWeek();
            $this->setStartYear();
            $this->endWeek = $this->getWeek($this->lastEventEndTimeUnix);
            $this->endYear = $this->getYear($this->lastEventEndTimeUnix);
        }
    }

    private function generateEvents($icalEvents){
        $events = array();
        foreach ($icalEvents as $icalEvent){
            array_push($events, new Event($icalEvent));
        }
        return $events;
    }

    public function printSchedule()
    {
        if ($this->error) print '<div id="error">' . $this->getErrorMessage() . "</div>";
        $content = "";
        $year = $this->startYear;
        $numberOfWeeks = $this->getNumberOfWeeks();
        $currentWeek = $this->startWeek;

        // Prints all weeks
        for ($i = 0; $i < $numberOfWeeks; $i++) {
            if ($currentWeek == 54) {
                $currentWeek = 1;
                $year++;
            }
            $week = new Week($this->events, $year, $currentWeek);
            $content .= $week->getWeekContent();
            $currentWeek++;
        }
        print $content;
    }

    public function getMenuListItems(){
        $listItems = "";
        $year = $this->startYear;
        $currentWeek = $this->startWeek;

        for($i=0;$i<$this->getNumberOfWeeks();$i++){
            $listItems .= '<li data-menuanchor="week'.$currentWeek.'-'.$year.'" ';
            $listItems .= '><a href="#week'.$currentWeek.'-'.$year.'">'.$currentWeek.'</a></li>';
            $currentWeek++;
            if ($currentWeek == 54){
                $currentWeek = 1;
                $year++;
            }
        }
        print $listItems . "<br />";
    }

    public function getSectionAnchors(){
        // anchors on form:
        // anchors: ['firstPage', 'secondPage', 'thirdPage', 'fourthPage', 'lastPage','afterpage'],
        $anchorString = " anchors: [";
        $year = $this->startYear;
        $currentWeek = $this->startWeek;
        for($i=0;$i<$this->getNumberOfWeeks();$i++){
            $anchorString .= "'week".$currentWeek."-".$year."', ";
            $currentWeek++;
            if ($currentWeek == 54){
                $currentWeek = 1;
                $year++;
            }
        }
        $anchorString .= "],";
        return $anchorString;
    }

    private function getWeek($unix){
        return idate("W", $unix);
    }

    private function getYear($unix){
        return idate("Y",$unix);
    }

    private function getEventTimeExtremeValue($allEvents, $extremeValue){
        if($extremeValue == "min") $token = "DTSTART";
        else if ($extremeValue == "max") $token = "DTEND";
        else return null;
        $wantedEventTime = null;
        foreach ($allEvents as $currentEvent) {
            $currentEventTime = iCalDateToUnixTimestamp($currentEvent->icalEvent[$token]);
            if ($wantedEventTime == null) $wantedEventTime = $currentEventTime;
            if ($extremeValue == "min" && $currentEventTime < $wantedEventTime) $wantedEventTime = $currentEventTime;
            else if ($extremeValue == "max" && $currentEventTime > $wantedEventTime) $wantedEventTime = $currentEventTime;
        }
        return $wantedEventTime;
    }


    public function getNumberOfWeeks(){
        $numberOfWeeks = ($this->endYear - $this->startYear)*53;
        $numberOfWeeks += $this->endWeek - $this->startWeek + 1;
        return $numberOfWeeks;
    }

    public function getStartWeek(){
        return $this->startWeek;
    }
    public function getStartYear(){
        return $this->startYear;
    }

    private function getErrorMessage(){
        return $this->errorMessage;
    }

    private function setStartWeek(){
        if (time() > $this->firstEventStartTimeUnix){
            if ($this->isTodaySunday()) $this->startWeek = ($this->getWeek(time()) % 54) + 1;
            else $this->startWeek = $this->getWeek(time());
        }
        else $this->startWeek = $this->getWeek($this->firstEventStartTimeUnix);
    }

    private function setStartYear(){
        if (time() > $this->firstEventStartTimeUnix) $this->startYear = $this->getYear(time());
        else $this->startYear= $this->getYear($this->firstEventStartTimeUnix);
    }

    private function isTodaySunday(){
        return date('D', time() === 'Sun');
    }

}
