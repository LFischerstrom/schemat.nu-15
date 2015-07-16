<?php

class Event
{

    const TIMEEDIT_TIMEZONE_BUG_CORRECTION_HOURS = 4;

    public $icalEvent;

    private $startTimeUnix;
    private $startTimeHour;
    private $startTimeMinutes;
    private $startTime;
    private $startDate;
    private $startDateString;

    private $startTimePercentage;
    private $heightPercentage;

    private $endTimeUnix;
    private $endTimeHour;
    private $endTimeMinutes;
    private $endTime;
    private $location;
    private $course;
    private $summary;
    private $allSummary;


    function __construct($icalEvent)
    {
        $this->icalEvent = $icalEvent;

        $this->startTimeUnix = iCalDateToUnixTimestamp($icalEvent['DTSTART']);
        $this->endTimeUnix = iCalDateToUnixTimestamp($icalEvent['DTEND']);


        $this->startDate = new DateTime('@' . $this->startTimeUnix);
        $this->startDateString = $this->startDate->format('Y-m-d');

        $startTimeDateTime = DateTime::createFromFormat('U',   $this->startTimeUnix, new DateTimeZone("Europe/Stockholm"));
        $this->startTimeHour = $startTimeDateTime->format('H') + self::TIMEEDIT_TIMEZONE_BUG_CORRECTION_HOURS;
        $this->startTimeMinutes = $startTimeDateTime->format('i');
        $startTimeMinutesInHourFormat = $this->startTimeMinutes / 60;
        $this->startTime = $this->startTimeHour + $startTimeMinutesInHourFormat;

        $endTimeDateTime = DateTime::createFromFormat('U', $this->endTimeUnix, new DateTimeZone("Europe/Stockholm"));
        $this->endTimeHour = $endTimeDateTime->format('H') + self::TIMEEDIT_TIMEZONE_BUG_CORRECTION_HOURS;
        $this->endTimeMinutes = $endTimeDateTime->format('i');
        $endTimeMinutesInHourFormat = $this->endTimeMinutes / 60;
        $this->endTime = $this->endTimeHour + $endTimeMinutesInHourFormat;

        $this->allSummary = $this->summary = $this->icalEvent['SUMMARY'];
        $this->location = $this->extractLocation();
        $this->course = $this->extractCourse();
    }

    public function getHtml(){
        $style = $this->getStyle();

        $html = '<div class="event" style="' . $style . '">';

        $html .= $this->getTimeDiv();

        $html .= '<div class="eventRow">';
        $html .= $this->getLocationDiv();
        $html .= $this->getCourseDiv();
        $html .= '</div>';

        $html .= $this->getRestDiv();



        $html .= '<div class="shadow"></div>' . "\r\n";

        // More info div
        $html .= '<div class="moreInfo">';
        $html .= '<div class="header"><div>';
        $html .= $this->getDayName(date( "w", $this->startTimeUnix)) . " " . $this->dateToMdString($this->startDate);
        $html .= "</div><div>";
        $html .= $this->startTimeHour . "." . $this->startTimeMinutes . " - " . $this->endTimeHour . "." . $this->endTimeMinutes ;
        $html .= '</div></div><div class="content">';
        $html .= str_replace("\\,", "<br />", $this->allSummary);
        $html .= '</div></div></div>';

        return $html;
    }

    private function getRestDiv(){
        $html = '<div class="rest"><div class="wrapper">';
        $html .= str_replace("\\,", "<br />", $this->summary);
        $html .= '</div></div>';
        return $html;
    }

    private function getTimeDiv(){
        $html = '<div class="time">';
        $html .= $this->startTimeHour . "." . $this->startTimeMinutes . "<span class='end'> - " . $this->endTimeHour . "." . $this->endTimeMinutes . "</span>";
        $html .= '</div>';
        return $html;
    }

    private function getLocationDiv(){
        $html = '<div class="location">';
        $html .= $this->location;
        $html .= '</div>';
        return $html;
    }

    private function getCourseDiv(){
        $html = '<div class="course">';
        // only make course div if there is a course. If not make space for rest line.
        if ($this->course != "") $html .= $this->course;
        // take first line from rest and put in course div
        else $html .= $this->extractNextLineFromSummary();
        $html .= '</div>';
        return $html;
    }

    private function getStyle(){
        $style = "";
        $style .= "top:" . $this->startTimePercentage . "%;";
        $style .= "height:" . $this->heightPercentage . "%;";
        return $style;
    }

    public function getStartDateString(){
        return $this->startDateString;
    }

    public function getStartTime(){
        return $this->startTime;
    }

    public function getEndTime(){
        return $this->endTime;
    }

    public function setStartTimePercentage($percent){
        $this->startTimePercentage = $percent;
    }

    public function setHeightPercentage($percent){
        $this->heightPercentage = $percent;
    }

    public function getStartTimeUnix(){
        return $this->startTimeUnix;
    }

    private function extractCourse()
    {
        $regex = "/Kurs: (.+?)\\\\,*/";
        preg_match($regex, $this->summary, $matches);
        $this->summary = preg_replace($regex,"",$this->summary);
        if (sizeof($matches) > 1) return $matches[1];
        else return "";
    }

    private function extractNextLineFromSummary(){
        $summaryFirstLine = strtok($this->summary, "\\,");
        $this->summary = preg_replace('/^.+\,/', '', $this->summary);
        return $summaryFirstLine;
    }

    private function extractLocation()
    {
        $regex = "/Sal: (.+?)\\\\,*/";
        preg_match($regex, $this->summary, $matches);
        $this->summary = preg_replace($regex,"",$this->summary);
        if (sizeof($matches) > 1) return $matches[1];
        else return "";
    }

    private function dateToMdString($date){
        $day = (int)$date->format('d');
        $month = (int)$date->format('m');
        $dateString = $day.'/'.$month;
        return $dateString;
    }

    private function getDayName($i){
        $dayNames = array(
            0=>'Mån',
            1=>'Tis',
            2=>'Ons',
            3=>'Tor',
            4=>'Fre',
            5=>'Lör',
            6=>'Sön',
        );
        return $dayNames[($i - 1) % 7];
    }

}