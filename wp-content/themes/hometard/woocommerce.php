<?php get_header('shop'); ?>

<!-- start content container -->
<div class="row">   
	<article class="col-md-<?php hometard_main_content_width_columns(); ?>">  
        <div class="woocommerce">
			<?php woocommerce_content(); ?>
        </div>
	</article>       
	<?php get_sidebar( 'right' ); ?>
</div>
<!-- end content container -->

<?php get_footer(); ?>

