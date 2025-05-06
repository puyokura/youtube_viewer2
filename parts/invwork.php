<?php
    // 設定の読み込み
    require_once("./settings/main.php");
    require_once("./parts/functions.php");
    class inv{
        private $domaininv;
        private $invidiousInstances;
        private $currentInstanceIndex = 0;
        
        public function __construct(){
            if (!isset($GLOBALS["AS"]["invidious"]) || empty($GLOBALS["AS"]["invidious"])){
                // inivdiousが取れなかったら終了
                error_log("invidiousの値が設定されていません！終了します。");
                http_response_code(500);
                echo("error while checking.");
                die();
            }
            
            // 配列でも文字列でも対応できるようにする
            $this->invidiousInstances = is_array($GLOBALS["AS"]["invidious"]) 
                ? $GLOBALS["AS"]["invidious"] 
                : [$GLOBALS["AS"]["invidious"]];
                
            // 最初のインスタンスを設定
            $this->domaininv = $this->invidiousInstances[0];
        }
        public function fetchurls(string $url, array $params, string $domain = null){
            // 外部ドメインが指定されている場合はそれを使用
            if ($domain !== null) {
                return $this->fetchFromDomain($url, $params, $domain);
            }
            
            // すべてのインスタンスを順番に試す
            $instanceCount = count($this->invidiousInstances);
            $startIndex = $this->currentInstanceIndex;
            
            // 現在のインデックスから開始して、すべてのインスタンスを試す
            for ($i = 0; $i < $instanceCount; $i++) {
                $index = ($startIndex + $i) % $instanceCount;
                $currentDomain = $this->invidiousInstances[$index];
                
                $result = $this->fetchFromDomain($url, $params, $currentDomain);
                
                if ($result !== false) {
                    // 成功したら、次回はこのインスタンスから開始
                    $this->currentInstanceIndex = $index;
                    $this->domaininv = $currentDomain;
                    return $result;
                }
                
                // このインスタンスが失敗したことをログに記録
                error_log("インスタンス {$currentDomain} への接続に失敗しました。次のインスタンスを試します。");
            }
            
            // すべてのインスタンスが失敗した場合
            error_log("すべてのinvidiousインスタンスへの接続に失敗しました。");
            return false;
        }
        
        // 特定のドメインからデータを取得する内部メソッド
        private function fetchFromDomain(string $url, array $params, string $domain) {
            // コマンドを組み立てる
            $cmd = "curl -m 5 -G ";
            foreach ($params as $param => $data) {
                $cmd .= "-d ".$param."=".escapeshellarg($data)." ";
            }
            $out = null;
            $retcode = null;
            $cmd .= escapeshellarg($domain.$url);
            
            // 取ってくる
            exec($cmd, $out, $retcode);
            
            // エラーが出たら負の値を出す
            if ($retcode != 0 || empty($out)){
                return false;
            }
            
            // デコードする
            $out = json_decode($out[0], true);
            if ($out == null){
                // 空っぽだったらfalse
                return false;
            }
            
            return $out;
        }
    }