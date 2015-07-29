<?php
include("connect.php");

new Stats();

class Stats
{

    private $allClasses;

    public function __construct()
    {
        //if (basename($_SERVER['PHP_SELF']) == "Stats.php")
        print $this->getStatsPage();
    }

    public function captureStats($id)
    {
        $id = htmlspecialchars($id);
        $ip = htmlspecialchars($_SERVER['REMOTE_ADDR']);
        $sql = "INSERT INTO stats (id, ip, class,timestamp) VALUES (id, '$ip','$id',CURRENT_TIMESTAMP)";
        $sql = mysql_query($sql);
    }

    public function getStatsPage(){
        $html = '<html>
        <head>
        <link rel="stylesheet" type="text/css" href="css/stats.css" />
        <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
        <script type="text/javascript" src="javascript/tablesorter/jquery-latest.js"></script>
        <script type="text/javascript" src="javascript/tablesorter/jquery.tablesorter.js"></script>

        </head>
        <body id="stats">
        <table id="table" class="tablesorter"><thead>';

        $html .= '<tr class="row">';
        $html .= '<th class="cell"></th>';
        $html .= '<th class="cell">Idag</th>';
        $html .= '<th class="cell"></th>';
        $html .= '<th class="cell">Igår</th>';
        $html .= '<th class="cell"></th>';
        $html .= '<th class="cell">Denna vecka</th>';
        $html .= '<th class="cell"></th>';
        $html .= '<th class="cell">Förra veckan</th>';
        $html .= '<th class="cell"></th>';
        $html .= '</tr>';

        $html .= '<tr class="row">';
        $html .= '<th class="cell">Ids</th>';
        $html .= '<th class="cell">A</th>';
        $html .= '<th class="cell">U</th>';
        $html .= '<th class="cell">A</th>';
        $html .= '<th class="cell">U</th>';
        $html .= '<th class="cell">A</th>';
        $html .= '<th class="cell">U</th>';
        $html .= '<th class="cell">A</th>';
        $html .= '<th class="cell">U</th>';
        $html .= '</tr></thead><tbody>';

        $todaysVisits = $this->getVisits($this->getTodaysDate(), $this->getTodaysDate());
        $yesterDaysVisits = $this->getVisits($this->getYesterDaysDate(), $this->getYesterDaysDate());
        $thisWeekVisits = $this->getVisits($this->getWeekStartDate(), $this->getTodaysDate());
        $lastWeekVisits = $this->getVisits($this->getLastWeekStartDate(), $this->getLastWeekEndDate());

        $allVisits = array_merge($thisWeekVisits,$lastWeekVisits);

        foreach ($allVisits as $id => $ipArray){
            $html .= '<tr class="row">';
            $html .= '<td class="cell">' . $id . "</td>";
            $html .= '<td class="cell">' . $this->getNumberOfVisits($todaysVisits,$id) . '</td>';
            $html .= '<td class="cell">' . $this->getUniqueNumberOfVisits($todaysVisits,$id) . '</td>';
            $html .= '<td class="cell">' . $this->getNumberOfVisits($yesterDaysVisits,$id) . '</td>';
            $html .= '<td class="cell">' . $this->getUniqueNumberOfVisits($yesterDaysVisits,$id) . '</td>';
            $html .= '<td class="cell">' . $this->getNumberOfVisits($thisWeekVisits,$id) . '</td>';
            $html .= '<td class="cell">' . $this->getUniqueNumberOfVisits($thisWeekVisits,$id) . '</td>';
            $html .= '<td class="cell">' . $this->getNumberOfVisits($lastWeekVisits,$id) . '</td>';
            $html .= '<td class="cell">' . $this->getUniqueNumberOfVisits($lastWeekVisits,$id) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>


         <script>

        $(document).ready(function()
         {
        $("#table").tablesorter();


         }
);
</script>

</body></html>';
        return $html;
    }



    private function getVisits($startDate, $endDate)
    {
        $sql = "SELECT ip,class FROM stats WHERE timestamp >= '$startDate' AND timestamp <  '$endDate' + INTERVAL 1 DAY ORDER BY class";
        $result = mysql_query($sql);

        $visits = array();
        if ($result) {
            while ($row = mysql_fetch_assoc($result)) {
                $id = $row['class'];
                $ip = $row['ip'];
                if (!isset($visits[$id])) $visits[$id] = array();
                if (!isset($visits[$id][$ip])) $visits[$id][$ip] = 1;
                else $visits[$id][$ip]++;
            }
            return $visits;
        }
        else return "SQL ERROR.";
    }

    private function getUniqueNumberOfVisits($visits, $id){
        if (isset($visits[$id])) return sizeof($visits[$id]);
        else return "";
    }

    private function getUniqueNumberOfVisitsIp($visits, $id){
        $ipList = array();
        if (!isset($visits[$id])) return $ipList;
        foreach ($visits[$id] as $ip => $nrOfVisits) array_push($ipList,$ip);
        return $ipList;
    }

    private function getNumberOfVisits($visits, $id){
        $allVisits = 0;
        if (!isset($visits[$id])) return "";
        foreach ($visits[$id] as $ip => $nrOfVisits) $allVisits += $nrOfVisits;
        return $allVisits;
    }

    private function prettyPrint($a) {
        echo '<pre>'.print_r($a,1).'</pre>';
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

}
