<?php
// header();

?>

<html>
<head>
    <title>Schemat.nu</title>
    <script src="javascript/list.js"></script>
    <script src="javascript/studentGroupList.js"></script>

    <script>
        function updateLinks(){
            // Adding cookie adding link
            var links = document.getElementsByClassName('link');
            var ids = document.getElementsByClassName('id');
            for(var i = 0; i<links.length; i++){
                links[i].setAttribute('href', "addCookie.php?id=" + ids[i].innerHTML );
            }
        }
    </script>
</head>

<body>
<?php
// if (id exists) -> show schedule
// else CreateSchedule?

?>


<div id="users">

    <input class="search" placeholder="Search" onchange="updateLinks()" autofocus />


    <ul class="list">
        <li>
            <a href="#" class="link">
                <div>
                    <span class="id"></span>
                    -
                    <span class="desc"></span>
                </div>
            </a>
        </li>
    </ul>

</div>


<script>

    var options = {
        valueNames: [ 'id', 'desc' ]
    };

    var userList = new List('users', options);

    // values is added from studentGroupList.js
    userList.add(values);

    userList.sort('id', { order: "asc" }); // Sorts the list in abc-order based on names

    updateLinks();


</script>


</body>
</html>