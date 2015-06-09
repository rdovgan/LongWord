<?php 
    global $wpdb;
    include_once '../wp-load.php' ;
    $word = $wpdb->get_row("SELECT * FROM words ORDER BY rand() LIMIT 1");
    $lang = $_POST['lang'];
    $curLevel = 4;
    $curId = $word->word_id;
    echo $curLevel."| ".$curId."| ";
    if($lang == 'ru'){
        echo $word->word_ru;
    }else{
        echo $word->word_ua;
    }