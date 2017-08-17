<?php
use \packages\base\translator;
use \packages\userpanel;
use \packages\userpanel\date;
use \themes\clipone\utility;
use \packages\notice\note;
$this->the_header();
?>
<div class="row">
	<div class="col-xs-12">
	<?php if(!empty($this->getNoteLists())){ ?>
		<div class="panel panel-default">
			<div class="panel-heading">
				<i class="fa fa-external-link"></i> <?php echo translator::trans('notice'); ?>
				<div class="panel-tools">
					<a class="btn btn-xs btn-link tooltips" title="<?php echo translator::trans('notice.search'); ?>" href="#search" data-toggle="modal" data-original-title=""><i class="fa fa-search"></i></a>
					<?php if($this->canAdd){ ?>
					<a class="btn btn-xs btn-link tooltips" title="<?php echo translator::trans('notice.note.add'); ?>" href="<?php echo userpanel\url('settings/notice/notes/add'); ?>"><i class="fa fa-plus"></i></a>
					<?php } ?>
					<a class="btn btn-xs btn-link panel-collapse collapses" href="#"></a>
				</div>
			</div>
			<div class="panel-body">
				<div class="table-responsive">
					<table class="table table-hover">
						<?php
						$hasButtons = $this->hasButtons();
						?>
						<thead>
							<tr>
								<th class="center">#</th>
								<th><?php echo translator::trans('notice.note.view'); ?></th>
								<th><?php echo translator::trans('notice.note.type'); ?></th>
								<th><?php echo translator::trans('notice.note.status'); ?></th>
								<?php if($hasButtons){ ?><th></th><?php } ?>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach($this->getNoteLists() as $note){
								$this->setButtonParam('edit', 'link', userpanel\url("settings/notice/notes/edit/{$note->id}"));
								$this->setButtonParam('delete', 'link', userpanel\url("settings/notice/notes/delete/{$note->id}"));
								$statusClass = utility::switchcase($note->status, [
									'label label-success' => note::active,
									'label label-danger' => note::deactive
								]);
								$statusTxt = utility::switchcase($note->status, [
									'notice.note.status.active' => note::active,
									'notice.note.status.deactive' => note::deactive,
								]);
							?>
							<tr>
								<td class="center"><?php echo $note->id; ?></td>
								<td><?php echo $this->getNoteViewName($note); ?></td>
								<td><?php echo $note->type; ?></td>
								
								<td><span class="<?php echo $statusClass; ?>"><?php echo translator::trans($statusTxt); ?></span></td>
								<?php
								if($hasButtons){
									echo("<td class=\"center\">".$this->genButtons()."</td>");
								}
								?>
							</tr>
							<?php
							}
							?>
						</tbody>
					</table>
				</div>
				<?php $this->paginator(); ?>
			</div>
		</div>
		<div class="modal fade" id="search" tabindex="-1" data-show="true" role="dialog">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title"><?php echo translator::trans('notice.search'); ?></h4>
			</div>
			<div class="modal-body">
				<form id="noteSearch" class="form-horizontal" action="<?php echo userpanel\url("settings/notice/notes"); ?>" method="GET">
					<?php
					$this->setHorizontalForm('sm-3','sm-9');
					$feilds = [
						[
							'name' => 'id',
							'type' => 'number',
							'ltr' => true,
							'label' => translator::trans("notice.note.id")
						],
						[
							'name' => 'type',
							'type' => 'select',
							'label' => translator::trans("notice.note.type"),
							'options' => $this->getTypeForSelect()
						],
						[
							'name' => 'status',
							'type' => 'select',
							'label' => translator::trans("notice.note.status"),
							'options' => $this->getStatusForSelect()
						],
						[
							'name' => 'word',
							'label' => translator::trans("notice.note.keyword")
						],
						[
							'type' => 'select',
							'label' => translator::trans('notice.search.comparison'),
							'name' => 'comparison',
							'options' => $this->getComparisonsForSelect()
						]
					];
					foreach($feilds as $input){
						$this->createField($input);
					}
					?>
				</form>
			</div>
			<div class="modal-footer">
				<button type="submit" form="noteSearch" class="btn btn-success"><?php echo translator::trans("notice.search"); ?></button>
				<button type="button" class="btn btn-default" data-dismiss="modal" aria-hidden="true"><?php echo translator::trans('notice.cancel'); ?></button>
			</div>
		</div>
	<?php } ?>
	</div>
</div>
<?php
$this->the_footer();
