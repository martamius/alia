<?php
class DefaultWidget extends WidgetGenerator{

	public function generate($columns){
	$buffer='<?php
/**
 * This file was generated by the Alia Toolkit. For more information, please see alia.sourceforge.net
 */



/**
 * '.CRUD_WIDGET.' class
 * 
 * A widget for manipulating a '.CRUD_CLASS.' object.
 * @uses AWidget
 */
class '.CRUD_WIDGET.' extends AWidget{
	static $recordClass=\''.CRUD_CLASS.'\';
	
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
		
		$this->defineSignal("saveSuccess");
		$this->defineSignal("saveFail",1);

		if(!$_record){
			$_record = new '.CRUD_CLASS.'();
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
			//we have to re-fetch the record on wakeup because doctrine doesn\'t serialize relationships
			$this->record = Doctrine::getTable("'.CRUD_CLASS.'")->find($this->primaryKey);
		}
		if(!$this->record){
			$this->record = new '.CRUD_CLASS.'();
		}
	}
	
	/**
	 * builds the layout and widgets that go in it. 
	 * 
	 * @access public
	 * @return void
	 */
	function buildLayout(){
		$layout = new AHTMLLayout($this,"/templates/'.CRUD_WIDGET.'Edit.tpl");
		$this->setLayout($layout);
		$x=0;
';
		
foreach($columns as $vals){
	$column=$vals['name'];
	$buffer.="\n\n".'		//'."The '$column' widget";
	switch($vals['type']){
		case 'integer':
			$buffer.="\n".'		$widget=new ALineEdit($this->record->'.$column.');';
			$buffer.="\n".'		$widget->setAttribute("maxlength",'.$vals['length'].');';
			$buffer.="\n".'		$widget->addAttribute("name")->setAttribute("name","'.$column.'");';
			$buffer.="\n".'		$layout->addWidget($widget, "'.$column.'");';
			$buffer.="\n".'		$this->validator->add($x++,"int",null,$widget->getAttribute("id"));';
			break;
		case 'string':
			$buffer.="\n".'		$widget=new ALineEdit($this->record->'.$column.');';
			$buffer.="\n".'		$widget->setAttribute("maxlength",'.$vals['length'].');';
			$buffer.="\n".'		$widget->addAttribute("name")->setAttribute("name","'.$column.'");';
			$buffer.="\n".'		$layout->addWidget($widget, "'.$column.'");';
			$buffer.="\n".'		$this->validator->add($x++,"string",array("maxLength"=>'.$vals['length'].'), $widget->getAttribute("id"));';

			
			break;
		default:
			$buffer.="\n\n".'		$layout->addWidget(new ALineEdit($this->record->'.$column.'), "'.$column.'");';
			break;
	}
}

$buffer.='
		
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
		Alia::connect($button,"clicked", null, null, "" . AJScript::emit("saveClicked",$this,array(';
	$x=0;
	foreach($columns as $vals){
	$column = $vals['name'];
	if($x++)$buffer.=',';
$buffer.="\n\t\t\t".'AJScript::formElementValue($this->getLayout()->getWidget("'.$column.'"))';
}
$buffer.=')));
		
		//connect the save() method to the saveClicked signal
		Alia::connect($this,"saveClicked",$this,"save");
		
	}


 
	/**
	 * Saves the record to the database
	 *
	 * Called when the user hits save button by default.
	 */
	function save(';
	$x=0;
foreach($columns as $vals){
	$column = $vals['name'];
	if($x++)$buffer.=',';
	$buffer.='$'.$column;
}
$buffer.='){
		$args = func_get_args();
		$result=$this->validator->validate($args,AValidator::IGNORE_MISSING_KEYS);
		if(is_array($result)){
			$this->emit("saveFail", $result);
			return false;
		}
		if($this->record){
			';
foreach($columns as $vals){
	$column = $vals['name'];
	switch($vals['type']){
		case 'integer':
			$buffer.="\n".'			$this->record->'.$column.'=(int)$'.$column.';';
			break;
		default:
			$buffer.="\n".'			$this->record->'.$column.'=$'.$column.';';
	}
}

$buffer.='
			$this->record->save();';
			if($key=getPrimaryKey($columns)){
				$buffer.="\n".'			$this->primaryKey = $this->record->'.$key.';'."\n";
			}
$buffer.='			$this->emit("saveSuccess");
		}
	}


}
';

		return $buffer;	
	
	}
}


