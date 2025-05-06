<?php
// 設定の読み込み
    require_once("./settings/main.php");
    require_once("./parts/functions.php");
    require_once("./parts/invwork.php");
    // パスワード認証を削除
    // vを取る、なかったら終了
    if (!isset($_GET["v"])){
        header("Content-type: application/json");
        echo (json_encode(array("error" => "Too short parameters")));
        die();
    }
    $invget = new inv();
    // 実行
    $invdata = $invget->fetchurls("/api/v1/videos/".$_GET["v"], array());
    if (!$invdata){
        header("Content-type: application/json");
        echo (json_encode(array("error" => "Cannot fetch!")));
        die();
    }
    gtagOut();
?>
<!DOCTYPE HTML>
<head>
    <meta charset="UTF-8">
    <title>見たら200なんよ</title>
    <link rel="stylesheet" href="./css/reset.css">
    <style>
        .back{
            position:fixed;
            width:100%;
            height:100vh;
            background: linear-gradient(#fff, #ccc);
            z-index:-2;
        }
        .back-touka{
            position:fixed;
            width:100%;
            height:100vh;
            z-index:-1;
            backdrop-filter:blur(4px) brightness(70%);
            --webkit-backdrop-filter:blur(4px) brightness(70%);
            background-color: rgba(255, 255, 255, 0.2);
        }
        .center{
            width:100vw;
            height:100vh;
        }
        .flex{
            display:flex;
            justify-content: center;
            max-width: calc(100vw - 50px);
        }
        .searchcontainer{
            border:1px solid #fff;
            border-radius: 15px;
        }
        .box{
            width:60vw;
            color:#121212;
            display:flex;
            flex-direction: column;
            align-items: center;
        }
        .search{
            width:60vw;
            height:1.5rem;
        }
        .box h1{
            text-align:center;
        }
        .box span{
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <span class="back"></span>
    <span class="back-touka"></span>
    <div class="center">
        <div class="flex">
            <div class="searchcontainer">
                <div class="box">
                    <h1>なんかちゅーぶviewer</h1><br>
                    <video controls id="video" style="max-width:500px;max-height: 80vh;"></video><br>
                    <span>
                        画質：<select type="dropdown" id="vq"></select><br>
                        音質：<select type="dropdown" id="aq"></select><br>
                        <button onClick="hozon();ga_changeload();">保存</button>※リロードされます
                    </span>
                </div>
                <div class="fixed-search">
                    <div class="search-padding">
                        <form onsubmit="return send(event)">
                            <input type="text" id="search" placeholder="検索...">
                        </form>
                        <span id="suggest"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<script>
    const d = document;
    let vq = d.getElementById("vq");
    let aq = d.getElementById("aq");
    let vid = d.getElementById("video");
    let aud = d.createElement("audio");
    let formats = <?php echo(json_encode($invdata)); ?>;
    let videoformats = {};
    let audioformats = {};
    formats.adaptiveFormats.forEach((e, n) => {
        if (e.type.indexOf("audio") != -1){
            audioformats[e.audioQuality+n] = e.url;
        } else if (e.type.indexOf("video") != -1){
            videoformats[e.qualityLabel+n] = e.url;
        }
    });
    videoformats = Object.entries(videoformats);
    videoformats.forEach((p) => {
        let op = d.createElement("option");
        op.innerHTML = p[0];
        vq.appendChild(op);
    });
    audioformats = Object.entries(audioformats);
    audioformats.forEach((p) => {
        let op = d.createElement("option");
        op.innerHTML = p[0];
        aq.appendChild(op);
    });
    var playb = "";
    function readcookie(){
        if (d.cookie.indexOf("quality") == -1) return;
        d.cookie.split(";").find((e) => e.indexOf("quality") != -1).split("=")[1].split(",").forEach((c, s) => {
            if (s == 0){
                videoformats.find((search) => search == c) != -1 ? vq.value = c : null;
            } else if (s == 1){
                audioformats.find((search) => search == c) != -1 ? aq.value = c : null;
            }
        });
    }
    readcookie();
    function hozon(){
        let current = vid.currentTime;
        d.cookie = `quality=${vq.value},${aq.value}; max-age=3600`;
        aud.src = audioformats[audioformats.findIndex((search) => search[0].indexOf(aq.value) != -1)][1];
        vid.src = videoformats[videoformats.findIndex((search) => search[0].indexOf(vq.value) != -1)][1];
        vid.currentTime = current;
    }
    hozon();
    vid.addEventListener("play",play);
    vid.addEventListener("pause", pause);
    function play(){
        if (playb != "" || vid.readyState < 3 || aud.readyState < 3){
            return;
        }
        aud.play();
        playb = setInterval(function(){
                if (vid.readyState < 3){
                    aud.pause();
                } else {
                    if (aud.paused){
                        aud.play();
                    }
                }
                if (aud.readyState < 3){
                    vid.pause();
                } else {
                    if (vid.paused){
                        vid.play();
                    }
                }
                if (Math.abs(vid.currentTime - aud.currentTime) > 0.3){
                    aud.currentTime = vid.currentTime;
                }
            }, 10);
    }
    function pause(){
        clearTimeout(playb);
        playb = "";
        vid.pause();
        aud.pause();
    }
    let di = (c) => {return d.getElementById(c)};
    let sug = di("suggest");
    let spsearch;
    let search = di("search");
    setInterval(function(){
        if (spsearch != search.value){
            spsearch = search.value;
            suggest();
        }
    }, 200);
    function send(e){
        e.preventDefault();
        location.href = `./search?q=${search.value}&page=1`;
    }
    async function suggest(){
        let suggestthing;
        await fetch("/suggest?q="+search.value).then((r) => r.json()).then((p) => {suggestthing = p});
        sug.innerHTML = "";
        suggestthing.forEach(e => {
            let button = d.createElement("button");
            button.className += "btn btn-outline-success";
            button.setAttribute("onclick", "setvalue(event);");
            button.innerText = e;
            sug.appendChild(button);
        });
        search.focus();
    }
    function setvalue(e){
            search.value = e.target.innerHTML;
            sug.innerHTML = "";
    }
    function ga_changeload(){
        gtag('event', 'update_vid_and_aud', {
            'gashitu':vq.value,
            'onshitu':aq.value
        });
    }
</script>
