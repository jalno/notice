import "bootstrap/js/modal";
import Error from "./Error";
import Modal from "./Modal";
import {Router, AjaxRequest, webuilder} from "webuilder";

declare const packages_notice_notes: {
	canAdd: boolean;
	notes: INote[],
};

interface IData {
	[key: string]: string | number | IData | any;
}

interface INote {
	title: string;
	content: string;
	type: "modal" | "alert";
	params: IData;
	data: IData;
}
export default class View {
	public static notes: INote[] = [];
	public static modals: Modal[] = [];
	public static errors: Error[] = [];

	public static initIfNeeded() {
		if (typeof packages_notice_notes !== undefined) {
			View.notes = packages_notice_notes.notes;
			if (View.notes) {
				View.init();
			}
			if (packages_notice_notes.canAdd) {
				View.shortcut();
			}
		}
	}

	public static init() {
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

	public static addModal(note:INote){
		const modal = new Modal();
		modal.setTitle(note.title);
		modal.setMessage(note.content);
		modal.setParam(note.params);
		modal.setData(note.data);
		View.modals.push(modal);
	}
	public static addError(note:INote) {
		const error = new Error();
		error.setType(note.params.style);
		error.setTitle(note.title);
		error.setMessage(note.content);
		error.setParam(note.params);
		error.setData(note.data);
		View.errors.push(error);
	}
	public static getErrorsHTML():string{
		let code = '';
		for(const error of this.errors){
			code += error.getHtml();
		}
		return code;
	}
	public static getModalsHTML(){
		let code = '';
		for(const modal of this.modals){
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
			if(!$('.main-container .main-content .container .errors .notices').length){
				$('.main-container .main-content .container .errors').append($('<div class="notices"></div>'));
			}
			$('.main-container .main-content .container .errors .notices').html(ErrorsHTML);
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
}