<link rel="stylesheet" href="{$systemurl}modules/servers/v2ray/templates/LegendSock/stylesheets/style.css">

<div id="LS">
    <div class="legend-responsive" style="margin: -5px; padding: 5px 0; background-color: white;">
        <table class="table" style="border: 1px solid #EAEAEA;">
            <thead>
            <tr>
                <th>UUID</th>
                <th>状态</th>
                <th>上传流量</th>
                <th>下载流量</th>
                <th>叠加流量</th>
                <th>剩余流量</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td style="width: 35%">{$v2ray_uuid}</td>
                <td style="width: 5%">{if $enable}激活{else}停用{/if}</td>
                <td style="width: 15%">{($upload / 1048576)|round:2} MB</td>
                <td style="width: 15%">{($download / 1048576)|round:2} MB</td>
                <td style="width: 15%">{($addition / 1048576)|round:2} MB</td>
                <td style="width: 15%">{((($traffic + $addition) - ($upload + $download)) / 1048576)|round:2} MB</td>
            </tr>
            </tbody>
        </table>
    </div>
</div>