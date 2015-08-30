<?php

class Day {

    const DAY_HEADER_HEIGHT = 20;

    private $date;
    private $dateString;
    private $events;
    private $startTime;
    private $endTime;

    public function __construct($date, $allEvents, $startTime, $endTime){
        $this->date = $date;
        $this->dateString = $date->format('Y-m-d');
        $this->startTime = $startTime;
        $this->endTime = $endTime;
        $this->events = $this->getEventsOnDate($allEvents);
        $this->addPercentageValuesToDayEvents($this->events);
    }

    public function isToday(){
        $today = Date("Y-m-d");
        if ($this->dateString == $today) return true;
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
            $event->setStartTimePercentage(($event->getStartTime() - $this->startTime) / ($this->endTime - $this->startTime) * 100);
            $event->setHeightPercentage(($event->getEndTime() - $event->getStartTime()) / ($this->endTime - $this->startTime) * 100);
        }
    }

    public function getCurrentTimeTopPercentage(){
        $dateTime = new DateTime();
        $hour = $dateTime->format('H');
        $minutes = $dateTime->format('i');
        $minutesInHourFormat = $minutes / 60;
        $decimalHour = $hour + $minutesInHourFormat;

        $decimalHour = 0;
        $topPercentage = ($decimalHour  - $this->startTime) / ($this->endTime - $this->startTime) * 100;

        return $topPercentage;
    }

}