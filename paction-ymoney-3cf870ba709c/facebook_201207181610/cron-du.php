<?php
//ini_set('default_charset', 'UTF-8');

// Define path to application directory
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/application'));

// Define application environment
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

// Ensure library/ is on include_path
/* set_include_path(implode(PATH_SEPARATOR, array(realpath(APPLICATION_PATH . '/library'), get_include_path()))); */
set_include_path(implode(PATH_SEPARATOR, array(realpath(APPLICATION_PATH . '/../library'), get_include_path())));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
                APPLICATION_ENV,
                APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap();
// ** Process
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
$facebook = new Facebook(array(
    'appId' => $config->ymoney->APP_ID,
    'secret' => $config->ymoney->APP_SECRET
));

define('PATH_TO_CERT', '/opt/www/facebook/library/ymoney.pem');
//define('PATH_TO_CERT', dirname($_SERVER['argv'][0]) . '/library/ymoney.pem');

$time_start = microtime(1);

Zend_Controller_Action_HelperBroker::addPrefix('Ymoney_Helper');
$actionModel = new Application_Model_DbTable_Action();
//$actionrubricModel = new Application_Model_DbTable_ActionRubric();
$usersModel = new Application_Model_DbTable_Users();

$sql = $db->query("SELECT DISTINCT u.id FROM ".$usersModel->getTableName()." u INNER JOIN ".$actionModel->getTableName()." ac ON (ac.user_id = u.id) 
		WHERE ac.draft = 'N' AND ac.hidden = 'N' AND ac.hidden = 'N' AND ac.blocked = 'N' AND ac.completed = 'N';");
$rows = $sql->fetchAll();

if (count($rows) > 0) {
	foreach ($rows as $row) {
		$user_id = $row['id'];
		$user = $facebook->api("/$user_id", 'GET');
		//$user = get_headers("http://www.facebook.com/$user_id");
		
		if (isset($user['error']) && isset($user['error']['message']) && $user['error']['message'] == 'Unsupported get request.') {
		//if(strstr($user[0], '404']){
			$sql = $db->query("DELETE FROM ".$usersModel->getTableName()." WHERE id='$user_id';"); 
			$sql->execute();
			/* $sql = $db->query("SELECT id FROM ".$actionModel->getTableName()." WHERE user_id='$user_id';");
			$actions = $sql->fetchAll();
			if (count($rows) > 0) {
				$actions_array = array();
				foreach ($actions as $action) {
					$actions_array[] = $action['id'];
				}
				$actions_array = implode(',', $actions_array);
				$sql = $db->query("DELETE FROM ".$actionrubricModel->getTableName()." WHERE action_id IN ($actions_array);");
				$sql->execute();
			} */
			$sql = $db->query("DELETE FROM ".$actionModel->getTableName()." WHERE user_id='$user_id';");
			$sql->execute();
		}
	}
}

$time_end = microtime(1);
$time = $time_end - $time_start;
echo "Worktime $time sec\n";
