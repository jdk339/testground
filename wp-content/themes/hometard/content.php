<article>
	<div <?php post_class(); ?>>                    
		<?php if ( has_post_thumbnail() ) : ?>                               
			<a class="featured-thumbnail" href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"> 
				<img class="lazy" src="<?php echo get_template_directory_uri() . '/img/placeholder.png' ?>" data-src="<?php the_post_thumbnail_url( 'hometard-single' ); ?>" alt="<?php the_title_attribute(); ?>" />
				<noscript>
					<?php the_post_thumbnail( 'hometard-single' ); ?>
				</noscript>
			</a>								               
		<?php endif; ?>	
		<div class="main-content row"> 
			<header class="col-md-4">
				<h2 class="page-header h1">                                
					<a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>" rel="bookmark">
						<?php the_title(); ?>
					</a>                            
				</h2>
				<div class="post-meta">
					<?php hometard_time_link(); ?>
					<?php hometard_posted_on(); ?>
					<?php hometard_entry_footer(); ?>
				</div><!-- .single-entry-summary -->
			</header>
			<div class="content-inner col-md-8">                                                      
				<div class="single-entry-summary">
					<?php the_excerpt(); ?> 
				</div><!-- .single-entry-summary -->
				<a class="btn btn-default btn-lg" href="<?php the_permalink(); ?>" > 
					<?php esc_html_e( 'Read more', 'hometard' ) ?>
				</a>
			</div>                                                             
		</div>                   
	</div>
</article>
