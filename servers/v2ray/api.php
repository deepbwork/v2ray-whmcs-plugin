<?php
require(dirname(dirname(dirname(dirname(__FILE__)))).'/init.php');
require('lib/DB.php');
use WHMCS\Database\Capsule;

function getUsers($db){
    $users = $db->get('user');
    $data = [];
    foreach ($users as $user){
        $user['id'] = $user['pid'];
        $user['v2ray_user'] = [
            "uuid" => $user['v2ray_uuid'],
            "email" => sprintf("%s@v2ray.user", $user['v2ray_uuid']),
            "alter_id" => $user['v2ray_alter_id'],
            "level" => $user['v2ray_level'],
        ];
        array_push($data, $user);
    }
    $res = [
        'msg' => 'ok',
        'data' => $data,
    ];

    echo json_encode($res);
}

function addTraffic($db){
    $input = file_get_contents("php://input");
    //file_put_contents('111.txt', json_encode($input));
    $datas = json_decode($input, true);
    foreach ($datas as $data) {
        $user = $db->where('pid', $data['user_id'])->getOne('user');
        $fetchData = [
            't' => time(),
            'u' => $user['u'] + $data['u'],
            'd' => $user['d'] + $data['d'],
            'enable' => $user['u'] + $user['d'] <= $user['transfer_enable']?1:0
        ];
        $result = $db->where('pid', $data['user_id'])->update('user', $fetchData);
    }
    
    $res = [
        "ret" => 1,
        "msg" => "ok",
    ];
    
    echo json_encode($res);
}


if(isset($_GET['databaseName']) && isset($_GET['token'])){
	$databaseName = $_GET['databaseName'];
	$token = $_GET['token'];
	$server = Capsule::table('tblservers')->where('name', $databaseName)->first();
    if($token !== $server->accesshash) {
        die('TOKEN ERROR!!');
    }
	$dbhost = $server->ipaddress ? $server->ipaddress : 'localhost';
	$dbuser = $server->username;
	$dbpass = decrypt($server->password);
	$db = new MysqliDb($dbhost, $dbuser, $dbpass, $databaseName, 3306);
	switch($_GET['method']) {
	    case 'getUsers': return getUsers($db);
	    break;
	    case 'addTraffic': return addTraffic($db);
	    break;
	    case 'cronLimit': return cronLimit($db);
	    break;
	}

}else{
	die('Invaild');
}