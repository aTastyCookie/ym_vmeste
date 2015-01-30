<?php

class Application_Model_DbTable_Users extends Ymoney_Db_Table {

    protected $_name = 'users';
    protected $_primary = 'id';

    public function getAdmins() {
        $rowArr = array();
        $sql = $this->select()
                ->setIntegrityCheck(false)
                ->from(array('u' => $this->getTableName()))
                ->where('u.admin = ?', 'Y');

        $rows = $this->fetchAll($sql);
        if ($rows) {
            $rowArr = $rows->toArray();
        }
        return $rowArr;
    }

    public function getUser($search, $search2 = null) {
        $rowArr = array();
        $sql = $this->select()
                ->from(array('u' => $this->getTableName()))
                ->where("u.id = '$search' OR u.url LIKE '$search' OR (u.firstname LIKE '$search' AND u.lastname LIKE '$search2')");

        $rows = $this->fetchAll($sql);
        if (count($rows) == 1) {
            return $rows;
        }
        else
            return false;
    }

}