<?php
// 設定の読み込み
require_once("./settings/main.php");
require_once("./parts/functions.php");
gtagOut();
?>
<!DOCTYPE HTML>
<head>
    <meta charset="UTF-8">
    <title>検索は200なんよ</title>
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
            width: 100vw;
            display: table-cell;
            vertical-align: middle;
            height: 100vh;
        }
        .flex{
            display:flex;
            justify-content: center;
        }
        .searchcontainer{
            border:1px solid #fff;
            border-radius: 15px;
        }
        .box{
            width:60vw;
            padding:30px;
            color:#121212;
        }
        .search{
            width:60vw;
            height:1.5rem;
        }
        .box h1{
            text-align:center;
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
                    <h1>なんかちゅーぶ</h1><br>
                    <form onsubmit="return send(event)">
                        <input type="text" class="search" id="search" placeholder="検索...">
                    </form>
                    <span id="suggest"></span>
                </div>
            </div>
        </div>
    </div>
</body>
<script>
    const d = document;
    let di = (c) => {return d.getElementById(c)};
    let sug = di("suggest");
    let search = di("search");
    let spsearch;
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
        let suggestthing = await fetch("./suggest?q="+search.value).then((r) => r.json()).then((p) => {return p;});
        sug.innerHTML = "";
        suggestthing.forEach(e => {
            let button = d.createElement("button");
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
</script>