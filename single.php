<?php if ( !defined( 'ABSPATH' ) ) exit; ?>
<?php get_header(); ?>
<main class="page-column-mainVisual">
    <div class="page-column-mainVisual__content">
        <div class="page-column-mainVisual__content-inner">
        </div>
    </div>
    <div class="page-column-mainVisual__button">
        <div class="page-column-mainVisual__button-link">お役立ちコラム</div>
    </div>
</main>
<?php breadcrumb(); ?>
<div class="page-column-mainContent single-post">
	<div class="page-column-mainContent-inner">
		<section class="post-column-sec01">

			<?php while (have_posts()) : the_post(); ?>

						<div class="post-column">
							<div>
								<div class="post-column__cd"><?php the_category(); ?><p class="post-column__date"><?php the_time( get_option( 'date_format' ) ); ?></p></div>
								<h4 class="post-column__title"><?php the_title(); ?></h4>
								<?php if (has_post_thumbnail()) : /* もしアイキャッチが登録されていたら */ ?>
									<figure><?php the_post_thumbnail(); ?></figure>
								<?php else: /* 登録されていなかったら */ ?>
									<figure><img src="<?php echo get_stylesheet_directory_uri(); ?>/img/no-image-320.png" alt="空の画像"></figure>
								<?php endif; ?>
								<p><?php the_content(); ?></p>
							</div>
						</div>			

				<?php endwhile ;?><?php wp_reset_postdata(); ?>	
		</section>
	</div>
	<?php get_template_part('tmp/related-entries'); //関連記事 ?>
	<?php get_template_part('tmp/pager-post-navi'); //ページネーション ?>
</div>

<?php get_footer(); ?>
