<?php

// Load the settings from the central config file
require_once 'phpCAS/docs/examples/config.example.php';

// Load the CAS lib
//require_once $phpcas_path . '/CAS.php';
require_once 'phpCAS/CAS.php';

// Enable debugging
phpCAS::setDebug();

// Initialize phpCAS
phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);

// For production use set the CA certificate that is the issuer of the cert
// on the CAS server and uncomment the line below
// phpCAS::setCasServerCACert($cas_server_ca_cert_path);

// For quick testing you can disable SSL validation of the CAS server.
// THIS SETTING IS NOT RECOMMENDED FOR PRODUCTION.
// VALIDATING THE CAS SERVER IS CRUCIAL TO THE SECURITY OF THE CAS PROTOCOL!
phpCAS::setNoCasServerValidation();

// force CAS authentication
phpCAS::forceAuthentication();

// at this step, the user has been authenticated by the CAS server
// and the user's login name can be read with phpCAS::getUser().

// logout if desired
if (isset($_REQUEST['logout'])) {
    phpCAS::logout();
}

// for this test, simply print that the authentication was successfull
?>
<html>
<head>
    <title>Schemat.nu - Skapa schema</title>
</head>
<body>
<h1>Skapa schema</h1>
<?php //require 'phpCAS/docs/examples/script_info.php' ?>
<p>Skapar schema för: <b><?php echo phpCAS::getUser(); ?></b>.</p>
<p>Ditt schema kommer nås direkt på schemat.nu</p>
<p><a href="?logout=">Logga ut</a></p>
</body>
</html>




