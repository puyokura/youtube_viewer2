<?php
    // cookieを読み込む
    function check_cookie(){
        global $AS;
        // gizoucookieもしくはgizouOKnameが設定されていなかったら終了
        if (!isset($AS) || $AS["gizoucookie"] == "" || $AS["gizouOKname"] == ""){
            error_log("cookieの値が設定されていません！終了します。");
            http_response_code(500);
            echo("error while checking.");
            die();
        }
        if (!isset($_COOKIE[$AS["gizoucookie"]]) || $_COOKIE[$AS["gizoucookie"]] != $AS["gizouOKname"]){
            // cookieなかったらリダイレクト処理
            return false; 
        } else {
            // そのまま続行
            return true;
        }
    }
    function redirectToMain(){
            // リダイレクト処理、必要時のみ
            http_response_code(301);
            header("Location: /");
    }
function gtagOut(){
    // gtagの出力
    echo ('<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-VB360QCBJ5"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag(\'js\', new Date());

  gtag(\'config\', \'G-VB360QCBJ5\');
</script>');
}