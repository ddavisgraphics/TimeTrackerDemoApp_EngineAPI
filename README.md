# Engine Framework 4.0 - LAMP Stack

This application is a demo application using the Engine framework from WVULibraries as a base.  This demo application is to teach the LAMP Stack to create web applications for a designer interested in programming aspects associated with the web.

## Initial files to change 
Each time that you setup vagrant and bootstrap.sh it is going to have a direct correlation to a new type of application, unless your building features on to one large application.  In the times that you want to make a new application you will have to change a few things in multiple files.  We are going to look at some of the files and places that you will need to make changes within this directory.  

#### Bootstrap.sh 
You will need to change the siteroot and serverurl variables to represent the names and document root of your application.  

```bash
SERVERURL="/home/yourAppName"
DOCUMENTROOT="public_html"
SITEROOT="/home/yourAppName/public_html/src"
```


#### serverConfiguration/httpd.conf 
This file has 2 lines that will need changed.  These are going to represent your document root.  At 292 you will see the following.  

``` 
DocumentRoot /home/yourAppName/public_html/src
``` 

The same goes at line 318 you will need to change it to the same as your document root.  It will look something like this.   

```
<Directory "/home/yourAppName/public_html/src">
```

That is all for the time being on the server side, but there may be more as we continue to develop the application.  From this point you should be able to get a clean install of vagrant running and have the start to setting up the web server.  

# Setting Up Engine

To appropriately setup Engine we are going to have to create a vagrant box for development and modify the box to represent a Linux server.  We are going to do this in the bootstrap.sh file.  Many of these commands will be bash commands and git commands to setup our dependencies and move files where they need to be.

In this example you are going to need to have VirtualBox and Vagrant installed on your computer.  These same tasks can be run from SSH of a Linux Server running the Centos 6.4 Operating System.

### Centos OS Vagrant Box
```ruby
config.vm.box = "centos6.4"
config.vm.box_url = "https://github.com/2creatives/vagrant-centos/releases/download/v0.1.0/centos64-x86_64-20131030.box"
```

### Linux Dependencies
First we want to declare certain variables that are going to help us to save time in setting up our box.  The variables will be used to determine where our files are stored and what the default root of our public facing file system is going to be.

The GITDIR Variable setups a directory in the tmp folder to hold files.  When the system restarts the tmp directory on a linux system is cleared.  The next gets the latest installs of the Engine Framework from Github and sets a home directory.  The SERVER URL is a setup of your base directory.  The Document root and site root variables setup your public facing directories and link your source.

```bash
GITDIR="/tmp/git"
ENGINEAPIGIT="https://github.com/wvulibraries/engineAPI.git"
ENGINEBRANCH="master"
ENGINEAPIHOME="/home/engineAPI"

SERVERURL="/home/timeTracker"
DOCUMENTROOT="public_html"
SITEROOT="/home/timeTracker/public_html/src"
```

The following code will install apache, mysql, php, git, and some other things that are generally needed for secure web development with the engine framework.

```bash
yum -y install httpd httpd-devel httpd-manual httpd-tools
yum -y install mysql-connector-java mysql-connector-odbc mysql-devel mysql-lib mysql-server
yum -y install mod_auth_kerb mod_auth_mysql mod_authz_ldap mod_evasive mod_perl mod_security mod_ssl mod_wsgi
yum -y install php php-bcmath php-cli php-common php-gd php-ldap php-mbstring php-mcrypt php-mysql php-odbc php-pdo php-pear php-pear-Benchmark
yum -y install emacs emacs-common emacs-nox
yum -y install git
```

Finally we setup apache and the configuration files to point towards our home that we declared in the variables.

```bash
echo "Modifying Apache"
mv /etc/httpd/conf.d/mod_security.conf /etc/httpd/conf.d/mod_security.conf.bak
/etc/init.d/httpd start
chkconfig httpd on

echo "Moving HTTPD Conf Files"
rm -f /etc/httpd/conf/httpd.conf
ln -s /vagrant/serverConfiguration/httpd.conf /etc/httpd/conf/httpd.conf
```

### SERVER Framework Installation
```bash
mkdir -p $GITDIR
cd $GITDIR
git clone -b $ENGINEBRANCH $ENGINEAPIGIT
git clone https://github.com/wvulibraries/engineAPI-Modules.git

mkdir -p $SERVERURL/phpincludes/
ln -s /vagrant/templates $GITDIR/engineAPI/engine/template/
ln -s $GITDIR/engineAPI-Modules/src/modules/* $GITDIR/engineAPI/engine/engineAPI/latest/modules/
ln -s $GITDIR/engineAPI/engine/ $SERVERURL/phpincludes/


mkdir -p $SERVERURL/$DOCUMENTROOT
ln -s /vagrant/src $SITEROOT
ln -s $SERVERURL/phpincludes/engine/engineAPI/latest $SERVERURL/phpincludes/engine/engineAPI/4.0

rm -f $GITDIR/engineAPI/engine/engineAPI/latest/config/defaultPrivate.php
ln -s /vagrant/serverConfiguration/defaultPrivate.php $GITDIR/engineAPI/engine/engineAPI/latest/config/defaultPrivate.php

mkdir -p $SERVERURL/phpincludes/databaseConnectors/
ln -s /vagrant/serverConfiguration/database.lib.wvu.edu.remote.php $SERVERURL/phpincludes/databaseConnectors/database.lib.wvu.edu.remote.php

ln -s $SERVERURL $ENGINEAPIHOME
ln -s $GITDIR/engineAPI/public_html/engineIncludes/ $SERVERURL/$DOCUMENTROOT/engineIncludes

chmod a+rx /etc/httpd/logs -R
sudo ln -s /etc/httpd/logs/error_log /vagrant/serverConfiguration/serverlogs/error_log
sudo ln -s /etc/httpd/logs/access_log /vagrant/serverConfiguration/serverlogs/access_log
```

### PHP Configuration
In the base directory we are going to have to setup an includes folder that adds the engine framework to our document.  We want to do this in a way that we only have to include it once.  The bootstrap is setup to be configured with a specific directory setup for your codebase, but you can always ammend it once you figure out the different layers of items and want to dive in the bash.

![Directory Structure](/Documentation/DirectorySetup.jpg?raw=true "Directory Structure")

# ENGINE FOR DEVELOPMENT
Talking with the developers of Engine, the framework had a few clear goals.
- Security - Specifically protection from injection and penetration attacks.
- Modular builds for rapid development
- Free form development allowing the developer to choose the software design patterns they use.  While Engine itself runs using a Singleton pattern, meaning their can only be one, the apps developed using engine is open for the developer to choose.

This example we are mainly going to talk about MVC.  Many of these same features will work with other examples as well depending on how you want to work with the different features and options.

In our application we have to setup engine to run on our pages and applications.  The way we are going to do this is by creating an engine.php file.

### Engine.php
```php
    // path to my engineAPI install
    require_once '/home/timeTracker/phpincludes/engine/engineAPI/4.0/engine.php';
    $engine = EngineAPI::singleton();

    // Setup Error Rorting
    errorHandle::errorReporting(errorHandle::E_ALL);

    // These are specific to EngineAPI and pulling the appropriate files
    recurseInsert("headerIncludes.php","php");

    // Setup Database Information for Vagrant or eventually the server
    $databaseOptions = array(
        'username' => 'username',
        'password' => 'password',
        'dbName'   => 'timeTracker'
    );

    // makes for easy db commands
    $db  = db::create('mysql', $databaseOptions, 'appDB');

    // Set localVars and engineVars variables
    $localvars  = localvars::getInstance();
    $enginevars = enginevars::getInstance();

    if (EngineAPI::VERSION >= "4.0") {
        $localvars  = localvars::getInstance();
        $localvarsFunction = array($localvars,'set');
    }
    else {
        $localvarsFunction = array("localvars","add");
    }

    // include base variables
    recurseInsert("includes/vars.php","php");

    // load a template to use
    templates::load('timeTemplate');
```

The 2 aspects of the page at the bottom include setting up a template and using local variables to call information and insert php logic into HTML.  This aspect will come in handy later and will be explained in the enxt session.  The important aspect is setting up the error handeling, the engineSingleton, and the database options.

## Simple MVC Style

MVC stand for Model View and Controller.  It is used to develop applications and keep a seperation of concerns and logic.  The model aspect directly deals with the data, logic, and rule of the application.  The View component can be thought of as dealing with logic and what the user sees.  The Controller takes information determines what model and view should be represented.

In our simple MVC we are going to take advantage of a routing system natively built in Engine.  We are going to create our own customer class and a function that will help to manage our routing and views rendering.  We are also going to use a few other things within engine that help to make templating seperation of logic much easier.

### Router

The router class is built into engine, if you want to use it there is a tiny bit of setup required.  The first is to the following htaccess to your main directory.  The current setup allows us to simply have this in the src folder, but depending on your setup you may have to use the command line to place it in your root directory.

**.htaccess**

```bash
<IfModule mod_rewrite.c>
    RewriteEngine On

    ## recursively search parent dir
    # if index.php is not found then
    # forward to the parent directory of current URI
    RewriteCond %{DOCUMENT_ROOT}/$1$2/index.php !-f
    RewriteRule ^(.*?)([^/]+)/[^/]+/?$ /$1$2/ [L]

    # if current index.php is found in parent dir then load it
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{DOCUMENT_ROOT}/$1/index.php -f
    RewriteRule ^(.*?)[^/]+/?$ /$1/index.php [L]
</IfModule>
```

**Router Example**
In order to use the router we must first instantiate the class.  This is done by declaring a router variable and calling an instance function.

```php
// Instantiate the class
$router = router::getInstance();
```

After declaring the class, use the class variable to set callbacks to use for certain routes.  The example below is an example of defining the home route and a callback function.

```php
// syntax
// $router->defineRoute(url, callbackfunction)

// example of syntax for home route and a function called displayHome
$router->defineRoute("/", 'displayRoute');

//after declaring the defineRoute we want to make sure the the router routes to that url
$router->route();
```

The above callback function will be able to take 2 parameters.  These parameters are going to be the URL and any variables declared within the URL.  Think of routes as really just ways to hold information and tell the system what to do next.

```php
    // example of the callback function
    function displayRoute($url, $vars){
        // prints the url as a string
        print "<pre>";
        var_dump($url);
        print "</pre>";

        // prints the variables in the URL as an array
        // for the above example they will be empty.
        print "<pre>";
        var_dump($vars);
        print "</pre>";
    }
```

Here is a more complete example, you can use different callback functions or the same callback functions to determine what you would like to do. This is by no means the limits of what you can do.  Its really just a starting point.  It uses the callbacks to point to each of the views and really control what happens next.

```php
    // Routing
    $router = router::getInstance();
    $router->defineRoute("/", 'displayRoute');
    $router->defineRoute("/{model}", 'displayRoute');
    $router->defineRoute("/{model}/{action}", 'displayRoute');
    $router->defineRoute("/{model}/{action}/{item}", 'displayRoute');
    $router->route();

    // example of the callback function
    function displayRoute($url, $vars){
        // prints the url as a string
        print "<pre>";
        var_dump($url);
        print "</pre>";

        // prints the variables in the URL as an array
        // for the above example they will be empty.
        print "<pre>";
        var_dump($vars);
        print "</pre>";
    }
```

Use the above example, testing the different results typed into the URL's we can see the produced results.

**URL Tests**

**URL:** _"/"_
```php
array(3) {
  ["URI"]=>
  string(1) "/"
  ["count"]=>
  int(1)
  ["items"]=>
  array(1) {
    [0]=>
    array(2) {
      ["path"]=>
      string(0) ""
      ["variable"]=>
      bool(false)
    }
  }
}
```

**URL:** _"/home"_
```php
array(3) {
  ["URI"]=>
  string(5) "/home"
  ["count"]=>
  int(1)
  ["items"]=>
  array(1) {
    [0]=>
    array(2) {
      ["path"]=>
      string(4) "home"
      ["variable"]=>
      bool(false)
    }
  }
}
array(1) {
  ["model"]=>
  string(4) "home"
}
```

**URL:** _"/test/edit"_
```php
array(3) {
  ["URI"]=>
  string(10) "/test/edit"
  ["count"]=>
  int(2)
  ["items"]=>
  array(2) {
    [0]=>
    array(2) {
      ["path"]=>
      string(4) "test"
      ["variable"]=>
      bool(false)
    }
    [1]=>
    array(2) {
      ["path"]=>
      string(4) "edit"
      ["variable"]=>
      bool(false)
    }
  }
}
array(2) {
  ["model"]=>
  string(4) "test"
  ["action"]=>
  string(4) "edit"
}
```

**URL:** _"/test/update/23"_
```php
array(3) {
  ["URI"]=>
  string(15) "/test/update/23"
  ["count"]=>
  int(3)
  ["items"]=>
  array(3) {
    [0]=>
    array(2) {
      ["path"]=>
      string(4) "test"
      ["variable"]=>
      bool(false)
    }
    [1]=>
    array(2) {
      ["path"]=>
      string(6) "update"
      ["variable"]=>
      bool(false)
    }
    [2]=>
    array(2) {
      ["path"]=>
      string(2) "23"
      ["variable"]=>
      bool(false)
    }
  }
}
array(3) {
  ["model"]=>
  string(4) "test"
  ["action"]=>
  string(6) "update"
  ["item"]=>
  string(2) "23"
}
```

The router doesn't prevent us from being able to use directories and custom setups outside of the MVC patterns.  You could very easily add a file that doesn't relate to the other style by developing as a folder and giving it an index.php.

Example would be is if I wanted to create a page about monkeys.  I could create a new folder in my source called monkey and inside that folder have an index.php.  This will get ignored by the router callback and wait for custom php or html to be rendered.

This is also consequently how we use our resources.  If we want to attach some CSS, Images, or JS. we need to place a blank index.php file inside of that folder.  This issue has been noted as an inconvenience and has been added to a debug list, but the features still work perfectly.  Just remember this while working.

# Engine Useful Tools

### Local Vars
Local variables or local vars are part of the engine system by default and can be used to transfer PHP Logic or results into HTML.  This is done by setting and getting methods that are declared with in Engine.  For each new function or class you will have to reinstantiate the class localvars.

```php
    // instantiate the local vars
    $localvars  = localvars::getInstance();

    // set
    $localvars->set(name, variable);

    // get
    $localvars->get(name);
```

An example might be dynamically setting a date inside of a footer.  We can do this using a little bit of PHP and the localvars.  This parameter will not render an array and doesn't know how to render any data types other than strings.  But you can see how using functions and variables can get you the desired results.

```php
    $localvars  = localvars::getInstance();
    $localvars->set('date', new Date('m-d-Y'));
```

```html
    <div> Today is - {local var="date"} </div>
```

### Validation
The validation class is an extension in EngineAPI that allows you to validate different types of variables and inputs.  You can validate phone numbers, ipaddresses, urls, email addresses, integers, alpha numeric strings, no spaces or special characters, and dates.  Each one of these aspects will return a boolean of true or false based on the information and criteria provided.

```php
    // instantiate new class
    $validate  = new validate;

    // test integer returns true
    $validate->integer(1); // returns true
    $validate->integer("1"); // returns true
    $validate->integer('test') // returns false

    // validate url
    $validate->url('http://www.google.com'); // returns true
    $validate->url('google.com'); // returns false

    // validate email
    $validate->emailAddr('something@gmail.com'); // is valid even though email may not exsist
```

For more examples and ideas you can read the [validation source code](https://github.com/wvulibraries/engineAPI/blob/develop/engine/engineAPI/latest/modules/validate/validate.php) and get the results you need.

### Form Builder

For builder is a way to dynamically create input forms that link directly to a database and can also connect to some other databases for linked table data.  The form have callbacks and process the post information.  You can edit form titles and add other form variables and fields.

#### formBuilder Options
- formEncoding       [str]
 - Optional form encoding (sets the enctype attribute on the <form> tag for example with file fields)
- browserValidation  [bool]
 - Set to false to disable browser-side form validation (default: true)
- insertTitle        [str]
 - Form title for insertForm (default: $formName as passed to formBuilder::createForm())
- updateTitle        [str]
 - Form title for updateForm (default: $formName as passed to formBuilder::createForm())
- editTitle          [str]
 - Form title for editTable (default: $formName as passed to formBuilder::createForm())
- templateDir        [str]
 - The directory where our form templates live (default: 'formTemplates' next to the module)
- template           [str]
 - The template name to load for this template (default: 'default')
- ajaxHandlerURL     [str]
 - URL for formBuilder ajax handler (default: the current URL)
- insertFormCallback [str]
 - Custom JavaScript function name to call to retrieve the updateForm in an expandable editTable (default: none)
- submitTextInsert   [str]
 - Button text for submit button on insertForm (default: 'Insert')
- submitTextUpdate   [str]
 - Button text for submit button on updateForm (default: 'Update')
- deleteTextUpdate   [str]
 - Button text for delete button on updateForm (default: 'Delete')
- submitTextEdit     [str]
 - Button text for submit button on editTable (default: 'Update')
- expandable         [bool]
 - Sets editTable as an 'expandable' editTable with drop-down update form (default: true)

#### Field Options:
- blankOption         [bool|str] Include a blank option on 'select' field. If it's a string, will be the label for the blank options (default: false)
- disabled            [bool]     Disable the field
- disableStyling      [bool]     If true, then ignores all CSS styling (ie fieldClass, fieldCSS, labelClass, & fieldCSS) (default: falsE)
- duplicates          [bool]     Allow duplicated (default: true)
- fieldClass          [str]      CSS Classes to add to the field
- fieldCSS            [str]      CSS Style to add to the field
- fieldID             [str]      id attribute for the field
- fieldMetadata       [array]    Array of key->value pairs to be added to the field through data- attributes
- hash                [str]      The mhash algorithm to use for password fields (default: sha512)
- help                [array]    Array of field help options
  - type             [str]      The type of help: modal, newWindow, hover, tooltip (default: tooltip)
  - text             [str]      Plaintext to display
  - url              [str]      URL of content
  - file             [str]      Local file to pull content from
- label               [str]      The label for the field (default: {} to field's name)
- labelClass          [str]      CSS Classes to add to the label
- labelCSS            [str]      CSS Classes to add to the label
- labelID             [str]      id attribute for the label
- labelMetadata       [array]    Array of key->value pairs to be added to the label through data- attributes
- linkedTo            [array]    Array of metadata denoting either a one-to-many or many-to-many relationship
  - foreignTable     [str]      The table where the values for this field live
  - foreignKey       [str]      The column on the foreignTable which contains the value
  - foreignLabel     [str]      The column on the foreignTable which contains the label
  - foreignOrder     [str]      Optional ORDER BY clause (default: '{foreignLabel} ASC')
  - foreignWhere     [str]      Optional WHERE clause
  - foreignLimit     [str]      Optional LIMIT clause
  - foreignSQL       [str]      Option raw SELECT SQL to be used. (1st column is treated as foreignKey and 2nd as foreignLabel)
  - linkTable        [str]      many-to-many: Linking table name
  - linkLocalField   [str]      many-to-many: Linking table column where the local key lives
  - linkForeignField [str]      many-to-many: Linking table column where the foreign key lives
- multiple            [bool]     Sets 'multiple' on a select field (default: false)
- options             [array]    Array of field options for select, checkbox, radio, and boolean
- placeholder         [str]      Text to put in field's placeholder="" attribute
- primary             [bool]     Sets the field as a primary field (multiple primary fields are allowed) (default: false)
- readonly            [bool]     Sets the field to be read-only (default: false)
- required            [bool]     Sets the field as required (default: false)
- showIn              [array]    Show/Hide the field in specified forms (default: array of all types)
- type                [str]      The type of field (see list of field types below)
- validate            [str]      The validate method to check the value against
- value               [str]      The initial value for this field

#### Field types:
- bool        Alias for 'boolean'
- boolean     Boolean (Yes/No) field
 - options
   - type    [string] Type of boolean field: check, checkbox, radio, select (default: select)
   - labels  [array]  Labels to use for 'Yes' and 'No' (default: ['NO\_LABEL','YES\_LABEL'])
- button      Standard button
- checkbox    Checkbox group
 - options   Array of value->label pairs to be displayed
- color       HTML5 color picker    dependant on browser support
- date        HTML5 date picker     dependant on browser support -- Converts and saves as unix time (using strtotime)
- datetime    HTML5 datetime picker dependant on browser support
- dropdown    Alias for 'select'
- email       HTML5 email field
- file        File field
- hidden      Hidden field (will be rendered just below <form> tag)
- image       HTML5 image field dependant on browser support
- month       HTML5 month picker dependant on browser support
- multiSelect multiSelect field requires linkedTo be defined
- number      HTML5 number field dependant on browser support
- password    Password field (will render a confirmation field as well)
- plaintext   Plaintext field with support for text-replacements note: replacements are case sensitive
- range       HTML5 range field dependant on browser support
- radio       Radio group
 - options   Array of value->label pairs to be displayed
- reset       Form reset button
- search      HTML5 search field
- select      \<select\> field
 - options   String of options or Array of value->label pairs to be displayed
- string      Alias for stext
- submit      Form submit button
- delete      Form submit button to delete the record
- text        simple \<input\> field
- textarea    Full \<textarea\>
- tel         HTML5 tel field
- time        HTML5 time picker dependant on browser support
- url         HTML5 url field
- week        HTML5 week picker dependant on browser support
- wysiwyg     Full WYSIWYG editor

Below is an example of a form builder that has only 2 fields.  Each field will be build from the addField function.
You can see examples of where to put the form options, field options, and field types using the example below.

```php
 // create customer form
    $form = formBuilder::createForm('formName');
    $form->linkToDatabase( array(
        'table' => 'formDatabaseTable'
    ));

    if(!is_empty($_POST) || session::has('POST')) {
        $processor = formBuilder::createProcessor();
        $processor->processPost();
    }

    // form titles
    $form->insertTitle = "Insert Title";
    $form->editTitle   = "Edit Title";
    $form->updateTitle = "Update Title";

    // form information
    $form->addField(array(
        'name'       => 'ID',
        'type'       => 'hidden',
        'value'      => $id,
        'primary'    => TRUE,
        'fieldClass' => 'id',
        'showIn'     => array(formBuilder::TYPE_INSERT, formBuilder::TYPE_UPDATE),
    ));

    $form->addField(array(
        'name'     => 'fieldName',
        'label'    => 'Field Label',
        'required' => TRUE
    ));
```

Below is an example of how you call the form you created by using the formName.  The one below is a unique example that allows the form to automatically descide between an update or an insert form based on if the primary key field has an id and it matches something within the database.

```html
{form name="formName" display="form"}
```

Below is an example of what an edit table will appear.  This will allow you to edit multiple fields rather quickly.  It is not a good option for particularly large forms.

```html
{form name="formName" display="edit"}
```

### MySQL
Using mysql we can insert, update, and edit databases by using some helpful code.

Example of MySQL
```php
    $db  = db::get($localvars->get('dbConnectionName'));
    $sql = "SELECT * FROM `someTable`";
    $sqlResult = $db->query($sql);

    if ($sqlResult->error()) {
        throw new Exception("ERROR SQL" . $sqlResult->errorMsg());
    }

    if ($sqlResult->rowCount() < 1) {
       return "There are no stuffs in the database.";
    }
    else {
        $data = array();
        while($row = $sqlResult->fetch()){
            $data[] = $row;
        }
        return $data;
    }
```

