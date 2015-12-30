<?php
    function determineAction($class, $action, $item){
        $localvars = localvars::getInstance();
        $validate  = new validate;
        if(class_exists($class)){
            $myClass   = new $class;
            $pageData  = "";
            // record Id Set to null
            $id = null;
            // create an array of valid actions
            $validActions = array('create', 'add', 'read', 'view', 'update', 'edit', 'delete', 'confirmDelete');
            // this is an $id only
            // not null and not an empty string
            if(!isnull($item) && !is_empty($item) && $validate->integer($item)){
                $id = $item;
            }
            // get a specific record or determine what to do
            if(!isnull($action) || in_array($action, $validActions)){
                if($validate->integer($action)){
                    $pageData = $myClass->getRecords($action);
                }
                else {
                    switch ($action) {
                        case 'create':
                        case 'add':
                        case 'update':
                        case 'edit':
                            if(isnull($id)){
                                $pageData = $myClass->setupForm();
                            }
                            else{
                                $pageData = $myClass->setupForm($id);
                            }
                        break;
                        case 'delete':
                            if(!isnull($id)){
                                $pageData = $myClass->deleteRecord($id);
                            } else {
                                $pageData = $myClass->deleteRecord();
                            }
                        break;
                        case 'confirmDelete':
                            if(!isnull($id)){
                                $pageData = "Are you sure you want to delete this record?";
                                $pageData .= $myClass->renderDeleteData($id);
                            } else {
                                header('Location:/404Error?invalidId=true');
                            }
                        break;
                        default:
                        case 'read':
                        case 'view':
                            // if isnull $id get all records
                            if(isnull($id)){
                                $pageData = $myClass->renderDataTable();
                            }
                            else{
                                $pageData = $myClass->renderSingleRecord($id);
                            }
                        break;
                    }
                }
            } else {
                 $pageData = $myClass->renderDataTable();
            }
            return $pageData;
        }
        else {
            header('Location:/404Error?ClassError=true');
        }
    }

    function getCompanyName($id){
        $localvars   = localvars::getInstance();
        $validate    = new validate;
        $customers   = new Customers;
        $returnValue = "";
        if(isnull($id) && !$validate->integer($id)){
            throw new Exception('not valid integer');
            return false;
        }
        else {
            $data        = $customers->getRecords($id);
            $returnValue = $data[0]['companyName'];
            return $returnValue;
        }
    }
?>