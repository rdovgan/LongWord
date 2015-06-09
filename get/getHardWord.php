<?php 
    global $wpdb;
    include_once '../wp-load.php' ;
    $word = $wpdb->get_row("SELECT * FROM hard_words ORDER BY rand() LIMIT 1");
    $lang = $_POST['lang'];
    $curLevel = 3;
    $curId = $word->hard_word_id;
    echo $curLevel."| ".$curId."| ";
    if($lang == 'ru'){
        echo $word->hard_word_ru;
    }else{
        echo $word->hard_word_ua;
    }