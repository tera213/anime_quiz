<?php
//共通変数・関数ファイルの読み込み
require('function.php');

Logger::debug('********************************************');
Logger::debug('クイズ投稿画面');
Logger::debug('********************************************');

//確認か送信かフラグ
$pageFlg = 0;

//バリデーションチェックのフラグ
$validFlg = true;
//エラー項目のメッセージ格納用
$errForm = array();

//DBから都道府県取得
$dbGet = new DBGetData();
$dbPrefecture = $dbGet->getPrefectures();

//POST送信があった場合
if(!empty($_POST)){
    Logger::debug('POST送信があります。');
    Logger::debug('POST情報:'.print_r($_POST, true));

    //確認ボタンか送信ボタンかの判定フラグ
    if(!empty($_POST['btn_confirm'])){
        //確認の場合
        $pageFlg = 1;
    }else if(!empty($_POST['btn_submit'])){
        //送信の場合
        $pageFlg = 2;
    }else if(!empty($_POST['btn_back'])){
        //戻るの場合
        $pageFlg = 0;
    }
    //確認が押された場合
    if($pageFlg == 1){
        //入力内容を連想配列に詰める
        $quizArray = array(
            'name' => $_POST['name'],
            'age' => $_POST['age'],
            'prefecture_id' => $_POST['prefecture_id'],
            'genru_id' => $_POST['genru_id'],
            'question' => $_POST['question'],
            'answer_true' => $_POST['answer_true'],
            'answer_false1' => $_POST['answer_false1'],
            'answer_false2' => $_POST['answer_false2']
        );
        //バリデーションクラスをインスタンス
        $valid = new Validation();
        //全項目の入力チェック
        foreach ($quizArray as $key => $val) {
            
            $validFlg = $valid->validRequired($val);
            if(!$validFlg){
                $errForm['common'] = Msg::ERR02;
            }
        }

        if(!empty($errForm['common'])){
            Logger::debug('入力チェックにエラーがあります。');
            //入力フォームでエラー表示
            $pageFlg = 0;
        }
    }

    //送信が押された場合
    if($pageFlg === 2){
        Logger::debug('送信が選択されました。');

        $dbReg = new DBRegistData();
        $result = $dbReg->registQuiz($quizArray); 

        if($result){
            $_SESSION['succes_msg'] = MSG::SUC01;
            Logger::debug('トップページへ遷移します。');

            header("Location:index.php");
        }
    }
}

?>

<?php
//サイトの名前
$siteTitle = '投稿画面'; 
require('head.php'); 
?>

<?php if(($pageFlg) === 0){ ?>
    <body class="bg-svg">
        <form class="form" method="post">
            <p <?php if(!empty($errForm['common'])) echo 'class="err-msg"' ?>><?php if(!empty($errForm['common'])) echo $errForm['common'] ?></p>
            <label>
                <p class="form-text">ニックネーム</p>
                <input type="text" name='name' value="<?php echo FormData::formDataSupp('name') ?>">
            </label>
            <label>
                <p class="form-text">年齢</p>
                <input type="number" name='age' value="<?php echo number_format(FormData::formDataSupp('age')) ?>">
            </label>
            <label>
                <p class="form-text">お住まいの地域</p>
                <select name="prefecture_id">
                    <option value="0" selected>選択してください</option>
                    <?php
                        foreach($dbPrefecture as $key => $val){
                    ?>
                            <option value="<?php echo $val['id'] ?>" 
                            <?php
                                if(!empty($_POST["prefecture_id"])){
                                    if($_POST["prefecture_id"] == $val['id']) echo 'selected';
                                }
                            ?>
                            ><?php echo $val['name'] ?></option>
                    <?php
                        }
                    ?>
                    
                </select>
            </label>
            <label>
                <p class="form-text">クイズのジャンル</p>
                <select name="genru_id">
                    <option value="0" selected>選択してください</option>
                    <option value="1"
                        <?php if(!empty($_POST['genru_id'])){
                            if($_POST['genru_id'] == 1) echo 'selected';} 
                        ?>
                    >バトル系</option>
                    <option value="2"
                        <?php if(!empty($_POST['genru_id'])){
                            if($_POST['genru_id'] == 2) echo 'selected';} 
                        ?>
                    >かわいい系</option>
                </select>
            </label>
            <label>
                <p class="form-text">クイズ本文</p>
                <textarea name="question" cols="30" rows="10"><?php echo FormData::formDataSupp('question') ?></textarea>
            </label>
            <label>
                <p class="form-text">正解の答え</p>
                <input type="text" name='answer_true' value="<?php echo FormData::formDataSupp('answer_true') ?>">
            </label>
            <label>
                <p class="form-text">不正解の答え1</p>
                <input type="text" name='answer_false1' value="<?php echo FormData::formDataSupp('answer_false1') ?>">
            </label>
            <label>
                <p class="form-text">不正解の答え2</p>
                <input type="text" name='answer_false2' value="<?php echo FormData::formDataSupp('answer_false2') ?>">
            </label>
            <label>
                <input class="btn form-btn" type="submit" value="確認!!" name="btn_confirm">
                <a class="btn form-btn" href="index.php">トップに戻る</a>
            </label>
        </form>
    </body>
    </html>
<?php } ?>


<?php if(($pageFlg) === 1){ ?>
    <body class="bg-svg">
        <form class="form" method="post">
            <p class="form-text">ニックネーム</p>
            <p class="confirm-text"><?php echo $_POST['name'] ?></p>

            <p class="form-text">年齢</p>
            <p class="confirm-text"><?php echo $_POST['age'] ?></p>

            <p class="form-text">お住まいの地域</p>
            <p class="confirm-text">
                <?php
                    foreach($dbPrefecture as $key => $val){
                        if($_POST['prefecture_id'] == $val['id']) echo $val['name'];
                    }
                ?>
            </p>

            <p class="form-text">クイズのジャンル</p>
            <p class="confirm-text">
                <?php
                    if($_POST['genru_id'] == 1){
                        echo 'バトル系';
                    }else{
                        echo 'かわいい系';
                    }
                ?>
            </p>
            
            <p class="form-text">クイズ本文</p>
            <P class="confirm-text"><?php echo $_POST['question'] ?></P>
            
            <p class="form-text">正解の答え</p>
            <p class="confirm-text"><?php echo $_POST['answer_true'] ?></p>
            
            <p class="form-text">不正解の答え1</p>
            <p class="confirm-text"><?php echo $_POST['answer_false1'] ?></p>
            
            <p class="form-text">不正解の答え2</p>
            <p class="confirm-text"><?php echo $_POST['answer_false2'] ?></p>

            <input type="hidden" name="name" value="<?php echo $_POST['name'] ?>">
            <input type="hidden" name="age" value="<?php echo $_POST['age'] ?>">
            <input type="hidden" name="prefecture_id" value="<?php echo $_POST['prefecture_id'] ?>">
            <input type="hidden" name="genru_id" value="<?php echo $_POST['genru_id'] ?>">
            <input type="hidden" name="question" value="<?php echo $_POST['question'] ?>">
            <input type="hidden" name="answer_true" value="<?php echo $_POST['answer_true'] ?>">
            <input type="hidden" name="answer_false1" value="<?php echo $_POST['answer_false1'] ?>">
            <input type="hidden" name="answer_false2" value="<?php echo $_POST['answer_false2'] ?>">
            
            <input class="btn form-btn" type="submit" value="投稿!!" name="btn_submit">
            <input class="btn form-btn" type="submit" value="修正" name="btn_back">
           
        </form>
    </body>
    </html>
<?php } ?>
