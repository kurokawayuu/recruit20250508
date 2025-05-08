<?php
/**
 * Template Name: Password Reset Page
 *
 * @package WordPress
 * @subpackage Your_Theme_Name
 * @since Your_Theme_Version
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

        <article>
            <header class="entry-header">
                <h1 class="entry-title">パスワードの再設定</h1>
            </header><div class="entry-content">
                <?php
                // WP-Membersのパスワードリセットフォームを表示
                if ( shortcode_exists( 'wpmem_form' ) ) {
                    echo do_shortcode( '[wpmem_form password]' );
                } else {
                    echo '<p>パスワードリセット機能を利用できません。管理者にお問い合わせください。</p>';
                }
                ?>
            </div></article></main></div><?php get_sidebar(); ?>
<?php get_footer(); ?>