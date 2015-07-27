<?php

// Requries file with DB constants. Also finds config file from cronjobs directory.
if(file_exists("config.php")) require("config.php");
else if (file_exists("../config.php")) require("../config.php");

mysql_connect(DB_HOST, DB_USER, DB_PASS) or die(mysql_error());
mysql_select_db(DB_DATABASE);
mysql_query("SET character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'");

?>