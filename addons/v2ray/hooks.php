<?php
require_once __DIR__ . '/class.php';
add_hook('DailyCronJob', 1, function() {
	try {
		$ls = new \V2ray\VExtended();
		$db = new \V2ray\VDatabase();
		$config = $ls->getConfig();
		$getData = $db->runSQL(array(
			'action' => array(
				'server' => array('sql' => 'SELECT id FROM tblservers WHERE type = \'v2ray\' AND disabled = 0', 'all' => true)
			),
			'trans'  => false
		));
		$cacheTraffic = 0;
		$cacheProduct = 0;
		foreach ($getData['server']['result'] as $serverID) {
			try {
				$data = $ls->getConnect($serverID['id']);
				$getData = $data->runSQL(array(
					'action' => array(
						'user'    => array('sql' => 'SELECT pid,u,d,transfer_enable FROM user', 'all' => true),
						'traffic' => array('sql' => 'SELECT sum(u+d) FROM user')
					),
					'trans'  => false
				));
				if (empty($getData['user']['result'])) {
					throw new Exception('数据库 ID #' . $serverID['id'] . ' 中没有找到产品');
				}
				$cacheTraffic = current($getData['traffic']['result']) + $cacheTraffic;
				$cacheProduct = $getData['user']['rows'] + $cacheProduct;
				foreach ($getData['user']['result'] as $product) {
					try {
						$getData = $db->runSQL(array(
							'action' => array(
								'product' => array(
									'sql' => 'SELECT * FROM tblhosting WHERE id = ?',
									'pre' => array($product['pid'])
								)
							),
							'trans'  => false
						));
						if (empty($getData['product']['result'])) {
							throw new Exception('无法在数据库中找到产品 ID #' . $product['pid'] . ' 的信息');
						}
						$userid = $getData['product']['result']['userid'];
						$hostingInfo = $getData['product']['result'];
						$regdate = explode('-', $getData['product']['result']['regdate']);
						$regdate = end($regdate);
						$getData = $db->runSQL(array(
							'action' => array(
								'setting' => array(
									'sql' => 'SELECT configoption4,configoption5 FROM tblproducts WHERE id = ?',
									'pre' => array($getData['product']['result']['packageid'])
								)
							),
							'trans'  => false
						));
						if (empty($getData['setting']['result'])) {
							throw new Exception('无法在数据库中找到套餐 ID #' . $getData['product']['result']['packageid'] . ' 的信息');
						}
						$today = date('j');
						$dayNumber = date('t');
						switch ($getData['setting']['result']['configoption5']) {
						case 1:
							if ($dayNumber < $regdate) {
								$regdate = $dayNumber;
							}
							if ($regdate == $today) {
								$ls->productReset($data, $product['pid']);
								if($hostingInfo['domainstatus'] === 'Active'){
									$ls->v2rayUnsuspend($data, $product['pid']);
								}
								$ls->recordLog('产品 ID #' . $product['pid'] . ' 已完成月结流量重置');
							}
							break;
						case 2:
							if ($today == 1) {
								$ls->productReset($data, $product['pid']);
								if($hostingInfo['domainstatus'] === 'Active'){
									$ls->v2rayUnsuspend($data, $product['pid']);
								}
								$ls->recordLog('产品 ID #' . $product['pid'] . ' 已完成月结流量重置');
							}
							break;
						default:
							break;
						}
						if ($config['autoSuspend']['switch'] == 'On') {
							if ($product['transfer_enable'] <= $product['u'] + $product['d']) {
								$ls->productSuspend($product['pid'], $config['autoSuspend']['tips']);
								$ls->sendEmail($config['autoSuspend']['mail'], $userid);
							}
						}
						if ($getData['setting']['result']['configoption4'] == 'On') {
							$mail = $config['common']['mail'];
							$mail['value'] = $mail['value'] / 100;
							$percent = 1 - (($product['u'] + $product['d']) / $product['transfer_enable']);
							if ($percent <= $mail['value']) {
								$getData = $data->runSQL(array(
									'action' => array(
										'check' => array(
											'sql' => 'SELECT mail FROM setting WHERE pid = ?',
											'pre' => array($product['pid'])
										)
									),
									'trans'  => false
								));
								if (empty($getData['check']['result'])) {
									$ls->sendEmail($mail['title'], $userid);
									$data->runSQL(array(
										'action' => array(
											'mail' => array(
												'sql' => 'INSERT INTO setting(pid,mail) VALUES(?,1)',
												'pre' => array($product['pid'])
											)
										)
									));
									$ls->recordLog('产品 ID #' . $product['pid'] . ' 已发送流量用量提醒，并成功在 setting 表中新建数据');
								}
								else {
									if ($getData['check']['result']['mail'] == 0) {
										$ls->sendEmail($mail['title'], $userid);
										$data->runSQL(array(
											'action' => array(
												'mail' => array(
													'sql' => 'UPDATE setting SET mail = 1 WHERE pid = ?',
													'pre' => array($product['pid'])
												)
											)
										));
										$ls->recordLog('产品 ID #' . $product['pid'] . ' 已发送流量用量提醒');
									}
								}
							}
						}
						$getData = $data->runSQL(array(
							'action' => array(
								'chart' => array(
									'sql' => 'SELECT upload,download FROM chart WHERE pid = ?',
									'pre' => array($product['pid'])
								)
							),
							'trans'  => false
						));
						if (empty($getData['chart']['result'])) {
							$getData['chart']['result']['upload'] = '0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0';
							$getData['chart']['result']['download'] = '0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0';
							$data->runSQL(array(
								'action' => array(
									'insert' => array(
										'sql' => 'INSERT INTO chart(pid,upload,download,date) VALUES(?,?,?,?)',
										'pre' => array($product['pid'], $getData['chart']['result']['upload'], $getData['chart']['result']['download'], time())
									)
								)
							));
						}
						$upload = explode(',', $getData['chart']['result']['upload']);
						$download = explode(',', $getData['chart']['result']['download']);
						$upload[$today] = $product['u'] - $upload[0];
						$download[$today] = $product['d'] - $download[0];
						if ($upload[$today] < 0) {
							$upload[$today] = 0;
						}
						if ($download[$today] < 0) {
							$download[$today] = 0;
						}
						$upload[0] = $product['u'];
						$download[0] = $product['d'];
						$chartUpload = '';
						$chartDownload = '';
						foreach ($upload as $key => $value) {
							if ($key == 0) {
								$chartUpload .= $product['u'];
							}
							else {
								$chartUpload .= ',' . $value;
							}
						}
						foreach ($download as $key => $value) {
							if ($key == 0) {
								$chartDownload .= $product['d'];
							}
							else {
								$chartDownload .= ',' . $value;
							}
						}
						$data->runSQL(array(
							'action' => array(
								'chart' => array(
									'sql' => 'UPDATE chart SET upload = ? , download = ? , date = ? WHERE pid = ?',
									'pre' => array($chartUpload, $chartDownload, time(), $product['pid'])
								)
							)
						));
						$ls->recordLog('产品 ID #' . $product['pid'] . ' 图表数据已更新');
					}
					catch (Exception $e) {
						$ls->recordLog($e->getMessage());
						continue;
					}
				}
			}
			catch (Exception $e) {
				$ls->recordLog($e->getMessage());
				continue;
			}
		}
		try {
			$getData = $db->runSQL(array(
				'action' => array(
					'traffic'    => array('sql' => 'SELECT value FROM v2ray_cache WHERE setting = \'traffic\''),
					'product'    => array('sql' => 'SELECT value FROM v2ray_cache WHERE setting = \'product\''),
					'trafficOld' => array('sql' => 'SELECT value FROM v2ray_cache WHERE setting = \'trafficOld\''),
					'productOld' => array('sql' => 'SELECT value FROM v2ray_cache WHERE setting = \'productOld\'')
				),
				'trans'  => false
			));
			if (empty($getData['trafficOld']['result']) || empty($getData['productOld']['result'])) {
				$db->runSQL(array(
					'action' => array(
						'oldInfo' => array('sql' => 'INSERT INTO v2ray_cache(setting, value) VALUES (\'trafficOld\', 0), (\'productOld\', 0)')
					)
				));
				$getData['trafficOld']['result']['value'] = 0;
				$getData['productOld']['result']['value'] = 0;
			}
			$todayTraffic = $cacheTraffic - $getData['trafficOld']['result']['value'];
			$todayProduct = $cacheProduct - $getData['productOld']['result']['value'];
			if ($todayTraffic < 0) {
				$todayTraffic = 0;
			}
			if ($todayProduct < 0) {
				$todayProduct = 0;
			}
			$db->runSQL(array(
				'action' => array(
					'traffic'    => array(
						'sql' => 'UPDATE v2ray_cache SET value = ? WHERE setting = \'traffic\'',
						'pre' => array($todayTraffic)
					),
					'product'    => array(
						'sql' => 'UPDATE v2ray_cache SET value = ? WHERE setting = \'product\'',
						'pre' => array($todayProduct)
					),
					'trafficOld' => array(
						'sql' => 'UPDATE v2ray_cache SET value = ? WHERE setting = \'trafficOld\'',
						'pre' => array($cacheTraffic)
					),
					'productOld' => array(
						'sql' => 'UPDATE v2ray_cache SET value = ? WHERE setting = \'productOld\'',
						'pre' => array($cacheProduct)
					)
				)
			));
			$ls->recordLog('当前已缓存昨日产品数量与流量消耗，完成时间为: ' . date('Y-m-d, h:i:s'));
		}
		catch (Exception $e) {
			$ls->recordLog($e->getMessage());
		}
	}
	catch (Exception $e) {
	}
});
?>
