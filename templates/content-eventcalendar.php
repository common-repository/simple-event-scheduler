<?php
	$tmpDate = new DateTime('today', $utz);
	$events = array();
	$days_count = SE_Scheduler_Settings::instance()->se_days_count;
	$col_width = floor(100 / $days_count);
	if ($user_id !== null) {
		for ($daynum = 0; $daynum < $days_count; $daynum++){
			$dt = $tmpDate->format('Y-m-d');
			$events[] = SE_Scheduler_Cache::get_events($user_id, $dt);
			$tmpDate->modify('+1 day');
		}
	}
	$date = new DateTime('today', $utz);
	$headerText = esc_html(SE_Scheduler_Utilities::get_eventcal_header($date));
	$is_avail = SE_Scheduler_Settings::instance()->se_event_avail;
	$add_class = esc_attr(SE_Scheduler_Settings::instance()->se_style_eventcalendar);
	$timeFormat = SE_Scheduler_Settings::instance()->se_time_format;
	$current_user_id = get_current_user_id();
	$super_mode = SE_Scheduler_Utilities::is_super_mode($user_id);
	$timezone_name = SE_Scheduler_Utilities::format_timezone_name($utz->getName()) . ' (GMT ' . $date->format('P') . ')';
?>
<div class="se-div<?php if (!empty($add_class)) echo ' ' . $add_class; ?>" data-uid="<?php echo ($user_id === null ? '' : $user_id); ?>" data-date="<?php echo $date->format('Y-m-d'); ?>">
<table class="se-ev">
	<colgroup>
	<?php for($i = 0; $i < $days_count - 1; $i++): ?>
		<col width="<?php echo $col_width; ?>%" />
	<?php endfor; ?>
		<col width="<?php echo strval(100 - $col_width * ($days_count - 1)); ?>%" />
	</colgroup>
	<thead>
	<tr>
		<th colspan="<?php echo $days_count; ?>">
		<?php if ($user_id !== null): ?>
			<div name="se-ev-leftar" class="se-leftar" style="display:none;"></div>
			<div name="se-ev-rightar" class="se-rightar"></div>
		<?php endif; ?>
			<span><?php echo $headerText; ?></span><br>
		<?php if ($user_id !== null): ?>
		<?php if (SE_Scheduler_Settings::instance()->se_timezone_avail): ?>
			<?php if ($current_user_id > 0 && $user_id == $current_user_id): ?>
			<i><?php echo __('All times listed are in your timezone', 'se-scheduler'); ?><br><?php echo $timezone_name; ?></i>
			<?php elseif ($user_id == 0): ?>
			<i><?php echo __('All times listed are in site`s timezone', 'se-scheduler'); ?><br><?php echo $timezone_name; ?></i>
			<?php else: ?>
			<i><?php echo __('All times listed are in user`s timezone', 'se-scheduler'); ?><br><?php echo $timezone_name; ?></i>
			<?php endif; ?>
		<?php else: ?>
			<i><?php echo __('All times listed are in site`s timezone', 'se-scheduler'); ?><br><?php echo $timezone_name; ?></i>
		<?php endif; ?>
		<?php endif; ?>
		</th>
	</tr>
	<tr>
	<?php for($i = 0; $i < $days_count; $i++): ?>
		<th class="se-ev-dayh"><?php echo SE_Scheduler_Utilities::get_day_header( $date ); ?></th>
	<?php 	$date->modify('+1 day'); ?>
	<?php endfor; ?>
	</tr>
	</thead>
	<tbody>
	<?php if ($user_id !== null && is_user_logged_in() && ($is_avail && ($user_id == 0 || $user_id == $current_user_id) || $super_mode)): ?>
	<tr>
	<?php for($i = 0; $i < $days_count; $i++): ?>
		<td><button name="se-event-add"><?php echo __('Add Event', 'se-scheduler'); ?></button></td>
	<?php endfor; ?>
	</tr>
	<?php endif; ?>
	<tr>
	<?php for($i = 0; $i < $days_count; $i++): ?>
		<td>
		<?php if ($user_id !== null): ?>
		<?php $attrs = array('current_user_id' => $current_user_id, 'is_avail' => $is_avail, 'super_mode' => $super_mode, 'timeFormat' => $timeFormat, 'events' => $events[$i]); ?>
		<?php echo SE_Scheduler_Utilities::load( 'content-events', $attrs ); ?>
		<?php endif; ?>
		</td>
	<?php endfor; ?>
	</tr>
</tbody></table>
</div>