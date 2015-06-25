<?php
require_once('ScheduleDownloader.php');
require_once('Schedule.php');

// if no cookie -> choose id;
if(!isset($_COOKIE['SchematId'])) {

    if($_SERVER["HTTP_HOST"] == "localhost") header('Location: '."/Schemat.nu-15/chooseId.php");
    else header('Location: '."/chooseId.php");
}
// else continue display page with schedule:


$id = $_COOKIE['SchematId'];

// Downloading current schedule
$downloader = new ScheduleDownloader();
//$downloadedFilePath = $downloader->downloadSchedule($id);

// Printing schedule
$schedule = new Schedule($id);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Schemat.nu</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="css/style.css" />
    <link rel="stylesheet" type="text/css" href="css/menu.css" />
    <link rel="stylesheet" type="text/css" href="css/event.css" />
    <link rel="stylesheet" type="text/css" href="fullPage.js/jquery.fullPage.css" />
    <script src='http://code.jquery.com/jquery-1.9.1.min.js'></script>
    <script src="fullPage.js/vendors/jquery.easings.min.js"></script>
    <script src="fullPage.js/vendors/jquery.slimscroll.min.js"></script>
    <script src="fullPage.js/jquery.fullPage.js"></script>
    <script src='javascript/jquery.overlaps.js'></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script>

        $(document).ready(function(){
            $("#popupMenu").toggle();
            $(".button").click(function(){$("#popupMenu").toggle();});
            $("li").click(function(){$("#popupMenu").toggle();});
        });

        // sets the week number header
        $(document).ready(function(){
            var hash = window.location.hash;
            var weekNumber = hash.match(/\d+/);
            if (weekNumber != null) $('#currentWeekNumber').text('Vecka ' + weekNumber);
        });

        // updates the week number header when changed week
        $(window).on('hashchange', function(e){
            var hash = window.location.hash;
            var weekNumber = hash.match(/\d+/);
            if (weekNumber != null) $('#currentWeekNumber').text('Vecka ' + weekNumber);
        });
    </script>



</head>
<body>




<div id="popupMenu">
    <ul id="menu">
        <?php print $schedule->getMenuListItems();?>
        <li class="option"><a href="removeCookie.php">Byt Schema</a></li><br />
        <li class="option"><a href="report.php">Rapportera fel</a></li>
    </ul>
</div>

<header>
    <div id="row">
        <div id="left"><h1><?php print $id ?></h1></div>
        <div id="middle"><h1 id="currentWeekNumber">Vecka <?php print $schedule->getStartWeek();?></h1></div>
        <div id="right"><a href="#"><img src="images/settings-white.png" class="button" alt=""/></a></div>
    </div>
</header>

<div id="fullpage">

    <?php $schedule->printSchedule(); ?>

</div>


<script>
    $('#fullpage').fullpage({
        <?php print $schedule->getSectionAnchors(); ?>
        menu: '#menu'
    });
</script>


<script>

    // Fixing overlapping events:
    var allOverlappingDivs = $('.event').overlaps();
    $.each(allOverlappingDivs, function(index, currentDiv) {
        if (!$(currentDiv).hasClass("overlapFixed")){
            $(currentDiv).addClass("currentDiv");
            var allDivsThatOverlapsCurrentDiv = $(".event").overlaps(".currentDiv");
            var allDivsThatOverlapsCurrentDivIncludingCurrentDiv = allDivsThatOverlapsCurrentDiv.add(currentDiv);
            var numberOfDivs = allDivsThatOverlapsCurrentDivIncludingCurrentDiv.size();
            $.each(allDivsThatOverlapsCurrentDivIncludingCurrentDiv, function(index, currentOverlappingDiv) {
                var width = 100 / numberOfDivs;
                $(currentOverlappingDiv).css("width", width + "%");
                $(currentOverlappingDiv).css("left", ((index+1)/numberOfDivs - 1/numberOfDivs)*100 + "%");
                $(currentOverlappingDiv).addClass("overlapFixed");
            });
            $(currentDiv).removeClass("currentDiv");
        }
    });

</script>

<p></p>

<script>
    // Fixing height bug
    fixTableHeight();
    fixResponsiveDays();

    $(window).on('resize', function(){
        fixTableHeight();
        fixResponsiveDays();
    });

    function fixTableHeight(){
        var height = $( "#fullpage" ).height();
        var menuHeight = 50;
        $(".table").height(height - menuHeight);
    }


    function fixResponsiveDays(){
        var numberOfDays = $('.cell', $($(".section").first())).length;
        var daysToShow = numberOfDays;
        var daysToScroll = numberOfDays;
        var width = $( "#fullpage" ).width();

        if (width > 800){
            daysToShow = numberOfDays;
        }
        else {
            if (numberOfDays == 5) daysToShow = 3;
            else if (numberOfDays == 6) daysToShow = 3;
            else if (numberOfDays == 7) daysToShow = 4;
        }

        daysToScroll = numberOfDays - daysToShow;

        var slides = $(".slide");
        $(".fp-slidesContainer").width(100 * numberOfDays/daysToShow + "%");
        slides.each(function(index) {
            $(this).css('width', 100 * daysToScroll/numberOfDays + '%');
        });
    }

</script>


</body>
</html>