<?php
	$current_user = wp_get_current_user();
	$user_name = $current_user->display_name;
	$user_id = $current_user->ID;
	if (is_super_admin()) {
		$user_id = isset($_GET['user_id']) && ctype_digit($_GET['user_id']) ? intval($_GET['user_id']) : 0;
		$hidGET = '';
		foreach($_GET as $key => $val) {
			if ($key != 'user_id')
				$hidGET .= '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($val) . '" />';
		}
		$users = get_users( array( 'orderby' => 'display_name', 'fields' => array( 'ID', 'display_name', 'user_login' ) ) );
		$opts = '';
		foreach($users as $user) {
			$sel = '';
			if ($user->ID == $user_id) {
				$sel = ' selected="selected"';
				$user_name = $user->display_name;
			}
			$opts .= sprintf('<option value="%d"%s>%s (%s)</option>', $user->ID, $sel, $user->display_name, $user->user_login);
		}
		if ($user_id == 0 && count($users) > 0) {
			$user_id = $users[0]->ID;
			$user_name = $users[0]->display_name;
		}
	}
	$timezones = SE_Scheduler_Utilities::timezone_list();
	$user_timezone = SE_Scheduler_Utilities::get_user_timezone($user_id);
?>
<div class="wrap">
<?php if (is_super_admin()): ?>
<h1><?php echo __('Users events', 'se-scheduler'); ?></h1>
<form method="get" action="<?php menu_page_url('se_scheduler_events') ?>">
	<?php echo $hidGET; ?>
	<label for="ddlUsers"><?php echo __('User', 'se-scheduler'); ?>:&nbsp</label><select id="ddlUsers" name="user_id" style="min-width: 300px" onchange="this.form.submit()"><?php echo $opts; ?></select>
</form>
<?php endif; ?>
<?php if ($user_id > 0): ?>
<?php if (SE_Scheduler_Settings::instance()->se_timezone_avail) : ?>
	<div class="se-timezone" data-uid="<?php echo $user_id; ?>">
		<label for="se-timezone"><?php echo __('Time zone:', 'se-scheduler'); ?></label>&nbsp;
		<select id="se-timezone" name="se-timezone">
		<?php foreach($timezones as $key=>$val): ?>
			<option value="<?php echo $key; ?>"<?php if ($key == $user_timezone) echo ' selected="selected"'; ?>><?php echo $val; ?></option>
		<?php endforeach; ?>
		</select>
		<div><button name="se-timezone-save"><?php echo __('Save Time Zone', 'se-scheduler'); ?></button></div>
	</div>
<?php endif; ?>
	<h2 style="margin-top: 50px"><?php echo sprintf(__('Events of %s', 'se-scheduler'), $user_name); ?></h2>
	<div style="max-width: 600px">
	<?php echo do_shortcode('[se_scheduler_eventcalendar for_user="' . $user_id . '"]'); ?>
	</div>
<?php endif; ?>
</div>