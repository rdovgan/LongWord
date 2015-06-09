<?php 
    global $wpdb;
    include_once '../wp-load.php' ;
    $word = $wpdb->get_row("SELECT * FROM easy_words ORDER BY rand() LIMIT 1");
    $lang = $_POST['lang'];
    $curLevel = 1;
    $curId = $word->easy_word_id;
    echo $curLevel."| ".$curId."| ";
    if($lang == 'ru'){
        echo $word->easy_word_ru;
    }else{
        echo $word->easy_word_ua;
    }