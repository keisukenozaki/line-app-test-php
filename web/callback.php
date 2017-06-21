<?php
$accessToken = getenv('LINE_CHANNEL_ACCESS_TOKEN');

$channel_id = "1517932295";
$channel_secret = "5ff988446003c365d05128b99d797582";

$MENU_KNOW_1 = "温度を知りたい";
$MENU_KNOW_2 = "湿度を知りたい";
$MENU_KNOW_3 = "音声情報を知りたい";
$MENU_KNOW_4 = "電力を知りたい";
$MENU_KNOW_5 = "電気料金を知りたい";
$MENU_KNOW_6 = "...";


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
$userProfile = api_get_user_profile_request($userId);
$profileObj = json_decode($userProfile, true);

$displayName = $profileObj["displayName"];

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
} else if ($text == $MENU_KNOW_1) {
	$response_format_text = menuKnow01();
} else if ($text == $MENU_KNOW_2) {
	$response_format_text = menuKnow02();
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
	    "Authorization: Bearer " . $GLOBALS['accessToken']
	    ));
	$result = curl_exec($ch);
	curl_close($ch);
    return $result;
}
//	    "X-Line-ChannelID: " . $GLOBALS['channel_id'],
//		"X-Line-ChannelSecret: " .$GLOBALS['channel_secret'],

// メニューの１番目が押された場合
function menuKnow01() {
	$textData = [
			"type" => "template",
			"altText" => "どちらの部屋の温度を知りたいですか",
			"template" => [
					"type" => "buttons",
					"thumbnailImageUrl" => "https://" . $_SERVER['SERVER_NAME'] . "/01.jpg",
					"title" => "温度確認",
					"text" => "どちらの部屋の温度が知りたいですか",
					"actions" => [
							[
									"type" => "message",
									"label" => "リビングルーム",
									"text" => "リビングルームの温度を確認して"
							],
							[
									"type" => "message",
									"label" => "○○ちゃんの部屋",
									"text" => "○○ちゃんの部屋の温度を確認して"
							],
							[
									"type" => "message",
									"label" => "○○くんの部屋",
									"text" => "○○くんの部屋の温度を確認して"
							]
					]
			]
	];
	return $textData;
}
// メニューの２番目が押された場合
function menuKnow02() {
	$textData = [
			"type" => "template",
			"altText" => "現在の湿度は63%です",
			"template" => [
					"type" => "message",
					"title" => "湿度確認",
					"text" => "現在の湿度は63%です"
			]
	];
	return $textData;
}
