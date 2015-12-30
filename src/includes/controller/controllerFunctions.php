<?php
    // displays routes
    function displayRoute($url, $vars){
        $localvars  = localvars::getInstance();

        $model  = (isset($vars['model']) ? $vars['model'] : null);
        $action = (isset($vars['action']) ? $vars['action'] : null);
        $item   = (isset($vars['item']) ? $vars['item'] : null);

        // expected pages
        $expectedModels = array(
            'customers',
            'projects',
            'timeTracker'
        );

        if(in_array($model, $expectedModels)){
            $pageVariables = array(
                'model'  => ucfirst($model),
                'action' => $action,
                'item'   => $item
            );

            $view  = new View($model, $pageVariables);
        } else if(isnull($model) || $model == "/" || $model == "home"){
            $pageVariables = array(
                'model' => ucfirst($model)
            );
            $view = new View('Home', $pageVariables);
        }
        else {
            $pageVariables = array(
                'model' => ucfirst($model)
            );
            // send to 404 error
            $view  = new View('Error', $pageVariables);
        }

        $html  = $view->render();
        $localvars->set('content', $html);
    }
?>