@push('stylesheet')
    <link href="https://unpkg.com/artplayer/dist/artplayer.js">
    <link href="https://unpkg.com/artplayer-plugin-danmuku/dist/artplayer-plugin-danmuku.js">
    <style>
        .timeline tr:hover td{
            background-color:#cccccc;
        }
        @media (prefers-color-scheme: dark) {
            .timeline tr:hover td{
            background-color:#333333;
            }
            .timeline td{
            color:white;
            }
        }
        .art-danmuku-emitter, .art-danmuku-theme-dark{
            display: none !important;
        }

    </style>
@endpush

@push('scripts')
    <script src="https://unpkg.com/artplayer/dist/artplayer.js"></script>
    <script src="https://unpkg.com/artplayer-plugin-danmuku/dist/artplayer-plugin-danmuku.js"></script>
    <script>
        $(function ()
        {
            const art = new Artplayer({
                container: document.querySelector('#video-player'),
                url: '{!! $file['download'] !!}',
                autoSize: true,
                fullscreen: true,
                fullscreenWeb: true,
                flip: true,
                playbackRate: true,
                aspectRatio: true,
                setting: true,
                autoMini: true,
                rotate: true,
                hotkey: true,
                pip: false,
                whitelist: ['*'],
                plugins: [
                    artplayerPluginDanmuku({
                        // danmuku: "https://danmu.ddindexs.com/Xml/{!! str_replace(".mp4",".xml",$file['name']) !!}",
                        danmuku: '{{ str_replace(".flv.mp4",".new.xml", $file['name']) }}?download=1',
                        speed: 10, // 弹幕持续时间，单位秒，范围在[1 ~ 10]
                        opacity: 0.75, // 弹幕透明度，范围在[0 ~ 1]
                        fontSize: 25, // 字体大小，支持数字和百分比
                        color: '#FFFFFF', // 默认字体颜色
                        mode: 0, // 默认模式，0-滚动，1-静止
                        margin: [10, 10], // 弹幕上下边距，支持数字和百分比
                        antiOverlap: true, // 是否防重叠
                        useWorker: true, // 是否使用 web worker
                        synchronousPlayback: false, // 是否同步到播放速度
                        filter: (danmu) => danmu.text.length < 50, // 弹幕过滤函数，返回 true 则可以发送
                        lockTime: 5, // 输入框锁定时间，单位秒，范围在[1 ~ 60]
                        maxLength: 100, // 输入框最大可输入的字数，范围在[0 ~ 500]
                        minWidth: 200, // 输入框最小宽度，范围在[0 ~ 500]，填 0 则为无限制
                        maxWidth: 400, // 输入框最大宽度，范围在[0 ~ Infinity]，填 0 则为 100% 宽度
                        theme: 'dark', // 输入框自定义挂载时的主题色，默认为 dark，可以选填亮色 light
                        beforeEmit: (danmu) => !!danmu.text.trim(), // 发送弹幕前的自定义校验，返回 true 则可以发送

                        // 通过 mount 选项可以自定义输入框挂载的位置，默认挂载于播放器底部，仅在当宽度小于最小值时生效
                        // mount: document.querySelector('.artplayer-danmuku'),
                    }),
                ],
            });
            // 监听准备完成
            art.on('ready', () => {
                console.info('加载完毕');
                console.info("视频URL=" + art.url);
                console.info(art.autoHeight);
                art.autoHeight = true;
                console.info(art.autoHeight);
                // $("div.art-danmuku-emitter.art-danmuku-theme-dark").remove();
                // $("div.art-controls-center").remove();
            });
            // 监听加载到的弹幕数组
            art.on('artplayerPluginDanmuku:loaded', (danmus) => {
                console.info('加载弹幕', danmus.length);
            });

            // 监听加载到弹幕的错误
            art.on('artplayerPluginDanmuku:error', (error) => {
                console.info('加载错误', error);
            });

            // 监听弹幕配置变化
            art.on('artplayerPluginDanmuku:config', (option) => {
                console.info('配置变化', option);
            });

            // 监听弹幕停止
            art.on('artplayerPluginDanmuku:stop', () => {
                console.info('弹幕停止');
            });

            // 监听弹幕开始
            art.on('artplayerPluginDanmuku:start', () => {
                console.info('弹幕开始');
            });

            // 监听弹幕隐藏
            art.on('artplayerPluginDanmuku:hide', () => {
                console.info('弹幕隐藏');
            });

            // 监听弹幕显示
            art.on('artplayerPluginDanmuku:show', () => {
                console.info('弹幕显示');
            });

            // 监听弹幕销毁
            art.on('artplayerPluginDanmuku:destroy', () => {
                console.info('弹幕销毁');
            });

            // 防止出现401 token过期
            art.on('error', function () {
                console.log('获取资源错误，开始重新加载！');
                let xhr = new XMLHttpRequest();

                xhr.open('GET', window.location.href + '&json=true', true);
                xhr.send();

                xhr.onreadystatechange = function (e) {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        console.log(xhr.responseText);
                        let resp = JSON.parse(xhr.responseText);
                        art.switchQuality(resp.data.download, "{!! $file['name'] !!}");
                        console.info("视频URL=" + art.url);
                    }
                }
            });


            // 如果是播放状态 & 没有播放完 每35分钟重载视频防止卡死
            setInterval(function () {
                if (art.playing) {
                    console.log('开始重新加载！');
                    let xhr = new XMLHttpRequest();

                    xhr.open('GET', window.location.href + '&json=true', true);
                    xhr.send();

                    xhr.onreadystatechange = function (e) {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            console.log(xhr.responseText);
                            let resp = JSON.parse(xhr.responseText);
                            art.switchQuality(resp.data.download, "{!! $file['name'] !!}");
                            console.info("视频URL=" + art.url);
                        }
                    }
                }
            }, 1000 * 60 * 35)

            // fetch('https://file.ddindexs.com/Pbf/{!! str_replace(".mp4",".pbf",$file['name']) !!}')
            //     .then(response => response.text())
            //     .then(rawPBFStr => {

            //         var timelineTable = document.querySelector('.timeline')
            //         var highlight = []

            //         var rawPBFList = rawPBFStr.split(/[\r\n]+/);
            //         rawPBFList.shift()
            //         rawPBFList.forEach(PBFItem => {
            //             if (!PBFItem) {
            //                 return
            //             }
            //             var PBFParts = PBFItem.split('*')
            //             if (PBFParts.length <= 2) {
            //                 return
            //             }
            //             var timeMarker = PBFParts.shift()
            //             var timeMarkerParts = timeMarker.split('=')
            //             var time = parseInt(timeMarkerParts[1]) / 1000
            //             var text = PBFParts[0]

            //             timeButtonTd = document.createElement('td');
            //             timeButtonTd.innerText = new Date(time * 1000).toISOString().substr(11, 8);
            //             timeButtonTd.style.width = "60px";
            //             timeTextTd = document.createElement('td');
            //             timeTextTd.innerText = text;

            //             timelineRow = document.createElement('tr');
            //             timelineRow.style.cursor = "pointer";
            //             timelineRow.addEventListener('click', () => {
            //                 art.seek = time;
            //             })
            //             timelineRow.appendChild(timeButtonTd);
            //             timelineRow.appendChild(timeTextTd);
            //             timelineTable.appendChild(timelineRow);

            //             highlight.push({
            //                 "time": time,
            //                 "text": text
            //             });
            //         });

            //         art.option.highlight = highlight;
            //     })
        });
    </script>
@endpush

<div class="mdui-center">
    <div id="video-player" style="width: auto; height: 60vh; position: relative;"></div>
    <table class="timeline" cellpadding="10px" cellspacing="0"  style="width: 100%"></table>
    <p class="text-danger">如无法播放或格式不受支持，推荐使用 PotPlayer 播放器在线播放</p>
</div>
