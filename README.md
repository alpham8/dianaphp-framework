# DianaPHP Framework


A simple PHP framework with an implementation of the mvc design pattern, an ORM and a String class.

License: MIT

## development in progress
This framework is not finished, yet. Breaking changes until the final release are possible.
But you can now use it as it is. It has been tested due to development over three years.
That's the reason why we have no test files.

## help wanted
In order to publish the final release, we need your help. Everything works as a charme right now.
But you can imagine, that the state of the art has changed for the last three years. So, we need your help to stay modern and to add some comfort functions that makes daily development with DianaPHP more joyfull.
You may find out what needs to be done in the section below.

## Using DianaPHP
Using DianaPHP is simple, you don't have to learn a lot. For now, clone this project. Later we will have an packagist repo from there you will be able to install DianaPHP simply with
```
composer create-project [...]
```

As mentioned above, Diana is based on the Model-View-Control Concept. That means that project structure looks like this:
* App
  * Mvc (the base folder for the MVC like files)
    * Controller (the folder where you place your Controllers in)
      * IndexController.php (the default view routes to index/index, if nothing is given in the URL. So you really need this controller)
    * Model (the folder where you place your Models in)
    * Routes (the folder with routes inside)
      * routes.ini (The file that contains all routes)
    * views (the folder where you place your views, templates and helpers in)
      * _templates (the folder for templates)
      * <controller name> (name the folder after your controller in lower case)
        * <action view>.phtml (name your action view after your controller action, or name it something else, if you have changed the default behaviour)
      * notallowed.phtml (A simple view if the user wasn't allowed to see the requested page)
  * Src (some files, which do not require to follow the MVC Pattern should be placed here. This is mainly for business logic class(es))
    * BootstrapPaginator.php (A class which uses the existing DianaPHP logic and combines it with a pager by using the Twitter Bootstrap's pager)

### Create a Controller
1. Just create your Controller under Controller to get started. You need to extend the BaseController class from there to get some basic functionallity.
2. Create a method with public access there. This is your action. You don't need to pre- or postfix something to it. The name is as the URL (or route) would look like (case sensenetive).
3. Define a view template. You need to define a view template, because this an convention. E. g. this could be done in the class constructor, because otherwise you need to set it in each action. If you don't need one, disable it by using
```
$this->_view->setTemplate(null);
```

### Defining Models
1. Create a Class that matches to your database table and postfix it with Model inside the Model folder. E. g. lets say you have customers. Then your model class is called CustomersModel.
2. Then you need to define your table like this:
```
<?php
namespace App\Mvc\Model
{
	use Diana\Core\Std\String;
	use Diana\Core\Persistence\Sql\BaseModel;


	class CustomersModel extends BaseModel
	{
		public function __construct()
		{
			parent::__construct();

            // define the table name
			$this->_sTableName = new String('customers');

            // define your columns
			$this->_arColumns = array(
										'id' => 'int',
                                        'first_name' => 'string',
                                        'last_name' => 'string',
										'created_at' => 'date',
										'description' => 'string',
                                        'address_id' => 'int'
								);

            // if needed define foreign keys.
            // You may also want to define columns which references another column without having an index on that.
            // This is also done here in the form local column name as array key => the foreign table
            // it then references it to the foreign table id column.
            $this->_arKeys = array(
                                'address_id' => 'address'
                            );
		}
	}
}
?>
```

#### Some notes on defining Models
You may define Models as you would like unless the following convenctions are met:
* The primary key column must always be called id
* use underscore for word saperator due to the getters and setters methods, which uses camelCase syntax for word separators
* foreign keys or other none key columns in arKeys always match the id on the foreign table


### Fetching data with the Models
This is made easy for you. Just set the criteria you need on the model, if would like to automatically create the where conditions for you.
It looks all columns, if some is set and then appends with the and condition connector.

Otherwise use the where methods on the Model:
```
$customersMdl->addWhereClause(array(SQL_ESC . $customersMdl->getTableName() . SQL_ESC . '.' . SQL_ESC . 'created_at' . SQL_ESC => array(
									   BaseModel::CRITERIA_OPERATOR => BaseModel::CRITERIA_EQUALS,
									   BaseModel::CRITERIA_VALUE => new Date('2016-12-01 10:01:59')));
```
You need to use several constants to get it wokring.
First, you need the criteraia operator.
Then you need the criteria value key.
And last if you have more than one condition, you need to define on the 2nd condition (and above) the condition connector (see constants on BaseModel).

#### data fetching modes
##### single record fetching
You may want to fetch only a single result, then just use the fetch method on the model. It reads all data and fetches it on itself.
If nothing is found, it sets the id to -1.
##### single record fetching with iterator
That is a special feature on DianaPHP, which many other ORM Frameworks doesn't offer.
You can fetch data by using the fetchFirst() / fetchNext() syntax. So in this way you could directly operate on data while fechting it instead of read all and do the work then, which causes two iterations over the data.
##### fetch all
Use the fetchAll method to fetch all data based on you criteria. You could optionally pass some parameters to that method. E. g. if you want to limit the results.
It will return an array of the found objects.

## defining a view
You need also to define a view, or locate multiple actions to one view. There you can access your data passed by the magic callers syntax with
```
$this->myControllerVar;
```
### view helpers
You may also use helper functions. You can simply call it with
```
$this->mySuperHelperFunction(...);
```
There are some several Framework defined helpers ready to use:
* AppendScript (creates the script tags for you)
* AppendStyle (creates the external css resource link tag for you)
* InternalAnchor (crates an App internal URL. Let's say you want to call the action start on the forum's controller. This is the best way to do it.)
* PasswordField (creates a password field for you)
* ReplaceDecimal (replaces the english decimal number separator . with the german , separator)
* SetTile (sets the page's title)
* Textfield (creates a textfield for you. This function has many options)
As you may guess there are several view helper lookup places. In the Framework folder itself and in App/Mvc/view/_helpers.
The helper functions are simple php functions inside a Upper cased file with camelCase syntax function name inside.


## The Framework structure
The whole Framework is located under root-path/Lib.

* Lib (Framework files)
  * Modules (some external modules)
  * Core (the framework core files)
    * Mvc (some Mvc related classes)
      * Helper
        * AppendScript.php
        * AppendStyle.php
        * InternalAnchor.php
        * PasswordField.php
        * ReplaceDecimal.php
        * SetTile.php
        * Textfield.php
      * Init
        * Boostrap.php (The bootstrapping class, which wraps everything up.)
        * DefaultWebRequestGlue.php (some middleware class which interacts besides the Dispatcher)
        * Dispatcher.php (The Dispatcher class, which calls everything from the start until the end of execution.)
        * WebRequestGlueInterface (The interface if you want to define your own middleware class.)
      * BaseController.php (The base controller which every controller needs to inherit from.)
      * Routes.php (The routing class.)
      * View.php (The view class which lets the view magic happen.)
    * Persistence (some files for the persistence layer.)
      * Sql (some sql related stuff)
        * BaseModel (The sql base model, which every model needs to inherit from.)
        * DBConnection (The singleton class for the database connectivity.)
        * ModelExeption (A special class for Exceptions inside BaseModel class.)
    * Std (Some standard in-/out operations)
      * Http (HTTP related in-/out stuff)
        * Escaper.php (The request parameter escaper class.)
        * Request.php (the request class)
        * Response.php (the response class)
      * Date.php (the Date class, which offers culture related date formatting in __toString())
      * Email.php (a simple emailer class)
      * String.php (the String class, which offers all String functionallity)
      * StringTok.php (old, depracted class. Example implentation of an string tokenizer)
      * StringTokenizer.php (old, depracted class. Another examople implementation of an string tokenizer)
      * Tokenizer.php (a string tokenizer implementation, because with plain PHP you could only open one tokenzier at the same time.)
    * Util (some utilize functionallity)
      * Authentification
        * AuthList.php (An Authentification list implementation. Basically, it does not depend on any database layer or any other layer. You need to load everything on your own in that class.)
        * Session.php (The DianaPHP's session handling class.)
      * ExceptionView.php (A class to pretty print a stack trace, whenether an exeption occured. This is just a tool, use it whenever you want to.)
  * config.php (The main config file for your project)
  * config.dist.php (The main config file template. Copy this to config.php and make your settings.)
* vendor (composer related 3rd party libraries)
* web (the basic folder for the assets. You can only place assets there)
  * css (css files are placed here.)
  * js (JavaScript files are placed here.)
  * lib (JavaScript librariers are placed here. Create a folder for each JavaScript library, that has more than one file inside.)
    * ext (JavaScript single file libraries are placed here.)
* .htacess (The apache rewrite rules are placed here)
* composer.json (Our composer configuration file)
* index.php (The main entry point for every request)

## Conventions
### Model conventions
See the entry above for defining Models
### folder structure
As desribed above, the folder structure is als a convention!
### Code conventions
We will soon change to PSR compatible code conventions. The differences for now are:
* No need for yoda style conditions. We don't believe that assinging a variable in if condition may break your application. This is bad style, so you shuld not do this in general.
* open and closing curley braces always have their own lines
* make an empty line before each return statement
* make one empty line before and after each if/elseif/else block
* use datatype prefixes on each simple datatype variables (i for integer, ar for array, s for string (both, class and simple type))
* use curley braced namespaces
* each protected and private mehtod or class member sould start with an underscore
* close php in each file
* use only one class per file (PSR-4 compliant)


## help wanted
### known issues (not fixed, yet)
- [ ] the Textfield helper does not work always. It sometimes causes a blank page.
- [ ] We could not figure out a method for using our own gzip handler callback function. We need this, if we want to use a template engine in future.

## open issues
- [ ] A console task runner interface should be added
- [ ] There are some issues with database data types. Not all databases have support for date and time in one column etc. Maybe we should the Doctrine DBAL there.
- [ ] YAML file support, to get the rid of plain PHP config implementation
- [ ] ORM migration support
- [ ] complete Framework documentation
- [ ] API reference (generated from code comments)
- [ ] Prefix all classes, files etc. with the author, since, version, etc. code comments
- [ ] reformat code to match to the PSR (or Symfony) conventions
- [ ] Add a profiler (which will be late binded to the view using output buffer). By adding a profiler, we also need environments. So that we can define which environment should show the profiler.
- [ ] Add ORM Cache (query cache) with "strategies": Simple (=file), In-Memory (Redis)
- [ ] Use the String class more as an internal data type. For now, we found no way to resolve it global namespace, but have it also namespaced under our folder

## Other points
We also have no homepage for the moment. It will come soon. So, if you have questions, issues, feel free to use the issue board on this repo.

### Contributing
By contributing to that project you agree, that every file made from you is also licensed under the MIT license.
