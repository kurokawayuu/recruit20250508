<?php
/**
 * Template Name: Favorites Page Revised
 *
 * @package WordPress
 * @subpackage Your_Theme_Name
 * @since Your_Theme_Version
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

        <?php if ( is_user_logged_in() ) : ?>
            <h1>気になる求人リスト</h1>

            <?php
            // AJAXでお気に入り解除処理を行うための準備 (wp_localize_script を functions.php で行うのが一般的)
            // 例:
            // wp_enqueue_script('my-favorites-script', get_template_directory_uri() . '/js/favorites.js', array('jquery'), null, true);
            // wp_localize_script('my-favorites-script', 'myFavoritesAjax', array(
            //    'ajaxurl' => admin_url('admin-ajax.php'),
            //    'nonce'   => wp_create_nonce('my_favorites_nonce')
            // ));
            ?>

            <div id="favorites-list-container">
                <?php
                $current_user_id = get_current_user_id();
                $favorite_job_ids = get_user_meta( $current_user_id, 'user_favorites', true );

                if ( ! empty( $favorite_job_ids ) && is_array( $favorite_job_ids ) ) :
                    $args = array(
                        'post_type'      => 'job_listing',
                        'post__in'       => $favorite_job_ids,
                        'posts_per_page' => -1,
                        'orderby'        => 'post__in',
                    );
                    $favorite_jobs_query = new WP_Query( $args );

                    if ( $favorite_jobs_query->have_posts() ) :
                ?>
                        <ul class="favorites-list">
                            <?php while ( $favorite_jobs_query->have_posts() ) : $favorite_jobs_query->the_post(); ?>
                                <li id="favorite-item-<?php the_ID(); ?>" class="favorite-job-item">
                                    <header class="entry-header">
                                        <?php the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?>
                                    </header>
                                    <div class="entry-summary">
                                        <?php the_excerpt(); ?>
                                    </div>
                                    <button class="remove-from-favorites" data-job-id="<?php the_ID(); ?>" data-nonce="<?php echo wp_create_nonce('remove_favorite_nonce_' . get_the_ID() ); ?>">気になるリストから削除</button>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                <?php
                        wp_reset_postdata();
                    else :
                        echo '<p>気になる求人はまだありません。</p>';
                    endif;
                else :
                    echo '<p>気になる求人はまだありません。</p>';
                endif;
                ?>
            </div>

            <?php // JavaScript でお気に入り解除処理を実装 ?>
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('.remove-from-favorites').on('click', function() {
                    var button = $(this);
                    var jobId = button.data('job-id');
                    var nonce = button.data('nonce'); // 個別のNonceを利用

                    if ( !confirm('この求人を気になるリストから削除しますか？') ) {
                        return;
                    }

                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: {
                            action: 'remove_from_favorites_action', // functions.php で定義するアクション名
                            job_id: jobId,
                            nonce: nonce // Nonceを送信
                        },
                        beforeSend: function() {
                            button.prop('disabled', true).text('処理中...');
                        },
                        success: function(response) {
                            if(response.success) {
                                $('#favorite-item-' + jobId).fadeOut(300, function() { $(this).remove(); });
                                // リストが空になった場合のメッセージ表示なども考慮
                                if ($('.favorites-list li').length === 1) { // これから消えるものを除いて0になる場合
                                     $('#favorites-list-container').html('<p>気になる求人はまだありません。</p>');
                                }
                            } else {
                                alert('エラー: ' + response.data.message);
                                button.prop('disabled', false).text('気になるリストから削除');
                            }
                        },
                        error: function() {
                            alert('通信エラーが発生しました。');
                            button.prop('disabled', false).text('気になるリストから削除');
                        }
                    });
                });
            });
            </script>

        <?php else : ?>
            <p>このページを表示するには<a href="<?php echo wp_login_url( get_permalink() ); ?>">ログイン</a>が必要です。</p>
        <?php endif; ?>

    </main></div><?php get_sidebar(); ?>
<?php get_footer(); ?>