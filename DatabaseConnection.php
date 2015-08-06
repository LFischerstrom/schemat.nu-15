<?php
require_once("connect.php");

class DatabaseConnection {

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
        $sql = "SELECT  id, code, object FROM
        (
            SELECT id, code, object FROM courses
            UNION ALL
            SELECT id, code, object FROM groups
        ) s GROUP BY code";
        $result = mysql_query($sql) or die(mysql_error());
        while( $row = mysql_fetch_assoc( $result)){
            $new_array[] = $row; // Inside while loop
        }
        return $new_array;
    }

    public function getNumberOfGroups(){
        $result = mysql_query("SELECT COUNT(*) FROM groups") or die(mysql_error());
        $row = mysql_fetch_row($result);
        return $row[0];
    }

    public function getNumberOfCourses(){
        $result = mysql_query("SELECT COUNT(*) FROM courses") or die(mysql_error());
        $row = mysql_fetch_row($result);
        return $row[0];
    }

    public function getNumberOfCoursesAndGroups(){
        $sql1 = " (SELECT COUNT(*) FROM courses) ";
        $sql2 = "SELECT COUNT(*)," . $sql1 . " FROM groups";
        $result = mysql_query($sql2) or die(mysql_error());
        $row = mysql_fetch_row($result);
        return $row[1]+$row[0];
    }

    public function getIds($offset, $amount){
        $sql = "SELECT  code FROM
        (
            SELECT code, object FROM courses
            UNION ALL
            SELECT code, object FROM groups
        ) s GROUP BY code LIMIT $offset, $amount";
        $result = mysql_query($sql) or die(mysql_error());
        $idArray = array();
        while( $row = mysql_fetch_assoc( $result)){
            array_push($idArray,$row); // Inside while loop
        }
        return $idArray;
    }

    public function removeAllCoursesForUser($user){
        $user = htmlspecialchars($user);
        $sql = "DELETE FROM users_courses
        WHERE user_id= (select id from users where liu_id = '$user');";
        mysql_query($sql) or die(mysql_error());
    }

    public function addCourseForUser($course, $user){
        $user = htmlspecialchars($user);
        $course = htmlspecialchars($course);
        $sql = "INSERT INTO users_courses (user_id, course_id )VALUES (
                  (SELECT id FROM users WHERE liu_id = '$user'),
                  (SELECT id FROM  (
            SELECT code, object FROM courses
            UNION ALL
            SELECT code, object FROM groups
        ) s GROUP BY code) WHERE code = '$course')
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

    public function getCoursesForUser($id){
        $id = htmlspecialchars($id);
        $sql = "SELECT code FROM courses WHERE id IN (SELECT course_id FROM users_courses WHERE user_id = (SELECT id FROM users WHERE liu_id = '$id'))";
        $result = mysql_query($sql) or die(mysql_error());
        $courseArray = array();
        while( $row = mysql_fetch_assoc( $result)){
            array_push($courseArray,$row);
        }
        return $courseArray;
    }


}