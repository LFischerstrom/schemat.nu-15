<?php

require_once("simple_html_dom.php");

//$mine = new Miner();

class Miner
{

    const GROUPS_FILE = "groups.html";
    const COURSES_FILE = "courses.html";
    const GROUPS_JSON = "groups.json";
    const COURSES_JSON = "courses.json";


    public function __construct()
    {

        // do this NOT every time
        /*$groups = $this->mine(self::GROUPS_FILE);
        $courses = $this->mine(self::COURSES_FILE);

        file_put_contents(self::GROUPS_JSON,json_encode($groups));
        file_put_contents(self::COURSES_JSON,json_encode($courses));
*/
    }



    private function mine($file){
        $html = new simple_html_dom();
        $html->load_file($file);
        $currentGroup = null;
        $currentCode = null;
        $array = array();

        foreach ($html->find('tr') as $element) {
            $group = $element->find("label", 0);
            $input = $element->find("td[class=choiceinput]");
            if ($group != null) $currentGroup = strtok($group->innertext, " ");
            if ($input != null) $currentCode = $input[0]->find("input")[0]->value;
            if ($group != null && $input != null) $array[$currentGroup] = $currentCode;
        }
        return $array;
    }

    public function getGroups(){
        return json_decode(file_get_contents(self::GROUPS_JSON), true);
    }

    public function getCourses(){
        return json_decode(file_get_contents(self::COURSES_JSON), true);
    }

    public function getGroupsAndCourses(){
        return array_merge($this->getGroups(), $this->getCourses());
    }



}