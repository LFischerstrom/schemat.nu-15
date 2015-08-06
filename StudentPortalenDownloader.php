<?php
require_once("simple_html_dom.php");
require_once("config2.php");

class StudentPortalenDownloader {

    public function downloadCoursesAndGroupsHtmlFiles(){

        $html = new simple_html_dom();

        $studentPortalenUrl = "https://www3.student.liu.se/portal/login";

        $html->load_fileX($studentPortalenUrl);
        $loginPara = $html->find("input[name=login_para]")[0]->value;
        $time = $html->find("input[name=time]")[0]->value;

        $coursesUrl = "https://www3.student.liu.se/portal/schema/ChooseCourse";
        $groupsUrl = "https://www3.student.liu.se/portal/schema/ChooseStudentGroup";

        //create array of data to be posted
        $post_data['login_para'] = $loginPara;
        $post_data['time'] = $time;
        $post_data['user'] = STUDENTPORTALEN_USERNAME;
        $post_data['pass2'] = STUDENTPORTALEN_PASSWORD;

        //traverse array and prepare data for posting (key1=value1)
        foreach ( $post_data as $key => $value) {
            $post_items[] = $key . '=' . $value;
        }

        //create the final string to be posted using implode()
        $post_string = implode ('&', $post_items);

        //create cURL connection
        $curl_connection = curl_init();
        curl_setopt($curl_connection, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl_connection, CURLOPT_USERAGENT,
            "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)");
        curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl_connection, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($curl_connection, CURLOPT_COOKIEJAR, 'cookie.txt');
        curl_setopt($curl_connection, CURLOPT_COOKIEFILE, 'cookie.txt');
        curl_setopt($curl_connection, CURLOPT_URL, $studentPortalenUrl);
        $result = curl_exec($curl_connection);

        // Courses
        curl_setopt($curl_connection, CURLOPT_URL, $coursesUrl);
        $result = curl_exec($curl_connection);
        $html->load($result);
        $puser = $html->find("input[name=puser]")[0]->value;
        $coursesUrl = 'https://www3.student.liu.se/portal/schema/SearchCourse?puser=' . $puser . '&searchKey=';
        curl_setopt($curl_connection, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl_connection, CURLOPT_URL, $coursesUrl);
        $result = curl_exec($curl_connection);
        file_put_contents("courses.html",$result);
        if ($result) print "Downloaded courses.html <br />";

        // Groups
        curl_setopt($curl_connection, CURLOPT_URL, $groupsUrl);
        $result = curl_exec($curl_connection);
        $html->load($result);
        $puser = $html->find("input[name=puser]")[0]->value;
        $groupsUrl = 'https://www3.student.liu.se/portal/schema/SearchStudentGroup?puser=' . $puser . '&searchKey=';
        curl_setopt($curl_connection, CURLOPT_URL, $groupsUrl);
        $result = curl_exec($curl_connection);
        file_put_contents("groups.html",$result);
        if ($result) print "Downloaded groups.html";


        if(!curl_exec($curl_connection)){
            die('Error: "' . curl_error($curl_connection) . '" - Code: ' . curl_errno($curl_connection));
        }

        //close the connection
        curl_close($curl_connection);

    }

}