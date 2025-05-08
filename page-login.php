<?php
/**
 * ログインページのテンプレート
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
                <h1 class="auth-title">ログイン</h1>
                <p class="auth-description">アカウントをお持ちの方はログインしてください。</p>
            </div>
                <?php echo do_shortcode('[wpmem_form login]'); ?>
			</div>
	</div>
		</main>
<?php get_footer(); ?>