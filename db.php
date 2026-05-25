<?php

function db_connect() {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $db = new PDO($dsn, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_AUTOCOMMIT, true);
    return $db;
}

function db_query($db, $sql, $params = []){
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

//tableはテーブル名、dataはカラム名=>値の連想配列を渡す事
function db_insert($db, $table, $data) {
    $columns = implode(", ", array_keys($data));
    $placeholders = ":" . implode(", :", array_keys($data));
    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    $stmt = $db->prepare($sql);
    $stmt->execute($data);
    return $db->lastInsertId();
}

function db_update($db, $table, $data,$where_clause, $where_params = [] ){
//dataはカラム名=>値の連想配列
// where_paramsはプレースホルダに入れるため、[":column" => value]の形式の連想配列
//where_clauseは、値を入れる場所だけを:columnの形式で置き換えたwhere句をそのまま文字列で渡す
    $set_parts = [];
    foreach(array_keys($data) as $key){
        $set_parts[] = "$key = :$key";
    }
    $columns = implode(", ", $set_parts);
    $sql = "UPDATE $table SET $columns WHERE $where_clause";
    $stmt = $db->prepare($sql);
    $stmt->execute(array_merge($data, $where_params));
}

//function db_delete($db, $table, $where):void{
//    $sql = "DELETE FROM $table WHERE $where";
//    $stmt = $db->prepare($sql);
//    $stmt->execute();
//}

function db_login($db, $user_input){
    $sql = "SELECT * FROM employee WHERE emp_id = :emp_id";
    $stmt = $db -> prepare($sql);
    $stmt -> execute(["emp_id" => $user_input["emp_id"]]);
    $user = $stmt -> fetch(PDO::FETCH_ASSOC);
    $login_success = false;
    if($user && password_verify($user_input["password"], $user["password"])){
        $login_success = true;
        session_regenerate_id(true);
        $_SESSION["connect_user"] = $user;
        $_SESSION["connection_time_limit"] = time()+3600;//1時間後にセッションの有効期限が切れる
    }
    return $login_success;
}