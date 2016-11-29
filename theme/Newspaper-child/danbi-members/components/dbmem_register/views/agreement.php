<div class="agreement-box" style="margin-bottom:14px; clear:both;">
	<label class="text"><b><?php echo $post->post_title; ?></b></label>
	<div class="div_textarea"><?php echo wpautop(wptexturize($post->post_content)); ?></div>
	<label style="display: inline-block;">
		<input type="checkbox" id="agree-to-<?php echo $post->ID; ?>" class="agreement" name="check_<?php echo $option_name; ?>" value="true"
			style="vertical-align:middle; margin-right:5px;"
            data-title="<?php echo $post->post_title; ?>"
            data-agree-alert="<?php printf( __('Please agree to %s.', 'danbi-members'), $post->post_title); ?>" />
		<span style="vertical-align: middle"><?php printf( __('I agree to %s.', 'danbi-members'), $post->post_title); ?></span>
		<span class="req">*</span>
	</label>
</div>
