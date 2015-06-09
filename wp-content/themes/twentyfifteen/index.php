<?php
include_once('simple_html_dom.php');
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * e.g., it puts together the home page when no home.php file exists.
 *
 * Learn more: {@link https://codex.wordpress.org/Template_Hierarchy}
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */
get_header();
$curLevel = 1;
$curId = 1;
?><script>lang = 'ua';curLevel=1;curId=1;</script><?php

function changeLang() {
    //do changes
    ?><script>lang = 'ddcc';</script><?php
}

//getData();

function getData() {
    global $wpdb;
    for ($i = 0; $i < 10; $i++) {
        $html = file_get_html('http://cr.flexo.name?lev=2');
        $word = strip_tags(reset($html->find(".game_main")));
        $word = trim(preg_replace("/&#?[a-z0-9]+;/i", "", $word));
        echo $word;
        $wpdb->insert('med_words', array('med_word_rus' => $word), array('%s'));
    }
}
?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <article class="page type-page status-publish hentry">  
            <button class="langBlock animated">UA</button>
            <header class="entry-header" style="margin-right: 35px;">
                <h1 class="entry-title">Get some word</h1>    
            </header>
            <div style="padding: 0 9.0909%; width: 100%;">
                <div class="blockWraper">
                    <div class="block">
                        <button id="btnEasy" class="btnGet">Easy</button>
                    </div>
                    <div class="block">
                        <button id="btnMed" class="btnGet">Medium</button>
                    </div>
                </div>
                <div class="blockWraper">
                    <div class="block">
                        <button id="btnHard" class="btnGet">Hard</button>
                    </div>
                    <div class="block">
                        <button id="btnWords" class="btnGet">Words</button>
                    </div>
                </div>
            </div>
            <p id="bigText" style="text-align: center; padding-bottom: 60px;clear: both;"></p>
        </article>

        <?php if (have_posts()) : ?>

    <?php if (is_home() && !is_front_page()) : ?>
                <header>
                    <h1 class="page-title screen-reader-text"><?php single_post_title(); ?></h1>
                </header>
            <?php endif; ?>

            <?php
            // Start the loop.
            while (have_posts()) : the_post();

                /*
                 * Include the Post-Format-specific template for the content.
                 * If you want to override this in a child theme, then include a file
                 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
                 */
                get_template_part('content', get_post_format());

            // End the loop.
            endwhile;

            // Previous/next page navigation.
            the_posts_pagination(array(
                'prev_text' => __('Previous page', 'twentyfifteen'),
                'next_text' => __('Next page', 'twentyfifteen'),
                'before_page_number' => '<span class="meta-nav screen-reader-text">' . __('Page', 'twentyfifteen') . ' </span>',
            ));

        // If no content, include the "No posts found" template.
        else :
        //get_template_part('content', 'none');

        endif;
        ?>

    </main><!-- .site-main -->
</div><!-- .content-area -->

<?php get_footer(); ?>
