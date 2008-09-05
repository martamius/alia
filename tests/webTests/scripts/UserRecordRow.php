<?php
/**
 * This file was generated by the Alia Toolkit. For more information, please see alia.sourceforge.net
 */



/**
 * UserRecordRow class
 * 
 * A widget for manipulating a User object.
 * @uses AWidget
 */
class UserRecordRow extends AWidget{
	static $recordClass='User';
	
	/**
	 * The records primary key. This is used to repopulate the widget on wakeup 
	 * 
	 * @var mixed
	 * @access private
	 */
	private $primaryKey=null;

	
	/**
	 * the AValidator object used for validation 
	 * 
	 * @var AValidator
	 * @access private
	 */
	private $validator;


	/**
	 * The actual doctrine record object that will be viewed/edited by this widget 
	 * 
	 * @var mixed
	 */
	private $record=null;




	function __construct($_record=null){
		if(!$_record){
			$_record = new User();
		}
		$this->record =$_record;
		$this->primaryKey=$_record->identifier();
		parent::__construct();
		
		//define our saveClicked signal
		$this->defineSignal("saveClicked");
		
		//instantiate the validator
		$this->validator = new AValidator();


		$this->buildLayout();
		$this->setupConnections();
		$this->validator->sendJScript();
	}



	/**
	 * Magic method is executed whenever the object is unserialized. 
	 * 
	 * @access protected
	 * @return void
	 */
	function __wakeup(){
		if($this->primaryKey){
			//we have to re-fetch the record on wakeup because doctrine doesn't serialize relationships
			$this->record = Doctrine::getTable("User")->find($this->primaryKey);
		}
		if(!$this->record){
			$this->record = new User();
		}
	}
	
	function __sleep(){
		return array("validator","primaryKey");
	}
	
	
	/**
	 * builds the layout and widgets that go in it. 
	 * 
	 * @access public
	 * @return void
	 */
	function buildLayout(){
		$layout = new AHTMLLayout($this,"/templates/UserRecordRowEdit.tpl");
		$this->setLayout($layout);
		$x=0;


		//The 'id' widget
		$widget=new ALineEdit($this->record->id);
		$widget->setAttribute("maxlength",20);
		$widget->addAttribute("name")->setAttribute("name","id");
		$layout->addWidget($widget, "id");
		$this->validator->add($x++,"int",null,$widget->getAttribute("id"));

		//The 'name' widget
		$widget=new ALineEdit($this->record->name);
		$widget->setAttribute("maxlength",30);
		$widget->addAttribute("name")->setAttribute("name","name");
		$layout->addWidget($widget, "name");
		$this->validator->add($x++,"string",array("maxLength"=>30), $widget->getAttribute("id"));

		//The 'username' widget
		$widget=new ALineEdit($this->record->username);
		$widget->setAttribute("maxlength",20);
		$widget->addAttribute("name")->setAttribute("name","username");
		$layout->addWidget($widget, "username");
		$this->validator->add($x++,"string",array("maxLength"=>20), $widget->getAttribute("id"));

		//The 'password' widget
		$widget=new ALineEdit($this->record->password);
		$widget->setAttribute("maxlength",16);
		$widget->addAttribute("name")->setAttribute("name","password");
		$layout->addWidget($widget, "password");
		$this->validator->add($x++,"string",array("maxLength"=>16), $widget->getAttribute("id"));
		
		//add a save button
		$saveButton = new APushButton("Save");
		$layout->addWidget($saveButton,"_saveButton");
	}
	



	/**
	 * sets up the signal and default connection for saving the form. 
	 * 
	 * @access public
	 * @return void
	 */
	function setupConnections(){
		$button=$this->getLayout()->getWidget("_saveButton");
		
		//emit a saveClicked signal when the user hits save. The signal passes all form information to all slots.
		Alia::connect($button,"clicked", null, null, "" . AJScript::emit("saveClicked",$this,array(
			AJScript::formElementValue($this->getLayout()->getWidget("id")),
			AJScript::formElementValue($this->getLayout()->getWidget("name")),
			AJScript::formElementValue($this->getLayout()->getWidget("username")),
			AJScript::formElementValue($this->getLayout()->getWidget("password")))));
		
		//connect the save() method to the saveClicked signal
		Alia::connect($this,"saveClicked",$this,"save");
		
	}


 
	/**
	 * Saves the record to the database
	 *
	 * Called when the user hits save button by default.
	 */
	function save($id,$name,$username,$password){
		$args = func_get_args();
		$result=$this->validator->validate($args);
		if(is_array($result)){
			Alia::sendJScript(AJScript::alert($result[0]->getMessage()));
			return false;
		}
		if($this->record){
			
			$this->record->id=(int)$id;
			$this->record->name=$name;
			$this->record->username=$username;
			$this->record->password=$password;
			$this->record->save();
			$this->primaryKey = $this->record->id;
			echo AJScript::alert("Changes Saved.");
		}
	}


}