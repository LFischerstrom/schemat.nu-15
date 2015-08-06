<?php

// Load the settings from the central config file
require_once 'phpCAS/docs/examples/config.example.php';

// Load the CAS lib
//require_once $phpcas_path . '/CAS.php';
require_once 'phpCAS/CAS.php';

// Enable debugging
phpCAS::setDebug();

// Initialize phpCAS
phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);

// For production use set the CA certificate that is the issuer of the cert
// on the CAS server and uncomment the line below
// phpCAS::setCasServerCACert($cas_server_ca_cert_path);

// For quick testing you can disable SSL validation of the CAS server.
// THIS SETTING IS NOT RECOMMENDED FOR PRODUCTION.
// VALIDATING THE CAS SERVER IS CRUCIAL TO THE SECURITY OF THE CAS PROTOCOL!
phpCAS::setNoCasServerValidation();

// force CAS authentication
phpCAS::forceAuthentication();

// at this step, the user has been authenticated by the CAS server
// and the user's login name can be read with phpCAS::getUser().

// logout if desired
if (isset($_REQUEST['logout'])) {
    phpCAS::logout();
}

require_once ("DatabaseConnection.php");
$db = new DatabaseConnection();
$user = phpCAS::getUser();
// Show schedule if already have one
if ($db->isUser($user) && sizeof($db->getCoursesForUser($user)) > 0 && !isset($_GET["edit"])){
    setcookie("SchematId",$user, time() + (365 * 24 * 60 * 60) );
    header("Location: /");
}
// else show this custom schedule page

?>

<html>
<head>
    <title>Schemat.nu</title>
    <meta charset="UTF-8">
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
    <script src='http://code.jquery.com/jquery-1.9.1.min.js'></script>

    <link rel="stylesheet" type="text/css" href="css/style.css" />
    <link rel="stylesheet" type="text/css" href="css/colors.css" />
    <link rel="stylesheet" type="text/css" href="css/chooseIdPage.css" />
    <link rel="stylesheet" type="text/css" href="css/menu.css" />

    <script src="javascript/list.js"></script>
    <script src="javascript/groups.js"></script>
    <script src="javascript/courses.js"></script>
    <script src="javascript/myJs.js"></script>
    <script src="//cdn.jsdelivr.net/jquery.scrollto/2.1.0/jquery.scrollTo.min.js"></script>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script>

        var liSelected;
        var li;
        var lastVisible
        var userList;

        $(document).ready(function() {
            li = $('.list li');

            prepareList();
            updateListVisibility();
            setupEnterPress();
            setupMenu();
            setupAddCourse();
            setupRemoveCourses();
        });

        $(document).on("keyup", function(e){
            updateSelection(e);
            updateListVisibility();
            updateNoResultStatus();
            setupAddCourse();
        });

        function setupAddCourse(){

            $(".list li").click(function(event){
                event.stopImmediatePropagation();
                var course = $(this).find(".id:first")[0].innerHTML;
                console.log(course);
                var html = '<div class="courseItem"><input readonly type="text" name="courses[]" value="'+course+'"><img src="images/cross.png" /></div>';
                $('.customPickList').append(html);
                $('.search').val('');
                $(".list").hide();
                $(".top").show();
                setupRemoveCourses();
            });

        }

        function setupRemoveCourses(){
            $(".courseItem img").click(function(event){
                event.stopImmediatePropagation();
                $(this).parent(".courseItem").remove();
            });
        }


    </script>


</head>
<body>

<div id="popupMenu">
    <ul id="menu">
        <li class="option"><a href="removeCookie.php">Byt Schema</a></li><br />
        <li class="option"><a href="report.php">Rapportera fel</a></li>
    </ul>
</div>

<header>
    <div id="row">
        <div id="left"><h1></h1></div>
        <div id="middle"><h1><?php echo phpCAS::getUser(); ?></h1></div>
        <div id="right"><a href="#"><img src="images/settings-white.png" class="button" alt=""/></a></div>
    </div>
</header>

<?php //require 'phpCAS/docs/examples/script_info.php' ?>

<?php include("classSearch.html") ?>

<div class="top">
    <form action="createCustomSchedule.php" method="post">
        <div class="customPickList">

            <?php

            $courses = $db->getCoursesForUser(phpCAS::getUser());

            foreach ($courses as $course){
                print '<div class="courseItem"><input type="text" name="courses[]" value="'.$course["code"].'" readonly /><img src="images/cross.png" /></div>';
            }
            ?>

        </div>
        <input type="submit" value="Skapa schema">
    </form>
</div>

<script>
    $("body").show();
    $(".list").show();
</script>

</body>
</html>




