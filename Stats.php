<?php
include("connect.php");
new Stats();

class Stats{

    private $allClasses;

    public function __construct(){
        if (basename($_SERVER['PHP_SELF']) == "Stats.php") $this->printStatsPage();
    }

    public function captureStats(){
        $currentClass = htmlspecialchars($id = $_COOKIE['SchematId']);
        $ip = htmlspecialchars($_SERVER['REMOTE_ADDR']);
        $sql = "INSERT INTO stats (id, ip, class,timestamp) VALUES (id, '$ip','$currentClass',CURRENT_TIMESTAMP)";
        $sql = mysql_query($sql);
    }

    // $unique: boolean
    // $specificClass = false or "class", "IT3" etc.
    public function getVisits($unique, $specificClass, $startDate, $endDate){
        if ($unique == true) $unique = "DISTINCT";
        else $unique = "";
        if ($specificClass == false) $specificClass = "";
        else $specificClass = "AND class='$specificClass'";
        $sql = "SELECT $unique ip FROM stats WHERE  `timestamp` >= '$startDate' AND `timestamp` <  '$endDate' + INTERVAL 1 DAY $specificClass ORDER BY class";
        $query = mysql_query($sql);
        $visits = mysql_num_rows($query);
        return $visits;
    }

    public function getAllClasses(){
        $classArray = array();
        $sql = "SELECT DISTINCT class FROM stats ORDER BY class";
        $sql = mysql_query($sql) or die ("Error: Couldn't catch classes");
        while ($resultat = mysql_fetch_array($sql))
        {
            $classArray[] = $resultat["class"];
        }
        return $classArray;
    }

    public function getIndividualScheduleVisits($unique, $since){
        if (!isset($this->allClasses)) $this->allClasses = $this->getAllClasses();
        $allClasses = $this->allClasses;
        foreach ($allClasses as $class) {
            $individualVisits[] =  $this->getVisits($unique, $class, "MONTH", 8);
        }

        return $individualVisits;
    }

    public function getTodaysDate(){
        return date("Y-m-d");
    }

    public function getYesterDaysDate(){
        return date("Y-m-d", time()-86400);
    }

    public function getWeekStartDate(){
        return date("Y-m-d", time()-(86400)*date('N')-1);
    }

    public function getLastWeekStartDate(){
        return date("Y-m-d", time()-(86400)*(date('N')-1+7));
    }

    public function getLastWeekEndDate(){
        return date("Y-m-d", time()-(86400)*(date('N')));
    }

    public function getMonthStartDate(){
        return date("Y-m-01");
    }

    public function getLastMonthStartDate(){
        return date("Y-m-01", strtotime("first day of previous month"));
    }

    public function getLastMonthEndDate(){
        return date("Y-m-t", strtotime("last day of previous month"));
    }

    public function getYearStartDate(){
        return date("Y-01-01");
    }

    public function getLastYearStartDate(){
        return date("Y-01-01", strtotime("first day of previous year"));
    }

    public function getLastYearEndDate(){
        return date("Y-12-31", strtotime("last day of previous year"));
    }

    public function getStatsColumn($header1, $header2, $header3,
                                   $fromDate1, $endDate1, $fromDate2, $endDate2){

        if (!isset($this->allClasses)) $this->allClasses = $this->getAllClasses();

        $content = "<div class='statsTable'>
			<div class='line1'>$header1</div>
			<div class='line2'>$header2</div>
			<div class='line2'>$header3</div>
			<div class=\"stats4columns\" style='border: 0px solid black;'>
			<div>A</div><div>U</div><div>A</div><div>U</div>";
        $allClasses = $this->allClasses;
        foreach ($allClasses as $class) {
            $content .= "
			<div>".
                $this->getVisits(false, $class, $fromDate1, $endDate1)
                ."</div><div>".
                $this->getVisits(true, $class, $fromDate1, $endDate1)
                ."</div><div>".
                $this->getVisits(false, $class, $fromDate2, $endDate2)
                ."</div><div>".
                $this->getVisits(true, $class, $fromDate2, $endDate2).
                "</div>";
        }

        $content .= "<strong><div>".
            $this->getVisits(false, false, $fromDate1, $endDate1)
            ."</div><div>".
            $this->getVisits(true, false, $fromDate1, $endDate1)
            ."</div><div>".
            $this->getVisits(false, false, $fromDate2, $endDate2)
            ."</div><div>".
            $this->getVisits(true, false, $fromDate2, $endDate2).
            "</div></strong>";

        $content .= "</div><br style=\"clear:both;\"></div>";
        return $content;
    }

    public function getClassList(){
        $content = "<div class='classTable'>
		<div>&nbsp;</div>
		<div>&nbsp;</div>
		<div>Scheman</div>
		";
        if (!isset($this->allClasses)) $this->allClasses = $this->getAllClasses();
        $allClasses = $this->allClasses;
        foreach ($allClasses as $class) {
            $content .= "<div>$class</div>";
        }
        $content .= "<div><strong>Totalt</strong></div></div>";
        return $content;
    }

    public function updateStats(){

        $allClasses = $this->allClasses;

        $content = $this->getClassList();

        $content .= $this->getStatsColumn("Dag","Idag","Igår", $this->getTodaysDate(), $this->getTodaysDate(),
            $this->getYesterDaysDate(), $this->getYesterDaysDate());

        $content .= $this->getStatsColumn("Vecka","Denna","Förra", $this->getWeekStartDate(), $this->getTodaysDate(),
            $this->getLastWeekStartDate(), $this->getLastWeekEndDate());

        $content .= $this->getStatsColumn("Månad","Denna","Förra", $this->getMonthStartDate(), $this->getTodaysDate(),
            $this->getLastMonthStartDate(), $this->getLastMonthEndDate());

        $content .= $this->getStatsColumn("År","Detta","Förra", $this->getYearStartDate(), $this->getTodaysDate(),
            $this->getLastYearStartDate(), $this->getLastYearEndDate());

        $this->writeTxtFile($content,"../stats/stats.txt");

    }

    public function writeTxtFile($content, $filePath) {
        $fh = fopen($filePath, 'w') or die("can't open file");
        fwrite($fh, $content);
        fclose($fh);
    }

    public function printStatsPage(){
        echo '<html><head>
    <link rel="stylesheet" type="text/css" href="css/stats.css" />

</head><body>';
        echo "<h1>Statistik</h1><p>Antal besökare(A) respektive unika besökare (U) på schemat.nu presenteras nedan.</p>";

        echo file_get_contents("stats/stats.txt");
        echo "</body></html>";
    }
}



?>

