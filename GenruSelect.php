<?php
//共通変数・関数ファイルの読み込み
require('function.php');

Logger::debug('********************************************');
Logger::debug('ジャンル選択画面');
Logger::debug('********************************************');

//post送信がある場合
if(!empty($_POST)){
    Logger::debug('POST送信があります。');
    Logger::debug('POST情報:'.print_r($_POST, true));

    if(!empty($_POST['genru-battle'])){
        $_SESSION['genru'] = 1;
    }else if(!empty($_POST['genru-cute'])){
        $_SESSION['genru'] = 2;
    }

    //ゲーム画面に遷移します。
    header("Location:Start.php");
}

?>



<?php
$siteTitle = 'ジャンル選択';
require('head.php');
?>
<body class="bg-svg">
    <div class="select-title">
        <h1>ジャンル選択</h1>
    </div>
    <form class="form" method="post">
        <div class="select-menu">
            <ul>
                <li><input class="btn" type="submit" value="バトル系アニメ!!" name="genru-battle"></li>
                <li><input class="btn" type="submit" value="かわいい系アニメ!!" name="genru-cute"></li>
                <li><a class="btn" href="index.php">トップへ戻る</a></li>
            </ul>
        </div>
    </form>
</body>
</html>