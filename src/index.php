<?php
    require_once "includes/engine.php";
    require_once "includes/controller/index.php";
    require_once "includes/models/index.php";

    $router = router::getInstance();
    $router->defineRoute("/", 'displayRoute');
    $router->defineRoute("/{model}", 'displayRoute');
    $router->defineRoute("/{model}/{action}", 'displayRoute');
    $router->defineRoute("/{model}/{action}/{item}", 'displayRoute');
    $router->route();

    templates::display('header');
?>

{local var="content"}

<?php
    templates::display('footer');
?>