<?php
/**
 * お問い合わせページのテンプレート
 * Template Name: お問い合わせページ
 */
get_header();
?>

<div class="content">
    <div class="container">
        <!-- パンくずリスト -->
        <div class="breadcrumb">
            <a href="<?php echo home_url(); ?>">ホーム</a> &gt;
            <span>お問い合わせ</span>
        </div>

        <div class="contact-page">
            <div class="contact-header">
                <h1 class="contact-title">お問い合わせ</h1>
                <div class="contact-description">
                    <p>こどもプラス求人サイトに関するお問い合わせは、以下のフォームよりお願いいたします。</p>
                    <p>担当者が確認後、3営業日以内にご返信いたします。</p>
                </div>
            </div>

            <div class="contact-content">
                <div class="contact-form-container">
                    <!-- Contact Form 7を使用する場合 -->
                    <?php
                    if (function_exists('wpcf7_contact_form')) {
                        // Contact Form 7のショートコードを取得して表示
                        $contact_form_id = get_field('contact_form_id'); // Advanced Custom Fieldsで設定したフォームID
                        
                        if (!empty($contact_form_id)) {
                            echo do_shortcode('[contact-form-7 id="' . $contact_form_id . '"]');
                        } else {
                            // デフォルトのフォームIDを使用
                            echo do_shortcode('[contact-form-7 id="123" title="お問い合わせフォーム"]');
                        }
                    } else {
                        // Contact Form 7が使用できない場合は独自のフォームを表示
                    ?>
                    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" class="contact-form">
                        <input type="hidden" name="action" value="custom_contact_form">
                        <?php wp_nonce_field('custom_contact_form_nonce', 'custom_contact_form_nonce'); ?>
                        
                        <div class="form-group">
                            <label for="contact-name">お名前<span class="required">*</span></label>
                            <input type="text" id="contact-name" name="contact-name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="contact-email">メールアドレス<span class="required">*</span></label>
                            <input type="email" id="contact-email" name="contact-email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="contact-tel">電話番号</label>
                            <input type="tel" id="contact-tel" name="contact-tel">
                        </div>
                        
                        <div class="form-group">
                            <label for="contact-subject">お問い合わせ件名<span class="required">*</span></label>
                            <input type="text" id="contact-subject" name="contact-subject" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="contact-type">お問い合わせ種別<span class="required">*</span></label>
                            <select id="contact-type" name="contact-type" required>
                                <option value="">選択してください</option>
                                <option value="求人について">求人について</option>
                                <option value="応募について">応募について</option>
                                <option value="サイトの使い方について">サイトの使い方について</option>
                                <option value="採用担当者様からのお問い合わせ">採用担当者様からのお問い合わせ</option>
                                <option value="その他">その他</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="contact-message">お問い合わせ内容<span class="required">*</span></label>
                            <textarea id="contact-message" name="contact-message" rows="5" required></textarea>
                        </div>
                        
                        <div class="form-group privacy-policy-agreement">
                            <label>
                                <input type="checkbox" name="privacy-agreement" required>
                                <a href="<?php echo home_url('/privacy-policy/'); ?>" target="_blank">プライバシーポリシー</a>に同意します
                            </label>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="submit-button">送信する</button>
                        </div>
                    </form>
                    <?php } ?>
                </div>
                
                <div class="contact-info">
                    <div class="contact-info-box">
                        <h3 class="contact-info-title">お電話でのお問い合わせ</h3>
                        <div class="contact-phone">
                            <i class="fas fa-phone"></i> 0120-000-000
                        </div>
                        <div class="contact-hours">
                            受付時間：平日 9:00〜18:00（土日祝休み）
                        </div>
                    </div>
                    
                    <div class="contact-info-box">
                        <h3 class="contact-info-title">よくあるご質問</h3>
                        <p>お問い合わせの前に、よくある質問をご確認ください。</p>
                        <a href="<?php echo home_url('/faq/'); ?>" class="faq-link">よくある質問を見る <i class="fas fa-chevron-right"></i></a>
                    </div>
                    
                    <div class="contact-info-box">
                        <h3 class="contact-info-title">当サイトについて</h3>
                        <p>こどもプラス求人サイトは、全国のこどもプラス教室で働く人材を募集するサイトです。</p>
                        <a href="<?php echo home_url('/about/'); ?>" class="about-link">こどもプラスについて <i class="fas fa-chevron-right"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // フォームバリデーション
    $('.contact-form').on('submit', function(e) {
        var valid = true;
        
        // 必須項目のチェック
        $(this).find('[required]').each(function() {
            if ($(this).val() === '') {
                $(this).addClass('error');
                valid = false;
            } else {
                $(this).removeClass('error');
            }
        });
        
        // プライバシーポリシーの同意チェック
        if (!$('input[name="privacy-agreement"]').is(':checked')) {
            $('input[name="privacy-agreement"]').closest('label').addClass('error');
            valid = false;
        } else {
            $('input[name="privacy-agreement"]').closest('label').removeClass('error');
        }
        
        if (!valid) {
            e.preventDefault();
            $('<div class="form-error-message">必須項目を入力し、プライバシーポリシーに同意してください。</div>').insertBefore($(this).find('.form-actions'));
            
            // エラーメッセージは5秒後に消える
            setTimeout(function() {
                $('.form-error-message').fadeOut(function() {
                    $(this).remove();
                });
            }, 5000);
        }
    });
});
</script>

<?php get_footer(); ?>