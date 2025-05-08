<?php
/**
 * 加盟教室用ログインページのテンプレート
 * Template Name: ログインページ
 */
get_header();

// リダイレクト先を取得
$redirect_to = isset($_GET['redirect_to']) ? $_GET['redirect_to'] : home_url();
?>
<main class="login-page">
<div class="content">

        <div class="auth-container">
            <div class="auth-header">
                <h1 class="auth-title">加盟教室用ログイン</h1>
            </div>
                <?php echo do_shortcode('[wpmem_form login]'); ?>
			</div>
	</div>
		</main>
<?php get_footer(); ?>