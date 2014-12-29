<?php
class Application_Model_DbTable_Usersaction extends Ymoney_Db_Table
{
    protected $_name = 'usersaction';
    protected $_primary = 'id';
    
    public function insertgroup($users, $action_id, $summ) {    	
    	foreach($users as $userrow) {
    		$data = array();
    		$data['user_id'] = $userrow;
    		$data['action_id'] = $action_id;
    		$data['summ'] = $summ;
    		$this->insert($data);
    	}
    	return false;
    }
    
    public function selectfriends($action_id) {
    	$returnArr = array();
    	
    	$sql = $this->select()
    	->from(array('a' => $this->getTableName()))
    	->where('a.action_id = ?', $action_id);
    	
    	$rows = $this->fetchAll($sql);
    	if ($rows) {
    		$rowArr = $rows->toArray();
    		foreach($rowArr as $row){
    			$returnArr[] = $row['user_id'];
    		}
    	}
    	
    	return $returnArr;
    }
}