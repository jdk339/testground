<?php get_header(); ?>

<!-- start content container -->
<div class="row">

    <div class="col-md-<?php hometard_main_content_width_columns(); ?>">
		<?php
		// if this was a search we display a page header with the results count. If there were no results we display the search form.
		if ( is_search() ) :

			echo "<h1 class='search-head text-center'>" . sprintf( __( 'Search Results for: %s', 'hometard' ), get_search_query() ) . "</h1>";
			
		endif;

		if ( have_posts() ) :

			while ( have_posts() ) : the_post();

				get_template_part( 'content', get_post_format() );


			endwhile;

			the_posts_pagination();

		else :

			get_template_part( 'content', 'none' );

		endif;
		?>

	</div>

	<?php get_sidebar( 'right' ); ?>

</div>
<!-- end content container -->

<?php get_footer(); ?>
