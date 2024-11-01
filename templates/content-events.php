<?php foreach($events as $event): ?>
<?php $many_users = count($event->VisitorsNames) > 5; ?>
<div class="se-ev-item<?php if ($current_user_id > 0 && $current_user_id == $event->UID) echo '-my'; ?>" data-id="<?php echo $event->ID; ?>">
	<?php echo SE_Scheduler_Utilities::get_event_timeframe_text($event, $timeFormat); ?>
	<div class="se-ev-item-nameblock">
	<div class="se-ev-item-name"><?php echo $event->Name; ?></div>
	<?php if (!empty($event->UserName)): ?>
	<div class="se-ev-item-i"><?php echo sprintf(__('by %s', 'se-scheduler'), $event->UserName); ?></div>
	<?php endif; ?>
	</div>
	<?php if ($is_avail && $current_user_id == $event->UID || $super_mode): ?>
	<div><button name="se-ev-item-edit"><?php echo __('Edit', 'se-scheduler'); ?></button></div>
	<div><button name="se-ev-item-delete"><?php echo __('Delete', 'se-scheduler'); ?></button></div>
	<?php endif; ?>
	<?php if ($is_avail && $current_user_id > 0 && $current_user_id != $event->UID): ?>
	<?php $in_list = SE_Scheduler_Utilities::user_in_array($current_user_id, $event->Visitors); ?>
	<div><button name="se-ev-item-visit"><?php echo ($in_list ? __('Cancel Visit', 'se-scheduler') : __('Visit', 'se-scheduler')); ?></button></div>
	<?php endif; ?>
	<?php if (!empty($event->VisitorsNames)): ?>
	<div class="se-ev-item-vis<?php if (!$many_users) echo '-s' ?>"><?php echo '<div>' . __('Visitors:', 'se-scheduler') . '</div><div>' . implode($event->VisitorsNames, '</div><div>') . '</div>'; if ($many_users) echo '<div class="se-ev-item-vis-grad"></div>'; ?></div>
	<?php if ($many_users): ?>
	<div><button name="se-ev-item-showus" data-alttext="<?php echo __('Hide', 'se-scheduler'); ?>"><?php echo sprintf(__('Show All %d', 'se-scheduler'), count($event->VisitorsNames)); ?></button></div>
	<?php endif; ?>
	<?php endif; ?>
</div>
<?php endforeach; ?>