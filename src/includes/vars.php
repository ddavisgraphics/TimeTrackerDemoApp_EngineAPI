<?php
    $localvars = localvars::getInstance();

    $localvars->set('siteRoot','/');
    $localvars->set('dbConnectionName', 'appDB');
    $localvars->set("appTitle","TimeTracker");
    $localvars->set("meta_authors", "David J. Davis");
    $localvars->set('appName', "TimeTracker 5000");

    $root = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    $localvars->set('root', $root);

?>