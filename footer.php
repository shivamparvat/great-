<?php $options = get_option('great'); ?>
	</div><!--#page-->
</div><!--.container-->
</div>
	<footer>
		<div class="footer-social">
			<div class="footer-social-inner">
				<div class="footer-inner">
					<?php mts_top_social_buttons(); ?>			
				</div>
			</div>
		</div>
		<div class="container">
			<div class="footer-widgets">
				<?php widgetized_footer(); ?>
               			<div class="copyrights">
					<?php mts_copyrights_credit(); ?>
				</div> 
			</div><!--.footer-widgets-->
		</div><!--.container-->
	</footer><!--footer-->
<?php mts_footer(); ?>
<?php wp_footer(); ?>
</body>
</html>