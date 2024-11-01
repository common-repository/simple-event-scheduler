<?php
	$captcha = new SE_Scheduler_Captcha();
	$add_class = esc_attr(SE_Scheduler_Settings::instance()->se_style_modal);
	$header_add = esc_html(SE_Scheduler_Settings::instance()->se_text_eventaddheader);
	$header_edit = esc_html(SE_Scheduler_Settings::instance()->se_text_eventeditheader);
	$timeFormat = SE_Scheduler_Settings::instance()->se_time_format;
	$is12 = strpos($timeFormat, 'g') !== false || strpos($timeFormat, 'h') !== false;
	$leadingZero = strpos($timeFormat, 'h') !== false || strpos($timeFormat, 'H') !== false;
	$submit_text = esc_html(SE_Scheduler_Settings::instance()->se_text_eventsubmit);
	$fields = apply_filters( 'se_event_fields', SE_Scheduler_Utilities::get_fields() );
	$dtPat = SE_Scheduler_Utilities::get_picker_pattern();
?>
<div id="se-modal" class="se-modal se-fade<?php if (!empty($add_class)) echo ' ' . $add_class; ?>" role="dialog" data-txtadd="<?php echo $header_add; ?>" data-txtedit="<?php echo $header_edit; ?>">
	<div class="se-modal-dialog">
		<div class="se-modal-content">
			<div class="se-modal-header">
				<h4 class="se-modal-title"></h4>
			</div>
			<div class="se-modal-body">
				<div>
					<label><?php echo __( 'Date', 'se-scheduler' ); ?></label><br/>
					<ul class="se-picker">
					<?php for($i = 0; $i < strlen($dtPat); $i++): ?>
					<?php if ($dtPat[$i] == 'D' || $dtPat[$i] == 'd'): ?>
						<li class="se-picker-elem">
							<span class="se-picker-up"></span><span class="se-picker-days" tabindex="-1"><?php echo $dtPat[$i] == 'D' ? '0' : ''; ?>1</span><span class="se-picker-down"></span>
					<?php elseif (strpos('oYy', $dtPat[$i]) !== false): ?>
						<li class="se-picker-elem">
							<span class="se-picker-up"></span><span class="se-picker-years" tabindex="-1">2000</span><span class="se-picker-down"></span>
					<?php else: ?>
						<li class="se-picker-elem"<?php if($dtPat[$i] == 'F') echo ' style="width:80px"'; ?>>
							<span class="se-picker-up"></span><span class="se-picker-months" tabindex="-1" data-indx="0">0</span><span class="se-picker-down"></span>
					<?php endif; ?>
						</li>
					<?php endfor; ?>
					</ul>
				</div>
				<div>
					<label><?php echo __( 'Time', 'se-scheduler' ); ?></label><br/>
					<ul class="se-picker">
						<li class="se-picker-elem">
							<span class="se-picker-up"></span><span class="se-picker-hours" tabindex="-1"><?php echo ($leadingZero ? '0' : '') . ($is12 ? '1' : '0'); ?></span><span class="se-picker-down"></span>
						</li>
						<li class="se-picker-separator">
							<span>:</span>
						</li>
						<li class="se-picker-elem">
							<span class="se-picker-up"></span><span class="se-picker-minutes" tabindex="-1">00</span><span class="se-picker-down"></span>
						</li>
						<?php if ($is12): ?>
						<li class="se-picker-elem">
							<span class="se-picker-up"></span><span class="se-picker-meridiem" tabindex="-1">AM</span><span class="se-picker-down"></span>
						</li>
						<?php endif; ?>
					</ul>
				</div>
				<?php echo $fields; ?>
				<div>
					<label for="se-captcha"><?php echo __( 'Input text on the image:', 'se-scheduler' ); ?></label>
					<img src="" width="<?php echo $captcha->img_size[0]; ?>" height="<?php echo $captcha->img_size[1]; ?>" alt="CAPTCHA"><br/>
					<input id="se-captcha" type="text" required="required">
					<i><?php echo __( 'Please, fill this field with correct value', 'se-scheduler' ); ?></i>
				</div>
			</div>
			<div class="se-modal-footer">
				<button type="button" class="btn btn-default" name="se-modal-add"><?php echo $submit_text; ?></button>
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo __( 'Close', 'se-scheduler' ); ?></button>
			</div>
		</div>
	</div>
</div>