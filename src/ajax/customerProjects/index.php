<?php
    $root = $_SERVER['DOCUMENT_ROOT'];

    require_once $root."/includes/engine.php";
    require_once $root."/includes/models/index.php";
    require_once $root."/includes/functions/index.php";

    $projects =  new Projects;
    $validate =  new validate;

    if(isset($_GET['MYSQL']['id']) && $validate->integer($_GET['MYSQL']['id'])){
        $data = $projects->getCustomerProjectsJSON($_GET['MYSQL']['id']);
    } else {
        $data = $projects->getCustomerProjectsJSON();
    }

    header('Content-Type: application/json');
    print $data;
?>