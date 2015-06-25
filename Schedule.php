<?php
require_once('ScheduleDownloader.php');
require_once('Day.php');
require_once('Week.php');
require_once('ics-parser/class.iCalReader.php');


class Schedule {

    private $id;
    private $icsFilePath;
    private $ical;
    private $events;
    private $startWeek;
    private $endWeek;

    function __construct($id){
        $this->id = $id;
        $sd = new ScheduleDownloader();
        $this->icsFilePath = $sd->getFilePath($this->id);
        // for testing
        // $this->icsFilePath = "ics-parser/MyCal.ics";
        $this->ics = file_get_contents($this->icsFilePath);
        $this->ical = new ICal($this->icsFilePath);
        $this->events = $this->ical->events();
        $this->startWeek = $this->setStartWeek($this->events);
        $this->endWeek = $this->setEndWeek($this->events);
    }

    // TODO: Must consider year!
    public function printSchedule(){
        $content = "";

        // Prints all weeks
        for ($currentWeek=$this->startWeek; $currentWeek < $this->endWeek +1; $currentWeek++){
            $week = new Week($this->events, 2015, $currentWeek);
            $content .= $week->getWeek();
        }
        print $content;
    }

    public function getMenuListItems(){
        $listItems = "";
        for($i=0;$i<$this->getNumberOfWeeks();$i++){
            $currentWeek = $this->startWeek+$i;
            $listItems .= '<li data-menuanchor="week'.$currentWeek.'" ';
            $listItems .= '><a href="#week'.$currentWeek.'">'.$currentWeek.'</a></li>';
        }
        print $listItems . "<br />";
    }

    public function getSectionAnchors(){
        // anchors on form:
        // anchors: ['firstPage', 'secondPage', 'thirdPage', 'fourthPage', 'lastPage','afterpage'],
        $anchorString = " anchors: [";
        for($i=0;$i<$this->getNumberOfWeeks();$i++){
            $currentWeek = $this->startWeek+$i;
            $anchorString .= "'week".$currentWeek."', ";
        }
        $anchorString .= "],";
        return $anchorString;
    }

    private function setStartWeek($allEvents){
        $startEvent = $allEvents[0];
        $eventStartTime =  iCalDateToUnixTimestamp($startEvent['DTSTART']);
        return idate('W', $eventStartTime);
    }

    private function setEndWeek($allEvents){
        $endEvent = $allEvents[sizeof($allEvents)-1];
        $eventEndTime = iCalDateToUnixTimestamp($endEvent['DTEND']);
        return idate('W', $eventEndTime);
    }

    public function getNumberOfWeeks(){
        return $this->endWeek - $this->startWeek + 1;
    }

    public function getStartWeek(){
        return $this->startWeek;
    }

}
