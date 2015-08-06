// dimming screen on click
$(document).ready(function() {

    initEventBgColor = $(".event:first").css("background-color");
    initCellBgColor = $(".cell:first").css("background-color");
    clickedMoreInfo = null;
    clickedMenu = null;

});


function setupDimScreen(){
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
}

function setStartSlide(){
    //0=Sun, 1=Mon, ..., 6=Sat
    var today = new Date().getDay();
    var numberOfDays = $('.cell', $($(".section").first())).length;
    var daysToShow = Math.ceil(numberOfDays/2);

    // Auto slide to second slide if:
    // 1. Todays day is not visible on first slide.
    // 2. Url typed is root (schemat.nu) or /#week[currentweek]-[currentyear]
    // 3. The first shown week is the actuall current week.
    if (today + 1 > daysToShow){
        var firstWeek = $(".section:first").attr("data-anchor");
        var url = $(location).attr('href');
        var index = url.lastIndexOf("/") + 2;
        url = url.substr(index);
        if (url == "" || firstWeek == url ){
            var currentWeek = new Date().getWeek();
            var weekNr = firstWeek.substring(4,6);
            if (weekNr == currentWeek) $.fn.fullpage.moveSlideLeft();
        }
    }
}



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


function fixOverlappingEvents2(){
    var allOverlappingDivs = $('.event').overlaps();
    var divs = [];

    // Creating array with overlaping divs and number of overlaps for each div
    // divs = {[div.object , overlaps]}
    $.each(allOverlappingDivs,function(index, div){
        divs[index] = [div,$(this).overlaps('.event')];
    });
    divs.sort(compare);


    var allCurOverlaps = null
    var commonOverlaps = null

    // removes all not unique divs
    $.each(divs, function(i, div) {
        if (div != undefined) allCurOverlaps = div[1];
        $.each(allCurOverlaps, function() {
            commonOverlaps = getCommonElements(allCurOverlaps, allOverlappingDivs);
            if (commonOverlaps.length > 0){
                remove(this,divs);
            }
        });
    });


    // Set left and width

    // For each overlapping cluster
    $.each(divs, function(index, cluster) {

        // For each div in the cluster // cluster: div => [div,div..]
        var masterDiv = cluster[0];
        var i = 1;
        var boxes = null;
        $.each(cluster[1], function(index, oDiv) {
            i++;
            if (overlapsAny(oDiv,cluster[1])){
                remove(oDiv,cluster[1]);
            }
            else if (boxes == null) boxes = i;
        });

        console.log(boxes);


    });



    function overlapsAny(div, allDivs){
        $.each(allDivs, function() {
            if ($(div).overlaps(this)) return true;
        });
        return false;
    }



    function remove(element, array){
        $.each(array, function(i){
            if(array[i][0] == element) {
                array.splice(i,1);
                return false;
            }
        });
    }

    function getCommonElements(array1, array2){
        var common = $.grep(array1, function(element) {
            return $.inArray(element, array2 ) !== -1;
        });
        return common;
    }

    function compare(a,b) {
        if (a[1].length < b[1].length)
            return 1;
        if (a[1].length > b[1].length)
            return -1;
        return 0;
    }


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

    var i = 0;
    // Fixing failing overlap fixes - max 200 to prevent infinit loop
    while ($('.event').overlaps().length > 0){
        i++; if (i>200) break;
        var divs = $('.event').overlaps();
        var div = divs[0];
        var cellWidth =  parseInt($(div).closest(".cell").css("width"), 10);
        var width  = getSmallestWidth($('.event').overlaps());
        var percentageWidth = width / cellWidth *100;
        var percentageLeft =  parseInt($(div).css("left") , 10)/ width *100;
        console.log(cellWidth);
        $(div).css("width", percentageWidth + "%");
        $(div).css("left", percentageLeft+percentageWidth + "%");
    }

}

function getSmallestWidth(array){
    var smallestWidth = null;
    $.each(array, function(){
        if (smallestWidth == null || $(this).width() < smallestWidth) smallestWidth = $(this).width();
    });
    return smallestWidth;
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

function fixResponsiveDays(){
    var allWeeks = $(".section");
    var width = $("#fullpage").width();

    allWeeks.each(function() {
        var slides = $(".slide", $(this));
        var numberOfDays = $('.cell', $(this)).length;
        var daysToShow = numberOfDays;
        if (width < 800) daysToShow = Math.ceil(numberOfDays / 2);
        var daysToScroll = numberOfDays - daysToShow;

        $(".fp-slidesContainer", $(this)).width(100 * numberOfDays/daysToShow + "%");
        slides.each(function() {
            $(this).css('width', 100 * daysToScroll/numberOfDays + '%');
        });
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


Date.prototype.getWeek = function() {
    var onejan = new Date(this.getFullYear(),0,1);
    return Math.ceil((((this - onejan) / 86400000) + onejan.getDay()+1)/7);
}

// Adds text if no results is found.
function updateNoResultStatus() {
    var inputLength = $("input:first").val().length;
    if (inputLength != 0 && li.length == 0){
        $('.list').append(
            $('<p>').append("Inga sökresultat."))
    }
};

function setupEnterPress(){
    $(document).keypress(function(e) {
        if(e.which == 13 && liSelected) {
            window.location.href = $(liSelected).find("a").attr('href');
        }
    });
}

function updateSelection(e){
    // down arrow
    if(e.which === 40 && li.length > 0){
        if(liSelected){
            liSelected.removeClass('selected');
            var next = liSelected.next();
            if(next.length > 0){
                liSelected = next.addClass('selected');
            }else{
                liSelected = li.eq(0).addClass('selected');
            }
        }else{
            liSelected = li.eq(0).addClass('selected');
        }
    }
    // up arrow
    else if(e.which === 38 && li.length > 0){
        if(liSelected){
            liSelected.removeClass('selected');
            var next = liSelected.prev();
            if(next.length > 0){
                liSelected = next.addClass('selected');
            }else{
                liSelected = li.last().addClass('selected');
            }
        }else{
            liSelected = li.last().addClass('selected');
        }
    }

    if (liSelected) $(".list").scrollTo(liSelected);

    $('.list li').hover(function() {
        if (liSelected) liSelected.removeClass('selected');
        liSelected = $(this).addClass('selected');
    }, function() {
        $(this).removeClass('selected');
        liSelected = false;
    });
}


function prepareList() {
    var options = {
        // valueNames: [ 'id', 'desc' ]
        valueNames: ['id']
    };
    userList = new List('classSearch', options);

    // Adding values from javascript/courses & groups
    userList.add(groups);
    userList.add(courses);

    userList.sort('id', { order: "asc" }); // Sorts the list in abc-order based on names
}


function updateLinks(){
    $("li a").each(function(){
        var course = $(this).find(".id").text();
        this.setAttribute('href', "addCookie.php?id=" + course);
    });
}


function updateListVisibility(){
    var inputLength = $("input:first").val().length;
    var list = $(".list");
    var checkBoxDiv = $(".top")
    if (inputLength == 0){
        list.hide();
        checkBoxDiv.show();
        $(".list li").removeClass('selected');
    }
    else{
        list.show();
        checkBoxDiv.hide();
    }
}

