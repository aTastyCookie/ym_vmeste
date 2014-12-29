<?php
class Application_Model_DbTable_History extends Ymoney_Db_Table
{
    protected $_name = 'history';
    protected $_primary = 'id';
    protected $_index = 'recipient';

    public function getHistory($recipient)
    {
    	//Zend_Debug::dump($recipient, 'recipient', true);
    	$rowArr = array();
    	if($recipient) {
	    	$sql = $this->select()
	    	->setIntegrityCheck(false)
	    	->from($this->getTableName());
	    	
	    	$sql->where('recipient = ?', $recipient);
	    	$sql->order('datetime DESC');
	    	//echo $sql->__toString(); exit;
	    	$rows = $this->fetchAll($sql);
	        if ($rows) {
	            $rowArr = $rows->toArray();
	        }
    	}
        return $rowArr;
    }
    
    public function addToHistory($fields) 
    {
    	return $this->insert($fields);
    }
}