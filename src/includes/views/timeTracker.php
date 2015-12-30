<?php
    require_once "includes/engine.php";
    require_once "includes/models/index.php";
    require_once "includes/functions/index.php";

    // instantiate classes
    $localvars = localvars::getInstance();
    $validate  = new validate;
    $tracker   = new TimeTracker;

    // set template vars
    $localvars->set('pageName', ucfirst($this->data['model']));

    // see what we are trying to view
    $model  = $this->data['model'];
    $action = $this->data['action'];
    $item   = $this->data['item'];

    $localvars->set('pageContent', $tracker->setupForm());
?>

<div class="wrapper">
    <div class="container">
        <h2> {local var="pageName"} </h2>
        <p> Track your time. </p>

        <h3> Pick a Customer </h3>
        <select class="customerSelect"> </select>

        <div class="projectContainer hidden">
            <h3> Pick a Project </h3>
            <select class="projectSelect"> </select>
        </div>

        <div class="projectTimer hidden row">
            <div class="col-sm-6">
                <a href="javascript:void(0)" class="startTimer btn btn-default btn-info btn-block"> Start Time </a>
            </div>
            <div class="col-sm-6">
                <a href="javascript:void(0)" class="endTimer btn btn-default btn-danger btn-block"> Stop Time </a>
            </div>
        </div>

        <div class="formInfo hidden">
            {local var="pageContent"}
        </div>
    </div>
</div>


<!-- JavaScript for timeTrackerPage  -->
<script src="{local var="root"}/includes/js/scripts.js"></script>