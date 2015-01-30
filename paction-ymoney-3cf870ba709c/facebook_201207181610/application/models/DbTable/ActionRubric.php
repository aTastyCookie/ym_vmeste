<?php
class Application_Model_DbTable_ActionRubric extends Ymoney_Db_Table {
	protected $_name = 'action_rubric';
	protected $_unique = 'action_rubric';
	protected $_primary = 'action_id';
	
	public function getActions($rubrics, $action_id, $limit = 3) {
		$rowArr = $result = array ();
		$sql = $this->select ()->from ( $this->getTableName () )->limit ( $limit );
	
		$sql->where ( "rubric_id IN($rubrics) AND action_id != $action_id" );
	
		$rows = $this->fetchAll ( $sql );
		if($rows) $rowArr = $rows->toArray ();
		$c = count($rowArr);
		if($c>0) for($i=0;$i<$c;$i++) $result[] = $rowArr[$i]['action_id'];
		return $result;
	}
	
	public function getRubrics($action_id, $limit = 10) {
		$rowArr = $result = array ();
		$sql = $this->select ()->from ( $this->getTableName () )->limit ( $limit );
	
		$sql->where ( "action_id = $action_id" );
	
		$rows = $this->fetchAll ( $sql );
		if($rows) $rowArr = $rows->toArray ();
		$c = count($rowArr);
		if($c>0) for($i=0;$i<$c;$i++) $result[] = $rowArr[$i]['rubric_id'];
		$result = implode(',', $result);
		return $result;
	}
}