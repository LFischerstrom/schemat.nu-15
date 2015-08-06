<?php
require_once("connect.php");

class DatabaseConnection {

    public function insertSchedule($code, $object, $type){
        $code = htmlspecialchars($code);
        $object = htmlspecialchars($object);
        $sql = "insert into schedules (id, code, object, type) values (id, '$code','$object', '$type')";
        $sql = mysql_query($sql);
    }


    public function insertCourse($code, $object){
        $code = htmlspecialchars($code);
        $object = htmlspecialchars($object);
        $sql = "insert into courses (id, code, object) values (id, '$code','$object')";
        $sql = mysql_query($sql) or die(mysql_error());
    }

    public function insertGroup($code, $object){
        $code = htmlspecialchars($code);
        $object = htmlspecialchars($object);
        $sql = "insert into groups (id, code, object) values (id, '$code','$object')";
        $sql = mysql_query($sql) or die(mysql_error());
    }

    public function getObject($code){
        $code = htmlspecialchars($code);
        $sql = "SELECT code, object FROM schedule WHERE code = '$code'";
        $result = mysql_query($sql) or die(mysql_error());
        $row = mysql_fetch_assoc( $result);
        return $row["object"];
    }

    public function getObject2($code){
        $code = htmlspecialchars($code);
        $sql = "SELECT  code, object FROM
        (
            SELECT code, object FROM courses
            UNION ALL
            SELECT code, object FROM groups
        ) a WHERE code = '$code'";
        $result = mysql_query($sql) or die(mysql_error());
        $row = mysql_fetch_assoc( $result);
        return $row["object"];
    }

    public function getAllGroupsAndCourses(){
        $sql = "SELECT  id, code, object FROM schedules";
        $result = mysql_query($sql) or die(mysql_error());
        while( $row = mysql_fetch_assoc( $result)){
            $new_array[] = $row; // Inside while loop
        }
        return $new_array;
    }

    // TODO: fixe type
    public function getGroups(){
        $sql = "SELECT  id, code, object FROM schedules WHERE TYPE = 1";
        $result = mysql_query($sql) or die(mysql_error());
        $array = array();
        while( $row = mysql_fetch_assoc( $result)){
            array_push($array,$row);
        }
        return $array;
    }

    // TODO: fixe type
    public function getCourses(){
        $sql = "SELECT  id, code, object FROM schedules WHERE TYPE = 2";
        $result = mysql_query($sql) or die(mysql_error());
        $array = array();
        while( $row = mysql_fetch_assoc( $result)){
            array_push($array,$row);
        }
        return $array;
    }


    // TODO: fixe type
    public function getNumberOfGroups(){
        $result = mysql_query("SELECT COUNT(*) FROM schedules WHERE type = 1")  or die(mysql_error());
        $row = mysql_fetch_row($result);
        return $row[0];
    }

    // TODO: fixe type
    public function getNumberOfCourses(){
        $result = mysql_query("SELECT COUNT(*) FROM schedules WHERE type = 0") or die(mysql_error());
        $row = mysql_fetch_row($result);
        return $row[0];
    }

    public function getNumberOfCoursesAndGroups(){
        $sql = " (SELECT COUNT(*) FROM schedules) ";
        $result = mysql_query($sql) or die(mysql_error());
        $row = mysql_fetch_row($result);
        return $row[0];
    }

    public function getIds($offset, $amount){
        $sql = "SELECT code FROM schedules LIMIT $offset, $amount";
        $result = mysql_query($sql) or die(mysql_error());
        $idArray = array();
        while( $row = mysql_fetch_assoc( $result)){
            array_push($idArray,$row); // Inside while loop
        }
        return $idArray;
    }

    public function removeAllSchedulesForUser($user){
        $user = htmlspecialchars($user);
        $sql = "DELETE FROM users_schedules
        WHERE user_id= (select id from users where liu_id = '$user');";
        mysql_query($sql) or die(mysql_error());
    }

    public function addScheduleForUser($code, $user){
        $user = htmlspecialchars($user);
        $code = htmlspecialchars($code);
        $sql = "INSERT INTO users_schedules (user_id, schedule_id )VALUES (
                  (SELECT id FROM users WHERE liu_id = '$user'),
                  (SELECT id FROM schedules WHERE code = '$code')
                  )";
        mysql_query($sql) or die(mysql_error());
    }

    public function addUser($user){
        $user = htmlspecialchars($user);
        $sql = "insert into users (id, liu_id) values (id, '$user')";
        // continue even if fail -> it means user already exists.
        mysql_query($sql);
    }

    public function isUser($id){
        $id = htmlspecialchars($id);
        $sql = "SELECT EXISTS(SELECT * FROM users WHERE liu_id = '$id')";
        $result = mysql_query($sql);
        $array = mysql_fetch_array($result);
        return $array[0];
    }

    public function getSchedulesForUser($id){
        $id = htmlspecialchars($id);
        $sql = "SELECT code FROM schedules WHERE id IN (SELECT schedule_id FROM users_schedules WHERE user_id = (SELECT id FROM users WHERE liu_id = '$id'))";
        $result = mysql_query($sql) or die(mysql_error());
        $courseArray = array();
        while( $row = mysql_fetch_assoc( $result)){
            array_push($courseArray,$row);
        }
        return $courseArray;
    }


}