<html>
<head>
    <title>Schemat - LiU - Linköpings universitet</title>
    <meta name="description" content="Schemat.nu visar LiU's schema - schemat vid Linköpings universitet. Bättre tillgänglighet och förenklad läsbarhet.">
    <meta charset="UTF-8">
    <script src='http://code.jquery.com/jquery-1.9.1.min.js'></script>

    <link rel="stylesheet" type="text/css" href="css/style.css" />
    <link rel="stylesheet" type="text/css" href="css/colors.css" />
    <link rel="stylesheet" type="text/css" href="css/chooseIdPage.css" />
    <link rel="stylesheet" type="text/css" href="css/menu.css" />
    <link href='http://fonts.googleapis.com/css?family=Roboto:500' rel='stylesheet' type='text/css'>
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

        $(document).ready(function(){
            prepareList();
            updateListVisibility();
            setupEnterPress();
            setupMenu();
        });


        $(document).on("keyup", function(e){
            li = $('.list li');
            updateSelection(e);
            updateLinks();
            updateListVisibility();
            updateNoResultStatus();
        });



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

<?php include("classSearch.html") ?>

<div class="top">

    <form action="customSchedule.php" method="get">
        <button class="liuLogin">
            <div><img src="images/liu.png"/></div><div>Personligt schema</div></button>

        <br style="clear: both"/>
    </form>


    <ul class="checklist">
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