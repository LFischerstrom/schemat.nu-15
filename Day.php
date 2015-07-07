<?php
require_once('ics-parser/class.iCalReader.php');


class Day {

    const STARTTIME =8;
    const ENDTIME = 18;
    const TIMEEDIT_TIMEZONE_BUG_CORRECTION_HOURS = 4;
    const DAY_HEADER_HEIGHT = 20;

    private $date;
    private $dateString;
    private $allEvents;
    private $dayEvents;

    public function __construct($date, $events){
        $this->allEvents = $events;
        $this->date = $date;
        $this->dateString = $date->format('Y-m-d');
        $this->dayEvents = $this->getEventsOnDate($this->allEvents);
    }

    // TODO: Fix to real today.
    public function isToday(){
        if ($this->dateString == "2015-08-19" ) return true;
        return false;
    }

    public function getDayHtml(){
        $html= "";

        for ($i = 0; $i<sizeof($this->dayEvents); $i++){
            $event = $this->dayEvents[$i];
            $eventStyle = "";

            $eventStartTimeUnix =  iCalDateToUnixTimestamp($event['DTSTART']);
            $eventStartTimeDateTime = DateTime::createFromFormat( 'U', $eventStartTimeUnix, new DateTimeZone("Europe/Stockholm"));
            $eventStartTimeHour = $eventStartTimeDateTime->format( 'H' ) + self::TIMEEDIT_TIMEZONE_BUG_CORRECTION_HOURS;
            $eventStartTimeMinutes = $eventStartTimeDateTime->format( 'i' );
            $eventStartTimeMinutesInHourFormat = $eventStartTimeMinutes / 60;
            $eventStartTime = $eventStartTimeHour + $eventStartTimeMinutesInHourFormat;


            $eventEndTime = iCalDateToUnixTimestamp($event['DTEND']);
            $eventEndTimeDateTime = DateTime::createFromFormat('U',$eventEndTime, new DateTimeZone("Europe/Stockholm"));
            $eventEndTimeHour = $eventEndTimeDateTime->format( 'H' ) + self::TIMEEDIT_TIMEZONE_BUG_CORRECTION_HOURS;
            $eventEndTimeMinutes = $eventEndTimeDateTime->format( 'i' );
            $eventEndTimeMinutesInHourFormat = $eventEndTimeMinutes / 60;
            $eventEndTime = $eventEndTimeHour + $eventEndTimeMinutesInHourFormat;


            $eventStartTimePercentage = ($eventStartTime  - self::STARTTIME) / (self::ENDTIME - self::STARTTIME) * 100;
            $eventHeightPercentage = ($eventEndTime - $eventStartTime) / (self::ENDTIME - self::STARTTIME) * 100 - 0; // 0.2 is to prevent a div collision handling bug

            $eventStyle .= "top:" . $eventStartTimePercentage ."%;";
            $eventStyle .= "height:" . $eventHeightPercentage."%;";

            $html .= '<div class="event" style="'.$eventStyle.'">';
            $html .= $eventStartTimeHour.".".$eventStartTimeMinutes ." - ". $eventEndTimeHour .".".$eventEndTimeMinutes ."<br />";
            $html .= str_replace("\\,",",<br />",@$event['SUMMARY']);
            $html .= '</div>' . "\r\n";
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
        $events = [];
        for ($i = 0; $i<sizeof($allEvents);$i++){
            $event = $allEvents[$i];
            $eventStartUnix = iCalDateToUnixTimestamp($event['DTSTART']);
            $eventStartDate = new DateTime('@' . $eventStartUnix);
            $eventStartDateString = $eventStartDate->format('Y-m-d');

            if ($eventStartDateString == $this->dateString){
                $events[sizeof($events)] = $event;
            }
        }
        return $events;
    }

}