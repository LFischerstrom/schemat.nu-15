<?php
require_once('ScheduleDownloader.php');
require_once('Day.php');
require_once('Week.php');
require_once('Event.php');
require_once('ics-parser/class.iCalReader.php');


class Schedule {

    const ICS_FILE_DIRECTORY = "schedules/";

    private $events;
    private $startWeek;
    private $endWeek;
    private $startYear;
    private $endYear;
    private $errorMessage;
    private $error = false;

    function __construct($id){

        // for testing
        //$this->icsFilePath = "ics-parser/MyCal.ics";

        $ical = new ICal(self::ICS_FILE_DIRECTORY . $id . ".txt");
        $icalEvents = $ical->events();
        if (!isset($icalEvents) || $icalEvents == null || $icalEvents == "") {
            $this->error = true;
            $this->errorMessage = "Inga bokningar hittade för $id";
        }
        else {
            $this->events = $this->generateEvents($icalEvents);
            $this->startWeek = $this->setStartWeek($this->events);
            $this->endWeek = $this->setEndWeek($this->events);
            $this->startYear = $this->setStartYear($this->events);
            $this->endYear = $this->setEndYear($this->events);
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

    private function setScheduleTimeLimits($allEvents, $isStart, $idateSymbol){
        if($isStart) $token = "DTSTART";
        else $token = "DTEND";
        $wantedEventTime = null;
        foreach ($allEvents as $currentEvent) {
            $currentEventTime = iCalDateToUnixTimestamp($currentEvent->icalEvent[$token]);
            if ($wantedEventTime == null) $wantedEventTime = $currentEventTime;
            if ($isStart){
                if ($currentEventTime < $wantedEventTime) $wantedEventTime = $currentEventTime;
            }
            else if ($currentEventTime > $wantedEventTime) $wantedEventTime = $currentEventTime;
        }
        return idate($idateSymbol, $wantedEventTime) ;
    }

    private function setStartWeek($allEvents){
        return $this->setScheduleTimeLimits($allEvents, true, "W");
    }

    private function setEndWeek($allEvents){
        return $this->setScheduleTimeLimits($allEvents, false, "W");
    }

    private function setStartYear($allEvents){
        return $this->setScheduleTimeLimits($allEvents, true, "Y");
    }

    private function setEndYear($allEvents){
        return $this->setScheduleTimeLimits($allEvents, false, "Y");
    }

    public function getNumberOfWeeks(){
        $numberOfWeeks = ($this->endYear - $this->startYear)*53;
        $numberOfWeeks += $this->endWeek - $this->startWeek + 1;
        return $numberOfWeeks;
    }

    public function getStartWeek(){
        return $this->startWeek;
    }

    private function getErrorMessage(){
        return $this->errorMessage;
    }
}
