<?php
/**
 * Template Name: Settings Page Revised
 *
 * @package WordPress
 * @subpackage Your_Theme_Name
 * @since Your_Theme_Version
 */

// メルマガ設定更新処理
if ( isset( $_POST['update_mailmagazine_settings'] ) && is_user_logged_in() ) {
    if ( ! isset( $_POST['mailmagazine_nonce_field'] ) || ! wp_verify_nonce( $_POST['mailmagazine_nonce_field'], 'mailmagazine_settings_action' ) ) {
        // Nonceエラー処理
        wp_die('セキュリティチェックに失敗しました。');
    } else {
        $current_user_id = get_current_user_id();
        $preference = isset( $_POST['mailmagazine_preference'] ) ? sanitize_text_field( $_POST['mailmagazine_preference'] ) : 'unsubscribe';
        update_user_meta( $current_user_id, 'mailmagazine_preference', $preference );
        // 更新完了メッセージなどを表示するためのフラグを立てるか、リダイレクトする
        // 例: $GLOBALS['mailmagazine_updated'] = true;
        // または wp_redirect( add_query_arg( 'updated', 'mailmagazine', get_permalink() ) ); exit;
        // ここでは単純化のため、ページ再読み込みで反映される想定
    }
}

// 退会申請処理 (より安全な場所に記述することを推奨: 例 initフックなど)
if ( isset( $_POST['confirm_delete_account'] ) && is_user_logged_in() ) {
    if ( ! isset( $_POST['delete_account_nonce'] ) || ! wp_verify_nonce( $_POST['delete_account_nonce'], 'delete_my_account_action' ) ) {
        wp_die('セキュリティチェックに失敗しました。(退会処理)');
    } else {
        // 実際の退会処理
        require_once(ABSPATH.'wp-admin/includes/user.php' );
        $current_user_id = get_current_user_id();
        // 関連データの処理など (オプション)
        // $reassign_user_id = null; // 投稿などを誰かに割り当てる場合、そのユーザーID。nullだと削除。
        // wp_delete_user( $current_user_id, $reassign_user_id );
        // wp_logout();
        // wp_redirect( home_url('/?account_deleted=true') ); // 退会完了ページへリダイレクト
        // exit;

        // 現状はダミーメッセージ
        echo '<div class="notice notice-warning"><p>退会機能は最終確認ステップです。本番では実際の処理をここに実装します。</p></div>';
        // $_POST = array(); // 処理後にPOSTデータをクリア
    }
}


get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

        <?php if ( is_user_logged_in() ) : ?>
            <?php $current_user_id = get_current_user_id(); ?>
            <h1>アカウント設定</h1>

            <section id="password-change">
                <h2>パスワード変更</h2>
                <p>現在のパスワードと新しいパスワードを入力して変更してください。</p>
                <?php
                // WP-Membersのユーザー編集フォームにはパスワード変更機能が含まれています。
                // このフォームでメールアドレスなど他の項目も編集できてしまうため、
                // パスワード変更専用にしたい場合は、WP-Membersのフィルターフックで
                // 表示フィールドをカスタマイズするか、自前でパスワード変更フォームを実装します。
                // ここでは、[wpmem_form user_edit] を使い、ユーザーが他の項目も編集できる前提とします。
                // もしパスワードフィールドだけを分離したい場合は、WP-Membersのドキュメントや
                // wpmem_user_edit_fields フックなどを調べてください。
                echo do_shortcode('[wpmem_form user_edit form_class="password-edit-form"]');
                // もしくは、WP-Members が提供するパスワード変更専用フォームがあればそれを使用します。
                // (WP-Members 3.1.7+ では [wpmem_form new_password] がありますが、これはリセットフロー用です)
                // [wpmem_form chgpass] ショートコードが使えます。
                // echo do_shortcode('[wpmem_form chgpass]');
                ?>
                <p><a href="<?php echo esc_url( home_url( '/password-reset/' ) ); ?>">パスワードをお忘れの場合はこちら (パスワードリセット)</a></p>
            </section>

            <hr>

            <section id="mailmagazine-settings">
                <h2>メルマガ設定</h2>
                <?php
                // if (isset($GLOBALS['mailmagazine_updated']) && $GLOBALS['mailmagazine_updated']) {
                //    echo '<div class="notice notice-success"><p>メルマガ設定を更新しました。</p></div>';
                // }
                // if (isset($_GET['updated']) && $_GET['updated'] === 'mailmagazine') {
                //    echo '<div class="notice notice-success"><p>メルマガ設定を更新しました。</p></div>';
                // }
                $mailmagazine_preference = get_user_meta( $current_user_id, 'mailmagazine_preference', true );
                ?>
                <form method="post" action="<?php echo esc_url( get_permalink() ); ?>">
                    <?php wp_nonce_field( 'mailmagazine_settings_action', 'mailmagazine_nonce_field' ); ?>
                    <p>
                        <label>
                            <input type="radio" name="mailmagazine_preference" value="subscribe" <?php checked( $mailmagazine_preference, 'subscribe' ); ?>>
                            メルマガを購読する
                        </label>
                    </p>
                    <p>
                        <label>
                            <input type="radio" name="mailmagazine_preference" value="unsubscribe" <?php checked( $mailmagazine_preference, 'unsubscribe' ); checked( empty($mailmagazine_preference), true ); /* デフォルト未設定なら非購読扱い */ ?>>
                            メルマガを購読しない
                        </label>
                    </p>
                    <p>
                        <input type="submit" name="update_mailmagazine_settings" value="メルマガ設定を保存">
                    </p>
                </form>
            </section>

            <hr>

            <section id="delete-account">
                <h2>退会申請</h2>
                <p>アカウントを退会される場合は、以下のボタンをクリックしてください。この操作は元に戻せません。関連する情報（気になる求人リストなど）も削除されます。</p>
                <form method="post" action="<?php echo esc_url( get_permalink() ); ?>" onsubmit="return confirm('本当に退会しますか？この操作は取り消すことができず、アカウントに関連する全てのデータが削除される可能性があります。');">
                    <?php wp_nonce_field( 'delete_my_account_action', 'delete_account_nonce' ); ?>
                    <input type="hidden" name="action" value="delete_my_account_custom">
                    <button type="submit" name="confirm_delete_account" class="button-danger">退会する</button>
                </form>
                <p class="small-text">注意: 退会処理には時間がかかる場合があります。処理が完了するとトップページにリダイレクトされます。</p>
            </section>

            <hr>

            <section id="logout">
                <h2>ログアウト</h2>
                <p><a href="<?php echo wp_logout_url( home_url() ); ?>" class="button">ログアウトする</a></p>
            </section>

        <?php else : ?>
            <p>このページを表示するには<a href="<?php echo wp_login_url( get_permalink() ); ?>">ログイン</a>が必要です。</p>
        <?php endif; ?>

    </main></div><?php get_sidebar(); ?>
<?php get_footer(); ?>