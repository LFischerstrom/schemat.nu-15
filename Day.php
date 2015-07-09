<?php

class Day {

    const START_TIME = 8;
    const END_TIME = 18;
    const DAY_HEADER_HEIGHT = 20;

    private $date;
    private $dateString;
    private $events;

    public function __construct($date, $allEvents){
        $this->date = $date;
        $this->dateString = $date->format('Y-m-d');
        $this->events = $this->getEventsOnDate($allEvents);
        $this->addPercentageValuesToDayEvents($this->events);
    }

    // TODO: Fix to real today.
    public function isToday(){
        if ($this->dateString == "2015-08-19" ) return true;
        return false;
    }

    public function getDayHtml(){
        $html= "";
        foreach ($this->events as $event){
            $html .= $event->getHtml();
        }
        return $html;
    }

    public function hasEventsOnDate(){
        foreach ($this->events as $event) {
            $eventStartUnix = iCalDateToUnixTimestamp($event['DTSTART']);
            $eventStartDate = new DateTime('@' . $eventStartUnix);
            $eventStartDateString = $eventStartDate->format('Y-m-d');

            if ($eventStartDateString == $this->dateString){
                return true;
            }
        }
        return false;
    }

    public function getEventsOnDate($allEvents){
        $events = array();
        foreach ($allEvents as $event){
            if ($event->getStartDateString() == $this->dateString) array_push($events, $event);
        }
        return $events;
    }

    private function addPercentageValuesToDayEvents($events)
    {
        foreach ($events as $event) {
            $event->setStartTimePercentage(($event->getStartTime() - self::START_TIME) / (self::END_TIME - self::START_TIME) * 100);
            $event->setHeightPercentage(($event->getEndTime() - $event->getStartTime()) / (self::END_TIME - self::START_TIME) * 100);
        }
    }

}