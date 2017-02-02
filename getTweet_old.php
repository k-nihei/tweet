<?php

 $z_dirname = dirname(__FILE__);    // disp.php でも利用
require_once($z_dirname . 'oauth/TwitterOAuth.php');
 
// 初期設定
$count = 200; // タイムライン ツイート読み込み数
$disp_count = 6; // ツイート表示数。実際に表示される数は、ここで設定した数よりも-1されます（本ファイル後述の rtrim で削除）
$delay = 5*60; // データファイルの更新間隔(sec)
$twitter_userid = '710314032398381056';
 
// api認証KEY
define('CONSUMER_KEY', 'EOtxYrUULah2wcX8DtaMDYQpA');
define('CONSUMER_SECRET', '3FetIXJheplcbT30c6GZ6o9EXuovzjqtj1Th6cfL5G6SdlPNu7');
define('ACCESS_TOKEN', '106972181-de7MOCY8Nz2N6noh4cUv50bQa9AT0AHYckC8RNSU');
define('ACCESS_TOKEN_SECRET', '9H8IWc2nS7TQO2iOrrrX4558O8IJ0AIyO9Sg97JSTw4Dn');
 
$now = date("Y-m-d H:i:s");
$s_now = strtotime($now);
 
$rwt = fopen($z_dirname . '/timestamp.php','r') or die ('file open error... timestamp.php read');
$writetime = fgets($rwt);
$s_writetime = strtotime($writetime);
fclose($rwt); 
 
// タイムスタンプが空、または更新間隔以上の時間経過で書き込み
if ($writetime == "" || $delay < ($s_now - $s_writetime)){
 
    // tweet取得
    $twObj = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);
 
    $Request = $twObj -> OAuthRequest(
        'https://api.twitter.com/1.1/statuses/retweets_of_me.json',
        'GET',
        array(
            'count' => $count,
            'include_entities' => 'true',
            'user_id' => $twitter_userid)
        );
 
    // jsonデータ整形
    $Req = json_decode($Request, true);     // 配列に格納
    $Req = sortArray($Req, 'retweet_count');    // リツイート数でソート
    $Req = cutArray($Req, $disp_count);     // 表示する数を調整
 
    for($i = 0; $i < count($Req); $i++){
        $retweet_count = $Req[$i]['retweet_count']; // リツイート数
        $expanded_url  = $Req[$i]['entities']['urls'][0]['expanded_url'];
        
        if (preg_match("/【ブログのURL ※ example.com など】/i",$expanded_url)){
            $rtData .= $retweet_count . "," . $expanded_url . "\n"; // ブログ記事に関係するツイートのみ追加
        }
    }
    $rtData = rtrim($rtData, "\n");    // 最後の連続改行を削除
 
    // タイムスタンプ更新
    $wwt = fopen ($z_dirname . '/timestamp.php', 'w') or die ('file open error... timestamp.php write');
    flock ($wwt, LOCK_EX);
    fwrite ($wwt, $now);
    flock ($wwt, LOCK_UN);
    fclose ($wwt);
 
    // データファイル更新
    $wd = fopen ($z_dirname . '/data.php', 'w') or die ('file open error... data.php write');
    flock ($wd, LOCK_EX);
    fwrite ($wd, $rtData);
    flock ($wd, LOCK_UN);
    fclose ($wd);
}
 
function sortArray($array, $sort_key_name) {
    // 要素を並べ替え
    foreach($array as $key => $row){
        $sort[$key] = $row[$sort_key_name];
    }
    //unset($row[$sort_key_name]);
    array_multisort($sort, SORT_DESC, $array);
    return $array;
}
 
function cutArray($array, $length){
    // 要素抜き出す
    $return_array = array_splice($array, 0, $length-1);
    return $return_array;
}
?>