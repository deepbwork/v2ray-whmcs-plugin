<link rel="stylesheet" href="{$systemurl}modules/servers/v2ray/templates/LegendSock/stylesheets/style.css">
<link rel="stylesheet" href="{$systemurl}modules/servers/v2ray/templates/LegendSock/stylesheets/font-awesome.min.css">
<script src="{$systemurl}modules/servers/v2ray/templates/LegendSock/javascripts/layer/layer.js"></script>
<script src="{$systemurl}modules/servers/v2ray/templates/LegendSock/javascripts/qrcode.js"></script>
<script src="{$systemurl}modules/servers/v2ray/templates/LegendSock/javascripts/common.js"></script>
<script src="{$systemurl}modules/servers/v2ray/templates/LegendSock/javascripts/chart.js"></script>
<script src="{$systemurl}modules/servers/v2ray/templates/LegendSock/javascripts/clipboard.min.js"></script>

<script src="{$systemurl}modules/servers/v2ray/templates/LegendSock/javascripts/custom.js"></script>
<style>
    #QRCode_HTML {
        display: none;
    }
    #QRCode {
        padding: 10px;
    }
    .layui-layer-content > p {
         color: #666;
         font-size: 12px;
         margin: 0 0 10px 0;
         text-align: center;
     }
</style>

<script>
    {if $chart|@count neq 0}
    var myChart = {
        type: 'line',
        data: {
            labels: [{foreach $chart['upload'] as $key => $value}{$key},{/foreach}],
            datasets: [{
                label: "{$LS_LANG['chart']['upload']} ( MB )",
                data: [{foreach $chart['upload'] as $value}{(($value) / 1048576)|round:2},{/foreach}],
                fill: false,
                borderDash: [5, 5],
                borderColor: "rgba(185,198,192,1)",
                backgroundColor: "rgba(185,198,192,0.2)",
                pointBorderColor: "rgba(222,137,171,1)",
                pointBackgroundColor: "rgba(222,137,171,1)",
                pointBorderWidth: 1
            }, {
                label: "{$LS_LANG['chart']['download']} ( MB )",
                data: [{foreach $chart['download'] as $value}{(($value) / 1048576)|round:2},{/foreach}],
                fill: false,
                borderDash: [5, 5],
                borderColor: "rgba(222,137,171,1)",
                backgroundColor: "rgba(222,137,171,0.2)",
                pointBorderColor: "rgba(185,198,192,1)",
                pointBackgroundColor: "rgba(185,198,192,1)",
                pointBorderWidth: 1
            }]
        }
    };
    {/if}
    window.onload = function() {
        var chart = document.getElementById("myChart").getContext("2d");
        window.myLine = new Chart(chart, myChart);
    };
</script>

<div id="QRCode_HTML">
    <div id="QRCode" style="width: 300px;height: 300px;"></div>
</div>

<div class="row" id="LS">
    {if $notice|@count neq 0}
        <div class="col-md-12">
            <div class="alert alert-warning alert-dismissible fade in" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                {if $notice|@count eq 1}
                    {$notice[0]|trim}
                {else}
                    <ul style="padding: 0px;">
                        {foreach $notice as $value}
                            <li>{$value|trim}</li>
                        {/foreach}
                    </ul>
                {/if}
            </div>
        </div>
    {/if}

    <div class="legend-responsive">
        <div class="col-md-4">
            <div class="box-sm">
                <div class="box-sm-title">
                    {$LS_LANG['product']['head']}
                </div>
                <div>
                    <span class="box-sm-font">{$product}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="box-sm">
                <div class="box-sm-title">
                    {$LS_LANG['nextduedate']}
                </div>
                <div>
                    <span class="box-sm-font">{$nextduedate}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="box-sm">
                <div class="box-sm-title">
                    {$LS_LANG['traffic']['head']}
                </div>
                <div>
                    <span class="box-sm-font">{if ($info['u'] + $info['d']) > 1073741824}{(($info['u'] + $info['d']) / 1073741824)|round:2} GB{else}{(($info['u'] + $info['d']) / 1048576)|round:2} MB{/if}</span><span class="box-sm-font-sm"> / {if (($info['transfer_enable'] - ($info['u'] + $info['d']))) > 1073741824}{(($info['transfer_enable'] - ($info['u'] + $info['d'])) / 1073741824)|round:2} GB{else}{(($info['transfer_enable'] - ($info['u'] + $info['d'])) / 1048576)|round:2} MB{/if}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12">
        <ul class="nav nav-tabs" role="tablist" style="margin-bottom: 18px;">
            <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">{$LS_LANG['page']['home']}</a></li>
            <li role="presentation"><a href="#other" aria-controls="other" role="tab" data-toggle="tab">{$LS_LANG['page']['other']}</a></li>
        </ul>
    </div>

    <div class="col-md-12 tab-content" style="padding: 0">
        <div role="tabpanel" class="tab-pane active" id="home">
            <div class="col-md-12">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h3 class="panel-title">{$LS_LANG['product']['title']}</h3>
                    </div>
                    <div class="legend-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>{$LS_LANG['product']['id']}</th>
                                <th>{$LS_LANG['product']['v2ray_uuid']}</th>
                                <th>{$LS_LANG['product']['v2ray_alter_id']}</th>
                                <th>{$LS_LANG['product']['v2ray_level']}</th>
                                <th>{$LS_LANG['product']['lastTime']}</th>
                                {if $apiUrl neq ''}<th>订阅地址</th>{/if}
                                <th>安全</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td style="width: 10%;">{$serviceid}</td>
                                <td style="width: 40%;"><span id="userId" onclick="javascript:document.getElementById('userId').innerHTML='{$info['v2ray_uuid']}';">{$LS_LANG['product']['show']}</span></td>
                                <td style="width: 5%;">{$info['v2ray_alter_id']}</td>
                                <td style="width: 5%;">{$info['v2ray_level']}</td>
                                <td style="width: 20%;">{$info['t']|date_format:'%Y-%m-%d, %H:%M'}</td>
                                {if $apiUrl neq ''}
                                <td style="width: 15%">
                                  <button type="button" class="btn btn-info btn-xs autoset" data-qrname="V2ray" data-link="{$apiUrl}?token={$uuid}&s=v2ray.subscribe&pid={$serviceid}" data-client="shadowrocket/v2rayNG/v2rayN/Quantumult" title="订阅地址" name="v2raylink" data-clipboard-text="{$apiUrl}?token={$uuid}&s=v2ray.subscribe&pid={$serviceid}">
                                    <span class="glyphicon glyphicon-link" aria-hidden="true"></span> 点击复制
                                  </button>
                                </td>
                                {/if}
                                <td style="width: 5%">
                                    <div class="btn-group btn-group-xs" role="group" aria-label="Extra-small button group">
                                      <button type="button" class="btn btn-info btn-xs autoset" id="securityReset">
                                        <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span> 重置
                                      </button>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!--div class="panel panel-info">
                    <div class="panel-heading">
                        <h3 class="panel-title">{$LS_LANG['plugin']['title']}</h3>
                    </div>
                    <div class="legend-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>{$LS_LANG['plugin']['guiconfig']}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td style="min-width: 190px; width: 25%;">
                                    <div class="btn-group btn-group-xs" role="group" aria-label="Extra-small button group">
                                        <button type="button" class="btn btn-info btn-xs autoset" name="guiconfig" data-guiconfig="{$guiconfig['ss']}">
                                            <span class="glyphicon glyphicon-send" aria-hidden="true"></span> {$LS_LANG['plugin']['general']}
                                        </button>
                                        <button type="button" class="btn btn-info btn-xs autohides" name="guiconfig" data-guiconfig="{$guiconfig['ssr']}">
                                            <span class="glyphicon glyphicon-export" aria-hidden="true"></span> {$LS_LANG['plugin']['ssr']}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div-->
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <span class="badge">{$LS_LANG['node']['head']['0']} {$node|@count} {$LS_LANG['node']['head']['1']}</span>
                        <h3 class="panel-title">{$LS_LANG['node']['title']}</h3>
                    </div>
                    <div class="legend-responsive">
                        <table class="table">
                            <thead>
                            <tr>
                                <th style="width: 10%;">{$LS_LANG['node']['name']}</th>
                                <th style="width: 20%;">{$LS_LANG['node']['host']}</th>
                                <th style="width: 10%;">{$LS_LANG['node']['port']}</th>
                                <th style="width: 10%;">{$LS_LANG['node']['security']}</th>
                                <th style="width: 20%;">{$LS_LANG['node']['remarks']}</th>
                                <th style="width: 5%;">{$LS_LANG['node']['isTLS']}</th>
                                <th style="width: 25%;">{$LS_LANG['node']['qrcode']}</th>
                            </tr>
                            </thead>
                            <tbody>
                            {if $node|@count neq 0}
                                {foreach $node as $key => $value}
                                    {$value=("|"|explode:$value)}
                                    <tr>
                                        <td>{$value[0]|trim}</td>
                                        <td>{$value[1]|trim}</td>
                                        <td>{$value[2]|trim}</td>
                                        <td>{$value[3]|trim}</td>
                                        <td>{$value[4]|trim}</td>
                                        <td>{if $value[5] eq 1}<span class="c-badge c-badge--success">√</span>{else}<span class="c-badge c-badge--danger">×</span>{/if}</td>
                                        <td>
                                            <div class="btn-group btn-group-xs" role="group" aria-label="Extra-small button group">
                                                <button type="button" class="btn btn-info btn-xs autohides" data-qrname="V2ray" data-qrcode="{$extend[$key]['v2rayOtherUrl']}" data-client="shadowrocket/v2rayNG/v2rayN" title="{$LS_LANG['node']['v2ray']['title']}" name="qrcode">
                                                    <span class="fa fa-qrcode" aria-hidden="true"></span>
                                                </button>
                                            </div>
                                            <div class="btn-group btn-group-xs" role="group" aria-label="Extra-small button group">
                                                <button type="button" class="btn btn-info btn-xs autohides" data-qrname="V2ray" data-qrcode="{$extend[$key]['quantumultUrl']}" data-client="Quantumult" title="{$LS_LANG['node']['v2ray']['title']}" name="qrcode">
                                                    Q</span>
                                                </button>
                                            </div>
                                            <div class="btn-group btn-group-xs" role="group" aria-label="Extra-small button group">
                                                <button type="button" class="btn btn-info btn-xs autoset" data-qrname="V2ray" data-client="shadowrocket/v2rayNG/v2rayN" title="{$LS_LANG['node']['v2ray']['titleUri']}" name="v2raylink" data-clipboard-text="{$extend[$key]['v2rayOtherUrl']}">
                                                    <span class="glyphicon glyphicon-link" aria-hidden="true"></span> {$LS_LANG['node']['v2ray']['importUri']}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                {/foreach}
                            {/if}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="other">
            <div class="col-md-12">
                {if $addition}
                    <div class="alert alert-info alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <h4 style="margin-top: 0; font-weight: bold;"><span class="glyphicon glyphicon-heart" aria-hidden="true"></span> {$LS_LANG['traffic']['title']}</h4>
                        <p style="font-size: 15px;">{$LS_LANG['traffic']['tips']['0']}</p>
                        <p style="font-size: 12px;">{$LS_LANG['traffic']['tips']['1']} <span style="font-weight: bold; color: red;">{$serviceid}</span> {$LS_LANG['traffic']['tips']['2']}</p>
                        <p style="margin-top: 10px;">
                            <button type="button" class="btn btn-default" onclick="javascript:if (confirm('{$LS_LANG['traffic']['confirm']}')) window.location.href='{$systemurl}cart.php?a=add&pid=2';">{$LS_LANG['traffic']['order']}</button>
                        </p>
                    </div>
                {/if}
                {if $chart|@count neq 0}
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <span class="badge" title="{$LS_LANG['chart']['date']}">{$chart['date']|date_format:'%Y-%m-%d'}</span>
                            <h3 class="panel-title">{$LS_LANG['chart']['title']}</h3>
                        </div>
                        <div class="panel-body">
                            <canvas id="myChart"></canvas>
                        </div>
                    </div>
                {/if}
                {if $resource|@count neq 0}
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h3 class="panel-title">{$LS_LANG['resource']}</h3>
                        </div>
                        <div class="list-group">
                            {foreach $resource as $value}
                                {$value=("|"|explode:$value)}
                                <a href="{$value[1]|trim}" class="list-group-item">
                                    <h4 class="list-group-item-heading">{$value[0]|trim}</h4>
                                    <p class="list-group-item-text">{$value[2]|trim}</p>
                                </a>
                            {/foreach}
                        </div>
                    </div>
                {/if}
            </div>
            {if $chart|@count neq 0}
                <div class="col-md-12">
                    <div class="alert alert-info" role="alert" style="text-align: center; font-size: 12px">
                        {$LS_LANG['chart']['tips']}
                    </div>
                </div>
            {/if}
        </div>
    </div>
</div>