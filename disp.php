<?php
//RT数ランキングデータ読み込み
$fr = fopen ($z_dirname . '/data.php', 'r') or die ('file open error... data.php');
 
$count = 0;
$twrank .= "<div class='twRankFlame' id='slider00'>\n"
    . "<ol class='twRankGuide'>\n";
 
while (! feof ($fr)) {
    $twdata = fgets ($fr, 4096);
    $twdata = mb_convert_encoding ($twdata, "utf8", auto);
    $twdata = preg_replace("/\n/", "", $twdata); // 正規表現で改行を削除
    
    list($tweets, $link) = explode(",", $twdata);
    $postid = url_to_postid($link); // 記事リンクから記事IDを取得 WordPressテンプレート関数
    $title  = get_the_title($postid); // 記事IDから記事タイトルを取得 WordPressテンプレート関数
    $thumbnail = get_the_post_thumbnail($postid , 'thumbnail');  // 記事IDからアイキャッチ画像を取得 WordPressテンプレート関数
    // 表示用整形
    $twrank .= "<li class='twRankCell' id='tw" . $count . "'>\n"
        . "<div class='tweets_flame'>" . $tweets . "<span>RT</span></div>\n"
        . "<div class='photo_flame'><a href='" . $link . "'>" . $thumbnail . "</a></div>\n"
        . "<div class='title_flame'><a href='" . $link . "'>" . $title . "</a></div>\n"
        . "</li>\n";
    $count ++;
}
 
$twrank .= "</ol>\n"
    . "</div>\n";
         
fclose ($fr);
 
echo $twrank;
?>