<?php 
    global $wpdb;
    include_once '../wp-load.php' ;
    $word = $wpdb->get_row("SELECT * FROM med_words ORDER BY rand() LIMIT 1");
    $lang = $_POST['lang'];
    $curLevel = 2;
    $curId = $word->med_word_id;
    echo $curLevel."| ".$curId."| ";
    if($lang == 'ru'){
        echo $word->med_word_ru;
    }else{
        echo $word->med_word_ua;
    }