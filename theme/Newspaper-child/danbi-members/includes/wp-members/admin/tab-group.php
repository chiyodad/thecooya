<?php
$fields = get_option('wpmembers_fields');
$all_fields = '';
if ($fields) {
	foreach ($fields as $field) {
		$all_fields .= empty($all_fields) ? $field[2] : ',' . $field[2];
	}
}
?>
<div class="metabox-holder">
	<div id="post-body">
		<div id="post-body-content">
			<div class="postbox">
				<h3><span><?php _e('Fields for Groups', 'danbi-members'); ?></span></h3>
				<div class="inside">
					<p>
						<?php _e('Write the names of the fields for groups with comma separated. For example, first_name,last_name,user_email. <b>The user_email field must be included</b>. If empty, all fields will be used.', 'danbi-members'); ?>
					</p>
					<form name="updatesettings" id="updatesettings" method="post" class="dbmem-updatesettings" data-tab="group">
						<input type="hidden" name="action" value="dbmembers_update_settings_group" />
						<?php wp_nonce_field( 'dbmembers_update_settings_group' ); ?>
						<table class="form-table">
							<thead>
								<tr>
									<td style="width:150px;"><b><?php _e('Group Name', 'danbi-members'); ?></b></td>
									<td><b><?php _e('Fields', 'danbi-members'); ?></b></td>
									<td style="width:200px;"><b><?php _e('Shortcode', 'danbi-members'); ?></b></td>
								</tr>
							</thead>
							<tbody>
							<?php foreach(Groups_Group::get_groups() as $group): ?>
								<tr>
									<td><?php echo $group->name; ?></td>
									<?php if ( $group->name === Groups_Registered::REGISTERED_GROUP_NAME ): ?>
										<td>
											<textarea class="large-text" readonly="readonly"><?php echo $all_fields; ?></textarea>
										</td>
										<td>
										</td>
									<?php else:
										$name = 'dbmembers_group_fields_' . $group->group_id;
										$value = get_option($name);
									?>
										<td>
											<textarea name="<?php echo $name; ?>" class="large-text"><?php echo $value; ?></textarea>
										</td>
										<td>
											[danbi-members group="<?php echo $group->group_id; ?>"]
										</td>
									<?php endif; ?>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table>
						<p>
							<?php _e('Prepend the above shortcode before <i>[wp-members page="register"]</i> in the register page.', 'danbi-members'); ?>
						</p>
						<br />
						<input type="submit" class="button-primary" value="<?php _e( 'Update Settings', 'wp-members' ); ?> &raquo;" /> 
					</form>
				</div><!-- .inside -->
			</div>
		</div><!-- #post-body-content -->
	</div><!-- #post-body -->
</div><!-- .metabox-holder -->		