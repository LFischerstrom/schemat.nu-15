<?php
require_once('Schedule.php');
include_once('Stats.php');

// if no cookie -> choose id;
if(!isset($_COOKIE['SchematId'])) {
    if($_SERVER["HTTP_HOST"] == "localhost") header('Location: '."/Schemat.nu-15/chooseId.php");
    else header('Location: '."/chooseId.php");
}
// else continue display page with schedule:

$id = $_COOKIE['SchematId'];

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

        $(document).ready(function(){
            var initEventBgColor= $(".event:first").css("background-color");
            var initCellBgColor= $(".cell:first").css("background-color");
            var clickedMoreInfo = null;
            var clickedMenu = null;

            setWeeknumberHeader();
            fixOverlappingEvents();
            fixTableHeight();
            fixResponsiveDays();
            removePartlyHiddenTextLinesInRest();
            setupMoreInfoBox();
            setupMenu();

            $(window).on('resize', function(){
                fixTableHeight();
                fixResponsiveDays();
                removePartlyHiddenTextLinesInRest();
            });

            // updates the week number header when changed week
            $(window).on('hashchange', function(e){
                var hash = window.location.hash;
                var weekNumber = hash.match(/\d+/);
                if (weekNumber != null) $('#currentWeekNumber').text('v ' + weekNumber);
            });

            // dimming screen on click
            $(document).click(function() {
                if (clickedMoreInfo != null && clickedMenu == null){
                    clickedMoreInfo.toggle();
                    dimScreen(true);
                    // After the moreInfo box been closed
                    if ( clickedMoreInfo.css("display") == "none"){
                        clickedMoreInfo.removeAttr("style");
                        dimScreen(false);
                        clickedMoreInfo = null;
                    }
                }
            });


            function setupMenu(){
                var menu = $("#popupMenu");

                $(".button").click(function(){
                    if (clickedMenu == null) clickedMenu = menu;
                });

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
            }

            // sets the week number header
            function setWeeknumberHeader(){
                var hash = window.location.hash;
                var weekNumber = hash.match(/\d+/);
                if (weekNumber != null) $('#currentWeekNumber').text('v ' + weekNumber);
            }

            // Fixing overlapping events:
            function fixOverlappingEvents(){

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
            }

            // Fixing height bug
            function fixTableHeight(){
                var height = $( "#fullpage" ).height();
                var menuHeight = 50;
                $(".table").height(height - menuHeight);
                $(".section").height(height);
            }

            // Make moreInfo box
            function setupMoreInfoBox() {


                $(".event").click(function () {

                    // Only if a moreInfo box is not already open.
                    if (clickedMoreInfo == null) {

                        // calc width
                        var moreInfo = $(this).find(".moreInfo");
                        var moreInfoWeekCells = moreInfo.closest( ".section").find(".cell");
                        var weekLastCell = moreInfoWeekCells.last();
                        var moreInfoCell = moreInfo.closest( ".cell");
                        var daysInCurrentWeek = moreInfoWeekCells.length;
                        var daysScrolledPerSlide = Math.floor(daysInCurrentWeek/2);
                        var scrolledWidthPerSlide = daysScrolledPerSlide * moreInfoCell.width();
                        var windowWidth = $(window).width();
                        var firstSlideWidth = $(".slide:first").width();
                        var fullPageWidth = $("#fullpage").width();

                        // width
                        if (windowWidth > 300) var moreInfoWidth = 300;
                        else var moreInfoWidth = windowWidth * 0.8;
                        moreInfo.width(moreInfoWidth);

                        moreInfo.toggle();

                        // top
                        var top = moreInfo.position().top;
                        if (top <= 0) top = -top + 75;

                        // left
                        var moreInfoLeft = moreInfo.position().left;
                        var slide = window.location.href.substring(window.location.href.lastIndexOf('/') + 1);
                        if (slide.length != 1) slide = 0;
                        var left = 0;

                        // if not fullpage
                        if (firstSlideWidth != 0 && firstSlideWidth != fullPageWidth){
                            // Chrome slide 1+ (index 0). Cond never true in IE
                            if (slide != 0 && moreInfoLeft <= scrolledWidthPerSlide) left = scrolledWidthPerSlide*slide;
                        }

                        left += (windowWidth - moreInfoWidth) / 2;

                        moreInfo.toggle();

                        // apply
                        moreInfo.css("left", left + "px");
                        moreInfo.css("top",  top + "px");
                        clickedMoreInfo = moreInfo;
                    }
                });
            }



            // TODO: Do this for every week, not just trust the first week.
            // Error might show when first week has an unusual number of days.
            function fixResponsiveDays(){
                var numberOfDays = $('.cell', $($(".section").first())).length;
                var daysToShow = numberOfDays;
                var daysToScroll = numberOfDays;
                var width = $( "#fullpage" ).width();

                if (width > 800){
                    daysToShow = numberOfDays;
                }
                else {
                    daysToShow = Math.ceil(numberOfDays/2);
                    /*
                     if (numberOfDays == 5) daysToShow = 3;
                     else if (numberOfDays == 6) daysToShow = 3;
                     else if (numberOfDays == 7) daysToShow = 4;
                     */
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

                    // removes location box if no location
                    var locationBox = $(this).find(".location");
                    if (locationBox.text() == "") locationBox.hide();

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
                });
            }

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
        <div id="middle"><h1 id="currentWeekNumber">v <?php print $schedule->getStartWeek();?></h1></div>
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

    $("#fullpage").show();
</script>

</body>
</html>


<?php // STATS
$st = new Stats();
$st->captureStats();
?>
