
  

    $(document).ready(function($) {
        // 声明一个 QRCode，选择 id 为 qrcode 的元素
        var qrcode = new QRCode("QRCode", {
            text: "default",
            width: 280,
            height: 280,
            colorDark : "#000",
            colorLight : "#FFF",
            correctLevel : QRCode.CorrectLevel.L
        });
      
        $("#securityReset").on('click', function(){
            layer.confirm('确定重置？',{}, function(index){
              $.get(location.href + '&method=securityReset', function(result){
                location.reload()
              	layer.close(index)
              });
            })
        });
        // 定义 name 为 qrcode 的元素按下时的事件
        $("[name='qrcode']").on('click',function() {
            qrcode.clear(); // 清空图像
            // QR 的名字
            qrname = $(this).attr('data-qrname');
            // QR 的主体内容
            var qrcontent = $(this).attr('data-qrcode');
            // 判断是 Shadowsocks 还是其他的二维码
            switch (qrname) {
                case 'Shadowsocks':
                    // 如果是 Shadowsocks
                    qrcontent = 'ss://' + window.btoa(qrcontent);
                    break;
                case 'ShadowsocksR':
                    // 如果是 ShadowsocksR
                    qrcontent = 'ssr://' + window.btoa(qrcontent);
                    break;
                case 'V2ray':
                    qrcontent = qrcontent;
                    break;
                default:
                    // 默认什么都不做
                    break;
            }
            
            if ($(this).attr('data-client')) {
                qrname = qrname + $(this).attr('data-client');
            }
            // 生成另一个图像
            qrcode.makeCode(qrcontent);
            // 弹出层
            layer.open({
                type: 1,
                title: $(this).attr('title'),
                shade: [0.8, '#000'],
                skin: 'layui-layer-demo',
                closeBtn: 1,
                shift: 2,
                shadeClose: true,
                content: document.getElementById('QRCode_HTML').innerHTML + '<p>请使用 ' + $(this).attr('data-client') + ' 进行扫描</p>'
            });
        });
        
        var clipboard = new ClipboardJS("[name='v2raylink']");
        clipboard.on('success', function(e) {
            console.info('Action:', e.action);
            console.info('Text:', e.text);
            console.info('Trigger:', e.trigger);
            layer.msg('已经复制到剪切板，请使用 ' + e.trigger.attributes.getNamedItem('data-client').value+' 进行添加');
            e.clearSelection();
        });

        $("[name='guiconfig']").on('click',function() {
            function download(fileName, blob){
                var aLink = document.createElement('a');
                var evt = document.createEvent("MouseEvents");
                evt.initEvent('click', true, false, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
                aLink.download = fileName;
                aLink.href = URL.createObjectURL(blob);
                aLink.dispatchEvent(evt);
            }
            function stringToBlob(text) {
                var u8arr = new Uint8Array(text.length);
                for (var i = 0, len = text.length; i < len; ++i) {
                    u8arr[i] = text.charCodeAt(i);
                }
                var blob = new Blob([u8arr]);
                return blob;
            }
            var json_content = $(this).attr('data-guiconfig');
            json_content = window.atob(json_content);
            json_content = json_content.replace(/\r\n|\n/g,"");
            json_content = json_content.replace(/\'/ig,"\"");
            var blob = stringToBlob(JSON.stringify(JSON.parse(json_content),null,2));
            download('gui-config.json', blob);
        });
    });