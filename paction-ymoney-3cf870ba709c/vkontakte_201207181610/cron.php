<?php

// Define path to application directory
defined('APPLICATION_PATH')
        || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/application'));

// Define application environment
defined('APPLICATION_ENV')
        || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
            realpath(APPLICATION_PATH . '/library'),
            realpath(APPLICATION_PATH . '/../library'),
            get_include_path(),
        )));

/** Zend_Application */
require_once 'Zend/Debug.php';
require_once 'Zend/Application.php';

function d($o) {
    Zend_Debug::dump($o);
}

// Create application, bootstrap, and run
$application = new Zend_Application(
                APPLICATION_ENV,
                APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap();
// ** Процесс
$config = Zend_Registry::get('appConfig');
$db = Zend_Db::factory($config->resources->db);
Zend_Registry::set('db', $db);
Zend_Db_Table_Abstract::setDefaultAdapter($db);

$modelLoader = new Zend_Application_Module_Autoloader(array(
            'namespace' => 'Application',
            'basePath' => APPLICATION_PATH));

require_once 'Ymoney/Controller/Action.php';
require_once 'Zen/Yandex/Client.php';
require_once 'Ymoney/facebook.php';

Zend_Controller_Action_HelperBroker::addPrefix('Ymoney_Helper');
$actionModel = new Application_Model_DbTable_Action();
$historyModel = new Application_Model_DbTable_History();
$actions = $actionModel->getActionsWithToken();

$refused_actions = array();
$old_actions = array();

$time_start = microtime(1);

$account = array();
$account['account'] = '';

//define('PATH_TO_CERT', dirname($_SERVER['argv'][0]) . '/library/ymoney.pem');
define('PATH_TO_CERT', '/opt/www/vkontakte/library/ymoney.pem');
echo "[" . date('d-m-Y H:i:s') . "] " . "\n|||||||||||||||||||||||||||||\n\n";

foreach ($actions as $action) {
   //print_r($action);
   echo "\n___________________________________\n";

   if (in_array($action['receiver'], $refused_actions)) {
       echo "[" . date('d-m-Y H:i:s') . "] " . "Action [" . $action['id'] . "] - ignore (refused)\n";
       continue;
   }

   if ($action['receiver'] != $account['account']) {
       // Get account and ZenYandexClient oblect
       echo "[" . date('d-m-Y H:i:s') . "] " . "Get account and ZenYandexClient oblect\n";
       $zayac = new ZenYandexClient($action['token']);
       $account = $zayac->getAccountInformation();
       if (is_string($account)) {
           echo "[" . date('d-m-Y H:i:s') . "] " . "Action [" . $action['id'] . "] - token refused\n";
           $refused_actions[] = $action['receiver'];
           continue;
       }
   }

   $ignore = false;

   if (!in_array($action['receiver'], $old_actions)) {


       $old_actions[] = $action['receiver'];

       // Get history for account
       echo "[" . date('d-m-Y H:i:s') . "] " . "History for account historyModel->getHistory({$action['receiver']})\n\n";
       $history = $historyModel->getHistory($action['receiver']);
       $needLoadMore = false;
       $needLoadAll = false;
       if (count($history) == 0) {
           echo "[" . date('d-m-Y H:i:s') . "] " . "-- No history for this account --\n\n";
           $lastOperationIdHistory = 0;
           $needLoadMore = true;
       } else {
           $lastOperationIdHistory = $history[0]['operation_id'];
           echo "[" . date('d-m-Y H:i:s') . "] " . "Last OperationId in History = $lastOperationIdHistory\n\n";
       }

       // Get last 10 operations
       echo "[" . date('d-m-Y H:i:s') . "] " . "Get last 1 operation\n";
       $operations = $zayac->listOperationHistory('deposition', null, 1);

       if (count($operations['operations']) == 0) {
           echo "[" . date('d-m-Y H:i:s') . "] " . "Action [" . $action['id'] . "] - ignore (no operations)\n";
           continue;
       }

       $alloperations = $operations['operations'];

       if ($lastOperationIdHistory == $operations['operations'][0]->getOperationId()) {
           echo "[" . date('d-m-Y H:i:s') . "] " . "Action [" . $action['id'] . "] - ignore (no new)\n";
           $ignore = true;
       } elseif (!$needLoadAll) {
           $needLoadMore = true;
       }

       if ($needLoadAll) {
           while (isset($operations['next_record']) && $operations['next_record'] > 0) {
               $operations = $zayac->listOperationHistory('deposition', $operations['next_record'], 20);
               $alloperations = array_merge($alloperations, $operations['operations']);
           }
       }
       if (!$ignore) {
           // View operations
           foreach ($alloperations as $operation) {
               $OperationId = $operation->getOperationId();
               $DateTime = $operation->getDateTime();
               echo $OperationId;
               if ($lastOperationIdHistory != $OperationId) {
                   echo "[" . date('d-m-Y H:i:s') . "] " . " <- Operation is not in history. It will added.\n";
                   $op = ZenYandexClient::getOperationDetails($OperationId);
                   //print_r($op);
                   if (!isset($op['message']))
                       $op['message'] = '';
                   if (!isset($op['details']))
                       $op['details'] = '';
                   if (!isset($op['title']))
                       $op['title'] = '';
                   if (!isset($op['sender']))
                       $op['sender'] = 'Источник неизвестен';
                   $fields = array(
                       'operation_id' => $OperationId,
                       'amount' => $op['amount'],
                       'datetime' => $DateTime,
                       'title' => $op['title'],
                       'sender' => $op['sender'],
                       'recipient' => $action['receiver'],
                       'message' => $op['message'],
                       'details' => $op['details']
                   );
                   //echo "historyModel->addToHistory\n";
                   //print_r($fields);
                   //echo "\n";
                   $addResult = $historyModel->addToHistory($fields);
                   echo "[" . date('d-m-Y H:i:s') . "] " . "Result of adding: $addResult\n";
               } else {
                   echo "[" . date('d-m-Y H:i:s') . "] " . " <- Operation is in history. It will ignored. Break\n";
                   $needLoadMore = false;
                   break;
               }
               echo "\n";
           }

           if ($needLoadMore) {
               while (isset($operations['next_record']) && $operations['next_record'] > 0 && $needLoadMore) {
                   $operations = $zayac->listOperationHistory('deposition', $operations['next_record'], 5);
                   $alloperations = $operations['operations'];
                   foreach ($alloperations as $operation) {
                       $OperationId = $operation->getOperationId();
                       $DateTime = $operation->getDateTime();
                       echo "[" . date('d-m-Y H:i:s') . "] " . $OperationId;
                       if ($lastOperationIdHistory != $OperationId) {
                           echo " <- Operation is not in history. It will added.\n";
                           $op = ZenYandexClient::getOperationDetails($OperationId);
                           //print_r($op);
                           if (!isset($op['message']))
                               $op['message'] = '';
                           if (!isset($op['details']))
                               $op['details'] = '';
                           if (!isset($op['title']))
                               $op['title'] = '';
                           if (!isset($op['sender']))
                               $op['sender'] = 'Источник неизвестен';
                           $fields = array(
                               'operation_id' => $OperationId,
                               'amount' => $op['amount'],
                               'datetime' => $DateTime,
                               'title' => $op['title'],
                               'sender' => $op['sender'],
                               'recipient' => $action['receiver'],
                               'message' => $op['message'],
                               'details' => $op['details']
                           );
                           //echo "historyModel->addToHistory\n";
                           //print_r($fields);
                           //echo "\n";
                           $addResult = $historyModel->addToHistory($fields);
                           echo "[" . date('d-m-Y H:i:s') . "] " . "Result of adding: $addResult\n";
                       } else {
                           echo "[" . date('d-m-Y H:i:s') . "] " . " <- Operation is in history. It will ignored. Break\n";
                           $needLoadMore = false;
                           break;
                       }
                       echo "\n";
                   }
               }
           }
       } // end Ignore
   }

   $current_sum = 0;
   $count = 0;
   $daycount = 0;
   $daylastcount = 0;
   $weekcount = 0;
   $weeklastcount = 0;
   $week2count = 0;
   $week3count = 0;
   $monthcount = 0;
   $monthlastcount = 0;
   $month2count = 0;

   $AHDate1 = explode(" ", $action['date_start']);
   $AHDate2 = explode(":", $AHDate1[1]);
   if ($AHDate2[0] == '10') {
       $action['date_start'] = $AHDate1[0] . ' 00:00:00';
   }
   $history = $historyModel->getHistory($action['receiver']);

   if ($action['source'] == 'ym') {
       $current_sum = $account['balance'];
       echo "[" . date('d-m-Y H:i:s') . "] " . "action[" . $action['id'] . "][source] = 'ym'. current_sum = $current_sum\n";
       foreach ($history as $operation) {
           $count++;
           $timestamp = $operation['datetime'];

           // This day
           $from = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
           $to = mktime();
           if ($timestamp <= $to && $timestamp >= $from) {
               $daycount++;
           }
           // Last day
           $from = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 86400;
           $to = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
           if ($timestamp < $to && $timestamp >= $from) {
               $daylastcount++;
           }

           // This week
           $today = date('N') - 1;
           $from = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 86400 * $today;
           $to = mktime();
           if ($timestamp <= $to && $timestamp >= $from) {
               $weekcount++;
           }
           // Last week
           $from = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 86400 * $today - 86400 * 7;
           $to = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 86400 * $today;
           if ($timestamp < $to && $timestamp >= $from) {
               $weeklastcount++;
           }
           // 2 weeks
           $from = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 86400 * $today - 86400 * 14;
           $to = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 86400 * $today - 86400 * 7;
           if ($timestamp < $to && $timestamp >= $from) {
               $week2count++;
           }
           // 3 weeks
           $from = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 86400 * $today - 86400 * 21;
           $to = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 86400 * $today - 86400 * 14;
           if ($timestamp < $to && $timestamp >= $from) {
               $week3count++;
           }

           // This month
           $today = date('j') - 1;
           $from = mktime(0, 0, 0, date('n'), 1, date('Y'));
           $to = mktime();
           if ($timestamp <= $to && $timestamp >= $from) {
               $monthcount++;
           }
           // Last month
           $lastmonthdays = date('t', mktime(0, 0, 0, date('n') - 1));
           $from = mktime(0, 0, 0, date('n'), 1, date('Y')) - 86400 * $lastmonthdays;
           $to = mktime(0, 0, 0, date('n'), 1, date('Y'));
           if ($timestamp <= $to && $timestamp >= $from) {
               $monthlastcount++;
           }
           // 2 months
           $lastmonthdays2 = date('t', mktime(0, 0, 0, date('n') - 2)) + $lastmonthdays;
           $from = mktime(0, 0, 0, date('n'), 1, date('Y')) - 86400 * $lastmonthdays2;
           $to = mktime(0, 0, 0, date('n'), 1, date('Y')) - 86400 * $lastmonthdays;
           if ($timestamp <= $to && $timestamp >= $from) {
               $month2count++;
           }
       }
// /*   } elseif ($action['source'] == 'fromdate') {
//        foreach ($history as $operation) {
//            $timestamp = $operation['datetime'];
//            echo "ymfromdate: " . $action['ymfromdate'] . "\n";
//            echo "timestamp: " . date('Y-m-d H:i:s', $timestamp) . "\n";
//            echo "operation[amount]: " . $operation['amount'] . "\n";
//            $ADate = explode(" ", $action['ymfromdate']);
//            $ADate = explode("-", $ADate[0]);
//            if ($ADate[0] == '0000') {
//                $ADate = array(date('Y'), date('m'), date('d'));
//                $action['ymfromdate'] = date('Y') . '-' . date('m') . '-' . date('d');
//            }
//            //echo "timestamp $timestamp >= ".mktime(0, 0, 0, $ADate[1], $ADate[2], $ADate[0])."(".$ADate[2]."-".$ADate[1]."-".$ADate[0].") = ".
//            //($timestamp >= mktime(0, 0, 0, $ADate[1], $ADate[2], $ADate[0]))
//            //."\n";
//            if ($timestamp >= mktime(0, 0, 0, $ADate[1], $ADate[2], $ADate[0])) {
//                $current_sum += $operation['amount'];
//                $count++;
//                // This day
//                $from = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
//                $to = mktime();
//                if ($timestamp <= $to && $timestamp >= $from) {
//                    $daycount++;
//                }
//                // Last day
//                $from = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 86400;
//                $to = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
//                if ($timestamp < $to && $timestamp >= $from) {
//                    $daylastcount++;
//                }
//
//                // This week
//                $today = date('N') - 1;
//                $from = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 86400 * $today;
//                $to = mktime();
//                if ($timestamp <= $to && $timestamp >= $from) {
//                    $weekcount++;
//                }
//                // Last week
//                $from = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 86400 * $today - 86400 * 7;
//                $to = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 86400 * $today;
//                if ($timestamp < $to && $timestamp >= $from) {
//                    $weeklastcount++;
//                }
//                // 2 weeks
//                $from = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 86400 * $today - 86400 * 14;
//                $to = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 86400 * $today - 86400 * 7;
//                if ($timestamp < $to && $timestamp >= $from) {
//                    $week2count++;
//                }
//                // 3 weeks
//                $from = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 86400 * $today - 86400 * 21;
//                $to = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 86400 * $today - 86400 * 14;
//                if ($timestamp < $to && $timestamp >= $from) {
//                    $week3count++;
//                }
//
//                // This month
//                $today = date('j') - 1;
//                $from = mktime(0, 0, 0, date('n'), 1, date('Y'));
//                $to = mktime();
//                if ($timestamp <= $to && $timestamp >= $from) {
//                    $monthcount++;
//                }
//                // Last month
//                $lastmonthdays = date('t', mktime(0, 0, 0, date('n') - 1));
//                $from = mktime(0, 0, 0, date('n'), 1, date('Y')) - 86400 * $lastmonthdays;
//                $to = mktime(0, 0, 0, date('n'), 1, date('Y'));
//                if ($timestamp <= $to && $timestamp >= $from) {
//                    $monthlastcount++;
//                }
//                // 2 months
//                $lastmonthdays2 = date('t', mktime(0, 0, 0, date('n') - 2)) + $lastmonthdays;
//                $from = mktime(0, 0, 0, date('n'), 1, date('Y')) - 86400 * $lastmonthdays2;
//                $to = mktime(0, 0, 0, date('n'), 1, date('Y')) - 86400 * $lastmonthdays;
//                if ($timestamp <= $to && $timestamp >= $from) {
//                    $month2count++;
//                }
//            }
//        }*/
   } elseif ($action['source'] == 'fromapp' || $action['source'] == 'fromdate') {
       echo "From application:\n";
       foreach ($history as $operation) {
           $text = '«' . $action['name'] . '»';
           if (mb_strstr($operation['message'], $text) || mb_strstr($operation['details'], $text)) {
               echo "Текст совпал: " . $text . "\n";
               $current_sum += $operation['amount'];
               $count++;
               $timestamp = $operation['datetime'];

               // This day
               $from = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
               $to = mktime();
               if ($timestamp <= $to && $timestamp >= $from) {
                   $daycount++;
               }
               // Last day
               $from = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 86400;
               $to = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
               if ($timestamp < $to && $timestamp >= $from) {
                   $daylastcount++;
               }

               // This week
               $today = date('N') - 1;
               $from = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 86400 * $today;
               $to = mktime();
               if ($timestamp <= $to && $timestamp >= $from) {
                   $weekcount++;
               }
               // Last week
               $from = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 86400 * $today - 86400 * 7;
               $to = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 86400 * $today;
               if ($timestamp < $to && $timestamp >= $from) {
                   $weeklastcount++;
               }
               // 2 weeks
               $from = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 86400 * $today - 86400 * 14;
               $to = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 86400 * $today - 86400 * 7;
               if ($timestamp < $to && $timestamp >= $from) {
                   $week2count++;
               }
               // 3 weeks
               $from = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 86400 * $today - 86400 * 21;
               $to = mktime(0, 0, 0, date('n'), date('j'), date('Y')) - 86400 * $today - 86400 * 14;
               if ($timestamp < $to && $timestamp >= $from) {
                   $week3count++;
               }

               // This month
               $today = date('j') - 1;
               $from = mktime(0, 0, 0, date('n'), 1, date('Y'));
               $to = mktime();
               if ($timestamp <= $to && $timestamp >= $from) {
                   $monthcount++;
               }
               // Last month
               $lastmonthdays = date('t', mktime(0, 0, 0, date('n') - 1));
               $from = mktime(0, 0, 0, date('n'), 1, date('Y')) - 86400 * $lastmonthdays;
               $to = mktime(0, 0, 0, date('n'), 1, date('Y'));
               if ($timestamp <= $to && $timestamp >= $from) {
                   $monthlastcount++;
               }
               // 2 months
               $lastmonthdays2 = date('t', mktime(0, 0, 0, date('n') - 2)) + $lastmonthdays;
               $from = mktime(0, 0, 0, date('n'), 1, date('Y')) - 86400 * $lastmonthdays2;
               $to = mktime(0, 0, 0, date('n'), 1, date('Y')) - 86400 * $lastmonthdays;
               if ($timestamp <= $to && $timestamp >= $from) {
                   $month2count++;
               }
           }
       }
   }

   $findaction = $actionModel->searchAction($action['id']);
   $fields = array(
       'all_sum' => $current_sum,
       'date_start' => $action['date_start'],
       'ymfromdate' => $action['ymfromdate'],
       'daystat' => $daycount,
       'weekstat' => $weekcount,
       'monthstat' => $monthcount,
       'daylaststat' => $daylastcount,
       'weeklaststat' => $weeklastcount,
       'monthlaststat' => $monthlastcount,
       'week2stat' => $week2count,
       'week3stat' => $week3count,
       'month2stat' => $month2count
   );
   if (($current_sum + $action['all_sum']) >= $action['required_sum'] && $action['required_sum'] > 0) {
       $fields['completed'] = 'Y';
       $fields['top'] = 'N';
   }
   
   $actionModel->update($fields, 'id=' . $action['id']);
   echo "[" . date('d-m-Y H:i:s') . "] " . "Action [" . $action['id'] . "], operations: " . $count . "\n";
}
echo "[" . date('d-m-Y H:i:s') . "] " . "Updated actions: " . count($actions) . "\n";

// Search duplicates of actions
$actionModel->duplicates();

$time_end = microtime(1);
$time = $time_end - $time_start;
echo "Worktime $time sec\n";
