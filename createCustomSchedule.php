<?php
require_once 'phpCAS/CAS.php';
require_once 'phpCAS/docs/examples/config.example.php';
phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);
phpCAS::setNoCasServerValidation();

// force CAS authentication
phpCAS::forceAuthentication();

print_r($_POST['courses']);

if (!isset($_POST['courses'])) header("Location: /");
$schedules = $_POST['courses'];
$user = phpCAS::getUser();



// create or update custom schedule
require_once("DatabaseConnection.php");
$db = new DatabaseConnection();

// add user if not exist
$db->addUser($user);

// remove all entries for user
$db->removeAllSchedulesForUser($user);

// add the new entries for the user
foreach ($schedules as $schedule) {
   $db->addScheduleForUser($schedule, $user);
}

// Sets cookie
setcookie("SchematId",$user);

// Go to index -> schedule will be displayed
header("Location: /");