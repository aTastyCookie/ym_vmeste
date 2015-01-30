<?php
class Application_Model_DbTable_Rubric extends Ymoney_Db_Table {
	protected $_name = 'rubric';
	protected $_primary = 'id';
	
	public function getRubrics($key = false, $limit = 5) {
		$rowArr =  $result = array ();
		$sql = $this->select ()->from ( $this->getTableName () )->order ( 'name' )->limit ( $limit );
		
		if ($key)
			$sql->where ( "name LIKE '%$key%'" );
		
		$rows = $this->fetchAll ( $sql );
		if ($rows)
			$rowArr = $rows->toArray ();
		$c = count($rowArr);
		if($c>0) for($i=0;$i<$c;$i++) $result[] = array('label'=>$rowArr[$i]['name'], 'value'=>$rowArr[$i]['name']);
		return $result;
	}
}