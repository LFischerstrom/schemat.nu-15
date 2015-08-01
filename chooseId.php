<html>
<head>
    <title>Schemat - LiU - Linköpings universitet</title>
    <meta name="description" content="Schemat.nu visar LiU's schema - schemat vid Linköpings universitet. Bättre tillgänglighet och förenklad läsbarhet.">
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="css/style.css" />
    <link rel="stylesheet" type="text/css" href="css/colors.css" />
    <link rel="stylesheet" type="text/css" href="css/chooseIdPage.css" />
    <link rel="stylesheet" type="text/css" href="css/menu.css" />
    <script src='http://code.jquery.com/jquery-1.9.1.min.js'></script>
    <script src="javascript/list.js"></script>
    <script src="javascript/groups.js"></script>
    <script src="javascript/courses.js"></script>
    <script src="//cdn.jsdelivr.net/jquery.scrollto/2.1.0/jquery.scrollTo.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">


    <script>

        var liSelected;
        var li;
        var lastVisible
        var userList;
        var clickedMenu = null;

        $(document).ready(function(){
            var clickedMoreInfo = null;

            prepareList();
            updateListVisibility();
            setupEnterPress();
            setupMenu();

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
                        if ( menu.css("display") == "none"){
                            clickedMenu = null;
                        }
                    }
                });
            }
        });


        $(document).on("keyup", function(e){
            li = $('.list li');
            updateSelection(e);
            updateLinks();
            updateListVisibility();
            updateNoResultStatus();
        });

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


    </script>
</head>
<body>

<div id="popupMenu">
    <ul id="menu">
        <li class="option"><a href="report.php">Rapportera fel</a></li>
    </ul>
</div>

<header>
    <div id="row">
        <div id="left"><h1></h1></div>
        <div id="middle"><h1 id="currentWeekNumber">Schemat.nu</h1></div>
        <div id="right"><a href="#"><img src="images/settings-white.png" class="button" alt=""/></a>  </div>
    </div>
</header>




<div id="classSearch">
    <input class="search" placeholder="Sök klass eller kurskod" autofocus />
    <ul class="list">
        <li>
            <a href="#" class="link">
                <div class="listItem">
                    <div class="id"></div>
                    <div class="desc"></div>
                </div>
            </a>
        </li>
    </ul>
</div>

<div class="top">
    <ul>
        <li>Visar schemat nu, inget tjafs.</li>
        <li>Kommer ihåg ditt schemaval.</li>
        <li>Stödjer både klasser och kurser.</li>
        <li>Stabil mobilanpassning.</li>
    </ul>
</div>

<script>
    $("body").show();
    $(".list").show();

</script>
</body>
</html>