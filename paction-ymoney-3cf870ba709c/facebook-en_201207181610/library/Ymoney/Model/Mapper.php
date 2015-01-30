<?php

abstract class Ymoney_Model_Mapper
{
    protected $_model;
    protected $_dbTable;
    
    public function __construct()
    {
        $this->setDbTable($this->_dbTable);
    }
    
	public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }
 
    public function getDbTable()
    {
        return $this->_dbTable;
    }
	
	public function find($id)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return;
        }
        $row = $result->current();
        if ($row) {
            $modelItem = new $this->_model($row->toArray());
        }
        
        return $modelItem;
    }
 
    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries   = array();
        foreach ($resultSet as $row) {
            $entries[] = new $this->_model($row->toArray());
        }
        
        return $entries;
    }
}
