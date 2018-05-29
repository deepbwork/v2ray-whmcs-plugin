<?php
$config = [
    'manager' => [
        'page' => 100,                            // 控制面板每页显示的产品数量
    ],
    'common' => [
        'mail' => [
            'title' => 'Traffic over quota',    // 流量超限会发送的邮件名称
            'value' => 10,                      // 流量低于百分之多少将会发送邮件
        ],
    ],
    'script' => 'dea5da6e350541c9',             // 自动输出安装脚本所需要使用的密码
    'autoSuspend' => [
        'switch' => 'On',                       // 流量用尽是否在 WHMCS 中暂停产品，On/Off 分别代表开启与关闭此功能
        'tips' => 'Out of traffic',             // 流量用尽产品自动暂停暂停理由
        'mail' => 'Out of traffic',             // 流量用尽会发送的邮件名称
    ],
    'recordLog' => 'On',                        // 可选是否记录系统日志，On/Off 分别代表开启与关闭功能
];