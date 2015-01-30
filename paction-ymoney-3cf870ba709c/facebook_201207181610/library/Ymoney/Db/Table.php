<?php
class Ymoney_Db_Table extends Zend_Db_Table_Abstract {
    protected $_tablePrefix = 'ym_';

    protected function _setupTableName()
    {
        $this->_name = $this->_tablePrefix . $this->_name;
        
        parent::_setupTableName();
    }

    public function getTableName() 
    {
        return $this->_name;
    }

    protected function _setupPrimaryKey()
    {
        if(!$this->_primary) {
            $this->_primary = 'id';
        }
        parent::_setupPrimaryKey();
    }

    public function  insert(array $data) {
        parent::insert($data);
        return $this->getAdapter()->lastInsertId($this->_name);
    }
    
    public function countRows($where = null)
    {
        $sql = 'SELECT COUNT(*) FROM `' . $this->_name . '`';
        if ($where) {
            $sql .= ' WHERE ' . $where;
        }
        
        $count = (int)$this->getAdapter()->query($sql)->fetchColumn();
        
        return $count;
    }
    
    public function getFoundRows() 
    {
        $select = new Zend_Db_Select($this->getAdapter());
        return $this->getAdapter()
                ->fetchOne($select->from(null, new Zend_Db_Expr('FOUND_ROWS()')));
    }
}