<?php
class Application_Model_DbTable_Pages extends Ymoney_Db_Table
{
    protected $_name = 'pages';
    protected $_primary = 'id';

//    public function getUsers($onlywithtokens = true, $onlywithactions = true)
//    {
//    	$rowArr = array();
//    	$sql = $this->select()
//    	->setIntegrityCheck(false)
//    	->from(array('u' => $this->getTableName()))
//    	->join(array('a' => 'ym_action'), 'u.id = a.user_id', array('receiver'=>'a.receiver'))
//    	->group('u.id');
//    	
//    	if($onlywithactions) {
//    		$sql->where('a.completed = ?', 'N');
//    		$sql->where('a.draft = ?', 'N');
//    		$sql->where('a.blocked = ?', 'N');
//    	}
//    	
//    	if($onlywithtokens) {
//    		$sql->where('u.token IS NOT NULL');
//    	}
//    	$rows = $this->fetchAll($sql);
//        if ($rows) {
//            $rowArr = $rows->toArray();
//        }
//        echo $sql->__toString();
//        return $rowArr;
//    }
}