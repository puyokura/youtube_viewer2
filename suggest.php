<?php
    header("Content-type: application/JSON");
    require_once("./parts/invwork.php");
    require_once("./parts/functions.php");
    // パスワード認証を削除
    if (!isset($_GET["q"])){
        echo json_encode(array("error" => "Too short parameters"));
        die();
    }
    $invget = new inv();
    $jsonvalue = $invget->fetchurls("/ac/", array("q" => urlencode($_GET["q"])), "https://ac.duckduckgo.com");
    if (!$jsonvalue){
        echo json_encode(array("error" => "Fetch failed"));
        die();
    }
    $jsonvalue1 = array();
    foreach ($jsonvalue as $k) {
        array_push($jsonvalue1, $k["phrase"]);
    }
    echo (json_encode($jsonvalue1));