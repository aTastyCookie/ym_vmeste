<?php
class Application_Model_DbTable_Shelpusers extends Ymoney_Db_Table
{
    protected $_name = 'social_help_usersaction';
    protected $_primary = array('user_id', 'action_id');
    
    public function insertuser($user_id, $action_id) {    	
        $sql = $this->select()
                ->from($this->getTableName(), array('COUNT(*) as cnt'))
                ->where('user_id = ?', $user_id)
                ->where('action_id = ?', $action_id);
        $result = $this->fetchRow($sql);
        if($result['cnt'] == 0) {
            $data = array();
            $data['user_id'] = $user_id;
            $data['action_id'] = $action_id;
            $this->insert($data);
        }
    }
    
    public function selectUsers($action_id = NULL) {
        $sql = $this->select()
                ->from($this->getTableName(), array('user_id'))
                ->where('action_id = ?', $action_id);
        $rows = $this->fetchAll($sql);
        if($rows) {
            $rowArr = $rows->toArray();
        }
        
        return $rowArr;
    }
}