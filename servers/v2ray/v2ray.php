<?php

function v2ray_MetaData() {
	return array('DisplayName' => 'V2ray', 'DefaultNonSSLPort' => '3306');
}

function v2ray_ConfigOptions($vars) {
	try {
		list($vars['whmcsVersion']) = explode('.', $vars['whmcsVersion']);
		if ($vars['whmcsVersion'] == 7) {
			throw new Exception('当前正在使用 WHMCS 7，因此将为你输入简化内容');
		}
		$db = new \V2ray\VDatabase();
		$ls = new \V2ray\VExtended();
		try {
			$utils = new \V2ray\VUtils();
			$getData = $db->runSQL(array(
				'action' => array(
					'setting' => array(
						'sql' => $utils->getCode()[2],
						'pre' => array($_GET['id'])
					)
				),
				'trans' => false
			));
			$result = '<div id="LS">';
			$result .= $ls->getSmarty(array('file' => 'setting', 'vars' => $getData['setting']['result']));
		}
		catch (Exception $e) {
			$result = '<div id="LS" style="margin: -15px 0px -30px;">';
			$result .= $ls->getSmarty(array(
				'file' => 'tips/danger',
				'vars' => array('message' => $e->getMessage())
			));
		}
		echo '<script type="text/template" id="html_template">' . $result . '</div></script><script>var html = document.getElementById(\'html_template\').innerHTML;$(document).ready(function(){$("#v2ray + table[class=\'form\']").replaceWith(html);});</script><div id="v2ray"></div>';
		return array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13);
	}
	catch (Exception $e) {
		return array(
			//configoption1
			array(
				'FriendlyName' => '固定流量',
				'Type' => 'text',
				'Size' => '25',
				'Default' => '0'
			),
			//configoption2
			array(
				'FriendlyName' => '流量单位',
				'Type' => 'dropdown',
				'Options' => array(
					'KB' => 'KB',
					'MB' => 'MB',
					'GB' => 'GB',
					'TB' => 'TB'
				),
				'Default' => 'MB'
			),
			//configoption3
			array(
				'FriendlyName' => '流量报表',
				'Type' => 'dropdown',
				'Options' => array(
					'On' => '开启',
					'Off' => '关闭'
				),
				'Default' => 'On'
			),
			//configoption4
			array(
				'FriendlyName' => '用量邮件',
				'Type' => 'dropdown',
				'Options' => array(
					'On' => '开启',
					'Off' => '关闭'
				),
				'Default' => 'On'
			),
			//configoption5
			array(
				'FriendlyName' => '重置日期',
				'Type' => 'dropdown',
				'Options' => array(
					1 => '自然月结',
					2 => '每月首日',
					3 => '不重置'
				),
				'Default' => '0'
			),
			//configoption6
			array(
				'FriendlyName' => '通知信息',
				'Type' => 'textarea',
				'Rows' => '3',
				'Cols' => '25',
				'Default' => ''
			),
			//configoption7
			array(
				'FriendlyName' => '节点信息',
				'Type' => 'textarea',
				'Rows' => '3',
				'Cols' => '25',
				'Default' => ''
			),
			//configoption8
			array(
				'FriendlyName' => '资源信息',
				'Type' => 'textarea',
				'Rows' => '3',
				'Cols' => '25',
				'Default' => ''
			)
		);
	}
}
function v2ray_TestConnection($vars)
{
	try {
		if (!empty($vars['serverhostname'])) {
			$hostname = $vars['serverhostname'];
		}
		else {
			if (!empty($vars['serverip'])) {
				$hostname = $vars['serverip'];
			}
			else {
				throw new Exception('无法取得数据库连接地址');
			}
		}
		$data = new \V2ray\VDatabase('', $vars['serverusername'], $vars['serverpassword'], $hostname, $vars['serverport']);
		$getData = $data->runSQL(array(
			'action' => array(
				'database' => array(
					'sql' => 'SHOW DATABASES',
					'all' => true
				)
			),
			'trans' => false
		));
		if (empty($getData['database']['result'])) {
			throw new Exception('当前的数据库账户下没有可以操作的数据库');
		}
		return array('success' => '账户连接成功');
	}
	catch (Exception $e) {
		logModuleCall('V2ray', explode('_', 'v2ray_TestConnection')[1], $vars, $e->getMessage(), $e->getTraceAsString());
		return array('error' => $e->getMessage());
	}
}
function v2ray_CreateAccount($vars)
{
	try {
		switch ($vars['status']) {
			case 'Pending':
			case 'Terminated':
			case 'Cancelled':
			case 'Fraud':
				$ls = new \V2ray\VExtended();
				$db = new \V2ray\VDatabase();
				$data = $ls->getConnect($vars['serverid']);
				$getData = $data->runSQL(array(
					'action' => array(
						'check' => array(
							'sql' => 'SELECT * FROM user WHERE pid = ?',
							'pre' => array($vars['serviceid'])
						)
					),
					'trans' => false
				));
				if (!empty($vars['configoptions']['traffic'])) {
					$traffic = $vars['configoptions']['traffic'];
				}
				else {
					$traffic = $vars['configoption1'];
				}
				$traffic = $traffic * 1024;
				switch ($vars['configoption2']) {
					case 'MB':
						$traffic = $traffic * 1024;
						break;
					case 'GB':
						$traffic = $traffic * 1048576;
						break;
					case 'TB':
						$traffic = $traffic * 1073741824;
						break;
					default:
						break;
				}
				$v2rayUUID = $ls->getRandUUID();
				$data->runSQL(array(
					'action' => array(
						'chart' => array(
							'sql' => 'INSERT INTO chart(pid,upload,download,date) VALUES (?,\'0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0\',\'0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0\',?)',
							'pre' => array($vars['serviceid'], time())
						),
						'setting' => array(
							'sql' => 'INSERT INTO setting(pid,date) VALUES (?,?)',
							'pre' => array($vars['serviceid'], time())
						),
						'user' => array(
							'sql' => 'INSERT INTO user(pid,v2ray_uuid,transfer_enable) VALUES (?,?,?)',
							'pre' => array($vars['serviceid'], $v2rayUUID, $traffic)
						)
					)
				));
				$db->runSQL(array(
					'action' => array(
						'mark' => array(
							'sql' => 'UPDATE tblhosting SET domain = ? WHERE id = ?',
							'pre' => array($v2rayUUID, $vars['serviceid'])
						)
					)
				));
				return 'success';
			case 'Active':
				throw new Exception('当前产品已经是激活状态，请勿重复开通');
				break;
			case 'Suspended':
				throw new Exception('当前产品为暂停状态，在未终止产品前请勿重复开通');
				break;
			default:
				throw new Exception('未知的产品状态，请刷新当前页面重新操作');
		}
	}
	catch (Exception $e) {
		logModuleCall('V2ray', explode('_', 'v2ray_CreateAccount')[1], $vars, $e->getMessage(), $e->getTraceAsString());
		return $e->getMessage();
	}
}
function v2ray_SuspendAccount($vars)
{
	try {
		$ls = new \V2ray\VExtended();
		$data = $ls->getConnect($vars['serverid']);
		$data->runSQL(array(
			'action' => array(
				'suspend' => array(
					'sql' => 'UPDATE user SET enable = 0 WHERE pid = ?',
					'pre' => array($vars['serviceid'])
				)
			)
		));
		return 'success';
	}
	catch (Exception $e) {
		logModuleCall('V2ray', explode('_', 'v2ray_SuspendAccount')[1], $vars, $e->getMessage(), $e->getTraceAsString());
		return $e->getMessage();
	}
}
function v2ray_UnsuspendAccount($vars)
{
	try {
		$ls = new \V2ray\VExtended();
		$data = $ls->getConnect($vars['serverid']);
		$data->runSQL(array(
			'action' => array(
				'unsuspend' => array(
					'sql' => 'UPDATE user SET enable = 1 WHERE pid = ?',
					'pre' => array($vars['serviceid'])
				)
			)
		));
		return 'success';
	}
	catch (Exception $e) {
		logModuleCall('V2ray', explode('_', 'v2ray_UnsuspendAccount')[1], $vars, $e->getMessage(), $e->getTraceAsString());
		return $e->getMessage();
	}
}
function v2ray_TerminateAccount($vars)
{
	try {
		switch ($vars['status']) {
			case 'Active':
				throw new Exception('该用户服务为激活状态，删除可能导致黑户');
			case 'Suspended':
				$ls = new \V2ray\VExtended();
				$data = $ls->getConnect($vars['serverid']);
				$data->runSQL(array(
					'action' => array(
						'terminate' => array(
							'sql' => 'DELETE FROM user WHERE pid = ?',
							'pre' => array($vars['serviceid'])
						)
					)
				));
				return 'success';
			default:
				throw new Exception('由于产品尚未开通，因此无法为你终止服务');
		}
	}
	catch (Exception $e) {
		logModuleCall('V2ray', explode('_', 'v2ray_TerminateAccount')[1], $vars, $e->getMessage(), $e->getTraceAsString());
		return $e->getMessage();
	}
}
function v2ray_ChangePackage($vars)
{
	try {
		if ($vars['status'] == 'Active') {
			$ls = new \V2ray\VExtended();
			$data = $ls->getConnect($vars['serverid']);
			if (!empty($vars['configoptions']['traffic'])) {
				$traffic = $vars['configoptions']['traffic'];
			}
			else {
				$traffic = $vars['configoption1'];
			}
			$traffic = $traffic * 1024;
			switch ($vars['configoption2']) {
				case 'MB':
					$traffic = $traffic * 1024;
					break;
				case 'GB':
					$traffic = $traffic * 1048576;
					break;
				case 'TB':
					$traffic = $traffic * 1073741824;
					break;
				default:
					break;
			}
			$data->runSQL(array(
				'action' => array(
					'package' => array(
						'sql' => 'UPDATE user SET transfer_enable = ? WHERE pid = ?',
						'pre' => array($traffic, $vars['serviceid'])
					)
				)
			));
			return 'success';
		}
		throw new Exception('产品并非已激活状态，无法更改套餐');
	}
	catch (Exception $e) {
		logModuleCall('V2ray', explode('_', 'v2ray_ChangePackage')[1], $vars, $e->getMessage(), $e->getTraceAsString());
		return $e->getMessage();
	}
}
function v2ray_ResetTraffic($vars)
{
	try {
		if ($vars['status'] == 'Active') {
			$ls = new \V2ray\VExtended();
			$data = $ls->getConnect($vars['serverid']);
			$data->runSQL(array(
				'action' => array(
					'reset' => array(
						'sql' => 'UPDATE user SET enable = 1, u = 0, d = 0  WHERE pid = ?',
						'pre' => array($vars['serviceid'])
					)
				)
			));
			return 'success';
		}
		throw new Exception('产品并非已激活状态，无法重置');
	}
	catch (Exception $e) {
		logModuleCall('V2ray', explode('_', 'v2ray_ResetTraffic')[1], $vars, $e->getMessage(), $e->getTraceAsString());
		return $e->getMessage();
	}
}
function v2ray_AdminCustomButtonArray()
{
	return array('Reset Traffic' => 'ResetTraffic');
}

function v2ray_AdminServicesTabFields($vars)
{
	try {
		switch ($vars['status']) {
			case 'Active':
			case 'Suspended':
				$ls = new \V2ray\VExtended();
				$data = $ls->getConnect($vars['serverid']);
				$getData = $data->runSQL(array(
					'action' => array(
						'user' => array(
							'sql' => 'SELECT * FROM user WHERE pid = ?',
							'pre' => array($vars['serviceid'])
						)
					),
					'trans' => false
				));
				$getAddition = $data->runSQL(array(
					'action' => array(
						'addition' => array(
							'sql' => 'SELECT addition FROM setting WHERE pid = ?',
							'pre' => array($vars['serviceid'])
						)
					),
					'trans' => false
				));
				return array(
					'产品信息' => $ls->getSmarty(array(
						'file' => 'tabfields',
						'vars' => array(
						    'v2ray_uuid' => $getData['user']['result']['v2ray_uuid'],
						    'enable' => $getData['user']['result']['enable'],
							'upload' => $getData['user']['result']['u'],
							'download' => $getData['user']['result']['d'],
							'traffic' => $getData['user']['result']['transfer_enable'],
							'addition' => $getAddition['addition']['result']['addition']
						)
					)
				));
			default:
				break;
		}
	}
	catch (Exception $e) {
		logModuleCall('V2ray', explode('_', 'v2ray_AdminServicesTabFields')[1], $vars, $e->getMessage(), $e->getTraceAsString());
	}
}

function v2ray_ClientArea($vars)
{
	if (empty($_GET['theme'])) {
		if (empty($_SESSION['LS']['theme'])) {
			$themeName = \Illuminate\Database\Capsule\Manager::table('tbladdonmodules')->where('setting', 'V2ray_Template')->first()->value;
			if (empty($themeName)) {
				exit('无法取得前台主题名称，请访问后台 Setup -> Addon Modules 查看 V2ray Manager 的前台主题设置');
			}
		}
		else {
			$themeName = $_SESSION['LS']['theme'];
		}
	}
	else {
		$themeName = $_GET['theme'];
		$_SESSION['LS']['theme'] = $themeName;
	}
	define('__THEME__', 'templates/' . $themeName . '/');
	try {
		if ($vars['status'] == 'Active') {
			$ls = new \V2ray\VExtended();
			$db = new \V2ray\VDatabase();
			$data = $ls->getConnect($vars['serverid']);
			$templates = array();
			$templates['LS_LANG'] = $ls->getLanguage();
			$getData = $data->runSQL(array(
				'action' => array(
					'user' => array(
						'sql' => 'SELECT * FROM user WHERE pid = ?',
						'pre' => array($vars['serviceid'])
					)
				),
				'trans' => false
			));

			if (empty($getData['user']['result'])) {
				throw new Exception('无法从数据库中取得当前产品的信息，请检查产品是否并未处于开通状态');
			}


			if (!empty($_GET['method'])&&$_GET['method']=='securityReset') {
				$ls->securityReset($db, $data, $vars);
			}

			$templates['info'] = $getData['user']['result'];
			$templates['info']['obfs'] = preg_replace('/_compatible/m', '', $templates['info']['obfs']);
			$templates['info']['protocol'] = preg_replace('/_compatible/m', '', $templates['info']['protocol']);
			$server = $db->runSQL(array(
				'action' => array(
					'info' => array(
						'sql' => 'SELECT node,notice,resource FROM v2ray_setting WHERE sid = ?',
						'pre' => array($vars['serverid'])
					),
					'addition' => array(
						'sql' => 'SELECT id FROM tblproducts WHERE servertype = \'legendtraffic\' AND configoption1 = ?',
						'pre' => array($vars['packageid'])
					),
					'hosting' => array(
						'sql' => 'SELECT userid FROM tblhosting WHERE id = ?',
						'pre' => array($vars['serviceid'])
					),
					'setting' => array(
						'sql' => 'SELECT value FROM v2ray_cache WHERE setting = ?',
						'pre' => array('apiUrl')
					)
				),
				'trans' => false
			));
			//getUser
			$user = $db->runSQL(array(
				'action' => array(
					'clients' => array(
						'sql' => 'SELECT uuid,groupid FROM tblclients WHERE id = ?',
						'pre' => array($server['hosting']['result']['userid'])
					)
				),
				'trans' => false
			));
          
			$templates['uuid'] = $user['clients']['result']['uuid'];
			$templates['apiUrl'] = $server['setting']['result']['value'];
			if (!empty($vars['configoption6'])) {
				$templates['notice'] = explode('|', $vars['configoption6']);
			}
			else {
				if (!empty($server['info']['result']['notice'])) {
					$templates['notice'] = explode('|', $server['info']['result']['notice']);
				}
			}
			if (!empty($vars['configoption7'])) {
				$templates['node'] = explode(PHP_EOL, $vars['configoption7']);
			}
			else {
				if (!empty($server['info']['result']['node'])) {
					$templates['node'] = explode(PHP_EOL, $server['info']['result']['node']);
				}
				else {
					throw new Exception('尚未设置节点信息，请登录后台访问 V2ray Manager 查看');
				}
			}
			if (!empty($vars['configoption8'])) {
				$templates['resource'] = explode(PHP_EOL, $vars['configoption8']);
			}
			else {
				if (!empty($server['info']['result']['resource'])) {
					$templates['resource'] = explode(PHP_EOL, $server['info']['result']['resource']);
				}
			}
			if (!empty($server['addition']['result']['id'])) {
				$templates['addition'] = $server['addition']['result']['id'];
			}
			if ($vars['configoption3'] == 'On') {
				$getData = $data->runSQL(array(
					'action' => array(
						'chart' => array(
							'sql' => 'SELECT upload,download,date FROM chart WHERE pid = ?',
							'pre' => array($vars['serviceid'])
						)
					),
					'trans' => false
				));
				$chartUpload = explode(',', $getData['chart']['result']['upload']);
				$chartDownload = explode(',', $getData['chart']['result']['download']);
				
				foreach ($chartUpload as $key => $value) {
					if ($key == 0 || date('j') <= $key) {
						unset($chartUpload[$key]);
						unset($chartDownload[$key]);
					}
				}
				$templates['chart'] = array(
					'upload' => $chartUpload,
					'download' => $chartDownload,
					'date' => $getData['chart']['result']['date']
				);
				
			}
			foreach ($templates['node'] as $key => $value) {
				$value = explode('|', $value);
				$templates['extend'][$key]['v2rayIosUrl'] = "vmess://".base64_encode($value[3].":".$templates['info']['v2ray_uuid']."@".$value[1].":".$value[2]);
    		    $config = [
                    "ps" => $value[0],
                    "add" => $value[1],
                    "port" => $value[2],
                    "id" => $templates['info']['v2ray_uuid'],
                    "aid" => "2",
                    "net" => "tcp",
                    "type" => $value[3],
                    "host" => "",
                    "tls" => (int)$value[5]?"tls":""
                ];
                // var_dump($value[0].'= vmess, '.$value[1].', '.$value[2].', '.$value[3].', "'.$templates['info']['v2ray_uuid'].'", over-tls='.((int)$value[5]?"true":"false").', certificate=1');exit;
                $templates['extend'][$key]['quantumultUrl'] = "vmess://".base64_encode($value[0].'= vmess, '.$value[1].', '.$value[2].', chacha20-ietf-poly1305, "'.$templates['info']['v2ray_uuid'].'", over-tls='.((int)$value[5]?"true":"false").', certificate=1');
    		    $templates['extend'][$key]['v2rayOtherUrl'] = "vmess://".base64_encode(json_encode($config));
			}
			
			$result = array('tabOverviewReplacementTemplate' => __THEME__ . 'client', 'templateVariables' => $templates);
		}
		else {
			throw new Exception('当前产品未处于已激活状态');
		}
	}
	catch (Exception $e) {
		logModuleCall('V2ray', explode('_', 'v2ray_ClientArea')[1], $vars, $e->getMessage(), $e->getTraceAsString());
		switch ($vars['status']) {
			case 'Pending':
				$result = array(
					'tabOverviewReplacementTemplate' => __THEME__ . 'tips/danger',
					'templateVariables' => array('message' => '当前产品尚未开通，请检查账单是否已完成支付、且管理员是否已通过你的订单审核')
				);
				break;
			case 'Active':
				isset($_SESSION['adminid']) ? $result = array(
					'tabOverviewReplacementTemplate' => __THEME__ . 'tips/danger',
					'templateVariables' => array('message' => $e->getMessage())
				) : ($result = array(
						'tabOverviewReplacementTemplate' => __THEME__ . 'tips/danger',
						'templateVariables' => array('message' => '当前产品暂时无法使用，请联系管理员')
				));
				break;
			case 'Suspended':
				$result = array(
					'tabOverviewReplacementTemplate' => __THEME__ . 'tips/danger',
					'templateVariables' => array('message' => '当前产品处于暂停状态，暂停原因: ' . $vars['templatevars']['suspendreason'])
				);
				break;
			case 'Terminated':
				$result = array(
					'tabOverviewReplacementTemplate' => __THEME__ . 'tips/danger',
					'templateVariables' => array('message' => '由于当前产品已终止服务，因此无法为你显示产品信息')
				);
				break;
			case 'Cancelled':
				$result = array(
					'tabOverviewReplacementTemplate' => __THEME__ . 'tips/danger',
					'templateVariables' => array('message' => '由于当前产品已取消服务，因此无法为你显示产品信息')
				);
				break;
			case 'Fraud':
				$result = array(
					'tabOverviewReplacementTemplate' => __THEME__ . 'tips/danger',
					'templateVariables' => array('message' => '当前产品被判定为欺诈订单，请联系管理员')
				);
				break;
			default:
				$result = array(
					'tabOverviewReplacementTemplate' => __THEME__ . 'tips/danger',
					'templateVariables' => array('message' => '未知的产品状态，请联系管理员')
				);
		}
	}
	return $result;
}

define('__V2RAYS__', dirname(dirname(dirname(dirname(__FILE__)))) . '/modules/addons/v2ray/');
require_once __V2RAYS__ . 'class.php';
$ls = new \V2ray\VExtended();
$db = new \V2ray\VDatabase();
switch ($ls->getPageName()) {
	case 'configservers.php':
		if ($_GET['action'] == 'manage') {
			if (isset($_GET['id'])) {
				try {
					$getData = $db->runSQL(array(
						'action' => array(
							'check' => array(
								'sql' => 'SELECT type FROM tblservers WHERE id = ?',
								'pre' => array($_GET['id'])
							)
						),
						'trans' => false
					));
					if ($getData['check']['result']['type'] == 'v2ray') {
						echo $ls->getSmarty(array(
							'file' => 'tips/warning',
							'vars' => array('message' => '为了防止数据库密码被恶意破解，请尽可能为数据库设置较为复杂的密码或随机字符串')
						));
					}
				}
				catch (Exception $e) {
				}
			}
		}
		break;
	case 'configproducts.php':
		if ($_GET['action'] == 'edit') {
			if (isset($_GET['id'])) {
				try {
					$getData = $db->runSQL(array(
						'action' => array(
							'check' => array(
								'sql' => 'SELECT servertype FROM tblproducts WHERE id = ?',
								'pre' => array($_GET['id'])
							)
						),
						'trans' => false
					));
					if ($getData['check']['result']['servertype'] == 'v2ray') {
						echo $ls->getSmarty(array(
							'file' => 'tips/warning',
							'vars' => array('message' => '当前页面为产品设置，如果相应的设置项不知道应该填写什么、那么请保持默认')
						));
					}
				}
				catch (Exception $e) {
				}
			}
		}
		break;
	default:
		break;
}
unset($ls);
unset($db);

?>
