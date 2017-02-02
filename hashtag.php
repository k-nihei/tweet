<?php
 
require_once("oauth/TwitterOAuth.php");
 
$consumerKey = "JZO7anqtpuCV845XjKvTIAID3";
$consumerSecret = "cflqal7J36vbJUAd7wNaYBe3ZBrvrJMNVsH6n8QPmKQFVNDcnZ";
$accessToken = "106972181-9w4RUGjypkPd08ntxwLlwUvxMJzzlklwp0RzGd4W";
$accessTokenSecret = "12UxzLxNm5qn4LLjt2iwL6IT65HWPe7QTyykrGzUniDb2";
 
$twObj = new TwitterOAuth($consumerKey,$consumerSecret,$accessToken,$accessTokenSecret);

$andkey = "webnaut AND beeworks";
$options = array('q'=>$andkey,'count'=>'30');
 
$json = $twObj->OAuthRequest(
    'https://api.twitter.com/1.1/search/tweets.json',
    'GET',
    $options
);
 
$jset = json_decode($json, true);

foreach ($jset['statuses'] as $result){
    $name = $result['user']['name'];
    $link = $result['user']['profile_image_url'];
    $content = $result['text'];
    $updated = $result['created_at'];
    $time = $time = date("Y-m-d H:i:s",strtotime($updated));
 
    echo "<img src='".$link."''>"." | ".$name." | ".$content." | ".$time;
    echo '<br>';
}
?>