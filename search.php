<?php
    
    // 設定の読み込み
    require_once("./settings/main.php");
    require_once("./parts/functions.php");
    require_once("./parts/invwork.php");
    // パスワード認証を削除
    // q,pageを取る、なかったら終了
    if (!isset($_GET["q"]) || !isset($_GET["page"])){
        header("Content-type: application/json");
        echo (json_encode(array("error" => "Too short parameters")));
        die();
    }
    $invget = new inv();
    // コマンドを組み立て
    $comarg = array(
        "q" => urlencode($_GET["q"]),
        "page" => $_GET["page"]
    );
    // 実行
    $invdata = $invget->fetchurls("/api/v1/search", $comarg);
    if (!$invdata){
        header("Content-type: application/json");
        echo (json_encode(array("error" => "Cannot fetch!")));
        die();
    }
    gtagOut();
?>
<!DOCTYPE HTML>
<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta charset="UTF-8">
        <title>検索してみたら200なんよ</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
        <style>
            html,head.body{
                margin:0;
                padding:0;
            }
            .fixed-search{
                position:fixed;
                z-index:999;
                width:100%;
                background:#d0d0d0;
                max-height:10rem;
                top:0;
            }
            .search-padding{
                padding: 0.5em;
            }
            .search-padding button{
                background:#fff;
            }
            .suggest{
                display: flex;
                flex-wrap:wrap;
                justify-content: center;
            }
            .search-out{
                margin-top:10rem;
                width:100%;
                padding: 1rem;
            }
            .search-cards{
                display:flex;
                flex-wrap:wrap;
                gap:1rem;
            }
            #revnext{
                text-wrap: nowrap;
                text-align:center;
            }
        </style>
    </head>
    <body>
        <div class="fixed-search">
            <div class="search-padding">
                <form onsubmit="return send(event)">
                    <input type="text" class="search form-control me-2" id="search" placeholder="検索...">
                </form>
                <span id="suggest"></span>
            </div>
        </div>
        <div class="search-out">
            <div class="search-cards" id="card"></div>
            <div id="revnext"></div>
        </div>
    </body>
    <script>
        let output = <?php echo(json_encode($invdata)) ?>;
        const d = document;
        let di = (c) => {return d.getElementById(c)};
        let sug = di("suggest");
        let search = di("search");
        let card = di("card");
        let revnext = di("revnext");
        search.value = new URL(location.href).searchParams.get("q");
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
        function writeSearchResult(){
            let searchres = output.filter((s) => s.type == "video");
            let page = new URL(location.href).searchParams.get("page");
            searchres.forEach((dt) => {
                let searched = `<div class="card shadow-sm" style="width: 18rem;">
                            <a href="${"./watch?v="+dt.videoId}">
                                <img src="${dt.videoThumbnails.filter((s) => s.quality == 'default')[0].url}" class="card-img-top" alt="${dt.title}">
                            </a>
                            <div class="card-body">
                            <h5 class="card-title">${dt.title}</h5>
                            <hr>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">${dt.author}</li>
                                <li class="list-group-item">${dt.viewCount+" 回視聴"}</li>
                            </ul>
                            </div>
                            </div>`;
                card.insertAdjacentHTML("beforeend", searched);
            });
            if (!isNaN(Number(page))){
                    let nextbutton = "";
                    if (page >= 2){
                        nextbutton += `<span><a href="./search?q=${search.value}&page=${Number(page) - 1}">前へ</a></span>`;
                    }
                    if (page >= 1){
                        nextbutton += `<span style="margin-left:0.5em;"><a href="./search?q=${search.value}&page=${Number(page) + 1}">次へ</a></span>`;
                    }
                    revnext.insertAdjacentHTML("beforeend", nextbutton);
                }
        }
        writeSearchResult();
    </script>
</html>
