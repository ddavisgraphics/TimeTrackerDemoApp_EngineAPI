<?php
    $root = $_SERVER['DOCUMENT_ROOT'];

    require_once $root."/includes/engine.php";
    require_once $root."/includes/models/index.php";
    require_once $root."/includes/functions/index.php";

    $customer = new Customers;
    $valdiate = new validate;

    if(isset($_GET['MYSQL']['id']) && $validate->interger($_GET['MYSQL']['id'])){
        $data = $customer->getJSON($_GET['MYSQL']['id']);
    } else {
        $data = $customer->getJSON();
    }

    header('Content-Type: application/json');
    print $data;
?>