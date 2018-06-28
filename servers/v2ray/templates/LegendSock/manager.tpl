<link rel="stylesheet" href="{$systemurl}modules/servers/v2ray/templates/LegendSock/stylesheets/style.css">
<script src="{$systemurl}modules/servers/v2ray/templates/LegendSock/javascripts/common.js"></script>

<style type="text/css">
    h1 {
        display: none;
    }
</style>

<div class="row" id="LS">
    <div class="col-md-12">
        {if $tips eq 'success'}
            {include file="{$template}tips/success.tpl"}
        {elseif $tips eq 'danger'}
            {include file="{$template}tips/danger.tpl"}
        {else}
            {include file="{$template}tips/warning.tpl"}
        {/if}
        {$information}
    </div>
    <div class="col-sm-3">

        <div class="health-status-block status-badge-green clearfix">
            <div class="icon">
                <i class="fa fa-cubes"></i>
            </div>
            <div class="detail">
                <span class="count">{$productCount}</span>
                <span class="desc">昨日有 {$productCountOld} 个产品，今日{if $productCount gt $productCountOld}有提升{elseif $productCount lt $productCountOld}有下降{else}无变化{/if}</span>
            </div>
        </div>

    </div>
    <div class="col-sm-3">

        <div class="health-status-block status-badge-pink clearfix">
            <div class="icon">
                <i class="fa fa-exchange"></i>
            </div>
            <div class="detail">
                <span class="count">{($trafficCount / 1073741824)|round:2} GB </span>
                <span class="desc">昨日 {($trafficCountOld / 1073741824)|round:2} GB，今日{if $trafficCount gt $trafficCountOld}有提升{elseif $trafficCount lt $trafficCountOld}有下降{else}无变化{/if}</span>
            </div>
        </div>

    </div>
    <div class="col-md-12">
        <ul class="nav nav-tabs" role="tablist" style="margin-bottom: 18px;margin-top: 18px">
            <li role="presentation" class="active"><a href="#home" aria-controls="home" role="tab" data-toggle="tab">仪表盘</a></li>
            {if $page['name'] eq 'home'}
                <li role="presentation"><a href="#other" aria-controls="other" role="tab" data-toggle="tab">配置</a></li>
            {/if}
            <li style="float: right; padding: 5px 0px;">
                <form action="{$modulelink}" method="post" id="converter">
                    <input name="action" value="converter" type="hidden">
                </form>
                {if $page['name'] eq 'home'}
                {else}
                    <button type="button" class="btn btn-default btn-xs" onclick="javascript:if (confirm('将跳转至 V2ray 控制面板首页')) window.location.href='addonmodules.php?module=v2ray';">
                        <span class="glyphicon glyphicon-home" aria-hidden="true"></span> 回到首页
                    </button>
                {/if}
            </li>
        </ul>
    </div>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="home">
            <div class="col-md-12">
                {if $page['name'] eq 'converter'}
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <span class="badge">共找到 {$converter['rows']} 个数据库</span>
                            <h3 class="panel-title">数据库转换</h3>
                        </div>
                        <div class="legend-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>数据库名称</th>
                                    <th>数据库用户</th>
                                    <th>数据库密码</th>
                                    <th>数据库主机</th>
                                    <th>数据库端口</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach $converter['info'] as $key => $value}
                                    <tr>
                                        <td>{$key}</td>
                                        <td>{$value['database']}</td>
                                        <td>{$value['username']}</td>
                                        <td>{$value['password']}</td>
                                        <td>{$value['hostname']}</td>
                                        <td>{$value['port']}</td>
                                        <td>
                                            <form action="{$modulelink}" method="post" style="margin: 0;" id="converter_{$key}">
                                                <input name="action" value="submit_converter" type="hidden">
                                                <input name="id" value="{$key}" type="hidden">
                                            </form>
                                            <button type="button" class="btn btn-danger btn-xs" onclick="javascript:if(confirm('请确认数据库已提前备份')) if (confirm('你真的确定要这么做吗？')) document.getElementById('converter_{$key}').submit();">
                                                <span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> 开始转换
                                            </button>
                                        </td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        </div>
                    </div>
                {elseif $page['name'] eq 'product'}
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <span class="badge">共找到 {$product['rows']} 个产品</span>
                            <h3 class="panel-title">产品管理</h3>
                        </div>
                        <div class="legend-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>用户 ID</th>
                                    <th>产品 ID</th>
                                    <th>产品状态</th>
                                    <th>服务状态</th>
                                    <th>套餐流量</th>
                                    <th>剩余流量</th>
                                    <th>最近连接</th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach $product['result'] as $value}
                                    <tr>
                                        {if $value['uid'] eq 'unknown'}
                                            <td>{$value['uid']}</td>
                                            <td>{$value['pid']}</td>
                                        {else}
                                            <td onclick="javascript:if (confirm('将跳转至账户页面')) window.location.href='clientsprofile.php?userid={$value['uid']}';">
                                                {$value['uid']}
                                            </td>
                                            <td onclick="javascript:if (confirm('将跳转至产品页面')) window.location.href='clientsservices.php?userid={$value['uid']}&id={$value['pid']}';">
                                                {$value['pid']}
                                            </td>
                                        {/if}
                                        <td>{$value['status']}</td>
                                        <td>{if $value['enable']}正常使用{else}停用{/if}</td>
                                        <td>{($value['transfer_enable'] / 1048576)|round:2} MB {if $value['addition']}<span style="color: #CCC;">( 包含叠加 {($value['addition'] / 1048576)|round:2} MB )</span>{/if}</td>
                                        <td>{(($value['transfer_enable'] - ($value['u'] + $value['d'])) / 1048576)|round:2} MB</td>
                                        <td>{$value['t']|date_format:'%Y-%m-%d, %H:%M'}</td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        </div>
                        {if $previous eq true || $next eq true}
                            <div class="panel-body" style="border-top: 1px solid #EAEAEA;">
                                <nav>
                                    <ul class="pager" style="margin: 0;">
                                        {if $previous eq true}
                                            <li class="previous">
                                                <form action="{$modulelink}" method="post" id="previous">
                                                    <input name="action" value="product" type="hidden">
                                                    <input name="direction" value="previous" type="hidden">
                                                    <input name="id" value="{$product['id']}" type="hidden">
                                                    <input name="page" value="{$page['number']}" type="hidden">
                                                </form>
                                                <a href="javascript:document.getElementById('previous').submit();"><span aria-hidden="true">←</span> 上一页</a>
                                            </li>
                                        {/if}
                                        {if $next eq true}
                                            <li class="next">
                                                <form action="{$modulelink}" method="post" id="next">
                                                    <input name="action" value="product" type="hidden">
                                                    <input name="direction" value="next" type="hidden">
                                                    <input name="id" value="{$product['id']}" type="hidden">
                                                    <input name="page" value="{$page['number']}" type="hidden">
                                                </form>
                                                <a href="javascript:document.getElementById('next').submit();">下一页 <span aria-hidden="true">→</span></a>
                                            </li>
                                        {/if}
                                    </ul>
                                </nav>
                            </div>
                        {/if}
                    </div>
                {elseif $page['name'] eq 'notice'}
                    <div class="panel panel-warning">
                        <div class="panel-heading">
                            <h3 class="panel-title">编辑通知</h3>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <p>
                                    格式: <code>第一条通知|第二条通知|第 N 条通知</code>
                                </p>
                                <form action="{$module}" method="post" id="notice">
                                    <input type="hidden" name="action" value="edit_notice">
                                    <input type="hidden" name="id" value="{$id}">
                                    <div class="form-group">
                                        <textarea class="form-control" rows="10" name="notice">{$notice}</textarea>
                                    </div>
                                </form>
                            </div>
                            <button onclick="javascript:if(confirm('这将会覆盖原来数据库中的通知信息')) document.getElementById('notice').submit();" class="btn btn-warning"><span class="glyphicon glyphicon-open" aria-hidden="true"></span> 提交修改</button>
                        </div>
                    </div>
                {elseif $page['name'] eq 'node'}
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <h3 class="panel-title">编辑节点</h3>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <p>
                                    格式: <code>节点名称|连接地址|连接端口|加密方式|备注信息|是否TLS 1开启0关闭</code>
                                </p>
                                <form action="{$module}" method="post" id="node">
                                    <input type="hidden" name="action" value="edit_node">
                                    <input type="hidden" name="id" value="{$id}">
                                    <div class="form-group">
                                        <textarea class="form-control" rows="10" name="node">{$node}</textarea>
                                    </div>
                                </form>
                            </div>
                            <button onclick="javascript:if(confirm('这将会覆盖原来数据库中的节点信息')) document.getElementById('node').submit();" class="btn btn-info"><span class="glyphicon glyphicon-open" aria-hidden="true"></span> 提交修改</button>
                        </div>
                    </div>
                {elseif $page['name'] eq 'resource'}
                    <div class="panel panel-success">
                        <div class="panel-heading">
                            <h3 class="panel-title">编辑资源</h3>
                        </div>
                        <div class="panel-body">
                            <div class="form-group">
                                <p>
                                    格式: <code>资源名称|资源地址|资源描述</code>
                                </p>
                                <form action="{$module}" method="post" id="resource">
                                    <input type="hidden" name="action" value="edit_resource">
                                    <input type="hidden" name="id" value="{$id}">
                                    <div class="form-group">
                                        <textarea class="form-control" rows="10" name="resource">{$resource}</textarea>
                                    </div>
                                </form>
                            </div>
                            <button onclick="javascript:if(confirm('这将会覆盖原来数据库中的资源信息')) document.getElementById('resource').submit();" class="btn btn-success"><span class="glyphicon glyphicon-open" aria-hidden="true"></span> 提交修改</button>
                        </div>
                    </div>
                {else}
                    <div class="panel panel-info">
                        <div class="panel-heading">
                            <span class="badge">共找到 {$database['rows']} 个数据库</span>
                            <h3 class="panel-title">数据库管理</h3>
                        </div>
                        <div class="legend-responsive">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>数据库名称</th>
                                    <th>数据库主机</th>
                                    <th>部署状态</th>
                                    <th>产品数量</th>
                                    <th>通知信息</th>
                                    <th>节点信息</th>
                                    <th>资源信息</th>
                                    <th>初始化</th>
                                    <th>格式化</th>
                                </tr>
                                </thead>
                                <tbody>
                                {foreach $database['info'] as $key => $value}
                                    <tr>
                                        <td>{$key}</td>
                                        <td>{$value['database']}</td>
                                        <td>{$value['hostname']}</td>
                                        <td><span class="glyphicon glyphicon-{if $value['status']}ok{else}remove{/if}"></span></td>
                                        <td>
                                            {if $value['count']}
                                                <form action="{$modulelink}" method="post" style="margin: 0;">
                                                    <input name="action" value="product" type="hidden">
                                                    <input name="id" value="{$key}" type="hidden">
                                                    <button type="submit" class="btn btn-default btn-xs"{if !$value['status']} disabled="disabled"{/if}>
                                                        {$value['count']}
                                                    </button>
                                                </form>
                                            {else}
                                                0
                                            {/if}
                                        </td>
                                        <td>
                                            <form action="{$modulelink}" method="post" style="margin: 0;">
                                                <input name="action" value="notice" type="hidden">
                                                <input name="id" value="{$key}" type="hidden">
                                                <button type="submit" class="btn btn-warning btn-xs"{if !$value['status']} disabled="disabled"{/if}>
                                                    <span class="glyphicon glyphicon-bullhorn" aria-hidden="true"></span> 编辑通知
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <form action="{$modulelink}" method="post" style="margin: 0;">
                                                <input name="action" value="node" type="hidden">
                                                <input name="id" value="{$key}" type="hidden">
                                                <button type="submit" class="btn btn-info btn-xs"{if !$value['status']} disabled="disabled"{/if}>
                                                    <span class="glyphicon glyphicon-tasks" aria-hidden="true"></span> 编辑节点
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <form action="{$modulelink}" method="post" style="margin: 0;">
                                                <input name="action" value="resource" type="hidden">
                                                <input name="id" value="{$key}" type="hidden">
                                                <button type="submit" class="btn btn-success btn-xs"{if !$value['status']} disabled="disabled"{/if}>
                                                    <span class="glyphicon glyphicon-th-list" aria-hidden="true"></span> 编辑资源
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <form action="{$modulelink}" method="post" style="margin: 0;">
                                                <input name="action" value="init" type="hidden">
                                                <input name="id" value="{$key}" type="hidden">
                                                <button type="submit" class="btn btn-primary btn-xs">
                                                    <span class="glyphicon glyphicon-floppy-open" aria-hidden="true"></span> 初始化表
                                                </button>
                                            </form>
                                        </td>
                                        <td>
                                            <form action="{$modulelink}" method="post" style="margin: 0;" id="format_{$key}">
                                                <input name="action" value="format" type="hidden">
                                                <input name="id" value="{$key}" type="hidden">
                                            </form>
                                            <button type="button" class="btn btn-danger btn-xs" onclick="javascript:if(confirm('这将会清空数据库中的所有内容，你确认要这么做吗？')) if (confirm('你真的确定要这么做吗？')) document.getElementById('format_{$key}').submit();"{if !$value['status']} disabled="disabled"{/if}>
                                                <span class="glyphicon glyphicon-floppy-remove" aria-hidden="true"></span> 格式化表
                                            </button>
                                        </td>
                                    </tr>
                                {/foreach}
                                </tbody>
                            </table>
                        </div>
                    </div>
                {/if}
            </div>
        </div>
        {if $page['name'] eq 'home'}
            <div role="tabpanel" class="tab-pane" id="other">
                <div class="col-md-12">
                    <div class="alert alert-warning alert-dismissible fade in" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                        <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span> <strong>部署须知！</strong> 需要完整实现模块的各项功能、必须使用指定服务器后端(v2ray-whmcs-backend)及API端(v2ray-whmcs-api)。
                    </div>
                    {if $errorHost}
                        <div class="alert alert-danger alert-dismissible fade in" role="alert">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
                            <span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span> <strong>出现错误！</strong> 当前有数据库连接地址填写 127.0.0.1 或 localhost，请访问 Setup -> Products / Services -> Servers 将相应的数据库连接地址修改为公网( IP or CNAME )地址
                        </div>
                    {/if}
                </div>
                <div class="col-md-3">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-success">
                                <div class="panel-heading">
                                    <h3 class="panel-title">配置</h3>
                                </div>
                                <div class="panel-body" style="color: #666;">
                                    <p><strong>API配置</strong></p>
                                    <p>为了兼容如订阅等功能需要配置API来兼容:</p>
                                    <form action="{$module}" method="post" id="apiConfig">
                                        <input type="hidden" name="action" value="submit_apiConfig">
                                        <div class="form-group">
                                            <input class="form-control" rows="10" name="url" placeholder="https://api.com/api.php (精确到文件)" value="{$apiUrl}">
                                        </div>
                                    </form>
                                  	<button onclick="javascript:if(confirm('这将会覆盖原来数据库中的配置信息')) document.getElementById('apiConfig').submit();" class="btn btn-success"><span class="glyphicon glyphicon-open" aria-hidden="true"></span> 提交修改</button>
                                </div>
                            </div>
                            <div class="panel panel-success">
                                <div class="panel-heading">
                                    <h3 class="panel-title">扩展功能</h3>
                                </div>
                                <div class="panel-body" style="color: #666;">
                                    <p><strong>模板切换</strong></p>
                                    <p>支持以 Smarty 规范编写的模板，放置于如下目录:</p>
                                    <pre>{$root}modules/servers/v2ray/templates</pre>
                                    <p>若模板放置位置正确、则可以在填写授权编号的地方自动显示</p>
                                    <p><strong>语言输出</strong></p>
                                    <p>支持以数组方式编写语言包，语言包放置于如下目录:</p>
                                    <pre>{$root}modules/servers/v2ray/languages</pre>
                                    <p style="margin-bottom: 0">若放置正确、系统即可跟随客户设置的 WHMCS 语言自动切换</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="panel panel-info">
                                <div class="panel-heading">
                                    <h3 class="panel-title">半自动安装</h3>
                                </div>
                                <div class="panel-body" style="color: #666;">
                                    <p><strong>安装说明</strong></p>
                                    <p>使用此方法需要手动输入数据库信息后方可自动安装，仅支持 CentOS 7</p>
                                    <p><strong>安装 & 升级 & 卸载方式</strong></p>
                                    <p style="margin-bottom: 0">详情请查阅 Github 文字教程: <a href="https://github.com/deepbwork/v2ray-whmcs-backend" target="_blank">https://github.com/deepbwork/v2ray-whmcs-backend</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        {/if}
    </div>
    <div class="col-md-12">
        <p style="text-align: center; color: #CCC; font-size: 12px;">Powered by Hostribe(Legendsock) Redesign Deepbwork, Version: {$version}</p>
    </div>
</div>