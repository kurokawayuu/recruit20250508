<?php
/**
 * Template Name: Members Page Revised
 *
 * @package WordPress
 * @subpackage Your_Theme_Name
 * @since Your_Theme_Version
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

        <?php if ( is_user_logged_in() ) : ?>
            <?php
            $current_user = wp_get_current_user();
            $current_user_id = $current_user->ID;
            ?>
            <h1><?php echo esc_html( $current_user->display_name ); ?>さんのマイページ</h1>

            <p>ようこそ、<?php echo esc_html( $current_user->display_name ); ?>さん。</p>

            <nav class="mypage-navigation">
                <ul>
                    <li><a href="<?php echo esc_url( home_url( '/members/profiles/' ) ); ?>">プロフィール</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/members/favorites/' ) ); ?>">気になる求人リスト</a></li>
                    <li><a href="<?php echo esc_url( home_url( '/members/settings/' ) ); ?>">設定</a></li>
                    <li><a href="<?php echo wp_logout_url( home_url() ); ?>">ログアウト</a></li>
                </ul>
            </nav>

            <section id="recommended-jobs">
                <h2>あなたのエリア・職種に合った求人</h2>
                <?php
                // ユーザーの登録情報からエリアと職種を取得
                // メタキー: prefectures (都道府県), municipalities (市区町村), jobtype (希望職種)
                $user_prefecture = get_user_meta( $current_user_id, 'prefectures', true );
                $user_city = get_user_meta( $current_user_id, 'municipalities', true ); // 市区町村も使う場合
                $user_job_type = get_user_meta( $current_user_id, 'jobtype', true );

                if ( $user_prefecture && $user_job_type ) :
                    $args = array(
                        'post_type'      => 'job_listing', // 求人情報の投稿タイプ名
                        'posts_per_page' => 5,             // 表示件数
                        'tax_query'      => array(
                            'relation' => 'AND', // 複数のタクソノミー条件をANDで結ぶ
                            array(
                                'taxonomy' => 'job_area',    // エリアのタクソノミースラッグ
                                'field'    => 'name',        // または 'slug' や 'term_id'
                                'terms'    => $user_prefecture,
                            ),
                            array(
                                'taxonomy' => 'job_category', // 職種のタクソノミースラッグ
                                'field'    => 'name',         // または 'slug' や 'term_id'
                                'terms'    => $user_job_type,
                            ),
                        ),
                        // 必要に応じて他の条件も追加 (例: 'meta_query' で市区町村を絞り込むなど)
                    );
                    $recommended_jobs_query = new WP_Query( $args );

                    if ( $recommended_jobs_query->have_posts() ) :
                ?>
                        <ul class="job-list">
                            <?php while ( $recommended_jobs_query->have_posts() ) : $recommended_jobs_query->the_post(); ?>
                                <li>
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                    <?php // 必要に応じて給与や勤務地などの情報を表示 ?>
                                    <div class="job-meta">
                                        <?php
                                        // 例: 勤務地タクソノミーの表示
                                        $terms = get_the_terms( get_the_ID(), 'job_location_taxonomy' ); // 実際のタクソノミースラッグに置き換え
                                        if ( $terms && ! is_wp_error( $terms ) ) {
                                            echo '<span class="location">';
                                            foreach ( $terms as $term ) {
                                                echo esc_html( $term->name ) . ' ';
                                            }
                                            echo '</span>';
                                        }
                                        ?>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                        <?php // echo '<a href="/all-recommended-jobs/">もっと見る</a>'; // 一覧ページへのリンク ?>
                    <?php
                        wp_reset_postdata();
                    else :
                        echo '<p>現在、合致する求人は見つかりませんでした。</p>';
                    endif; // $recommended_jobs_query->have_posts()
                else :
                    echo '<p>プロフィールでご希望のエリアと職種を登録すると、関連する求人が表示されます。</p>';
                    echo '<p><a href="' . esc_url( home_url( '/members/profiles/' ) ) . '">プロフィールを更新する</a></p>';
                endif; // $user_prefecture && $user_job_type
                ?>
            </section>

        <?php else : ?>
            <h1>ログイン</h1>
            <p>マイページをご利用いただくにはログインが必要です。</p>
            <?php echo do_shortcode('[wpmem_form login]'); ?>
            <p><a href="<?php echo esc_url( home_url( '/password-reset/' ) ); ?>">パスワードをお忘れですか？</a></p>
            <p>アカウントをお持ちでない方は<a href="<?php echo esc_url( home_url( '/register/' ) ); // WP-Membersの登録ページのスラッグ ?>">こちらで新規登録</a></p>
        <?php endif; ?>

    </main></div><?php get_sidebar(); ?>
<?php get_footer(); ?>