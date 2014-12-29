<?php

class Application_Model_DbTable_Action extends Ymoney_Db_Table {

    protected $_name = 'action';
    protected $_primary = 'id';

    public function getActions($user_id = null, $top = null, $drafts = null, $new = null,
            $search = null, $hidden = null, $blocked = null, $pop = null, $limit = null,
            $offset = null, $onlyuseractions = null, $completed = null, $group = null,
            $asPages = null, $onlyLink = null) {

        $rowArr = array();

        $sql = $this->select()
                ->setIntegrityCheck(false)
                ->from(array('a' => $this->getTableName()))
                ->joinInner(array('u' => 'ym_users'), 'u.id = a.user_id', array('Username' => 'u.name', 'Firstname' => 'u.firstname', 'Lastname' => 'u.lastname', 'Userurl' => 'u.url'))
                ->joinLeft(array('p' => 'ym_pages'), 'p.id = a.page_id', array('Pid' => 'p.page_id', 'Pagename' => 'p.name', 'Pagelink' => 'p.url'))
                ->group('a.id');


        if ($user_id && !$onlyuseractions) {
            if ($group == 2) {
                $sql->joinLeft(array('au' => 'ym_usersaction'), 'au.action_id = a.id', array());
                $sql->where(" (a.user_id = '$user_id' OR au.user_id = '$user_id') ");
            } else {
                $sql->where('a.user_id = ?', $user_id);
            }

            if (!$drafts) {
                $sql->where('a.draft = ?', 'N');
            } else {
                $sql->where('a.draft = ?', 'Y');
            }
            if (!$blocked) {
                $sql->where('a.blocked = ?', 'N');
            } else {
                $sql->where('a.blocked = ?', 'Y');
            }
        } elseif ($user_id && $onlyuseractions) {
            $sql->where('a.user_id = ?', $user_id);
            $sql->where('a.page_id IS NULL');
        }

        if ($group == 1) {
            $sql->where('a.group = ?', 1);
        } elseif ($group == 2) {
            
        } else {
            $sql->where('a.group = ?', 0);
        }

        if ($hidden) {
            $sql->where('a.hidden = ?', 'Y')
                    ->where('a.blocked = ?', 'N');
        }
        if ($blocked) {
            $sql->where('a.hidden = ?', 'N')
                    ->where('a.blocked = ?', 'Y');
        }

        if (!$blocked && !$hidden && !$user_id && !$drafts && !$completed && !$onlyuseractions) {
            $sql->where('a.completed = ?', 'N')
                    ->where('a.draft = ?', 'N')
                    ->where('a.hidden = ?', 'N')
                    ->where('a.blocked = ?', 'N')
                    ->where('UNIX_TIMESTAMP(a.date_start) <= ?', mktime());
        }

        if ($pop) {
            $sql->order('a.all_sum DESC');
            $sql->order('UNIX_TIMESTAMP(a.date_start) DESC')
                    ->order('UNIX_TIMESTAMP(a.date_modified) DESC')
                    ->where('a.all_sum > 0');
        } else {
            $sql->order('UNIX_TIMESTAMP(a.date_start) DESC')
                    ->order('UNIX_TIMESTAMP(a.date_modified) DESC');
        }

        if ($completed) {
            $sql->where('a.completed = ?', 'Y');
        } else {
            $sql->where('a.completed = ?', 'N');
        }

        if ($drafts) {
            $sql->where('a.draft = ?', 'Y');
        }

        if ($new) {
            $sql->where('a.date_created >= ?', date('Y-m-d H:i:s', (mktime() - 86400)));
        }

        if ($top) {
            $sql->where('a.top = ?', 'Y');
        }

        if ($top || $pop) {
            $sql->where('a.nomain != 1');
        }
        
        if ($search) {
        	$search = implode(",", $search); //var_dump($search); exit;
        	$sql->joinInner(array('r' => 'ym_rubric'), "r.name IN ($search)", array('Rid' => 'r.id'));
        	$sql->joinInner(array('ar' => 'ym_action_rubric'), '(ar.action_id = a.id AND ar.rubric_id = r.id)', array('RAid' => 'ar.rubric_id'));
        }

        if ($asPages) {
            $sql->where('a.page_id IS NOT NULL');
        }
        
        if ($onlyLink) {
            $sql->where('a.only_link = ?', 'Y');
        } else {
            $sql->where('a.only_link = ?', 'N');
        }

        if ($offset) {
            $sql->limit($limit, $offset);
        }

        if ($limit && !$offset) {
            $sql->limit($limit);
        }
        /*echo "<!--";
          echo $sql->__toString();
          echo "-->\n";*/ 
        //echo $sql->__toString()."<br>";
        $rows = $this->fetchAll($sql);
        if ($rows) {
            $rowArr = $rows->toArray();
        }

        return $rowArr;
    }

    public function getAllActions() {
        $rowArr = array();

        $sql = $this->select()
                ->setIntegrityCheck(false)
                ->from(array('a' => $this->getTableName()))
                ->joinInner(array('u' => 'ym_users'), 'u.id = a.user_id', array('Username' => 'u.name', 'Firstname' => 'u.firstname', 'Lastname' => 'u.lastname', 'Userurl' => 'u.url'));

        $rows = $this->fetchAll($sql);
        if ($rows) {
            $rowArr = $rows->toArray();
        }

        return $rowArr;
    }

    public function getAction($id) {
        $rowArr = array();

        $sql = $this->select()
                ->setIntegrityCheck(false)
                ->from(array('a' => $this->getTableName()))
                ->joinInner(array('u' => 'ym_users'), 'u.id = a.user_id', array('Username' => 'u.name', 'Firstname' => 'u.firstname', 'Lastname' => 'u.lastname', 'Userurl' => 'u.url'))
                ->joinLeft(array('p' => 'ym_pages'), 'p.id = a.page_id', array('Pid' => 'p.page_id', 'Pagename' => 'p.name', 'Pagelink' => 'p.url'))
                ->where('a.id = ?', $id)
                ->limit(1);

        $rows = $this->fetchAll($sql);
        if ($rows) {
            $rowArr = $rows->toArray();
        }

        return $rowArr[0];
    }

    public function getTokenByUser($userid) {
        $result = false;
        if ($userid) {
            $sql = $this->select()
                    ->setIntegrityCheck(false)
                    ->from(array('a' => $this->getTableName()), array('token' => 'a.token'))
                    ->where('a.user_id = ?', $userid)
                    ->where('a.token IS NOT NULL')
                    ->order('UNIX_TIMESTAMP(a.date_modified)')
                    ->limit(1);
//echo $sql->__toString();
            $rows = $this->fetchAll($sql);
            if ($rows) {
                $rowArr = $rows->toArray();
                if (isset($rowArr[0]) && strlen($rowArr[0]['token']) > 5) {
                    $result = $rowArr[0]['token'];
                }
            }
        }
//    	exit;
        return $result;
    }

    public function getActionsWithToken() {
        $rowArr = array();

        $sql = $this->select()
                ->setIntegrityCheck(false)
                ->from(array('a' => $this->getTableName()));

        $sql->where('a.completed = ?', 'N')
                ->where('a.draft = ?', 'N')
                ->where('a.blocked = ?', 'N')
                ->where('a.token IS NOT NULL');

        $rows = $this->fetchAll($sql);
        if ($rows) {
            $rowArr = $rows->toArray();
        }

        return $rowArr;
    }

    public function getThisDayActions() {
        $rowArr = array();
        $sql = $this->select()
                ->from(array('a' => $this->getTableName()));
        $sql->where('a.date_created >= ?', date('Y-m-d 00:00:00'));
        $rows = $this->fetchAll($sql);
        if ($rows) {
            $rowArr = $rows->toArray();
        }
        return $rowArr;
    }

    public function getLastDayActions() {
        $rowArr = array();
        $sql = $this->select()
                ->from(array('a' => $this->getTableName()));
        $sql->where('a.date_created >= ?', date('Y-m-d 00:00:00', (mktime() - 86400)))
                ->where('a.date_created < ?', date('Y-m-d 00:00:00'));
        $rows = $this->fetchAll($sql);
        if ($rows) {
            $rowArr = $rows->toArray();
        }
        return $rowArr;
    }

    public function getThisWeekActions() {
        $rowArr = array();
        $sql = $this->select()
                ->from(array('a' => $this->getTableName()));
        $today = date('N') - 1;
        $sql->where('a.date_created >= ?', date('Y-m-d 00:00:00', (mktime() - 86400 * $today)))
                ->where('a.date_created < ?', date('Y-m-d 00:00:00'));
        $rows = $this->fetchAll($sql);
        if ($rows) {
            $rowArr = $rows->toArray();
        }
        return $rowArr;
    }

    public function getLastWeekActions() {
        $rowArr = array();
        $sql = $this->select()
                ->from(array('a' => $this->getTableName()));
        $today = date('N') - 1;
        $sql->where('a.date_created >= ?', date('Y-m-d 00:00:00', (mktime() - 86400 * $today - 86400 * 7)))
                ->where('a.date_created < ?', date('Y-m-d 00:00:00', (mktime() - 86400 * $today)));
        $rows = $this->fetchAll($sql);
        if ($rows) {
            $rowArr = $rows->toArray();
        }
        return $rowArr;
    }

    public function get2WeekActions() {
        $rowArr = array();
        $sql = $this->select()
                ->from(array('a' => $this->getTableName()));
        $today = date('N') - 1;
        $sql->where('a.date_created >= ?', date('Y-m-d 00:00:00', (mktime() - 86400 * $today - 86400 * 14)))
                ->where('a.date_created < ?', date('Y-m-d 00:00:00', (mktime() - 86400 * $today - 86400 * 7)));
        $rows = $this->fetchAll($sql);
        if ($rows) {
            $rowArr = $rows->toArray();
        }
        return $rowArr;
    }

    public function get3WeekActions() {
        $rowArr = array();
        $sql = $this->select()
                ->from(array('a' => $this->getTableName()));
        $today = date('N') - 1;
        $sql->where('a.date_created >= ?', date('Y-m-d 00:00:00', (mktime() - 86400 * $today - 86400 * 21)))
                ->where('a.date_created < ?', date('Y-m-d 00:00:00', (mktime() - 86400 * $today - 86400 * 14)));
        $rows = $this->fetchAll($sql);
        if ($rows) {
            $rowArr = $rows->toArray();
        }
        return $rowArr;
    }

    public function getThisMonthActions() {
        $rowArr = array();
        $sql = $this->select()
                ->from(array('a' => $this->getTableName()));
        $today = date('j') - 1;
        $sql->where('a.date_created >= ?', date('Y-m-01 00:00:00', (mktime() - 86400 * $today)));
        $rows = $this->fetchAll($sql);
        if ($rows) {
            $rowArr = $rows->toArray();
        }
        return $rowArr;
    }

    public function getLastMonthActions() {
        $rowArr = array();
        $sql = $this->select()
                ->from(array('a' => $this->getTableName()));
        $today = date('j') - 1;
        $sql->where('a.date_created >= ?', date('Y-m-01 00:00:00', (mktime() - 86400 * $today - 86400 * 2)))
                ->where('a.date_created < ?', date('Y-m-01 00:00:00', (mktime() - 86400 * $today)));
        $rows = $this->fetchAll($sql);
        if ($rows) {
            $rowArr = $rows->toArray();
        }
        return $rowArr;
    }

    public function get2MonthActions() {
        $rowArr = array();
        $sql = $this->select()
                ->from(array('a' => $this->getTableName()));
        $today = date('j') - 1;
        $lastmonthdays = date('t', mktime(0, 0, 0, date('n') - 1));
        $sql->where('a.date_created >= ?', date('Y-m-01 00:00:00', (mktime() - 86400 * $today - 86400 * $lastmonthdays - 86400 * 2)))
                ->where('a.date_created < ?', date('Y-m-01 00:00:00', (mktime() - 86400 * $today - 86400 * $lastmonthdays)));
        $rows = $this->fetchAll($sql);
        if ($rows) {
            $rowArr = $rows->toArray();
        }
        return $rowArr;
    }

    public function getThisDayPayments() {
        $rowArr = array();
        $result = 0;
        $sql = $this->select()
                ->from(array('a' => $this->getTableName()), array('payments' => 'daystat'));
        $rows = $this->fetchAll($sql);
        if ($rows) {
            $rowArr = $rows->toArray();
            foreach ($rowArr as $row) {
                $result += $row['payments'];
            }
        }
        return $result;
    }

    public function getLastDayPayments() {
        $rowArr = array();
        $result = 0;
        $sql = $this->select()
                ->from(array('a' => $this->getTableName()), array('payments' => 'daylaststat'));
        $rows = $this->fetchAll($sql);
        if ($rows) {
            $rowArr = $rows->toArray();
            foreach ($rowArr as $row) {
                $result += $row['payments'];
            }
        }
        return $result;
    }

    public function getThisWeekPayments() {
        $rowArr = array();
        $result = 0;
        $sql = $this->select()
                ->from(array('a' => $this->getTableName()), array('payments' => 'weekstat'));
        $rows = $this->fetchAll($sql);
        if ($rows) {
            $rowArr = $rows->toArray();
            foreach ($rowArr as $row) {
                $result += $row['payments'];
            }
        }
        return $result;
    }

    public function getLastWeekPayments() {
        $rowArr = array();
        $result = 0;
        $sql = $this->select()
                ->from(array('a' => $this->getTableName()), array('payments' => 'weeklaststat'));
        $rows = $this->fetchAll($sql);
        if ($rows) {
            $rowArr = $rows->toArray();
            foreach ($rowArr as $row) {
                $result += $row['payments'];
            }
        }
        return $result;
    }

    public function get2WeekPayments() {
        $rowArr = array();
        $result = 0;
        $sql = $this->select()
                ->from(array('a' => $this->getTableName()), array('payments' => 'week2stat'));
        $rows = $this->fetchAll($sql);
        if ($rows) {
            $rowArr = $rows->toArray();
            foreach ($rowArr as $row) {
                $result += $row['payments'];
            }
        }
        return $result;
    }

    public function get3WeekPayments() {
        $rowArr = array();
        $result = 0;
        $sql = $this->select()
                ->from(array('a' => $this->getTableName()), array('payments' => 'week3stat'));
        $rows = $this->fetchAll($sql);
        if ($rows) {
            $rowArr = $rows->toArray();
            foreach ($rowArr as $row) {
                $result += $row['payments'];
            }
        }
        return $result;
    }

    public function getThisMonthPayments() {
        $rowArr = array();
        $result = 0;
        $sql = $this->select()
                ->from(array('a' => $this->getTableName()), array('payments' => 'monthstat'));
        $rows = $this->fetchAll($sql);
        if ($rows) {
            $rowArr = $rows->toArray();
            foreach ($rowArr as $row) {
                $result += $row['payments'];
            }
        }
        return $result;
    }

    public function getLastMonthPayments() {
        $rowArr = array();
        $result = 0;
        $sql = $this->select()
                ->from(array('a' => $this->getTableName()), array('payments' => 'monthlaststat'));
        $rows = $this->fetchAll($sql);
        if ($rows) {
            $rowArr = $rows->toArray();
            foreach ($rowArr as $row) {
                $result += $row['payments'];
            }
        }
        return $result;
    }

    public function get2MonthPayments() {
        $rowArr = array();
        $result = 0;
        $sql = $this->select()
                ->from(array('a' => $this->getTableName()), array('payments' => 'month2stat'));
        $rows = $this->fetchAll($sql);
        if ($rows) {
            $rowArr = $rows->toArray();
            foreach ($rowArr as $row) {
                $result += $row['payments'];
            }
        }
        return $result;
    }

    public function setToken($userid, $token, $account) {
        $result = $this->update(array('token' => $token, 'receiver' => $account), "user_id = $userid");

        return $result;
    }
    
    public function setIdentified($userid, $identified) {
    	$result = $this->update(array('identified' => (int)$identified), "user_id = $userid");
    
    	return $result;
    }

    public function searchAction($id) {
        $rowArr = array();
        $result = 0;
        $sql = $this->select()
                ->from(array('a' => $this->getTableName()))
                ->where('id = ?', $id);

        $rows = $this->fetchRow($sql);
        //if($id==50) { var_dump($rows);exit; }     
        if ($rows) {
            foreach ($rowArr as $row) {
                $result = $row;
            }
        }
        return $result;
    }

    public function duplicates() {
        $rowArr = array();
        $rowsduplArr = array();
        $duplicateindexes = array();
        $sql = $this->select()->from(array('a' => $this->getTableName()));

        $sql->where('a.completed = ?', 'N')
                ->where('a.draft = ?', 'N')
                ->where('a.hidden = ?', 'N')
                ->where('a.blocked = ?', 'N')
                ->where('a.dupl = ?', 0)
                ->where('a.checked = ?', 0);
        $rows = $this->fetchAll($sql);

        $ActionModel = new Application_Model_DbTable_Action();
        if ($rows) {
            $rowArr = $rows->toArray();
            foreach ($rowArr as $row) {
                if (in_array($row['id'], $duplicateindexes))
                    continue;
                $where = array();
                if (strlen($row['name']) > 0)
                    $where[] = "a.name LIKE '%" . $row['name'] . "%'";
                if (strlen($row['description']) > 0)
                    $where[] = "a.description LIKE '%" . $row['description'] . "%'";
                if (strlen($row['url']) > 0)
                    $where[] = "a.url LIKE '%" . $row['url'] . "%'";
                if (strlen($row['video']) > 0)
                    $where[] = "a.video LIKE '%" . $row['video'] . "%'";
                if (count($where) > 0) {
                    $where = implode(' OR ', $where);
                    $where = '(' . $where . ')';
                    //echo $where; exit;
                    $sqldupl = $this->select()->from(array('a' => $this->getTableName()))
                            ->where($where . " AND UNIX_TIMESTAMP(a.date_modified) > UNIX_TIMESTAMP('" . $row['date_modified'] . "')");
                    //echo $sqldupl->__toString()."\n\n"; //exit;
                    $rowsdupl = $this->fetchAll($sqldupl);
                    if ($rowsdupl) {
                        $rowsduplArr = $rowsdupl;
                        foreach ($rowsduplArr as $rowdupl) {
                            echo "Duplicate " . $rowdupl['id'] . " of action " . $row['id'] . "\n";
                            $duplicateindexes[] = $rowdupl['id'];
                            $ActionModel->update(array('dupl' => $row['id']), 'id=' . $rowdupl['id']);
                        }
                    }
                }
            }
        }
    }

    public function getDuplicates($id = null) {
        $rowArr = array();

        $sql = $this->select()
                ->setIntegrityCheck(false)
                ->from(array('a' => $this->getTableName()))
                ->joinInner(array('u' => 'ym_users'), 'u.id = a.user_id', array('Username' => 'u.name', 'Firstname' => 'u.firstname', 'Lastname' => 'u.lastname', 'Userurl' => 'u.url'));
        if ($id) {
            $sql->where('a.dupl = ?', $id);
        } else {
            $sql->where('a.dupl != ?', 0);
            $sql->where('a.checked = ?', 0);
        }

        $rows = $this->fetchAll($sql);
        if ($rows) {
            $rowArr = $rows->toArray();
        }

        return $rowArr;
    }

}