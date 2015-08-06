<?php
require_once("Schedule.php");
$id = $_COOKIE['SchematId'];
$schedule = new Schedule($id);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Schemat.nu</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="css/style.css" />
    <link rel="stylesheet" type="text/css" href="css/moreInfo.css" />
    <link rel="stylesheet" type="text/css" href="css/menu.css" />
    <link rel="stylesheet" type="text/css" href="css/event.css" />
    <link rel="stylesheet" type="text/css" href="css/colors.css" />
    <link rel="stylesheet" type="text/css" href="fullPage.js/jquery.fullPage.css" />
    <link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
    <script src='http://code.jquery.com/jquery-1.9.1.min.js'></script>
    <script src="fullPage.js/vendors/jquery.easings.min.js"></script>
    <script src="fullPage.js/vendors/jquery.slimscroll.min.js"></script>
    <script src="fullPage.js/jquery.fullPage.js"></script>
    <script src='javascript/jquery.overlaps.js'></script>
    <script src='javascript/myJs.js'></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script>

        $(document).ready(function(){

            setWeeknumberHeader();
            fixOverlappingEvents();
            fixTableHeight();
            fixResponsiveDays();
            removePartlyHiddenTextLinesInRest();
            setupMoreInfoBox();
            setupMenu();
            setStartSlide();
            setupDimScreen();

            $(window).on('resize', function(){
                fixTableHeight();
                fixResponsiveDays();
                removePartlyHiddenTextLinesInRest();
            });

            // updates the week number header when changed week
            $(window).on('hashchange', function(e){
                setWeeknumberHeader();
            });

        });

    </script>

</head>
<body>

<div id="popupMenu">
    <ul id="menu">
        <?php print $schedule->getMenuListItems();?>
        <?php
        require_once("DatabaseConnection.php");
        $db = new DatabaseConnection();
        if($db->isUser($id)){
            print ' <li class="option"><a href="customSchedule.php?edit=true">Ändra schema</a></li><br />';
        }
        ?>
        <li class="option"><a href="removeCookie.php">Byt Schema</a></li><br />
        <li class="option"><a href="report.php">Rapportera fel</a></li>
    </ul>
</div>

<header>
    <div id="row">
        <div id="left"><h1><?php print $id ?></h1></div>
        <div id="middle"><h1 id="currentWeekNumber">v <?php print $schedule->getStartWeek();?></h1></div>
        <div id="right"><a href="#"><img src="images/settings-white.png" class="button" alt=""/></a></div>
    </div>
</header>

<div id="fullpage">
    <?php
    $schedule->printSchedule();
    ?>
</div>


<script>
    $('#fullpage').fullpage({
        <?php print $schedule->getSectionAnchors(); ?>
        menu: '#menu'
    });

    $("#fullpage").show();

</script>

</body>
</html>



