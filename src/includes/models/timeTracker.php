<?php

class TimeTracker {
    public function getRecords($id = null){
        try {
            // call engine
            $engine    = EngineAPI::singleton();
            $localvars = localvars::getInstance();
            $db        = db::get($localvars->get('dbConnectionName'));
            $sql       = "SELECT * FROM `timeTracking`";
            $validate  = new validate;
            // test to see if Id is present and valid
            if(!isnull($id) && $validate->integer($id)){
                $sql .= sprintf('WHERE timeID = %s LIMIT 1', $id);
            }
            // if no valid id throw an exception
            if(!$validate->integer($id) && !isnull($id)){
                throw new Exception("An invalid ID was given!");
            }
            // get the results of the query
            $sqlResult = $db->query($sql);
            // if return no results
            // else return the data
            if ($sqlResult->rowCount() < 1) {
               return "There has been no time tracking done up to this point.";
            }
            else {
                $data = array();
                while($row = $sqlResult->fetch()){
                    $data[] = $row;
                }
                return $data;
            }
        } catch (Exception $e) {
            errorHandle::errorMsg($e->getMessage());
        }
    }
    public function setupForm($id = null){
         try {
            // call engine
            $engine    = EngineAPI::singleton();
            $localvars = localvars::getInstance();
            $validate  = new validate;
            // create customer form
            $form = formBuilder::createForm('TimeTracker');
            $form->linkToDatabase( array(
                'table' => 'timeTracking'
            ));
            if(!is_empty($_POST) || session::has('POST')) {
                $processor = formBuilder::createProcessor();
                $processor->processPost();
            }
            // form titles
            $form->insertTitle = "";
            $form->editTitle   = "";
            $form->updateTitle = "";
            // if no valid id throw an exception
            if(!$validate->integer($id) && !isnull($id)){
                throw new Exception(__METHOD__.'() - Not a valid integer, please check the integer and try again.');
            }
            // form information
            $form->addField(array(
                'name'       => 'timeID',
                'type'       => 'hidden',
                'value'      => $id,
                'primary'    => TRUE,
                'fieldClass' => 'id',
                'showIn'     => array(formBuilder::TYPE_INSERT, formBuilder::TYPE_UPDATE),
            ));
            $form->addField(array(
                'name'     => 'projectIdLink',
                'type'     => 'hidden',
                'label'    => 'Project ID:',
                'required' => TRUE,
                'fieldClass' => 'projectID'
            ));
            $form->addField(array(
                'name'     => 'customerIdLink',
                'type'     => 'hidden',
                'label'    => 'Customer ID:',
                'fieldClass' => 'customerID',
                'required' => TRUE
            ));
            $form->addField(array(
                'name'       => 'startTime',
                'type'       => 'hidden',
                'label'      => 'start time:',
                'fieldClass' => 'startTime',
                'required'   => TRUE
            ));
            $form->addField(array(
                'name'       => 'endTime',
                'type'       => 'hidden',
                'label'      => 'end time:',
                'fieldClass' => 'endTime',
                'required'   => TRUE
            ));
            $form->addField(array(
                'name'     => 'totalHours',
                'type'     => 'hidden',
                'label'    => 'total time:',
                'required' => TRUE,
                'fieldClass' => 'totalHours'
            ));
            $form->addField(array(
                'name'            => "completed",
                'label'           => "Has this project been completed?",
                'showInEditStrip' => TRUE,
                'type'            => 'boolean',
                'duplicates'      => TRUE,
                'options'         => array("YES","N0")
            ));
            $form->addField(array(
                'name'            => "descriptionOfWork",
                'label'           => "Enter a description of the project:",
                'type'            => 'textarea',
            ));
            // buttons and submissions
            $form->addField(array(
                'showIn'     => array(formBuilder::TYPE_UPDATE),
                'name'       => 'update',
                'type'       => 'submit',
                'fieldClass' => 'submit',
                'value'      => 'Update'
            ));
            $form->addField(array(
                'showIn'     => array(formBuilder::TYPE_UPDATE),
                'name'       => 'delete',
                'type'       => 'delete',
                'fieldClass' => 'delete hidden',
                'value'      => 'Delete'
            ));
            $form->addField(array(
                'showIn'     => array(formBuilder::TYPE_INSERT),
                'name'       => 'insert',
                'type'       => 'submit',
                'fieldClass' => 'submit',
                'value'      => 'Submit'
            ));
            return '{form name="TimeTracker" display="form"}';
        } catch (Exception $e) {
            errorHandle::errorMsg($e->getMessage());
        }
    }
}

?>