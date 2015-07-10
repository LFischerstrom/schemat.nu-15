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
// $downloadedFilePath = $downloader->downloadSchedule($id);

// Printing schedule
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
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script>


        // Event more info width and position
        $(document).ready(function(){

            var initEventBgColor= $(".event:first").css("background-color");
            var initCellBgColor= $(".cell:first").css("background-color");

            function dimScreen($bool){

                if ($bool){
                    $(".event").css("cursor", "initial");
                    $(".event").css("background-color", "rgba(0 ,0,0,0.3)");
                    $(".cell").css("background-color", "rgba(0 ,0,0,0.3)");
                }
                else {
                    $(".event").css("cursor", "pointer");
                    $(".event").css("background-color", initEventBgColor);
                    $(".cell").css("background-color", initCellBgColor);
                }
            }

            var clickedMoreInfo = null;
            var clickedMenu = null;
            var menu = $("#popupMenu");


            $(document).click(function() {
                if (clickedMoreInfo != null && clickedMenu == null){
                    clickedMoreInfo.toggle();
                    dimScreen(true);
                    // After the moreInfo box been closed
                    if ( clickedMoreInfo.css("display") == "none"){
                        dimScreen(false);
                        clickedMoreInfo = null;
                    }
                }
            });



            // Make moreInfo box
            $(".event").click(function(){
                var activeSlide = $('.slide.active');
                var activeSlideWidth = activeSlide.width();
                var activeSlidePos = activeSlide.position().left;
                if (activeSlideWidth > 300) var moreInfoWidth = 300;
                else var moreInfoWidth = activeSlideWidth*0.8;
                var left = activeSlidePos + (activeSlideWidth - moreInfoWidth)/2;
                var moreInfo = $(this).find(".moreInfo");
                moreInfo.width(moreInfoWidth);
                moreInfo.css("left",left + "px");

                // Opens moreInfo box if no other already is open.
                if (clickedMoreInfo == null) clickedMoreInfo = moreInfo;

            });



            // Menu
            $(document).click(function(){
                if (clickedMenu != null){
                    menu.toggle();
                    if (clickedMoreInfo != null){
                        clickedMoreInfo.hide();
                        clickedMoreInfo = null;
                    }
                    dimScreen(true);
                    if ( menu.css("display") == "none"){
                        dimScreen(false);
                        clickedMenu = null;
                    }
                }

            });

            $(".button").click(function(){
                if (clickedMenu == null) clickedMenu = menu;
            });


            // sets the week number header
            var hash = window.location.hash;
            var weekNumber = hash.match(/\d+/);
            if (weekNumber != null) $('#currentWeekNumber').text('Vecka ' + weekNumber);

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


            fixTableHeight();
            fixResponsiveDays();
            removePartlyHiddenTextLinesInRest();


            $(window).on('resize', function(){
                fixTableHeight();
                fixResponsiveDays();
                removePartlyHiddenTextLinesInRest();
            });


            // Fixing height bug
            function fixTableHeight(){
                var height = $( "#fullpage" ).height();
                var menuHeight = 50;
                $(".table").height(height - menuHeight);
                $(".section").height(height);
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
                slides.each(function() {
                    $(this).css('width', 100 * daysToScroll/numberOfDays + '%');
                });
            }

            function removePartlyHiddenTextLinesInRest(){
                var events = $(".event");
                var bordersAndPadding = 8;

                events.each(function(){
                    var restWidth = $(this).find(".rest").width();
                    var restHeight = $(this).height() - $(this).find(".time").height() - $(this).find(".eventRow").height() - bordersAndPadding;

                    // removes single line if it is not fully visible
                    $(this).find(".wrapper")
                        .css('-webkit-column-width',restWidth + "px")
                        .css('column-width',restWidth + "px")
                        .css('height',restHeight+ 'px');
                    if (restHeight < 16) $(this).find(".rest").css("display","none");
                    else $(this).find(".rest").css("display","inline");

                    // removes end time if box is too small
                    if ($(this).width() < 80) $(this).find(".end").css("display","none");
                    else $(this).find(".end").css("display","inline");

                    // hide course box if overflowing and moving course text to rest div.
                    $(this).find(".course").css("display","inline");
                    $(this).find(".courseText").remove();
                    var eventRowWidth = $(this).find(".eventRow").width();
                    var eventWidth = $(this).width();
                    if (eventRowWidth > eventWidth){
                        var courseText = $(this).find(".course").text();
                        $(this).find(".course").css("display","none");
                        $(this).find(".wrapper").prepend("<span class='courseText'>" + courseText + "<br /></span>");
                    }
                })
            }
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

<p></p>

</body>
</html>