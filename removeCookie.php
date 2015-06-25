<?php

setcookie("SchematId", "", time()-3600);

// redirect to index.php
if($_SERVER["HTTP_HOST"] == "localhost") header('Location: '."/Schemat.nu-15/");
else header('Location: '."/");

?>