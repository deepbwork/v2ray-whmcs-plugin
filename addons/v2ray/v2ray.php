<?php

if (isset($_GET['verify'])) {
	exit($_GET['verify']);
}
require_once __DIR__ . '/class.php';
if (!function_exists('v2ray_activate')) {
	function v2ray_activate()
	{
		try {
			$db = new \V2ray\VDatabase();
			if ($db->checkTable('v2ray_cache') || $db->checkTable('v2ray_setting')) {
				throw new Exception('当前 WHMCS 数据库已存在 V2ray 的数据表，请登录 phpMyAdmin 检查 [ v2ray_cache / v2ray_setting ] 等表名是否存在');
			} else {
				$db->putSQL(array('sql' => 'activate'));
				if (!$db->checkTable('v2ray_cache') || !$db->checkTable('v2ray_setting')) {
					throw new Exception('模块所需要的数据库导入失败，请尝试重新启用模块使模块尝试再次导入');
				}
				$result = array('status' => 'success', 'description' => '模块 [ V2ray Manager ] 启用成功');
			}
		}
    catch (Exception $e) {
			$result = array('status' => 'error', 'description' => '模块 [ V2ray Manager ] 激活失败，错误信息: ' . $e->getMessage());
		}
		return $result;
	}
}
if (!function_exists('v2ray_deactivate')) {
	function v2ray_deactivate()
	{
		try {
			$db = new \V2ray\VDatabase();
			if (!$db->checkTable('v2ray_cache') || !$db->checkTable('v2ray_setting')) {
				throw new Exception('当前 WHMCS 数据库缺少 V2ray 的数据表，请登录 phpMyAdmin 检查 [ v2ray_cache / v2ray_setting ] 等表名是否存在');
			} else {
				$db->deleteTable(array('v2ray_cache', 'v2ray_setting'));
				if ($db->checkTable('v2ray_cache') || $db->checkTable('v2ray_setting')) {
					throw new Exception('模块所对应的数据库删除失败，请登录 phpMyAdmin 手动删除 [ v2ray_cache / v2ray_setting ] 等数据表');
				}
				$result = array('status' => 'success', 'description' => '模块 [ V2ray Manager ] 关闭成功');
			}
		} catch (Exception $e) {
			$result = array('status' => 'error', 'description' => '模块 [ V2ray Manager ] 关闭失败，错误信息: ' . $e->getMessage());
		}
		return $result;
	}
}
if (!function_exists('v2ray_config')) {
	function v2ray_config()
	{
		$result = array(
			'name' => 'V2ray Manager',
			'description' => '基于 WHMCS 的 V2ray 开通 / 销售 / 管理工具，可配合相应的支付接口实现自动化运营<br /><span style="font-size: 12px; color: #888;">Official website: <a href="https://www.v2ray.com/" target="_blank">https://www.v2ray.com/</a></span>',
			'version' => '0.1',
			'author' => 'LegendSock',
			'fields' => array(
				'V2ray_Template' => array(
					'FriendlyName' => '前台主题',
					'Type' => 'dropdown',
					'Options' => array(),
					'Default' => 'LegendSock'
				)
			)
		);
		$ls = new \V2ray\VExtended();
		try {
			$templates = $ls->getDirectory(array('name' => 'templates'));
			foreach ($templates as $key => $value) {
				switch ($value) {
					case 'NeWorld':
						$result['fields']['V2ray_Template']['Options'][$key] = 'NeWorld ( 官方模板 )';
						break;
					case 'Hostribe':
						$result['fields']['V2ray_Template']['Options'][$key] = 'Hostribe ( 官方模板 )';
						break;
					case 'LegendSock':
						$result['fields']['V2ray_Template']['Options'][$key] = 'LegendSock ( 官方模板 )';
						break;
					default:
						$result['fields']['V2ray_Template']['Options'][$key] = $value;
				}
			}
			if (empty($result['fields']['V2ray_Template']['Options'])) {
				throw new Exception('在模板文件夹 [ ' . __V2RAY__ . 'templates ] 下未寻找到可用的模板，请检查文件夹以及文件权限');
			}
		} catch (Exception $e) {
			$result['fields']['V2ray_Template']['Description'] = $e->getMessage();
		}

		return $result;
	}
}

if (!function_exists('v2ray_output')) {
	function v2ray_output($vars)
	{
		try {
			$ls = new \V2ray\VExtended();

			try {
				$utils = new \V2ray\VUtils();
				//$code = new \V2ray\License($license);
				//print_r($code->getCode());die();
				$db = new \V2ray\VDatabase();
				$getServer = $db->runSQL(array(
					'action' => array(
						'server' => array('sql' => $utils->getCode()[3], 'all' => true)
					),
					'trans'  => false
				));

				if (empty($getServer['server']['result'])) {
					throw new Exception('当前 WHMCS 中无 V2ray 后端数据库，请打开 Setup -> Products / Services -> Servers 新建');
				}
				$result = array(
					'file' => 'manager',
					'vars' => array(
						'tips' => 'warning',
						'version' => $vars['version'],
						'message' => '部分功能涉及数据库操作，为避免出错、请小心谨慎的使用 V2ray 控制面板',
						'database' => array('rows' => $getServer['server']['rows']),
						'information' => ''
					)
				);
				$result['vars']['script'] = (string) $ls->getConfig()['script'];
				if ($result['vars']['script'] == 'test') {
					$configFile = __DIR__ . '/config.php';
					if (is_readable($configFile) && is_writeable($configFile)) {
						$configContent = file_get_contents($configFile);
						$configContent = preg_replace('/\'test\'/m', '\'' . substr(md5(time()), 0, 16) . '\'', $configContent);
						$configWrite = file_put_contents($configFile, $configContent);
						if ($configWrite) {
							$result['vars']['tips'] = 'success';
							$result['vars']['message'] = '后端全自动配置功能启用成功，请刷新当前页面后进行其他操作';
						}
            else {
							$result['vars']['tips'] = 'warning';
							$result['vars']['message'] = '配置文件自动修改失败，若需要启用全自动安装功能、请手动修改配置文件 [ ' . __DIR__ . '/config.php ] 中的 script 字段';
						}
					}
          else {
						$result['vars']['tips'] = 'warning';
						$result['vars']['message'] = '系统检测到当前未启用后端自动配置功能，请修改配置文件 [ ' . __DIR__ . '/config.php ] 中的 script 字段以启用';
					}
				}
				$result['vars']['root'] = __ROOT__;
				
				// $result['vars']['verifySite'] = '传奇梭客';
				// $result['vars']['verifySiteType'] = '官方站点';
				// $license = explode('-', $license);
				// switch ($license[0]) {
				// case 'ls':
				// 	$result['vars']['verifySite'] = 'NeWorld';
				// 	$result['vars']['verifySiteType'] = '合作站点';
				// 	break;

				// case 'LS':
				// 	$result['vars']['verifySite'] = '主机部落';
				// 	$result['vars']['verifySiteType'] = '合作站点';
				// 	break;

				// case 'LegendSock':
				// 	$result['vars']['verifySite'] = '传奇梭客';
				// 	$result['vars']['verifySiteType'] = '官方站点';
				// 	break;

				// default:
				// 	throw new Exception('无法识别当前所使用的授权许可编号来自于哪个注册站点');
				// }

				$result['vars']['notice'] = $utils->getCode()[0];
				$result['vars']['update'] = $utils->getCode()[1];
				$result['vars']['productCount'] = 0;
				$result['vars']['trafficCount'] = 0;
				foreach ($getServer['server']['result'] as $key => $value) {
					try {
						$data = $ls->getConnect($value['id']);
						$result['vars']['database']['info'][$value['id']]['database'] = $value['name'];
						$result['vars']['database']['info'][$value['id']]['hostname'] = !empty($value['hostname']) ? $value['hostname'] : $value['ipaddress'];
						if ($result['vars']['database']['info'][$value['id']]['hostname'] == 'localhost' || $result['vars']['database']['info'][$value['id']]['hostname'] == '127.0.0.1') {
							$result['vars']['errorHost'] = true;
						}
						$getProduct = $data->runSQL(array(
							'action' => array(
								'product' => array('sql' => $utils->getCode()[4]),
								'traffic' => array('sql' => $utils->getCode()[5])
							),
							'trans'  => false
						));
						if (empty($getProduct['product']['result'])) {
							$result['vars']['database']['info'][$value['id']]['count'] = 0;
						}
            else {
							$result['vars']['database']['info'][$value['id']]['count'] = current($getProduct['product']['result']);
							$result['vars']['productCount'] = $result['vars']['productCount'] + $result['vars']['database']['info'][$value['id']]['count'];
							$result['vars']['trafficCount'] = $result['vars']['trafficCount'] + current($getProduct['traffic']['result']);
						}
						if ($data->checkTable('chart') && $data->checkTable('setting') && $data->checkTable('user')) {
							$result['vars']['database']['info'][$value['id']]['status'] = true;
						}
            else {
							$result['vars']['database']['info'][$value['id']]['status'] = false;
						}
					}
					catch (Exception $e) {
						$result['vars']['information'] .= $ls->getSmarty(array(
							'file' => 'tips/danger',
							'vars' => array('message' => '数据库 ID: #' . $value['id'] . ' 获取数据时发生错误，错误信息: "<strong style="color: red;">' . $e->getMessage() . '</strong>"')
						));
					}
				}
				$result['vars']['productCountOld'] = \Illuminate\Database\Capsule\Manager::table('v2ray_cache')->where('setting', 'product')->first()->value;
				$result['vars']['trafficCountOld'] = \Illuminate\Database\Capsule\Manager::table('v2ray_cache')->where('setting', 'traffic')->first()->value;
				$result['vars']['apiUrl'] = \Illuminate\Database\Capsule\Manager::table('v2ray_cache')->where('setting', 'apiUrl')->first()->value;
				$lastProduct = \Illuminate\Database\Capsule\Manager::table('v2ray_cache')->where('setting', 'productOld')->first()->value;
				$lastTraffic = \Illuminate\Database\Capsule\Manager::table('v2ray_cache')->where('setting', 'trafficOld')->first()->value;
				$result['vars']['productCount'] = $result['vars']['productCount'] - $lastProduct;
				$result['vars']['trafficCount'] = $result['vars']['trafficCount'] - $lastTraffic;
				if ($result['vars']['productCount'] < 0) {
					$result['vars']['productCount'] = 0;
				}
				if ($result['vars']['trafficCount'] < 0) {
					$result['vars']['trafficCount'] = 0;
				}
				switch ( $_POST['action'] ) {
					case 'converter':
						try {
							foreach ($getServer['server']['result'] as $key => $value) {
								$data = $ls->getConnect($value['id']);
								if ($data->checkTable('v2ray_hour') && $data->checkTable('v2ray_month') && $data->checkTable('user')) {
									$result['vars']['converter']['info'][$value['id']] = $ls->getConnect($value['id'], true);
								}
							}
							$result['vars']['converter']['rows'] = count($result['vars']['converter']['info']);
							if (empty($result['vars']['converter']['rows'])) {
								throw new Exception('仅支持自动转换 LegendSock 1.x 的数据库，当前没有可以提供转换的数据库');
							}
							$result['vars']['tips'] = 'warning';
							$result['vars']['page']['name'] = 'converter';
							$result['vars']['message'] = '当前将会列出可提供转换的数据库，这涉及到数据库操作、请谨慎使用转换器的功能';
						}
            catch (Exception $e) {
							$result['vars']['tips'] = 'danger';
							$result['vars']['message'] = '可转换数据库信息取出失败，错误信息: ' . $e->getMessage();
						}
						break;
					case 'product':
						try {
							$data = $ls->getConnect($_POST['id']);
							$getCount = $data->runSQL(array(
								'action' => array(
									'count' => array('sql' => $utils->getCode()[4])
								),
								'trans'  => false
							));
							if (empty($getCount['count']['result'])) {
								throw new Exception('无法取出数据库 ID #' . $_POST['id'] . ' 的产品数量');
							}
							$count = current($getCount['count']['result']);
							$page_num = $ls->getConfig()['manager']['page'];
							if ($count <= $page_num) {
								$result['vars']['previous'] = false;
								$result['vars']['next'] = false;
							}
              else {
								floor($count / $page_num) <= $_POST['page'] && $_POST['direction'] == 'next' ? $result['vars']['next'] = false : ($result['vars']['next'] = true);
								$result['vars']['previous'] = true;
							}
							switch ($_POST['direction']) {
								case 'previous':
									if ($_POST['page'] == 2) {
										$result['vars']['previous'] = false;
									}
									$start = ($_POST['page'] - 2) * $page_num;
									$result['vars']['page']['number'] = $_POST['page'] - 1;
									break;
								case 'next':
									$start = $_POST['page'] * $page_num;
									$result['vars']['page']['number'] = $_POST['page'] + 1;
									break;
								default:
									$start = 0;
									$result['vars']['page']['number'] = 1;
									$result['vars']['previous'] = false;
							}
							$getData = $data->runSQL(array(
								'action' => array(
								'product' => array('sql' => 'SELECT * FROM user LIMIT ' . $start . ',' . $page_num, 'all' => true)
								),
								'trans' => false
							));
							if (empty($getData['product']['result'])) {
								throw new Exception('无法取出数据库 ID #' . $_POST['id'] . ' 中的产品，请重新打开控制面板尝试');
							}
							$getData['product']['id'] = $_POST['id'];
							$getData['product']['rows'] = $count;
							$result['vars']['product'] = $getData['product'];
							foreach ($result['vars']['product']['result'] as $key => $value) {
								try {
									$getData = $db->runSQL(array(
										'action' => array(
											'info' => array(
												'sql' => 'SELECT userid,domainstatus FROM tblhosting WHERE id = ?',
												'pre' => array($value['pid'])
											)
										),
										'trans' => false
									));
									if (empty($getData['info']['result'])) {
										$result['vars']['product']['result'][$key]['uid'] = 'unknown';
										$result['vars']['product']['result'][$key]['status'] = 'unknown';
										$result['vars']['information'] .= $ls->getSmarty(array(
											'file' => 'tips/danger',
											'vars' => array('message' => '当前 WHMCS 中未搜寻到产品 ID: #' . $value['pid'] . ' 的信息，这有可能是一个黑户')
										));
									}
                  else {
										$result['vars']['product']['result'][$key]['uid'] = $getData['info']['result']['userid'];
										$result['vars']['product']['result'][$key]['status'] = $getData['info']['result']['domainstatus'];
									}
								} catch (Exception $e) {
									$result['vars']['information'] .= $ls->getSmarty(array(
										'file' => 'tips/danger',
										'vars' => array('message' => '产品 ID: #' . $value['pid'] . ' 信息获取失败，错误信息: "<strong style="color: red;">' . $e->getMessage() . '</strong>"')
									));
								}
							}
							$result['vars']['page']['name'] = 'product';
							$result['vars']['message'] = '当前页面将会输出数据库 ID #' . $_POST['id'] . ' 中 user 表的所有产品 ( 包括未在 WHMCS 中注册、但数据库中存在的 )';
						} catch (Exception $e) {
							$result['vars']['information'] .= $ls->getSmarty(array(
								'file' => 'tips/danger',
								'vars' => array('message' => '数据库 ID #' . $_POST['id'] . ' 产品信息获取失败，错误信息: "<strong style="color: red;">' . $e->getMessage() . '</strong>"')
							));
						}
						break;
					case 'notice':
						$getData = $db->runSQL(array(
							'action' => array(
								'notice' => array(
									'sql' => 'SELECT notice FROM v2ray_setting WHERE sid = ?',
									'pre' => array($_POST['id'])
								)
							),
							'trans' => false
						));
						if (empty($getData['notice']['result']['notice'])) {
							$result['vars']['notice'] = '通知 1 | 通知 2 | 通知 N';
						}
            else {
							$result['vars']['notice'] = $getData['notice']['result']['notice'];
						}
						$result['vars']['id'] = $_POST['id'];
						$result['vars']['page']['name'] = 'notice';
						$result['vars']['message'] = '当前页面可编辑产品控制页面的滚动通知信息，默认写一条不滚动、留空则自动隐藏通知';
						break;
					
					case 'apiConfig':
						$getData = $db->runSQL(array(
							'action' => array(
								'cache' => array(
									'sql' => 'SELECT value FROM v2ray_cache WHERE setting = ?',
									'pre' => array('apiUrl')
								)
							),
							'trans' => false
						));
						$result['vars']['apiUrl'] = $getData['cache']['result']['value'];
					case 'node':
						$getData = $db->runSQL(array(
							'action' => array(
								'node' => array(
								'sql' => 'SELECT node FROM v2ray_setting WHERE sid = ?',
								'pre' => array($_POST['id'])
								)
							),
							'trans' => false
						));
						if (empty($getData['node']['result']['node'])) {
							$result['vars']['node'] = "节点 1 | a.xxx.com | 443 | chacha20-poly1305 | 节点 1 描述\n节点 2 | a.xxx.com | 443 | chacha20-poly1305 | 节点 2 描述\n节点 3 | a.xxx.com | 443 | chacha20-poly1305 | 节点 3 描述";
						}
            else {
							$result['vars']['node'] = $getData['node']['result']['node'];
						}
						$result['vars']['id'] = $_POST['id'];
						$result['vars']['page']['name'] = 'node';
						$result['vars']['message'] = '当前页面可编辑产品控制页面的节点信息，为必填项、用于展示节点与生成二维码';
						break;
					case 'resource':
						$getData = $db->runSQL(array(
							'action' => array(
								'resource' => array(
								'sql' => 'SELECT resource FROM v2ray_setting WHERE sid = ?',
								'pre' => array($_POST['id'])
								)
							),
							'trans' => false
						));
						if (empty($getData['resource']['result']['resource'])) {
							$result['vars']['resource'] = "资源 1 | 描述 1 | 链接 1\n资源 2 | 描述 2 | 链接 2\n资源 3 | 描述 3 | 链接 3";
						}
            else {
							$result['vars']['resource'] = $getData['resource']['result']['resource'];
						}
						$result['vars']['id'] = $_POST['id'];
						$result['vars']['page']['name'] = 'resource';
						$result['vars']['message'] = '当前页面可编辑产品控制页面的资源信息，留空则自动隐藏资源下载栏目';
						break;
					case 'init':
						try {
							$data = $ls->getConnect($_POST['id']);
							if ($data->checkTable('chart') || $data->checkTable('setting') || $data->checkTable('user')) {
								throw new Exception('数据库已存在关于 V2ray 的数据表，请登录 phpMyAdmin 进入检查');
							}
							$data->putSQL(array('sql' => 'init'));
							if ($data->checkTable('chart') && $data->checkTable('setting') && $data->checkTable('user')) {
								$result['vars']['tips'] = 'success';
								$result['vars']['message'] = '数据库 ID #' . $_POST['id'] . ' 初始化成功，单击 "回到首页" 或重新打开 V2ray 控制面板即可编辑相应的通知、节点、资源信息';
							}
              else {
								throw new Exception('导入 [ init.sql ] 后数据库中仍然缺少相应的表，请检查 SQL 文件是否与官方一致');
							}
						} catch (Exception $e) {
							$result['vars']['tips'] = 'danger';
							$result['vars']['message'] = '数据库 ID #' . $_POST['id'] . ' 初始化失败，错误信息: ' . $e->getMessage();
						}
						break;
					case 'format':
						try {
							$data = $ls->getConnect($_POST['id']);
							$data->clearTable();
							$result['vars']['tips'] = 'success';
							$result['vars']['message'] = '数据库 ID #' . $_POST['id'] . ' 格式化成功，现在这个数据库相当于一个全新已初始化过的数据库';
						}
            catch (Exception $e) {
							$result['vars']['tips'] = 'danger';
							$result['vars']['message'] = '数据库 ID #' . $_POST['id'] . ' 格式化失败，错误信息: ' . $e->getMessage();
						}
						break;
					case 'submit_converter':
						try {
							$data = $ls->getConnect($_POST['id']);
							$data->deleteTable(array('v2ray_hour', 'v2ray_month'));
							$data->putSQL(array('sql' => 'converter/LegendSock_1.x'));
							if ($data->checkTable('chart') && $data->checkTable('setting') && $data->checkTable('user')) {
								$result['vars']['tips'] = 'success';
								$result['vars']['message'] = '数据库 ID #' . $_POST['id'] . ' 转换成功，单击 "回到首页" 或重新打开 V2ray 控制面板即可编辑相应的通知、节点、资源信息';
							}
              else {
								throw new Exception('导入 [ converter.sql ] 后数据库中仍然缺少相应的表，请检查 SQL 文件是否与官方一致');
							}
						}
            catch (Exception $e) {
							$result['vars']['tips'] = 'danger';
							$result['vars']['message'] = '数据库 ID: #' . $_POST['id'] . ' 数据转换失败，错误信息: "<strong style="color: red;">' . $e->getMessage() . '</strong>"';
						}
						break;
					case 'edit_notice':
						try {
							$getData = $db->runSQL(array(
								'action' => array(
									'notice' => array(
										'sql' => 'SELECT notice FROM v2ray_setting WHERE sid = ?',
										'pre' => array($_POST['id'])
									)
								),
								'trans' => false
							));
							if ($getData['notice']['rows'] == 0) {
								$db->runSQL(array(
									'action' => array(
										'notice' => array(
											'sql' => 'INSERT INTO v2ray_setting(sid,notice) VALUES (?,?)',
											'pre' => array($_POST['id'], $_POST['notice'])
										)
									)
								));
							}
              else {
								$db->runSQL(array(
									'action' => array(
										'notice' => array(
											'sql' => 'UPDATE v2ray_setting SET notice = ? WHERE sid = ?',
											'pre' => array($_POST['notice'], $_POST['id'])
										)
									)
								));
							}
							$result['vars']['tips'] = 'success';
							$result['vars']['message'] = '数据库 ID #' . $_POST['id'] . ' 通知信息修改成功，更新浏览器缓存后、刷新前台客户中心即可查阅';
						}
            catch (Exception $e) {
							$result['vars']['tips'] = 'danger';
							$result['vars']['message'] = '数据库 ID #' . $_POST['id'] . ' 通知信息修改失败，错误信息: ' . $e->getMessage();
						}
						break;
					case 'edit_node':
						try {
							$getData = $db->runSQL(array(
								'action' => array(
									'node' => array(
										'sql' => 'SELECT node FROM v2ray_setting WHERE sid = ?',
										'pre' => array($_POST['id'])
									)
								),
								'trans' => false
							));
							if ($getData['node']['rows'] == 0) {
								$db->runSQL(array(
									'action' => array(
										'node' => array(
											'sql' => 'INSERT INTO v2ray_setting(sid,node) VALUES (?,?)',
											'pre' => array($_POST['id'], $_POST['node'])
										)
									)
								));
							}
              else {
								$db->runSQL(array(
									'action' => array(
										'node' => array(
											'sql' => 'UPDATE v2ray_setting SET node = ? WHERE sid = ?',
											'pre' => array($_POST['node'], $_POST['id'])
										)
									)
								));
							}
							$result['vars']['tips'] = 'success';
							$result['vars']['message'] = '数据库 ID #' . $_POST['id'] . ' 节点信息修改成功，更新浏览器缓存后、刷新前台客户中心即可查阅';
						}
            catch (Exception $e) {
							$result['vars']['tips'] = 'danger';
							$result['vars']['message'] = '数据库 ID #' . $_POST['id'] . ' 节点信息修改失败，错误信息: ' . $e->getMessage();
						}
						break;
					case 'submit_apiConfig':
						try {
							$getData = $db->runSQL(array(
								'action' => array(
									'cache' => array(
										'sql' => 'SELECT value FROM v2ray_cache WHERE setting = ?',
										'pre' => array('apiUrl')
									)
								),
								'trans' => false
							));
							if ($getData['cache']['rows'] == 0) {
								$db->runSQL(array(
									'action' => array(
										'cache' => array(
											'sql' => 'INSERT INTO v2ray_cache(setting,value) VALUES (?,?)',
											'pre' => array('apiUrl', $_POST['url'])
										)
									)
								));
							} else {
								$db->runSQL(array(
									'action' => array(
										'resource' => array(
											'sql' => 'UPDATE v2ray_cache SET value = ? WHERE setting = ?',
											'pre' => array($_POST['url'], 'apiUrl')
										)
									)
								));
							}
							$result['vars']['tips'] = 'success';
							$result['vars']['message'] = 'API信息修改成功，更新浏览器缓存后、刷新即可查阅';
						}
            catch (Exception $e) {
							$result['vars']['tips'] = 'danger';
							$result['vars']['message'] = 'API信息修改失败，错误信息: ' . $e->getMessage();
						}
						break;
					case 'edit_resource':
						try {
							$getData = $db->runSQL(array(
								'action' => array(
									'resource' => array(
										'sql' => 'SELECT resource FROM v2ray_setting WHERE sid = ?',
										'pre' => array($_POST['id'])
									)
								),
								'trans' => false
							));
							if ($getData['resource']['rows'] == 0) {
								$db->runSQL(array(
									'action' => array(
										'resource' => array(
											'sql' => 'INSERT INTO v2ray_setting(sid,resource) VALUES (?,?)',
											'pre' => array($_POST['id'], $_POST['resource'])
										)
									)
								));
							}
              else {
								$db->runSQL(array(
									'action' => array(
										'resource' => array(
											'sql' => 'UPDATE v2ray_setting SET resource = ? WHERE sid = ?',
											'pre' => array($_POST['resource'], $_POST['id'])
										)
									)
								));
							}
							$result['vars']['tips'] = 'success';
							$result['vars']['message'] = '数据库 ID #' . $_POST['id'] . ' 资源信息修改成功，更新浏览器缓存后、刷新前台客户中心即可查阅';
						}
            catch (Exception $e) {
							$result['vars']['tips'] = 'danger';
							$result['vars']['message'] = '数据库 ID #' . $_POST['id'] . ' 资源信息修改失败，错误信息: ' . $e->getMessage();
						}
						break;
					default:
						$result['vars']['page']['name'] = 'home';
						break;
				}
				$result = $ls->getSmarty($result);
			}
      catch (Exception $e) {
				$result = $ls->getSmarty(array(
					'file' => 'tips/danger',
					'vars' => array('message' => $e->getMessage())
				));
			}
			echo $result;
		}
    catch (Exception $e) {
			$errorInfo = strpos($e->getMessage(), 'template_c') ? '因为 [ ' . $GLOBALS['templates_compiledir'] . ' ] 不具备读写权限' : '错误信息: ' . $e->getMessage();
			exit('抱歉，V2ray Manager 启动失败，' . $errorInfo);
		}
	}
}
