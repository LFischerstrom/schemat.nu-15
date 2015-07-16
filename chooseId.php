<?php
// header();

?>

<html>
<head>
    <title>Schemat.nu</title>

    <link rel="stylesheet" type="text/css" href="css/style.css" />
    <link rel="stylesheet" type="text/css" href="css/menu.css" />
    <link rel="stylesheet" type="text/css" href="css/colors.css" />
    <link rel="stylesheet" type="text/css" href="css/chooseIdPage.css" />
    <script src='http://code.jquery.com/jquery-1.9.1.min.js'></script>
    <script src="javascript/list.js"></script>
    <script src="javascript/studentGroupList.js"></script>
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
        });


        $(document).on("keyup", function(e){
            li = $('.list li');

            updateSelection(e);
            updateLinks();
            updateListVisibility();


        });

        function setupEnterPress(){
            $(document).keypress(function(e) {
                if(e.which == 13 && liSelected) {
                    window.location.href = $(liSelected).find("a").attr('href');
                }
            });
        }


        function updateSelection(e){

            if(e.which === 40){
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
            }else if(e.which === 38){
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
            // values is added from studentGroupList.js
            var options = {
                valueNames: [ 'id', 'desc' ]
            };
            userList = new List('classSearch', options);
            userList.add(values);
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
<header>
    <div id="row">
       <!-- <div id="left"><h1></h1></div> -->
        <div id="middle"><h1 id="currentWeekNumber">Schemat.nu</h1></div>
        <!-- <div id="right"><a href="#"><img src="images/settings-white.png" class="button" alt=""/></a>  </div>-->
    </div>
</header>




<div id="classSearch">

    <input class="search" placeholder="Ange klass (t.ex. IT1)" autofocus />

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

    <p>Detta behöver endast anges en gång per enhet.</p>

</div>

<div class="top">
    <ul>
        <li>Visar schemat nu, inget tjafs.</li>
        <li>Tokbra mobilanpassning.</li>
        <li>Stödjer både klasser, kurser och personliga scheman.</li>
    </ul>
</div>
<div class="bottom">
    <p>Syftet med Schemat.nu är att förbättra tillgängligheten och förenkla läsbarheten av främst LiU-studenters schema.
        Schemat.nu är ej en produkt från Linköpings universitet. Frågor, synpunkter och felrapporter lämnas till henha972@<span class="displaynone">null</span>student.liu.se</p>.
</div>


</body>
</html>