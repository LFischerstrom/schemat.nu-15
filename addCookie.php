<?php

// get header
if (isset($_GET["id"])){
    $id = htmlspecialchars($_GET["id"]);

    // addCookie
    setcookie(
        "SchematId",
        $id,
        time() + (365 * 24 * 60 * 60)  // 1 year
    );

}

// redirect to index.php
if($_SERVER["HTTP_HOST"] == "localhost") header('Location: '."/Schemat.nu-15/");
else header('Location: '."/");