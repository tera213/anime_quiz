<?php
//共通変数・関数ファイルの読み込み
require('function.php');

Logger::debug('********************************************');
Logger::debug('クイズ画面');
Logger::debug('********************************************');

Logger::debug('ジャンル：'.$_SESSION['genru']);

Logger::debug('セッションの中身:'.print_r($_SESSION, true));

//初回起動時の処理
if(!isset($_SESSION['initFlg'])){
    Logger::debug('初回アクセスまたはリスタートです。');

    //クイズ取得のクラスをインスタンス
    $dbGet = new DBGetData();
    //クイズの全IDを取得
    $dbQuizIdList = $dbGet->getQuizList($_SESSION['genru']);
    //ランダムでクイズを10個取得
    $_SESSION['dbQuizData'] = $dbGet->getQuizRand($dbQuizIdList, $_SESSION['genru']);
    
    //出題者クラスをインスタンス
    $_SESSION['quesioner'] = new Questioner('三玖', 'image/miku.png');
    //回答フォーム生成
    $_SESSION['answerArray'] = $_SESSION['quesioner']->answerFormCreate($_SESSION['dbQuizData'][0]);

    //初回起動かどうかのフラグ
    $_SESSION['initFlg'] = true;

    //クイズのカウント
    $_SESSION['quizCount'] = 0;

    //正解か不正解のフラグ
    $answerFlg = false;
    //出題ページ0、回答後ページ1、不正解ページ2
    $pageFlg = 0;
}

//回答のpost送信がある場合
if(!empty($_POST['btn_answer'])){
    Logger::debug('POST送信があります。');
    Logger::debug('POST情報:'.print_r($_POST, true));

    //正解か判定する(正解の場合はtrueを返す)
    $answerFlg = $_SESSION['quesioner']->isAnswerJudg($_POST['answer']);

    if($answerFlg){
        $pageFlg = 1;
        $judgeMsg = Msg::JUG01;
        //回答回数
        $_SESSION['quizCount'] += 1;
        //回答数が10回に達した場合
        if($_SESSION['quizCount'] > 9){
            //クイズ終了の処理に遷移
            session_destroy();
            header("Location:Clear.php");
            exit;
        }

    }else{
        $pageFlg = 2;
        $judgeMsg = Msg::JUG02;
        //ジャンルと判定メッセージ以外のセッションは削除
        
    }

    
}

//次に進むが押された場合
if(!empty($_POST['btn_next'])){
    Logger::debug('次へ進むが選択されました。');
    //回答フォーム生成
    $_SESSION['answerArray'] = $_SESSION['quesioner']->answerFormCreate($_SESSION['dbQuizData'][$_SESSION['quizCount']]);
    //ページフラグを出題ページに
    $pageFlg = 0;
}

//もう一度挑戦が押された場合
if(!empty($_POST['btn_retry'])){
    Logger::debug('もう一度挑戦するが選択されました。');

    //ジャンル名避難
    $tmp_genru = $_SESSION['genru'];
    Logger::debug($tmp_genru);

    //セッション削除
    $_SESSION = array();

    //ジャンル名のみセッションに格納
    $_SESSION['genru'] = $tmp_genru;

    Logger::debug('セッションの中身:'.print_r($_SESSION, true));
    //クイズスタート画面に遷移
    header("Location:Start.php");
    exit();
}

//クイズをやめるが押された場合
if(!empty($_POST['btn_giveup'])){
    Logger::debug('やめるが選択されました。');

    //セッションを削除
    session_destroy();

    //終了後画面に遷移
    header("Location:End.php");
}

?>




<?php
$siteTitle = 'クイズ画面';
require('head.php');
?>
<?php if($pageFlg == 0){ //出題ページ
?>
<body class="bg-svg">
    <div class="quiz-area">
        <h1>問題<?php echo $_SESSION['quizCount']+1 ?></h1>
        <textarea name="quiz" cols="30" rows="10" readonly><?php echo trim($_SESSION['dbQuizData'][$_SESSION['quizCount']]['question']) ?></textarea>
    </div>
    <div class="quiz-menu">
        <img class="questioner" src="<?php echo $_SESSION['quesioner']->getImg() ?>" alt="questioner">
        <div class="answer-menu">
            <form method="POST">
                <?php 
                    $i = 0;
                    foreach($_SESSION['answerArray'] as $key => $val){
                        $i++;
                ?>
                        <input class="radio-btn" id="radio<?php echo $i ?>" type="radio" name="answer" value="<?php echo $key?>">
                        <label class="radio-label" for="radio<?php echo $i ?>"><?php echo $i.':'. $val ?></label>
                <?php
                    }
                ?>
                <div class="submit-btn">
                    <input class="btn" type="submit" value="答える!" name="btn_answer">
                    <input class="btn" type="submit" value="クイズをやめる" name="btn_giveup">
                </div>
            </form>
        </div>
    </div>
</body>
</html>
<?php
}else{
?>
<body class="bg-svg">
        <div class="msg-area">
            <p class="judge-msg"><?php echo $judgeMsg ?></p>
        </div>
        <div class="btn-area">
            <form method="POST">
                <div class="submit-btn">
                    <?php if($pageFlg == 1){ //正解の場合
                    ?>
                        <input class="btn" type="submit" value="次に進む" name="btn_next">
                        <input class="btn" type="submit" value="クイズをやめる" name="btn_giveup">
                    <?php
                    }else if($pageFlg == 2){
                    ?>
                        <input class="btn" type="submit" value="もう一度挑戦" name="btn_retry">
                        <input class="btn" type="submit" value="クイズをやめる" name="btn_giveup">
                    <?php
                    }
                    ?>
                </div>
            </form>
        </div>
</body>
</html>
<?php
}
?>