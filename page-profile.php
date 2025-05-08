<?php
/**
 * Template Name: User Profile Page Revised
 *
 * @package WordPress
 * @subpackage Your_Theme_Name
 * @since Your_Theme_Version
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

        <?php if ( is_user_logged_in() ) : ?>
            <h1>プロフィール</h1>
            <p>ご登録情報を確認・編集できます。</p>
            <?php
            // WP-Members のプロフィール編集フォームを表示
            // メールアドレス、パスワード、お名前、電話番号、性別、年齢、住所関連、
            // 就業状況、希望時期、希望職種、経験年数、資格など、
            // WP-Membersのフィールド設定で「Profile」にチェックを入れた項目が表示・編集可能
            echo do_shortcode('[wpmem_form user_edit]');
            ?>

        <?php else : ?>
            <p>このページを表示するには<a href="<?php echo wp_login_url( get_permalink() ); ?>">ログイン</a>が必要です。</p>
        <?php endif; ?>

    </main></div><?php get_sidebar(); ?>
<?php get_footer(); ?>