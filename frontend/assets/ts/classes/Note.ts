import * as $ from "jquery";
import "select2";
import "jquery-ui/ui/widgets/autocomplete.js";
import "jquery-bootstrap-checkbox";
import "bootstrap/js/modal";
import "bootstrap/js/tooltip";
import {Router, webuilder, AjaxRequest} from "webuilder";
import "ion-rangeslider";
import "jquery.growl";
import "bootstrap-inputmsg";
interface user{
	id:number;
	name:string;
	lastname:string;
	email:string;
	cellphone:string;
	
}
interface searchResponse extends webuilder.AjaxResponse{
	items: user[];
}
interface variable{
	key:string;
	description:string;
}
declare const CKEDITOR:any;
export default class Note{
	static $form;
	private static runSelect2():void{
		$('select', Note.$form).attr('dir','rtl').select2({
			language:'fa',
			minimumResultsForSearch: Infinity
		});
		$('select[name=view]', Note.$form).select2({
			tags: true,
			multiple: false,
			language:'fa'
		});
		$('select[name=view]', Note.$form).change(Note.initalForm);
	}
	private static initalForm():void{
		const $view = $('select[name=view] option:selected', Note.$form);
		const isNew = ($view.data('select2-tag') || $view.data('custom'));
		if(isNew){
			$('input[name=kind]', Note.$form).val('address');
			$('input[name=address]', Note.$form).parent().slideDown();
		}else{
			$('input[name=kind]', Note.$form).val('view');
			$('input[name=address]', Note.$form).parent().slideUp();
		}
	}
	private static typeListener():void{
		$('select[name=type]', Note.$form).on('change', function(){
			const option = $('option:selected', $(this)).val();
			$(`.not-type:not(.type-${option})`, Note.$form).hide();
			$(`.not-type.type-${option}`, Note.$form).show();
			$('.preview', Note.$form).html('');
		}).trigger('change');
	}
	private static runUserSearch(){
		$('input[name=username]', Note.$form).on('keyup keypress keydown', function(){
			function select(event, ui):boolean{
				const $form = $(this).parents('form');
				const $users = $('.user-selection', $form);
				let $exist = false;
				$('.panel.panel-white input[type=checkbox]', $users).each(function(){
					if($(this).val() == ui.item.id){
						$exist = true;
						return false;
					}
				});
				$(this).val('');
				if(!$exist){
					$('.panel.panel-white', $users).slideDown();
					const $input = `<div class="checkbox"><label class=""><input name="users[${ui.item.id}]" value="${ui.item.id}" type="checkbox" checked>${ui.item.name} ${ui.item.lastname ? ' '+ui.item.lastname : ''}</label></div>`
					$($input).appendTo($('.panel-body .mCSB_container', $users));
					Note.runBootstrapCheckbox();
					return false;
				}
			}
			$(this).autocomplete({
				source: function( request, response ) {
					$.ajax({
						url: Router.url("userpanel/users"),
						dataType: "json",
						data: {
							ajax: 1,
							word: request.term
						},
						success: function( data: searchResponse) {
							if(data.status){
								response( data.items );
							}
						}
					});
				},
				select: select,
				create: function() {
					$(this).data('ui-autocomplete')._renderItem = function( ul, item:user ) {
						return $( "<li>" )
							.append( "<strong>" + item.name+(item.lastname ? ' '+item.lastname : '')+ "</strong><small class=\"ltr\">"+item.email+"</small><small class=\"ltr\">"+item.cellphone+"</small>" )
							.appendTo( ul );
					}
				}
			});
		});
	}
	private static runBootstrapCheckbox(){
		$('.user-selection .checkbox input, .user-selection .checkbox-inline input').bootstrapCheckbox()
	}
	private static userListener():void{
		$('input[name=user]', Note.$form).on('change', function(){
			if($(this).prop('checked')){
				if($(this).val() == 'selection'){
					$('.user-selection', Note.$form).slideDown();
				}else{
					$('.user-selection', Note.$form).slideUp();
				}
			}
		}).trigger('change');
		$('input[name=usertypes]', Note.$form).on('change', function(){
			if($(this).prop('checked')){
				const parents = $(this).data('parent');
				for(const parent of parents){
					$(`input[name=usertypes][value=${parent}]`, Note.$form).prop('checked', true);
				}
				Note.runBootstrapCheckbox();
			}
		});
	}
	private static preview():void{
		function getErrorsHTML(type:string, title?:string, content?:string):string{
			let icon:string = '';
			switch(type){
				case('success'):
					type = 'success';
					if(!title){
						title = 'موفق';
					}
					icon = '<i class="fa fa-check-square-o"></i>'
					break;
				case('fatal'):
					type = 'danger';
					if(!title){
						title = 'خطا';
					}
					icon = '<i class="fa fa-times-circle"></i>'
					break;
				case('warning'):
					type = 'warning';
					if(!title){
						title = 'هشدار';
					}
					icon = '<i class="fa fa-exclamation-triangle"></i>'
					break;
				case('notice'):
					type = 'info';
					if(!title){
						title = 'توجه';
					}
					icon = '<i class="fa fa-info-circle"></i>'
					break;
			}
			const code = `<div class="alert alert-${type}">
								<button data-dismiss="alert" class="close" type="button">&times;</button>
								<h4 class="alert-heading">${icon} ${title}</h4> 
								<p>${content ? content : 'محتوای اعلان'}</p>
							</div>
							`
			return code;
		}
		$('.btn-preview').on('click', function(e){
			e.preventDefault();
			const type = $('select[name=type] option:selected').val();
			const title = $('input[name=title]', Note.$form).val();
			const content = CKEDITOR.instances['content'].getData();
			switch(type){
				case('alert'):
					let style = $('input[name=style]:checked', Note.$form).val();
					if(!style){
						style = 'success';
					}
					const code = getErrorsHTML(style, title, content);
					$('.preview', Note.$form).html(code);
					break;
				case('modal'):
					const modal = $('#modal-preview');
					if(title){
						$('.modal-header .modal-title', modal).html(title);
					}
					if(content){
						$('.modal-body', modal).html(content);
					}
					modal.modal('show')
					break;
			}
		});
	}
	private static runjQRangeSlider(){
		const $start = $("input[name='time[start]']", Note.$form);
		const $end = $("input[name='time[end]']", Note.$form);
		const from = parseInt($start.val() as string);
		const to = parseInt($end.val() as string);
		function valuesChangingListener(obj: IonRangeSliderEvent){
			$start.val(obj.from);
			$end.val(obj.to);
		}
		$(".slider", Note.$form).ionRangeSlider({
			type: "double",
			grid: true,
			min: 0,
			max: 23,
			from: from,
			to: to,
			min_interval: 1,
			onChange: valuesChangingListener,
			prefix: 'ساعت '
		});
	}
	private static runFormSubmitListener(){
		Note.$form.on('submit', function(e){
			e.preventDefault();
			let data = new FormData(this as HTMLFormElement);
			data.set('content', CKEDITOR.instances['content'].getData());
			$(this).formAjax({
				data: data,
				contentType: false,
				processData: false,
				success: (data: webuilder.AjaxResponse) => {
					$.growl.notice({
						title:"موفق",
						message:"انجام شد ."
					});
					if(data.redirect){
						window.location.href = data.redirect;
					}
				},
				error: function(error:webuilder.AjaxError){
					if(error.error == 'data_duplicate' || error.error == 'data_validation'){
						let $input = $('[name='+error.input+']');
						let $params = {
							title: 'خطا',
							message:''
						};
						if(error.error == 'data_validation'){
							$params.message = 'داده وارد شده معتبر نیست';
						}else if(error.error == 'data_duplicate'){
							$params.message = 'داده وارد شده تکراری میباشد';
						}
						if($input.length){
							$input.inputMsg($params);
						}else{
							$.growl.error($params);
						}
					}else{
						$.growl.error({
							title:"خطا",
							message:'درخواست شما توسط سرور قبول نشد'
						});
					}
				}
			});
		});
	}
	public static init(){
		const $body = $('body');
		if($body.hasClass('notice add-note')){
			Note.$form = $('.notice-note-add', $body);
		}else if($body.hasClass('notice edit-note')){
			Note.$form = $('.notice-note-edit', $body);
		}
		Note.preview();
		Note.runSelect2();
		Note.initalForm();
		Note.typeListener();
		Note.userListener();
		Note.runUserSearch();
		Note.runjQRangeSlider();
		Note.runFormSubmitListener();
	}
	public static initIfNeeded(){
		const $body = $('body');
		if($body.hasClass('notice add-note') || $body.hasClass('notice edit-note')){
			Note.init();
		}
	}
}