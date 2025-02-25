<?php
use \packages\base\view\error;
use \packages\base\translator;
use \packages\userpanel;
$this->the_header();
?>
<div class="row">
	<div class="col-xs-12">
		<form class="notice-note-add" action="<?php echo userpanel\url("settings/notice/notes/add"); ?>" method="post">
			<div class="preview"></div>
			<div class="panel panel-default">
				<div class="panel-heading">
					<i class="fa fa-plus"></i> <?php echo translator::trans("notice.note.add"); ?>
					<div class="panel-tools">
						<a class="btn btn-xs btn-link panel-collapse collapses" href="#"></a>
					</div>
				</div>
				<div class="panel-body">
					<div class="row">
						<div class="col-sm-6">
						<?php
						$this->createField([
							'name' => 'view',
							'type' => 'select',
							'label' => translator::trans('notice.note.view'),
							'options' => $this->getViewsForSelect()
						]);
						?>
						</div>
						<div class="col-sm-6">
						<?php
						$this->createField([
							'type' => 'select',
							'name' => 'type',
							'label' => translator::trans('notice.note.type'),
							'options' => $this->getTypeForSelect()
						]);
						?>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-6">
						<?php
						$this->createField([
							'type' => 'select',
							'name' => 'status',
							'label' => translator::trans('notice.note.status'),
							'options' => $this->getStatusForSelect()
						]);
						?>
						</div>
						<div class="col-sm-6">
						<?php echo $this->createField([
							'name' => 'kind',
							'type' => 'hidden'
						]); ?>
						<?php echo $this->createField([
							'name' => 'address',
							'label' => translator::trans('notice.note.address'),
							'ltr' => true,
							'placeholder' => 'https://www.yourdomain.com/page'
						]); ?>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-6">
						<?php
						$this->createField([
							'name' => 'create_at',
							'label' => translator::trans('notice.note.create_at'),
							'ltr' => true
						]);
						?>
						</div>
						<div class="col-sm-6">
						<?php
						$this->createField([
							'name' => 'expire_at',
							'label' => translator::trans('notice.note.expire_at'),
							'ltr' => true
						]);
						?>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-8 col-sm-offset-2">
							<?php
							$this->createField(array(
								'type' => 'hidden',
								'name' => "time[start]"
							));
							$this->createField(array(
								'type' => 'hidden',
								'name' => "time[end]"
							));
							?>
							<div class="slider"></div>
						</div>
					</div>
					<div class="not-type type-alert">
						<div class="row">
							<div class="col-sm-6">
								<?php foreach(['success', 'warning'] as $state){ ?>
									<div class="alert alert-<?php echo $state; ?>">
										<button class="close" type="button" titile="<?php echo translator::trans('notice.close'); ?>">&times;</button>
										<h4 class="alert-heading">
											<?php echo translator::trans('notice.note.error.state.heading.'.$state); ?>
										</h4>
										<?php $this->createField([
											'name' => 'style',
											'type' => 'radio',
											'options' => [
												[
													'label' => translator::trans('notice.note.error.state.'.$state),
													'value' => $state == 'success' ? error::SUCCESS : error::WARNING
												]
											]
										]); ?>
									</div>
								<?php } ?>
							</div>
							<div class="col-sm-6">
								<?php foreach(['info', 'danger'] as $state){ ?>
									<div class="alert alert-<?php echo $state; ?>">
										<button class="close" type="button" titile="<?php echo translator::trans('notice.close'); ?>">&times;</button>
										<h4 class="alert-heading">
											<?php echo translator::trans('notice.note.error.state.heading.'.$state); ?>
										</h4>
										<?php $this->createField([
											'name' => 'style',
											'type' => 'radio',
											'options' => [
												[
													'label' => translator::trans('notice.note.error.state.'.$state),
													'value' => $state == 'info' ? error::NOTICE : error::FATAL
												]
											]
										]); ?>
									</div>
								<?php } ?>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-6">
						<?php $this->createField([
							'name' => 'user',
							'type' => 'radio',
							'options' => [
								[
									'label' => translator::trans('notice.note.user.all'),
									'value' => 'all'
								],
								[
									'label' => translator::trans('notice.note.user.selection'),
									'value' => 'selection'
								]
							]
						]); ?>
						<?php $this->createField([
							'name' => 'show-option',
							'type' => 'radio',
							'options' => [
								[
									'label' => translator::trans('notice.note.show.once'),
									'value' => 'once'
								],
								[
									'label' => translator::trans('notice.note.show.closable'),
									'value' => 'closable'
								],
								[
									'label' => translator::trans('notice.note.show.to_expire'),
									'value' => 'expire_at'
								]
							]
						]); ?>
						</div>
						<div class="col-sm-6 user-selection">
							<?php $this->createField([
								'name' => 'usertypes[]',
								'label' => translator::trans('notice.note.user.usertypes'),
								'type' => 'checkbox',
								'inline' => true,
								'options' => $this->getUserTypesForSelect()
							]); ?>
							<?php $this->createField([
								'name' => 'username',
								'label' => translator::trans("notice.note.user")
							]); ?>
							<div class="panel panel-white" style="display: none;">
								<div class="panel-heading">
									<i class="fa fa-user-plus"></i> <?php echo translator::trans("notice.note.users"); ?>
									<div class="panel-tools">
										<a class="btn btn-xs btn-link panel-collapse collapses" href="#"></a>
									</div>
								</div>
								<div class="panel-body panel-scroll" style="height: 200px;">
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-6">
							<?php $this->createField([
								'name' => 'title',
								'label' => translator::trans('notice.note.title')
							]); ?>
						</div>
						<div class="col-sm-6">
						</div>
					</div>
					<div class="row">
						<div class="col-sm-12">
						<?php 
						$this->createField(array(
								'type' => 'textarea',
								'name' => 'content',
								'label' => translator::trans("notice.note.content"),
								'rows' => 4,
								'class' => 'form-control ckeditor'
							));
						?>
						</div>
					</div>
					<div class="row">
						<div class="col-sm-6 pull-left">
							<div class="btn-group btn-group-justified">
								<div class="btn-group">
									<a href="<?php echo userpanel\url('settings/notice/notes'); ?>" class="btn btn-default"><i class="fa fa-chevron-circle-right"></i> <?php echo translator::trans("notice.return"); ?></a>
								</div>
								<div class="btn-group">
									<a class="btn btn-teal btn-preview"><i class="fa fa-eye"></i> <?php echo translator::trans("notice.preview"); ?></a>
								</div>
								<div class="btn-group">
									<button type="submit" class="btn btn-success"><i class="fa fa-plus"></i> <?php echo translator::trans("notice.add"); ?></button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<div class="modal fade" id="modal-preview" tabindex="-1" data-show="true" role="dialog">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
		<h4 class="modal-title"><?php echo translator::trans('notice.note.title'); ?></h4>
	</div>
	<div class="modal-body">
	<?php echo translator::trans('notice.note.content'); ?>
	</div>
</div>
<?php
$this->the_footer();
