<?php
require_once("simple_html_dom.php");



class Miner
{

    const GROUPS_FILE = "groups.html";
    const COURSES_FILE = "courses.html";
    const COURSES_JS = "javascript/courses.js";
    const GROUPS_JS = "javascript/groups.js";

    private function mine($file){
        $htmlString = html_entity_decode(file_get_contents($file));
        $html = new simple_html_dom();
        $html->load($htmlString);
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

    public function mineCourses(){
        $courses = $this->mine(self::COURSES_FILE);

        // Adds all new courses to the database but doesn't update the old if any changes.
        require_once("DatabaseConnection.php");
        $db = new DatabaseConnection();
        foreach($courses as $course => $object){
            $db->insertCourse($course, $object);
        }

        $this->createJsArrayCoursesFile();
    }

    public function mineGroups(){
        $groups = $this->mine(self::GROUPS_FILE);

        // Adds all new courses to the database but doesn't update the old if any changes.
        require_once("DatabaseConnection.php");
        $db = new DatabaseConnection();
        foreach($groups as $group => $object){
            $db->insertGroup($group, $object);
        }

        $this->createJsArrayGroupFile();
    }

    private function createJsArrayGroupFile(){
        $groups = $this->getGroups();


        $arrayText = "var groups = [";
        foreach ($groups as $group => $id){
            $arrayText .= "{ id:'". $group ."'} ,";
        }
        // removes last comma
        $arrayText = rtrim($arrayText, ",");
        $arrayText .= "];";
        file_put_contents(self::GROUPS_JS, $arrayText);
    }

    private function createJsArrayCoursesFile(){
        $courses = $this->getCourses();
        $arrayText = "var courses = [";

        foreach ($courses as $course => $id){
            $arrayText .= "{ id:'". $course."'} ,";
        }
        // removes last comma
        $arrayText = rtrim($arrayText, ",");
        $arrayText .= "];";
        file_put_contents(self::COURSES_JS,$arrayText);
    }


}