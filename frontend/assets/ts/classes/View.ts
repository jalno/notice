import * as $ from "jquery";
import "bootstrap/js/modal";
import Error from "./Error";
import Modal from "./Modal";
import {Router, AjaxRequest, webuilder} from "webuilder";
interface note{
	title:string;
	content:string;
	type:'modal' | 'alert';
	params:any;
	data:any;
	
}
export default class View{
	static notes:note[] = [];
	static Modals:Modal[] = [];
	static Errors:Error[] = [];
	public static addModal(note:note){
		const modal = new Modal();
		modal.setTitle(note.title);
		modal.setMessage(note.content);
		modal.setParam(note.params);
		modal.setData(note.data);
		View.Modals.push(modal);
	}
	public static addError(note:note){
		const error = new Error();
		error.setType(note.params.style);
		error.setTitle(note.title);
		error.setMessage(note.content);
		error.setParam(note.params);
		error.setData(note.data);
		View.Errors.push(error);
	}
	public static getErrorsHTML():string{
		let code = '';
		for(const error of this.Errors){
			code += error.getHtml();
		}
		return code;
	}
	public static getModalsHTML(){
		let code = '';
		for(const modal of this.Modals){
			code += modal.getHTML();
		}
		return code;
	}
	private static closableListener():void{
		$('.alert.notice.note-closable button.close, .modal.notice.note-closable button.close').on('click', function(){
			const note = $('.alert.notice.note-closable, .modal.notice.note-closable').data('note');
			AjaxRequest({
				url: Router.url(`userpanel/settings/notice/notes/close?ajax=1`),
				data: {
					note: note
				},
				type: 'post',
				success: (data: webuilder.AjaxResponse) => {
					
				},
				error: function(error:webuilder.AjaxError){
					$.growl.error({
						title:"خطا",
						message:'متاسفانه خطایی بوجود آمده'
					});
				}
			});
		});
	}
	public static run(){
		const ErrorsHTML = View.getErrorsHTML()
		if(ErrorsHTML){
			$('.main-container .main-content .container .errors').html(ErrorsHTML);
		}

		const ModalsHTML = View.getModalsHTML();
		if(ModalsHTML){
			$('.main-container .main-content .container').append(ModalsHTML);

			$('.modal.please-call-me').modal();
		}
		View.closableListener();
	}
	public static shortcut(){
		if(!$('.notice.shortcut').length){
			const bell = `<li><a target="_blank" href="${Router.url(`userpanel/settings/notice/notes/add?address=${window.location.href}`)}">
				<i class="fa fa-sticky-note-o"></i>
				<div></div></a></li>`
			$('.navbar-tools .nav.navbar-right').prepend(bell);
		}
	}
	public static init(){
		for(const note of View.notes){
			switch(note.type){
				case('alert'):
					View.addError(note);
					break
				case('modal'):
					View.addModal(note);
					break
			}
		}
		View.run();
	}
	public static initIfNeeded(){
		View.notes = packages_notice_notes.notes;
		if(packages_notice_notes.canAdd){
			View.shortcut();
		}
		if(View.notes){
			View.init();
		}
	}
}