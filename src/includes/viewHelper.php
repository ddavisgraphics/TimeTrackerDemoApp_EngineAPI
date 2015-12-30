<?php
    // require our functions
    require_once "includes/functions/index.php";

    // instantiate classes
    $localvars = localvars::getInstance();
    $validate  = new validate;

    // set template vars
    $localvars->set('pageName', ucfirst($this->data['model']));

    // see what we are trying to view
    $model  = $this->data['model'];
    $action = $this->data['action'];
    $item   = $this->data['item'];

    // set output and set local variable for html display
    $output = determineAction($model, $action, $item);
    $localvars->set('pageContent', $output);
?>