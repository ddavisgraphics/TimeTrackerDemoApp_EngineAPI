<?php
class Projects {
    public function getRecords($id = null){
        try {
            // call engine
            $engine    = EngineAPI::singleton();
            $localvars = localvars::getInstance();
            $db        = db::get($localvars->get('dbConnectionName'));
            $sql       = "SELECT * FROM `projects`";
            $validate  = new validate;

            // test to see if Id is present and valid
            if(!isnull($id) && $validate->integer($id)){
                $sql .= sprintf('WHERE projectID = %s LIMIT 1', $id);
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
               return "There are no projects in the database.";
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
            $form = formBuilder::createForm('Projects');
            $form->linkToDatabase( array(
                'table' => 'projects'
            ));

            if(!is_empty($_POST) || session::has('POST')) {
                $processor = formBuilder::createProcessor();
                $processor->processPost();
            }

            // form titles
            $form->insertTitle = "Add Project";
            $form->editTitle   = "Edit Project";
            $form->updateTitle = "Edit Project";

            // if no valid id throw an exception
            if(!$validate->integer($id) && !isnull($id)){
                throw new Exception(__METHOD__.'() - Not a valid integer, please check the integer and try again.');
            }

            // form information
            $form->addField(array(
                'name'       => 'projectID',
                'type'       => 'hidden',
                'value'      => $id,
                'primary'    => TRUE,
                'fieldClass' => 'id',
                'showIn'     => array(formBuilder::TYPE_INSERT, formBuilder::TYPE_UPDATE),
            ));

           $form->addField(array(
                'name'     => 'customerID',
                'label'    => 'What customer owns this project?',
                'type'     => 'select',
                'blankOption' => 'Select a Customer',
                'linkedTo' => array(
                    'foreignTable' => 'customers',
                    'foreignField' => 'id',
                    'foreignLabel' => 'companyName',
                ),
            ));

            $form->addField(array(
                'name'     => 'projectName',
                'label'    => 'Project Name:',
                'required' => TRUE
            ));

            $form->addField(array(
                'name'     => 'scope',
                'label'    => 'A simple statement of the scope of work being done:',
                'required' => TRUE
            ));

            $form->addField(array(
                'name'     => 'type',
                'label'    => 'Project Type:',
                'required' => TRUE,
                'type'     => 'select',
                'options'  =>  array(
                    'design'      => 'Design',
                    'development' => 'Programming or Development',
                    'consult'     => 'Meeting or Consultation',
                    'other'       => 'other'
                ),
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
                'name'            => "description",
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

            return '{form name="Projects" display="form"}';

        } catch (Exception $e) {
            errorHandle::errorMsg($e->getMessage());
        }
    }

    public function deleteRecord($id = null){
        try {
            // call engine
            $engine    = EngineAPI::singleton();
            $localvars = localvars::getInstance();
            $db        = db::get($localvars->get('dbConnectionName'));
            $validate  = new validate;

            // test to see if Id is present and valid
            if(isnull($id) || !$validate->integer($id)){
                throw new Exception('<div class="error">'.__METHOD__.'() -Delete failed, improper id or no id was sent. </div>');
            }

            // SQL Results
            $sql = sprintf("DELETE FROM `projects` WHERE projectID=%s LIMIT 1", $id);
            $sqlResult = $db->query($sql);

            if(!$sqlResult) {
                throw new Exception('<div class="error">'.__METHOD__.'Failed to delete Projects.</div>');
            }
            else {
                return "<div class='success'> Successfully deleted the project. </div>";
            }

        } catch (Exception $e) {
            errorHandle::errorMsg($e->getMessage());
            return $e->getMessage();
        }
    }


    public function renderDeleteData($id){
        try {
            $engine    = EngineAPI::singleton();
            $localvars = localvars::getInstance();
            $validate  = new validate;

            if(isnull($id) || !$validate->integer($id)){
                throw new Exception('Id is null or not an integer.  Please try again.');
            }
            else {
                $dataRecord = self::getRecords($id);
                $output = "";

                foreach($dataRecord as $data){
                     $output .= sprintf("<div class='projectRecord'>
                                            <h2 class='projectName'>%s</h2>
                                            <div class='projectInfo'>
                                                <div class='scope'>
                                                    <strong> Project Scope: </strong>
                                                    <p> %s </p>
                                                </div>
                                                <div class='type'>
                                                    <strong> Project Type: </strong>
                                                    <p> %s </p>
                                                </div>
                                                <div class='description'>
                                                    <strong> Project Description: </strong>
                                                    <p> %s </p>
                                                </div>
                                                <div class='completed'>
                                                    <strong> Is the Project Complete? </strong>
                                                    <span> %s </span>
                                                </div>
                                            </div>
                                            <div class='actions'>
                                                <a href='%s/projects/delete/%s'> Delete </a>
                                                <a href='%s/projects'> Cancel </a>
                                            </div>
                                        </div>",
                            $data['projectName'],
                            $data['scope'],
                            $data['type'],
                            $data['description'],
                            ($data['completed'] < 1 ? 'No' : 'Yes'),
                            $root, $data['projectID'],
                            $root
                    );
                }

                return $output;
            }

        } catch (Exception $e) {
            errorHandle::errorMsg($e->getMessage());
            return $e->getMessage();
        }
    }

    public function renderSingleRecord($id){
        try {
            $engine    = EngineAPI::singleton();
            $localvars = localvars::getInstance();
            $validate  = new validate;
            $root      = $localvars->get('root');

            if(isnull($id) || !$validate->integer($id)){
                throw new Exception('Id is null or not an integer.  Please try again.');
            }
            else {
                $dataRecord = self::getRecords($id);

                if(!is_array($dataRecord)){
                    return $dataRecord;
                }

                $output = "";
                foreach($dataRecord as $data){
                    $output .= sprintf("<div class='projectRecord'>
                                            <h2 class='projectName'>%s - <span> %s </span> </h2>
                                            <div class='projectInfo'>
                                                <div class='scope'>
                                                    <strong> Project Scope: </strong>
                                                    <p> %s </p>
                                                </div>
                                                <div class='type'>
                                                    <strong> Project Type: </strong>
                                                    <p> %s </p>
                                                </div>
                                                <div class='description'>
                                                    <strong> Project Description: </strong>
                                                    <p> %s </p>
                                                </div>
                                                <div class='completed'>
                                                    <strong> Is the Project Complete? </strong>
                                                    <span> %s </span>
                                                </div>
                                            </div>
                                            <div class='actions'>
                                                <a href='%s/projects/edit/%s'> Edit </a>
                                                <a href='%s/projects/delete/%s'> Delete </a>
                                            </div>
                                        </div>",
                            $data['projectName'],
                            getCompanyName($data['customerID']),
                            $data['scope'],
                            $data['type'],
                            $data['description'],
                            ($data['completed'] < 1 ? 'No' : 'Yes'),
                            $root, $data['projectID'],
                            $root, $data['projectID']
                    );
                }

                return $output;
            }

        } catch (Exception $e) {
            errorHandle::errorMsg($e->getMessage());
            return $e->getMessage();
        }
    }

     public function renderDataTable(){
        try {
            $engine     = EngineAPI::singleton();
            $localvars  = localvars::getInstance();
            $validate   = new validate;
            $dataRecord = self::getRecords();
            $root       = $localvars->get('root');

            $records    = "";

            foreach($dataRecord as $data){
                $records .= sprintf("<tr>
                                        <td>%s</td>
                                        <td>%s</td>
                                        <td>%s</td>
                                        <td>%s</td>
                                        <td>%s</td>
                                        <td>%s</td>
                                        <td> <a href='%s/projects/edit/%s'> <span class='glyphicon glyphicon-edit'></span> </a> </td>
                                        <td> <a href='%s/projects/confirmDelete/%s'> <span class='glyphicon glyphicon-trash'></span> </a></td>
                                    </tr>",
                        $data['projectName'],
                        getCompanyName($data['customerID']),
                        $data['scope'],
                        $data['type'],
                        $data['description'],
                        ($data['completed'] < 1 ? 'No' : 'Yes'),
                        $root, $data['projectID'],
                        $root, $data['projectID']
                );
            }

            $output     = sprintf("<div class='dataTable table-responsive'>
                                        <table class='table table-striped'>
                                            <thead>
                                                <tr class='info'>
                                                    <th> Project Name </th>
                                                    <th> Customer Name </th>
                                                    <th> Scope </th>
                                                    <th> Type </th>
                                                    <th> Description  </th>
                                                    <th> Completed</th>
                                                    <th> </th>
                                                    <th> </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                %s
                                            </tbody>
                                        </table>
                                    </div>",
                $records
            );

            return $output;

        } catch (Exception $e) {
            errorHandle::errorMsg($e->getMessage());
            return $e->getMessage();
        }
    }

    public function getJSON($id = null){
        $validate =  new validate;
        if(!isnull($id) && $validate->integer($id)){
            $data = self::getRecords($id);
        } else {
            $data = self::getRecords();
        }
        return json_encode($data);
    }

    public function getCustomerProjectsJSON($customerID){
        try {
            // call engine
            $engine    = EngineAPI::singleton();
            $localvars = localvars::getInstance();
            $db        = db::get($localvars->get('dbConnectionName'));
            $sql       = "SELECT * FROM `projects`";
            $validate  = new validate;

            // test to see if Id is present and valid
            if(!isnull($customerID) && $validate->integer($customerID)){
                $sql .= sprintf('WHERE customerID = %s', $customerID);
            }

            // if no valid id throw an exception
            if(!$validate->integer($customerID) && !isnull($customerID)){
                throw new Exception("An invalid ID was given!");
            }

            // get the results of the query
            $sqlResult = $db->query($sql);

            // if return no results
            // else return the data
            if ($sqlResult->rowCount() < 1) {
               return "There are no projects in the database.";
            }
            else {
                $data = array();
                while($row = $sqlResult->fetch()){
                    $data[] = $row;
                }
                return json_encode($data);
            }
        } catch (Exception $e) {
            errorHandle::errorMsg($e->getMessage());
        }
    }

}

?>