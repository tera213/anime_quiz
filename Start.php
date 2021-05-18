<?php
//共通変数・関数ファイルの読み込み
require('function.php');

Logger::debug('********************************************');
Logger::debug('クイズスタート画面');
Logger::debug('********************************************');

Logger::debug('セッションの中身:'.print_r($_SESSION, true));

?>

<?php
$siteTitle = 'スタート画面';
require('head.php');
?>

<body class="bg-svg">
    <div class="start-title">
        <h1>GAME START</h1>
    </div>
    <div class="start-menu">
        <ul>
            <li><a class="btn go-btn" href="Game.php">GO!!</a></li>
            <li><a class="btn" href="GenruSelect.php">ジャンル選択へ戻る</a></li>
            <li><a class="btn" href="index.php">トップへ戻る</a></li>
        </ul>
    </div>
</body>
</html>