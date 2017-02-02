<?php

// 初期化処理 ================================================================
define('INTERNAL_ENCODING', 'UTF-8');
mb_internal_encoding(INTERNAL_ENCODING);
mb_regex_encoding(INTERNAL_ENCODING);
define('MYSELF', basename($_SERVER['SCRIPT_NAME']));
define('REFERENCE', 'http://www.pahoo.org/e-soul/webtech/php06/php06-48-01.shtm');

//プログラム・タイトル
define('TITLE', 'リツイート回数上位ランク');

//リリース・フラグ（公開時にはTRUEにすること）
define('FLAG_RELEASE', TRUE);

//ランキング件数（100以下）
define('NUMBER_OF_RANK', 10);

//PHP5判定
if (! isphp5over()) {
	echo 'Error > Please let me run on PHP5 or more...';
	exit(1);
}

//Twitter API クラス
require_once('pahooTwitterAPI.php');

/**
 * 共通HTMLヘッダ
 * @global string $HtmlHeader
*/
$encode = INTERNAL_ENCODING;
$title = TITLE;
$HtmlHeader =<<< EOD
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="{$encode}">
<title>{$title}</title>
<meta name="author" content="studio pahoo" />
<meta name="copyright" content="studio pahoo" />
<meta name="ROBOTS" content="NOINDEX,NOFOLLOW" />
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="cache-control" content="no-cache">
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.1/jquery.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    $('table.stripe_table_1').css('border-color', '#FFFFFF');
    $('table.stripe_table_1 th').css('background-color', '#FFBB00');
    $('table.stripe_table_1 th').css('text-align', 'center');
    $('table.stripe_table_1 th').css('padding', '4px');
    $('table.stripe_table_1 tr:even').css('background-color', '#FFDD88');
    $('table.stripe_table_1 tr:odd' ).css('background-color', '#FFFFFF');
    $('table.stripe_table_1 td').css('padding', '4px');
});
</script>
</head>

EOD;

/**
 * 共通HTMLフッタ
 * @global string $HtmlFooter
*/
$HtmlFooter =<<< EOD
</body>
</html>

EOD;

// サブルーチン ==============================================================
/**
 * エラー処理ハンドラ
*/
function myErrorHandler ($errno, $errmsg, $filename, $linenum, $vars) {
	echo "Sory, system error occured !";
	exit(1);
}
error_reporting(E_ALL);
if (FLAG_RELEASE)	$old_error_handler = set_error_handler('myErrorHandler');

/**
 * PHP5以上かどうか検査する
 * @return	bool TRUE：PHP5以上／FALSE:PHP5未満
*/
function isphp5over() {
	$version = explode('.', phpversion());

	return $version[0] >= 5 ? TRUE : FALSE;
}

/**
 * リツイートを取得
 * @param	array  $retweet リツイート格納配列
 * @param	string $webapi  WebAPIのURL
 * @return	int 取得数／FALSE 取得失敗
*/
function getReTweets(&$retweets, &$webapi) {
	$ptw = new pahooTwitterAPI();

	$url    = 'https://api.twitter.com/1.1/statuses/retweets_of_me.json';
	$param  = array('count' => 100);
	$method = 'GET';

	$ret = $ptw->request_user($url, $method, $param);
	if ($ret == FALSE)	return FALSE;
	$webapi = $ptw->webapi;

	//情報を配列へ格納
	$cnt = 0;
	foreach ($ptw->responses as $item) {
		$id = $item->id_str;
		$retweets[$id]['url'] = 'https://twitter.com/' . $item->user->screen_name . '/status/' . $id;
		$retweets[$id]['created_at'] = $item->created_at;
		$retweets[$id]['text'] = $item->text;
		$retweets[$id]['retweet_count'] = $item->retweet_count;
		$cnt++;
	}

	//リツイート回数の降順ソート
	uasort($retweets, function($a, $b) {
		return $b['retweet_count'] - $a['retweet_count'];
	});

	$ptw = NULL;

	return $cnt;
}

/**
 * HTML BODYを作成する
 * @param	string $res 画像またはエラーメッセージ
 * @param	array  $retweet リツイート格納配列
 * @param	string $webapi  WebAPIのURL
 * @return	string HTML BODY
*/
function makeCommonBody($res, $retweets, $webapi) {
	$myself = MYSELF;
	$refere = REFERENCE;
	$phpver = phpversion();
	$title  = TITLE;
	$version = '<span style="font-size:small;">' . date('Y/m/d版', filemtime(__FILE__)) . '</span>';

	if (! FLAG_RELEASE) {
		$msg =<<< EOT
PHPver : {$phpver}<br />
WebAPI : <a href="{$webapi}">{$webapi}</a>
EOT;
	} else {
		$msg = '';
	}

	if ($res == '') {
		$res =<<< EOD
<table class="stripe_table_1" style="width:800px;">
<tr>
<th>回数</th>
<th>日　時</th>
<th>内　容</th>
</tr>

EOD;
		$cnt = 0;
		foreach ($retweets as $retweet) {
			if ($cnt >= NUMBER_OF_RANK)	break;
			$ti = strtotime($retweet['created_at']);
			$str = date('Y/m/d h:m', $ti);
			$res .=<<< EOD
<tr>
<td style="text-align:right;">{$retweet['retweet_count']}</td>
<td style="font-size:small;"><a href="{$retweet['url']}">{$str}</a></td>
<td>{$retweet['text']}</td>
</tr>

EOD;
			$cnt++;
		}
		$res .=<<< EOD
</table>

EOD;
	}

	$body =<<< EOT
<body>
<h2>{$title} {$version}</h2>
{$res}

<div style="border-style:solid; border-width:1px; margin:20px 0px 0px 0px; padding:5px; width:780px; font-size:small; overflow-wrap:break-word; word-break:break-all;">
※参考サイト：<a href="{$refere}">{$refere}</a>
<p>{$msg}</p>
</div>
</body>

EOT;
	return $body;
}

// メイン・プログラム ======================================================
$retweets = array();
$msg = $webapi = '';
$res = getReTweets($retweets, $webapi);
if ($res == FALSE) {
	$msg = "<span style=\"color:red;\">取得失敗</span>\n";
}

$HtmlBody = makeCommonBody($msg, $retweets, $webapi);

// 表示処理
echo $HtmlHeader;
echo $HtmlBody;
echo $HtmlFooter;

/*
** バージョンアップ履歴 ===================================================
 *
 * @version  2.0  2016/02/13  pahooTwitterAPIクラス利用
 * @version  1.0  2015/08/13
*/
?>
