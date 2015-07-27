<?php
require_once('Event.php');

class Week {
    private $dayNames;
    private $numberOfDaysInWeek;
    private $events;
    private $year;
    private $weekNumber;
    private $mondayDate;
    private $weekDaysWithEvents;
    private $latestEnd;
    private $earliestStart;

    function __construct($events, $year, $weekNumber){
        $this->year = $year;
        $this->weekNumber = $weekNumber;
        $this->dayNames = $this->getDayNames();
        $this->mondayDate = $this->getMondayDate();
        $this->events = $this->getEventsForWeek($events);
        $this->numberOfDaysInWeek = $this->getNumberOfDaysInWeek();
    }

    private function getMondayDate(){
        $week_start = new DateTime();
        $week_start->setISODate($this->year,$this->weekNumber);
        return $week_start;
    }

    private function getSundayDate(){
        $date = clone $this->mondayDate;
        $date = $date->modify('+6 days');
        return $date;
    }

    private function dateToMdString($date){
        $day = (int)$date->format('d');
        $month = (int)$date->format('m');
        $dateString = $day.'/'.$month;
        return $dateString;
    }

    public function getWeekContent(){
        $weekContent = '<div class="section"><div class="slide"><div class="table">';
        $weekContent .= '<div class="row">';
        $weekContent .= $this->getDaysContent();
        $weekContent .= '</div><!-- weekdayContents ends --></div><!-- table ends --></div><!-- slide end-->
        <div class="slide"></div></div><!-- section ends -->';
        return $weekContent;
    }

    private function getDaysContent(){
        $daysContent ="";
        $date = clone $this->mondayDate;
        for ($i=0; $i < $this->numberOfDaysInWeek; $i++){
            $day = new Day($date, $this->events, $this->earliestStart, $this->latestEnd);
            $daysContent .= '<div class="cell">';

            if ($day->isToday()) $todayClass = "today";
            else $todayClass = "";
            $daysContent .= '<div class="weekdayHeader '.$todayClass.'">' . $this->getDayHeader($i).'</div>';
            $daysContent .= '<div class="weekdayContent">';

            '.$todayClass.';
            $daysContent .= $day->getDayHtml();
            $daysContent .= '</div></div>';
            $date = $date->modify('+1 days');

        }
        return $daysContent;
    }

    private function getDayNames(){
        $dayNames = array(
            0=>'Mån',
            1=>'Tis',
            2=>'Ons',
            3=>'Tor',
            4=>'Fre',
            5=>'Lör',
            6=>'Sön',
        );
        return $dayNames;
    }

    private function getDayHeader($dayIndex){
        $mondayDate = clone $this->mondayDate;
        $currentDate = $mondayDate->modify('+'.$dayIndex.' days');
        $currentDateMd = $this->dateToMdString($currentDate);
        $currentDayName =  $this->dayNames[$dayIndex];
        $header = $currentDayName . " " . $currentDateMd;
        return $header;
    }


    private function getEventsForWeek($events){
        $eventsInWeek = array();
        $this->weekDaysWithEvents = array(false);
        $this->earliestStart = "8";
        $this->latestEnd = "17";
        $weekStartUnix = strtotime($this->mondayDate->format('Y-m-d'));
        $weekEndUnix = strtotime($this->getSundayDate()->format("Y-m-d 23:59:59"));

        if (!isset($events)) return $eventsInWeek;
        foreach ($events as $event) {
            if ($event->getStartTimeUnix() > $weekStartUnix && $event->getStartTimeUnix() < $weekEndUnix){
                array_push($eventsInWeek, $event);
                $this->weekDaysWithEvents[date('N', $event->getStartTimeUnix())] = true;
                if ((int) $event->getStartTimeHour() < $this->earliestStart){
                    $this->earliestStart = (int) $event->getStartTimeHour();
                }
                if ((int) $event->getEndTimeHour() > $this->latestEnd){
                    $this->latestEnd = (int) $event->getEndTimeHour();
                }
            }
        }
        return $eventsInWeek;
    }

    private function getNumberOfDaysInWeek()
    {
        if (isset($this->weekDaysWithEvents[7])) return 7;
        else if (isset($this->weekDaysWithEvents[6])) return 6;
        else return 5;
    }
}