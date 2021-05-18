<?php
//共通変数・関数ファイルの読み込み
require('function.php');

Logger::debug('********************************************');
Logger::debug('ゲームクリア画面');
Logger::debug('********************************************');


?>

<?php
$siteTitle = 'クリア画面';
require('head.php');
?>

<body class="bg-svg">
    <div class="msg-area">
        <p class="judge-msg">ゲームクリア!!</p>
    </div>
    <div class="start-menu">
        <ul>
            <li><a class="btn" href="GenruSelect.php">ジャンル選択へ戻る</a></li>
            <li><a class="btn" href="index.php">トップへ戻る</a></li>
        </ul>
    </div>
</body>
</html>