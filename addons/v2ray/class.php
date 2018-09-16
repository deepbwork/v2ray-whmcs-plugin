<?php

namespace V2ray;

if (!defined('WHMCS')) {
	die('Hey, bitches!<br /><br />What the fuck are you looking for?');
}
define('__ROOT__', dirname(dirname(dirname(dirname(__FILE__)))) . '/');
define('__V2RAY__', __ROOT__ . 'modules/servers/v2ray/');
require_once __ROOT__ . 'init.php';
if (!class_exists('VUtils')) {
	class VUtils
	{
		private $router;
		private $license;
		private $extended;
		private $code;
		public function __construct($license = '') {
			$this->code = [
				"0" => [],
				"1" => "2.2",
				"2" => "SELECT configoption1,configoption2,configoption3,configoption4,configoption5,configoption6,configoption7,configoption8,configoption9,configoption10,configoption11,configoption12,configoption13 FROM tblproducts WHERE id = ?",
				"3" => "SELECT id,name,ipaddress,hostname FROM tblservers WHERE type = 'v2ray' AND disabled = 0",
				"4" => "SELECT count(*) FROM user",
				"5" => "SELECT sum(u+d) FROM user",
			];
		}
		public function getRouter() {
			return gzinflate($this->getBase64());
		}
		public function getBase64() {
			return base64_decode('yygpKSi20tfPKdYryi8tSS3Sy8gvLinKTErVy0stAQA=');
		}
		public function getCode() {
			return (array) $this->code;
		}
	}
}
if (!class_exists('VExtended')) {
	class VExtended
	{
		public function getConnect($serverID = '', $info = false)
		{
			$serverID = (int) $serverID;
			if (empty($serverID)) {
				throw new \Exception('未定义数据库的 ID 信息，无法获取数据库连接信息');
			}
			list($getData) = \Illuminate\Database\Capsule\Manager::table('tblservers')->where('type', 'v2ray')->where('id', $serverID)->select('name', 'ipaddress', 'hostname', 'username', 'password', 'port')->get();
			if (empty($getData->name) || empty($getData->username)) {
				throw new \Exception('无法取得 ID #' . $serverID . ' 的数据库名或数据库登陆用户名，请刷新当前页面重试');
			}
			else {
				if (!empty($getData->hostname)) {
					$hostname = $getData->hostname;
				}
				else {
					if (!empty($getData->ipaddress)) {
						$hostname = $getData->ipaddress;
					}
					else {
						throw new \Exception('无法取得 ID #' . $serverID . ' 的数据库的连接地址');
					}
				}
				$password = $this->getPassword($getData->password);
				if ($info) {
					return array('database' => $getData->name, 'username' => $getData->username, 'password' => $password, 'hostname' => $hostname, 'port' => empty($getData->port) ? '3306' : $getData->port, $charset = 'UTF8');
				}
				return new VDatabase($getData->name, $getData->username, $password, $hostname, empty($getData->port) ? '3306' : $getData->port, $charset = 'UTF8');
			}
		}
		public function getConfig()
		{
			include __DIR__ . '/config.php';
			if (empty($config)) {
				throw new \Exception('无法取得配置信息，请检查 [ ' . __DIR__ . '/config.php ] 文件中是否存在有效信息');
			}
			return $config;
		}
		public function getLanguage()
		{
			if (empty($GLOBALS['_LANG']['locale'])) {
				throw new \Exception('无法读取 WHMCS 中语言包的 locale 字段，请检查当前 WHMCS 语言包是否正常');
			}
			include __V2RAY__ . 'languages/' . $GLOBALS['_LANG']['locale'] . '.php';
			if (empty($LS_LANG)) {
				throw new \Exception('无法读取语言包文件 [ ' . __V2RAY__ . 'languages/' . $GLOBALS['_LANG']['locale'] . '.php ] 请检查是否语言包权限、名称存在问题或语言包格式不正确');
			}
			return (array) $LS_LANG;
		}
		public function recordLog($log = '')
		{
			$config = $this->getConfig()['recordLog'];
			if (empty($config)) {
				throw new \Exception('未设置日志记录功能');
			}
			else {
				if ($config == 'On') {
					if (empty($log)) {
						throw new \Exception('未定义需要记录的日志内容');
					}
					else {
					    file_put_contents(__V2RAY__.'v2ray.log', $log . "\r\n", FILE_APPEND);
						$values['description'] = 'V2ray: ' . $log;
						$result = localAPI('logactivity', $values, (string) $this->getAdminUser());

						if ($result['result'] == 'success') {
							return true;
						}

						throw new \Exception('日志记录失败');
					}
				}
			}
		}
		public function getWebPage(array $arr)
		{
			if (empty($arr['url'])) {
				throw new \Exception('未定义需要获取内容的网页地址');
			}
			else {
				$curl = curl_init();
				curl_setopt($curl, CURLOPT_URL, $arr['url']);
				if (!empty($arr['post'])) {
					curl_setopt($curl, CURLOPT_POST, true);
					curl_setopt($curl, CURLOPT_POSTFIELDS, $arr['post']);
				}
				curl_setopt($curl, CURLOPT_TIMEOUT, 5);
				curl_setopt($curl, CURLOPT_HEADER, false);
				curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
				$data = curl_exec($curl);
				curl_close($curl);
				if (empty($data)) {
					throw new \Exception('无法取得网页内容，请刷新当前页面重试');
				}
				else {
					if (isset($arr['type'])) {
						switch ($arr['type']) {
							case 'json':
								$json = json_decode($data, true);
								if (empty($json)) {
									throw new \Exception('所请求网页返回的信息并非 JSON 代码');
								} else {
									$result = (array) $json;
								}
								break;
							case 'xml':
								$xml = (array) simplexml_load_string('<?xml version=\'1.0\'?><document>' . $data . '</document>');
								if (empty($xml)) {
									throw new \Exception('所请求网页返回的信息并非 XML 代码');
								} else {
									$result = (array) $xml;
								}
								break;
							default:
								$result = (string) $data;
						}
					}
					else {
						$result = (string) $data;
					}
					return $result;
				}
			}
		}
		public function getPageName()
		{
			$pageName = explode('/', $_SERVER['SCRIPT_NAME']);
			return (string) end($pageName);
		}
		public function sendEmail($email = '', $uid = '')
		{
			$values['id'] = (int) $uid;
			$values['messagename'] = (string) $email;
			localAPI('sendemail', $values, (string) $this->getAdminUser());
		}
		public function productSuspend($productID = '', $suspendReason = 'Overdue payment')
		{
			$values['accountid'] = (int) $productID;
			$values['suspendreason'] = $suspendReason;
			$result = localAPI('modulesuspend', $values, (string) $this->getAdminUser());
			if ($result['result'] == 'success') {
				return true;
			}
			throw new \Exception('产品 ID #' . $productID . ' 暂停失败');
		}
		
		public function v2rayUnsuspend($data = '', $productID = '' ){
			$data = (object) $data;
			$productID = (int) $productID;
			if (empty($data) || empty($productID)) {
				throw new \Exception('未定义数据库连接或需要重置流量的产品 ID 编号');
			}
			$data->runSQL(array(
				'action' => array(
					'user' => array(
						'sql' => 'UPDATE user SET enable=1 WHERE pid = ?',
						'pre' => array($productID)
					)
				)
			));
			return true;
		}

		public function productUnsuspend($productID = '', $suspendReason = 'ModuleUnsuspend')
		{
			$values['accountid'] = (int) $productID;
			$values['suspendreason'] = $suspendReason;
			$result = localAPI('ModuleUnsuspend', $values, (string) $this->getAdminUser());
			if ($result['result'] == 'success') {
				return true;
			}
			throw new \Exception('产品 ID #' . $productID . ' 解除暂停失败');
		}

		public function securityReset($db = '', $data = '', $vars){
          	$data = (object) $data;
			if(empty($vars) || empty($data)) {
				throw new \Exception('无法获取产品信息');
			}

			$data->runSQL(array(
				'action' => array(
					'v2ray' => array(
						'sql' => 'UPDATE user SET v2ray_uuid = ? WHERE pid = ?',
						'pre' => array($this->getRandUUID(), $vars['serviceid'])
					)
				)
			));

			$db->runSQL(array(
				'action' => array(
					'client' => array(
						'sql' => 'UPDATE tblclients SET uuid = ? WHERE id = ?',
						'pre' => array($this->getRandUUID(), $vars['userid'])
					)
				)
			));
          
          	exit(json_encode([
				  'code' => '0',
				  'msg' => 'success'
			  ]));
		}
		public function productReset($data = '', $productID = '')
		{
			$data = (object) $data;
			$productID = (int) $productID;
			if (empty($data) || empty($productID)) {
				throw new \Exception('未定义数据库连接或需要重置流量的产品 ID 编号');
			}
			$data->runSQL(array(
				'action' => array(
					'chart' => array(
						'sql' => 'UPDATE chart SET upload = \'0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0\' , download = \'0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0\' , date = ? WHERE pid = ?',
						'pre' => array(time(), $productID)
					),
					'setting' => array(
						'sql' => 'UPDATE setting SET mail = 1 , date = ? WHERE pid = ?',
						'pre' => array(time(), $productID)
					),
					'user' => array(
						'sql' => 'UPDATE user SET u = 0 , d = 0 WHERE pid = ?',
						'pre' => array($productID)
					)
				)
			));
			return true;
		}
		public function getSystemURL()
		{
			if (empty($GLOBALS['CONFIG'])) {
				$result = \Illuminate\Database\Capsule\Manager::table('tblconfiguration')->where('setting', 'SystemSSLURL')->first()->value;
				if (empty($result)) {
					$result = \Illuminate\Database\Capsule\Manager::table('tblconfiguration')->where('setting', 'SystemURL')->first()->value;
					if (empty($result)) {
						throw new \Exception('无法从数据库中获取 WHMCS 的地址');
					}
				}
			}
			else {
				if (!empty($GLOBALS['CONFIG']['SystemSSLURL'])) {
					$result = $GLOBALS['CONFIG']['SystemSSLURL'] . '/';
				}
				else {
					if (!!($result = $GLOBALS['CONFIG']['SystemURL'])) {
						$result = $GLOBALS['CONFIG']['SystemURL'] . '/';
					}
					else {
						throw new \Exception('无法从全局变量中获取 WHMCS 地址');
					}
				}
			}
			return $result;
		}
		public function getSmarty(array $page)
		{
			if (!is_readable(__ROOT__ . 'templates_c') || !is_writeable(__ROOT__ . 'templates_c')) {
				throw new \Exception('模板缓存目录 [ ' . __ROOT__ . 'templates_c ] 无法读取或写入，请检查目录权限');
			}
			$themeName = \Illuminate\Database\Capsule\Manager::table('tbladdonmodules')->where('setting', 'V2ray_Template')->first()->value;
			if (empty($themeName)) {
				throw new \Exception('无法获取当前主题的名称，请检查是否正确设置 V2ray 模块');
			}
			else {
				if (isset($page['file'])) {
					$smarty = new \Smarty();
					if (isset($page['vars'])) {
						if (is_array($page['vars'])) {
							$smarty->assign($page['vars']);
						}
						else {
							throw new \Exception('已定义的传值字段并非数组');
						}
					}
					isset($page['dir']) ? $dir = $page['dir'] : ($dir = __V2RAY__ . 'templates/' . $themeName . '/');
					$smarty->assign(array('systemurl' => $this->getSystemURL(), 'modulelink' => 'addonmodules.php?module=v2ray', 'template' => $dir));
					if (isset($page['cache']) && $page['cache'] == true) {
						$smarty->caching = true;
					}
					else {
						$smarty->caching = false;
					}
					return (string) $smarty->fetch($dir . $page['file'] . '.tpl');
				}
				throw new \Exception('未定义模板文件');
			}
		}
		public function getDirectory(array $arr)
		{
			empty($arr['dir']) ? $dir = __V2RAY__ : ($dir = $arr['dir'] . '/');
			if (file_exists($dir . $arr['name'])) {
				$result = array();
				$dir = opendir($dir . $arr['name']);
				while (($file = readdir($dir)) !== false) {
					$result[$file] = $file;
				}
				closedir($dir);
				foreach ($result as $key => $value) {
					if (($value == '.') || ($value == '..')) {
						unset($result[$key]);
					}
				}
				return (array) $result;
			}
			throw new \Exception('文件夹 [ ' . $dir . $arr['name'] . ' ] 不存在，请检查后重试');
		}
		public function getRand()
		{
			return (string) substr(md5(time() . rand(0, 10)), 0, 8);
		}
		public function getRandUUID()
		{
		    $str = md5(uniqid(mt_rand(), true));   
            $uuid  = substr($str,0,8) . '-';   
            $uuid .= substr($str,8,4) . '-';   
            $uuid .= substr($str,12,4) . '-';   
            $uuid .= substr($str,16,4) . '-';   
            $uuid .= substr($str,20,12);   
            return $prefix . $uuid;
		}
		public function getAdminUser()
		{
			$getInfo = \Illuminate\Database\Capsule\Manager::table('tbladmins')->select('username')->first();
			$adminUser = (string) $getInfo->username;
			if (empty($adminUser)) {
				throw new \Exception('无法获取 WHMCS 管理员名称信息');
			}
			else {
				return $adminUser;
			}
		}
		public function getPassword($password = '', $decrupt = true)
		{
			if (empty($password)) {
				throw new \Exception('未定义需要处理的密码内容');
			}
			else {
				$values['password2'] = $password;
				$decrupt ? $action = 'decryptpassword' : ($action = 'encryptpassword');
				$result = localAPI($action, $values, (string) $this->getAdminUser());
				if (empty($result)) {
					$decrupt ? $action = '解码' : ($action = '编码');
					throw new \Exception('密码' . $action . '失败');
				}
				else {
					return (string) $result['password'];
				}
			}
		}
	}
}
if (!class_exists('VDatabase')) {
	class VDatabase
	{
		private $pdo;
		public function __construct($database = '', $username = '', $password = '', $hostname = 'localhost', $port = 3306)
		{
			$database = (string) $database;
			$username = (string) $username;
			$password = (string) $password;
			$hostname = (string) $hostname;
			$port = (int) $port;
			if (empty($username)) {
				empty($GLOBALS['db_port']) ? $port = 3306 : ($port = $GLOBALS['db_port']);
				$this->pdo = new \PDO('mysql:dbname=' . $GLOBALS['db_name'] . ';host=' . $GLOBALS['db_host'] . ';charset=utf8;port=' . $port, $GLOBALS['db_username'], $GLOBALS['db_password']);
			}
			else {
				$this->pdo = new \PDO('mysql:dbname=' . $database . ';host=' . $hostname . ';charset=utf8;port=' . $port, $username, $password);
			}
		}
		public function runSQL(array $action)
		{
			if (empty($action['trans'])) {
				$trans = true;
			}
			else {
				if ($action['trans']) {
					$trans = true;
				}
				else {
					$trans = false;
				}
			}
			try {
				if ($trans) {
					$this->pdo->beginTransaction();
				}
				$result = array();
				foreach ($action['action'] as $key => $value) {
					if (empty($value['sql'])) {
						throw new \Exception('语法错误，必须传入一个数组，且包含 [ sql ] 键值');
					}
					$sql = $this->pdo->prepare($value['sql']);
					isset($value['pre']) ? $sql->execute($value['pre']) : $sql->execute();
					isset($value['all']) ? $sqlfetch = $sql->fetchAll(\PDO::FETCH_ASSOC) : ($sqlfetch = $sql->fetch(\PDO::FETCH_ASSOC));
					$result[$key]['rows'] = $sql->rowCount();
					if (!empty($sqlfetch)) {
						$result[$key]['result'] = $sqlfetch;
					}
				}
				if ($trans) {
					$this->pdo->commit();
				}
				return $result;
			}
			catch (\Exception $e) {
				if ($trans) {
					$this->pdo->rollBack();
				}
				throw new \Exception($e->getMessage());
			}
		}
		public function putSQL(array $action)
		{
			if (empty($action['sql'])) {
				throw new \Exception('未定义需要导入的数据库文件');
			}
			empty($action['dir']) ? $dir = __DIR__ . '/data/' : ($dir = $action['dir'] . '/');
			$file = $dir . $action['sql'] . '.sql';
			if (file_exists($file) && is_readable($file)) {
				$file = file_get_contents($file);
				$file = preg_replace('/--.*/i', '', $file);
				$file = preg_replace('/\\/\\*.*\\*\\/(\\;)?/i', '', $file);
				$file = explode(";\n", $file);
				foreach ($file as $value) {
					$value = trim($value);
					if (empty($value)) {
						continue;
					}
					$sql[] = $value;
				}
				if (empty($sql)) {
					throw new \Exception('需要导入的文件里面没有 SQL 语句');
				}
				foreach ($sql as $key => $value) {
					try {
						$this->runSQL(array(
						'action' => array(
						'put' => array('sql' => $value)
						)
						));
					}
					catch (\Exception $e) {
						throw new \Exception('导入 SQL 时，第 ' . $key . ' 行出现错误，错误信息: ' . $e->getMessage());
					}
				}
			}
			else {
				throw new \Exception('需要导入的 SQL 文件 [ ' . $file . '] 不存在或无法读取，请检查是否无访问权限');
			}
			return $this->pdo->exec($sql);
		}
		public function checkTable($tableName = '')
		{
			$tableName = (string) $tableName;
			if (empty($tableName)) {
				throw new \Exception('未定义需要查询的数据表名称');
			}
			$getData = $this->runSQL(array(
				'action' => array(
					'table' => array(
						'sql' => 'SHOW TABLES LIKE ?',
						'pre' => array($tableName)
					)
				),
				'trans' => false
			));
			if (current($getData['table']['result']) == $tableName) {
				return true;
			}
			return false;
		}
		public function clearTable()
		{
			if ($this->checkTable('chart') && $this->checkTable('setting') && $this->checkTable('user')) {
				$this->runSQL(array(
					'action' => array(
						'user' => array('sql' => 'TRUNCATE user'),
						'chart' => array('sql' => 'TRUNCATE chart'),
						'setting' => array('sql' => 'TRUNCATE setting')
					)
				));
			}
			else {
				throw new \Exception('格式化数据库需要清空 chart / setting / user 三个数据表，请检查数据库中是否缺少相应的表');
			}
		}
		public function deleteTable($tableName = '')
		{
			if (is_array($tableName)) {
				$sql = '';
				foreach ($tableName as $key => $value) {
					if ($key == 0) {
						$sql .= $value;
					} else {
						$sql .= ', ' . $value;
					}
				}
			}
			else {
				$sql = $tableName;
			}
			$sql = (string) 'DROP TABLE ' . $sql;
			if (empty($sql)) {
				throw new \Exception('已定义的数据表名称为空或并非字符串');
			}
			$this->runSQL(array(
				'action' => array(
					'delete' => array('sql' => $sql)
				)
			));
			if (is_array($tableName)) {
				foreach ($tableName as $value) {
					if ($this->checkTable($value)) {
						throw new \Exception('数据表 [ ' . $value . ' ] 删除失败，请重试操作');
					}
				}
			}
			else {
				if ($this->checkTable($tableName)) {
					throw new \Exception('数据表 [ ' . $tableName . ' ] 删除失败，请重试操作');
				}
			}
		}
	}
}

?>
