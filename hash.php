<?
require_once('./oauth/TwitterOAuth.php');

//twitterAppsで取得
$consumerKey        = JZO7anqtpuCV845XjKvTIAID3; // https://apps.twitter.com から取得
$consumerSecret     = cflqal7J36vbJUAd7wNaYBe3ZBrvrJMNVsH6n8QPmKQFVNDcnZ;　// https://apps.twitter.com から取得
$accessToken        = 106972181-9w4RUGjypkPd08ntxwLlwUvxMJzzlklwp0RzGd4W;　// https://apps.twitter.com から取得
$accessTokenSecret  = 12UxzLxNm5qn4LLjt2iwL6IT65HWPe7QTyykrGzUniDb2;　// https://apps.twitter.com から取得

$search_key = "#google -RT";　//検索キーワード, -RTはリトイートを除く

$options = array('q'=>$search_key, 'count'=>'100', 'lang'=>"jp", 'result_type' => 'recent');

$since_id = getMaxID(); //DBから現在の最大TweetIDを取得する処理
if ($since_id){
    $options['since_id'] = $since_id; //前回の最後に取得したツイートIDから
}
$twObj = new TwitterOAuth(
     $consumerKey, 
     $consumerSecret,
     $accessToken,
     $accessTokenSecret);

$json_data = $twObj->get(
    'search/tweets',
    $options
);

$statuses = null;
if ($json_data){
    $statuses = $json_data['statuses']; //ステータス情報取得
}

if ($statuses && is_array($statuses)) {
    $sts_cnt = count($statuses);
    // 一番古いデータからDBへ書き込む
    for ($i = $sts_cnt-1; $i >= 0; $i--) {
        $result = $statuses[$i];
        $has_media = true;
        $screen_name = $result['user']['screen_name'];
        $twitter_id = $result['user']['id_str'];

        //$cnt++;
        $tw_created_date = date('Y-m-d H:i:s', strtotime($result["created_at"]));
        $user_name      = $result['user']['name'];
        $tweet_id       = $result['id_str'];
        $profile_url    = $result['user']['profile_image_url'];
        $tweet_text     = $result['text'];

        $img_src = $short_url = $display_url = "";

        if (isset($result["entities"]['media'])){ //写真等がある場合、取得（ビデオリンクの場合、違う方法で取得可能）
            // 最初のメディアのみを取得する（全部取得できるように修正を)
            $img_src        = $result["entities"]['media'][0]['media_url'];
            $short_url      = $result["entities"]['media'][0]['url'];
            $display_url    = $result["entities"]['media'][0]['display_url'];
        }

        // DBへデータを書き込む処理
        writeToDatabase();
    }
}