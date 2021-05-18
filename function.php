<?php

//===========================================
// ログ
//===========================================
//ログを出力
ini_set('log_errors', 'on');
//ログの出力ファイルを指定
ini_set('error_log', 'php.log');

//===========================================
// セッション準備
//===========================================
//セッションが消されない様に置き場を変更する
session_save_path("/var/tmp/");
//ガーベージコレクションで削除される期限を伸ばす(30日間に)
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);
//ブラウザを閉じた時にクッキーが削除されない様にクッキー自体の有効期限を伸ばす
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30);
//セッションを使う
session_start();
//セッションIDを置き換える（セキュリティ対策）
session_regenerate_id();





//ログ出力クラス
class Logger {

    //デバックログ関数
    public static function debug($str, $debug_flg = true){
        if(!empty($debug_flg)){
            error_log('デバッグ：' . $str);
        }
    }
}

//メッセージクラス
class Msg{
    //エラーメッセージ
    const ERR01 = '入力必須項目です。';
    const ERR02 = '全て入力してください。';
    const ERR03 = '255文字以内で入力してください。';
    const ERR04 = '半角数字で入力してください。';
    //成功メッセージ
    const SUC01 = '投稿しました。';
    //正解ジャッジコール
    const JUG01 = '素晴らしい!正解!!';
    const JUG02 = '残念!不正解...';
}

//バリデーションクラス
class Validation{

    public function validRequired($str){
        if($str == ''){
            return false;
        }else{
            return true;
        }
    }
}

//===========================================
// DB関連
//===========================================

//DB接続設定定数
class DBConnSet{

    const DSN = "mysql:dbname=quizDB;host=localhost;charset=utf8";
    const USER = "root";
    const PASS = "root";
    const OPTION = array(
        //SQL実行失敗時にはエラーコードのみ設定
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        //デフォルトフェッチモードを連想配列形式に設定
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        //バッファードクエリを使う(一度に結果セットを全て取得し、サーバ負荷を軽減)
        //SELECTで得た結果に対してもrowCountメソッドを使えるようにする
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    );
}

//DB処理クラス
class DBProcess{
    public function dbConnect(){
        $dsn = DBConnSet::DSN;
        $user = DBConnSet::USER;
        $pass = DBConnSet::PASS;
        $option = DBConnSet::OPTION;
        //データベース接続
        $dbh = new PDO($dsn, $user, $pass, $option);
        return $dbh;
    }

    public function queryPost($dbh, $sql, $data){
        //クエリ作成
        $stmt = $dbh -> prepare($sql);
        //クエリ実行
        $stmt -> execute($data);
        if($stmt){
            Logger::debug('クエリ成功');
            return $stmt;
        }else{
            Logger::debug('クエリ失敗');
            Logger::debug('クエリに失敗したSQL文：'.$sql);
            
            return false;
        }
    }
}

//クイズ情報DB登録クラス
class DBRegistData extends DBprocess{

    public function registQuiz($array){

        try{
            //DB接続処理
            $dbh = parent::dbConnect();
            //sql文作成
            $sql = 'INSERT INTO quiz(name, age, prefecture_id, genru_id, question, answer_true, answer_false1, answer_false2, create_date) VALUE(:name, :age, :prefecture_id, :genru_id, :question, :answer_true, :answer_false1, :answer_false2, :create_date )';
            $data = array(":name" => $array['name'], ":age" => $array['age'], ":prefecture_id" => $array['prefecture_id'], ":genru_id" => $array['genru_id'], ":question" => $array['question'], ":answer_true" => $array['answer_true'], ":answer_false1" => $array['answer_false1'], ":answer_false2" => $array['answer_false2'], ":create_date" => date('Y-m-d H:i:s'));
            //クエリ実行
            $stmt = parent::queryPost($dbh, $sql, $data);
            
            if($stmt){
                Logger::debug('クイズを登録しました。');
                return true;
            }else{
                return false;
            }
        }catch(Exception $e){
            Logger::debug('エラー発生→'.$e->getMessage());
        }
    }
}

//クイズ情報取得クラス
class DBGetData extends DBprocess{

    //quizテーブルのidリスト取得
    public function getQuizList($genru){
        try{
            Logger::debug('quizテーブルのidリストを取得します。');
            //DB接続処理
            $dbh = parent::dbConnect();
            //sql文作成
            $sql = 'SELECT id FROM quiz WHERE genru_id = :genru';
            $data = array(":genru" => $genru);
            //クエリ実行
            $stmt = parent::queryPost($dbh, $sql, $data);
            
            if($stmt){
                return $stmt -> fetchAll();
            }else{
                return false;
            }
        }catch(Exception $e){
            Logger::debug('エラー発生→'.$e->getMessage());
        }
    }

    //quizテーブルからランダムにレコードを取得
    public function getQuizRand($quizList, $genru, $getCount = 10){
        try{
            Logger::debug('quizテーブルからランダムにレコードを取得します。');
            //クイズレコードのIDリスト
            $randNumList = array();
            //取得レコードの配列
            $result = array();
            //取得したクイズのIDリストからランダムで10個選ぶ
            $randNumList = array_rand($quizList, $getCount);
            //配列の中身をシャッフル
            $randNumListRnd = shuffle($randNumList);
            Logger::debug('$randNumListの中身:'.print_r($randNumListRnd,true));
            //DB接続処理
            $dbh = parent::dbConnect();
            //sql文作成
            $sql = 'SELECT id, name, age, prefecture_id, genru_id, question, answer_true, answer_false1, answer_false2 FROM quiz WHERE id = :id AND genru_id = :genru';
            for($i = 0; $i < 10; $i++){
                $data = array(":id" => $randNumList[$i], ":genru" => $genru);
                //クエリ実行
                $stmt = parent::queryPost($dbh, $sql, $data);
                $result[$i] = $stmt -> fetch(PDO::FETCH_ASSOC);
            }
            
            if($stmt){
                Logger::debug('取得した情報：'.print_r($result, true));
                return $result;
            }else{
                return false;
            }
        }catch(Exception $e){
            Logger::debug('エラー発生→'.$e->getMessage());
        }
    }

    //都道府県情報を取得
    public function getPrefectures(){
        try{
            Logger::debug('都道府県情報を取得します。');
            //DB接続処理
            $dbh = parent::dbConnect();
            //sql文作成
            $sql = 'SELECT id, name FROM prefectures';
            $data = array();
            //クエリ実行
            $stmt = parent::queryPost($dbh, $sql, $data);
            
            if($stmt){
                return $stmt -> fetchAll();
            }else{
                return false;
            }
        }catch(Exception $e){
            Logger::debug('エラー発生→'.$e->getMessage());
        }
    }
}

//===========================================
// フォーム関連
//===========================================

class FormData{
    public static function formDataSupp($str){
        if(!empty($_POST[$str])){
            return htmlspecialchars($_POST[$str], ENT_QUOTES); //サニタイズしたデータを返す
        }
    }

}

//===========================================
// クイズ関連
//===========================================

//出題者クラス
class Questioner{

    private $name;
    private $img;

    //コンストラクタ
    public function __construct($name, $img){
        $this->name = $name;
        $this->img = $img;
    }
    
    public function getName(){
        return $this->name;
    }

    public function getImg(){
        return $this->img;
    }

    //正解判定の関数
    public function isAnswerJudg($str){
        if($str == 'answer_true'){
            return true;
        }else{
            return false;
        }
    }

    //正解、不正解をコールする関数

    //回答生成の関数(答えを配列で順番をランダムに並び替えて返す)
    public function answerFormCreate($dbQuizData){

        //keysに抽出したいキーを値として格納
        $keys = ['answer_true', 'answer_false1', 'answer_false2'];
        //引数で渡された配列から答えのみ抽出して配列に格納する
        $answerArray = array_intersect_key($dbQuizData, array_flip($keys));

        //答えの順番をランダムに並び替える
        //キーを抽出
        $aryKey = array_keys($answerArray);
        //キーをシャッフル
        shuffle($aryKey);
        //並び替えた値を入れる配列
        $answerArrayRand = array();
        //並び替えた順番にキーとそれに対応する値を格納していく
        foreach($aryKey as $key){
            $answerArrayRand[$key] = $answerArray[$key];
        }
        Logger::debug(print_r($answerArrayRand, true));

        return $answerArrayRand;
    }
}

//回答者クラス...とりあえずは使わない
class Answerer{

    private $name;

}