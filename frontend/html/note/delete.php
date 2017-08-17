<?php
use \packages\base\translator;
use \packages\userpanel;
$this->the_header();
?>
<div class="row">
	<div class="col-xs-12">
		<form class="form-horizontal" action="<?php echo userpanel\url('settings/notice/notes/delete/'.$this->note->id); ?>" method="POST">
			<div class="alert alert-block alert-warning fade in">
				<h4 class="alert-heading"><i class="fa fa-exclamation-triangle"></i> <?php echo translator::trans('notice.attention'); ?>!</h4>
				<p>
					<?php echo translator::trans("notice.note.delete.warning", array('note_id' => $this->note->id)); ?>
				</p>
				<p>
					<a href="<?php echo userpanel\url('settings/notice/notes'); ?>" class="btn btn-light-grey"><i class="fa fa-chevron-circle-right"></i> <?php echo translator::trans('notice.return'); ?></a>
					<button type="submit" class="btn btn-danger"><i class="fa fa-trash-o"></i> <?php echo translator::trans("notice.delete") ?></button>
				</p>
			</div>
		</form>
	</div>
</div>
<?php
$this->the_footer();
