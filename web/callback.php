<?php
$accessToken = getenv('LINE_CHANNEL_ACCESS_TOKEN');

$channel_id = "1517932295";
$channel_secret = "5ff988446003c365d05128b99d797582";

require('../vendor/autoload.php');

//ユーザーからのメッセージ取得
$json_string = file_get_contents('php://input');
$jsonObj = json_decode($json_string);

$type = $jsonObj->{"events"}[0]->{"message"}->{"type"};
//メッセージ取得
$text = $jsonObj->{"events"}[0]->{"message"}->{"text"};
//ReplyToken取得
$replyToken = $jsonObj->{"events"}[0]->{"replyToken"};

$userId = $jsonObj->{"events"}[0]->{"source"}->{"userId"};

//メッセージ以外のときは何も返さず終了
if($type != "text"){
	exit;
}

$content = $jsonObj->result{0}->content;
$from = $content->from;

$displayName = "A";

// ユーザ情報取得
$displayName = api_get_user_profile_request($from);


// $bot = new \LINE\LINEBot($httpClient, ['channelSecret' => '<channel secret>']);
// $response = $bot->getProfile($userId);
// 
// if ($response->isSucceeded()) {
//   $profile = $response->getJSONDecodedBody();
//   $displayName = $profile['displayName'];
//   $userId = $profile['userId'];
//   $pictureUrl = $profile['pictureUrl'];
//   $statusMessage = $profile['statusMessage'];
// }



//返信データ作成
if ($text == 'はい') {
  $response_format_text = [
    "type" => "template",
    "altText" => "こちらの〇〇はいかがですか？",
    "template" => [
      "type" => "buttons",
      "thumbnailImageUrl" => "https://" . $_SERVER['SERVER_NAME'] . "/01.jpg",
      "title" => "ホーム確認",
      "text" => "どのサービスにしますか",
      "actions" => [
          [
            "type" => "postback",
            "label" => "温度確認",
            "data" => "action=buy&itemid=123"
          ],
          [
            "type" => "postback",
            "label" => "生体センサー",
            "data" => "action=pcall&itemid=123"
          ],
          [
            "type" => "uri",
            "label" => "電力量確認",
            "uri" => "https://" . $_SERVER['SERVER_NAME'] . "/"
          ],
          [
            "type" => "message",
            "label" => "その他",
            "text" => "その他を選択"
          ]
      ]
    ]
  ];
} else if ($text == 'いいえ') {
  exit;
} else if ($text == 'その他を選択') {
  $response_format_text = [
    "type" => "template",
    "altText" => "候補を３つご案内しています。",
    "template" => [
      "type" => "carousel",
      "columns" => [
          [
            "thumbnailImageUrl" => "https://" . $_SERVER['SERVER_NAME'] . "/02.jpg",
            "title" => "温度確認",
            "text" => "こちらにしますか？",
            "actions" => [
              [
                  "type" => "postback",
                  "label" => "温度確認",
                  "data" => "action=rsv&itemid=111"
              ],
              [
                  "type" => "postback",
                  "label" => "電話する",
                  "data" => "action=pcall&itemid=111"
              ],
              [
                  "type" => "uri",
                  "label" => "詳しく見る（ブラウザ起動）",
                  "uri" => "https://" . $_SERVER['SERVER_NAME'] . "/"
              ]
            ]
          ],
          [
            "thumbnailImageUrl" => "https://" . $_SERVER['SERVER_NAME'] . "/03.jpg",
            "title" => "生体確認",
            "text" => "それともこちら？（２つ目）",
            "actions" => [
              [
                  "type" => "postback",
                  "label" => "温度確認",
                  "data" => "action=rsv&itemid=222"
              ],
              [
                  "type" => "postback",
                  "label" => "電話する",
                  "data" => "action=pcall&itemid=222"
              ],
              [
                  "type" => "uri",
                  "label" => "詳しく見る（ブラウザ起動）",
                  "uri" => "https://" . $_SERVER['SERVER_NAME'] . "/"
              ]
            ]
          ],
          [
            "thumbnailImageUrl" => "https://" . $_SERVER['SERVER_NAME'] . "/04.jpg",
            "title" => "電力量確認",
            "text" => "はたまたこちら？（３つ目）",
            "actions" => [
              [
                  "type" => "postback",
                  "label" => "温度確認",
                  "data" => "action=rsv&itemid=333"
              ],
              [
                  "type" => "postback",
                  "label" => "電話する",
                  "data" => "action=pcall&itemid=333"
              ],
              [
                  "type" => "uri",
                  "label" => "詳しく見る（ブラウザ起動）",
                  "uri" => "https://" . $_SERVER['SERVER_NAME'] . "/"
              ]
            ]
          ]
      ]
    ]
  ];
} else {
  $response_format_text = [
    "type" => "template",
    "altText" => "こんにちは" . $displayName ."さん 何かご用ですか？（はい／いいえ）",
    "template" => [
        "type" => "confirm",
        "text" => "こんにちは" . $displayName ."さん 何かご用ですか？",
        "actions" => [
            [
              "type" => "message",
              "label" => "はい",
              "text" => "はい"
            ],
            [
              "type" => "message",
              "label" => "いいえ",
              "text" => "いいえ"
            ]
        ]
    ]
  ];
}

$post_data = [
	"replyToken" => $replyToken,
	"messages" => [$response_format_text]
	];

// HTTP request の仕様
// POST https://api.line.me/v2/bot/message/reply
// Request headers
// Request header	Description
// Content-Type	application/json
// Authorization	Bearer {Channel Access Token}

$ch = curl_init("https://api.line.me/v2/bot/message/reply");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json; charser=UTF-8',
    'Authorization: Bearer ' . $accessToken
    ));
$result = curl_exec($ch);
curl_close($ch);


function api_get_user_profile_request($userId) {

	// HTTP request
	// GET https://api.line.me/v2/bot/profile/{userId}
	// Request headers
	// Request header	Description
	// Authorization	Bearer {Channel Access Token}

	$ch = curl_init("https://api.line.me/v2/bot/profile/" . $userId);
//	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	    "Content-Type: application/json; charser=UTF-8",
	    "X-Line-ChannelID: " . $GLOBALS['channel_id'],
		"X-Line-ChannelSecret: " .$GLOBALS['channel_secret'],
	    "Authorization: Bearer " . $GLOBALS['accessToken']
	    ));
	$result = curl_exec($ch);
	curl_close($ch);
    return $result;


//    $url = "https://trialbot-api.line.me/v1/profiles?mids={$mid}";
//    $headers = array(
//        "X-Line-ChannelID: {$GLOBALS['channel_id']}",
//        "X-Line-ChannelSecret: {$GLOBALS['channel_secret']}"
//    ); 
// 
//    $curl = curl_init($url);
//    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
//    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//    $output = curl_exec($curl);
////    error_log($output);
//    return $output;

}

