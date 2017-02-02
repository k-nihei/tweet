<?php

	// 設定
	$api_key = 'EOtxYrUULah2wcX8DtaMDYQpA' ;		// APIキー
	$api_secret = '3FetIXJheplcbT30c6GZ6o9EXuovzjqtj1Th6cfL5G6SdlPNu7' ;		// APIシークレット
	$access_token = '106972181-de7MOCY8Nz2N6noh4cUv50bQa9AT0AHYckC8RNSU' ;		// アクセストークン
	$access_token_secret = '9H8IWc2nS7TQO2iOrrrX4558O8IJ0AIyO9Sg97JSTw4Dn' ;		// アクセストークンシークレット
	$request_url = 'https://api.twitter.com/1.1/statuses/retweets_of_me.json' ;		// エンドポイント
	$request_method = 'GET' ;

	// パラメータA (オプション)
	$params_a = array(
//		"count" => "30",
//		"since_id" => "643299864344788992",
//		"max_id" => "643299864344788992",
//		"trim_user" => "true",
//		"include_entities" => "true",
//		"include_user_entities" => "true",
	) ;

	// キーを作成する (URLエンコードする)
	$signature_key = rawurlencode( $api_secret ) . '&' . rawurlencode( $access_token_secret ) ;

	// パラメータB (署名の材料用)
	$params_b = array(
		'oauth_token' => $access_token ,
		'oauth_consumer_key' => $api_key ,
		'oauth_signature_method' => 'HMAC-SHA1' ,
		'oauth_timestamp' => time() ,
		'oauth_nonce' => microtime() ,
		'oauth_version' => '1.0' ,
	) ;

	// パラメータAとパラメータBを合成してパラメータCを作る
	$params_c = array_merge( $params_a , $params_b ) ;

	// 連想配列をアルファベット順に並び替える
	ksort( $params_c ) ;

	// パラメータの連想配列を[キー=値&キー=値...]の文字列に変換する
	$request_params = http_build_query( $params_c , '' , '&' ) ;

	// 一部の文字列をフォロー
	$request_params = str_replace( array( '+' , '%7E' ) , array( '%20' , '~' ) , $request_params ) ;

	// 変換した文字列をURLエンコードする
	$request_params = rawurlencode( $request_params ) ;

	// リクエストメソッドをURLエンコードする
	// ここでは、URL末尾の[?]以下は付けないこと
	$encoded_request_method = rawurlencode( $request_method ) ;
 
	// リクエストURLをURLエンコードする
	$encoded_request_url = rawurlencode( $request_url ) ;
 
	// リクエストメソッド、リクエストURL、パラメータを[&]で繋ぐ
	$signature_data = $encoded_request_method . '&' . $encoded_request_url . '&' . $request_params ;

	// キー[$signature_key]とデータ[$signature_data]を利用して、HMAC-SHA1方式のハッシュ値に変換する
	$hash = hash_hmac( 'sha1' , $signature_data , $signature_key , TRUE ) ;

	// base64エンコードして、署名[$signature]が完成する
	$signature = base64_encode( $hash ) ;

	// パラメータの連想配列、[$params]に、作成した署名を加える
	$params_c['oauth_signature'] = $signature ;

	// パラメータの連想配列を[キー=値,キー=値,...]の文字列に変換する
	$header_params = http_build_query( $params_c , '' , ',' ) ;

	// リクエスト用のコンテキスト
	$context = array(
		'http' => array(
			'method' => $request_method , // リクエストメソッド
			'header' => array(			  // ヘッダー
				'Authorization: OAuth ' . $header_params ,
			) ,
		) ,
	) ;

	// パラメータがある場合、URLの末尾に追加
	if( $params_a ) {
		$request_url .= '?' . http_build_query( $params_a ) ;
	}

	// オプションがある場合、コンテキストにPOSTフィールドを作成する (GETの場合は不要)
//	if( $params_a ) {
//		$context['http']['content'] = http_build_query( $params_a ) ;
//	}

	// cURLを使ってリクエスト
	$curl = curl_init() ;
	curl_setopt( $curl, CURLOPT_URL , $request_url ) ;
	curl_setopt( $curl, CURLOPT_HEADER, 1 ) ; 
	curl_setopt( $curl, CURLOPT_CUSTOMREQUEST , $context['http']['method'] ) ;	// メソッド
	curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER , false ) ;	// 証明書の検証を行わない
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER , true ) ;	// curl_execの結果を文字列で返す
	curl_setopt( $curl, CURLOPT_HTTPHEADER , $context['http']['header'] ) ;	// ヘッダー
//	if( isset( $context['http']['content'] ) && !empty( $context['http']['content'] ) ) {		// GETの場合は不要
//		curl_setopt( $curl , CURLOPT_POSTFIELDS , $context['http']['content'] ) ;	// リクエストボディ
//	}
	curl_setopt( $curl , CURLOPT_TIMEOUT , 5 ) ;	// タイムアウトの秒数
	$res1 = curl_exec( $curl ) ;
	$res2 = curl_getinfo( $curl ) ;
	curl_close( $curl ) ;

	// 取得したデータ
	$json = substr( $res1, $res2['header_size'] ) ;		// 取得したデータ(JSONなど)
	$header = substr( $res1, 0, $res2['header_size'] ) ;	// レスポンスヘッダー (検証に利用したい場合にどうぞ)

	// [cURL]ではなく、[file_get_contents()]を使うには下記の通りです…
	// $json = file_get_contents( $request_url , false , stream_context_create( $context ) ) ;

	// JSONをオブジェクトに変換
	$obj = json_decode( $json ) ;

	// HTML用
	$html = '' ;

	// タイトル
	$html .= '<h1 style="text-align:center; border-bottom:1px solid #555; padding-bottom:12px; margin-bottom:48px; color:#D36015;">GET statuses/retweets_of_me</h1>' ;

	// エラー判定
	if( !$json || !$obj ) {
		$html .= '<h2>エラー内容</h2>' ;
		$html .= '<p>データを取得することができませんでした…。設定を見直して下さい。</p>' ;
	}

	// 検証用
	$html .= '<h2>取得したデータ</h2>' ;
	$html .= '<p>下記のデータを取得できました。</p>' ;
	$html .= 	'<h3>ボディ(JSON)</h3>' ;
	$html .= 	'<p><textarea style="width:80%" rows="8">' . $json . '</textarea></p>' ;
	$html .= 	'<h3>レスポンスヘッダー</h3>' ;
	$html .= 	'<p><textarea style="width:80%" rows="8">' . $header . '</textarea></p>' ;

	// 検証用
	$html .= '<h2>リクエストしたデータ</h2>' ;
	$html .= '<p>下記内容でリクエストをしました。</p>' ;
	$html .= 	'<h3>URL</h3>' ;
	$html .= 	'<p><textarea style="width:80%" rows="8">' . $context['http']['method'] . ' ' . $request_url . '</textarea></p>' ;
	$html .= 	'<h3>ヘッダー</h3>' ;
	$html .= 	'<p><textarea style="width:80%" rows="8">' . implode( "\r\n" , $context['http']['header'] ) . '</textarea></p>' ;

	// フッター
	$html .= '<small style="display:block; border-top:1px solid #555; padding-top:12px; margin-top:72px; text-align:center; font-weight:700;">プログラムの説明: <a href="https://syncer.jp/Web/API/Twitter/REST_API/GET/statuses/retweets_of_me/" target="_blank">SYNCER</a></small>' ;

	// 出力 (本稼働時はHTMLのヘッダー、フッターを付けよう)
	echo $html ;