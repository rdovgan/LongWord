<?php
global $wpdb;
include_once '../wp-load.php';
$table = '';
$lang = $_POST['lang'];
$curLevel = $_POST['curLevel'];
$curId = $_POST['curId'];
switch ($curLevel) {
    case 1:
        $table = 'easy_';
        break;
    case 2:
        $table = 'med_';
        break;
    case 3:
        $table = 'hard_';
        break;
}
$word = $wpdb->get_row("SELECT * FROM " . $table . "words WHERE " . $table . "word_id=" . $curId . " LIMIT 1", ARRAY_A);
echo $word[$table . "word_" . $lang];
