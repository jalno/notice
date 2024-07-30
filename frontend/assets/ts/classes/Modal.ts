import {Router} from "webuilder";
export default class Modal{
	protected data:any;
	protected param:any;
	protected title:string;
	protected message:string;
	public setData(val:any, key?:string){
		if(key){
			this.data[key] = val;
		}else{
			this.data = val;
		}
	}
	public getData(key?:string):any{
		if(key){
			return(this.data.hasOwnProperty(key) ? this.data[key] : null);
		}else{
			return this.data;
		}
	}
	public setParam(param:any, key?:string){
		if(key){
			this.param[key] = param;
		}else{
			this.param = param;
		}
	}
	public getParam(key?:string):any{
		if(key){
			return(this.param.hasOwnProperty(key) ? this.param[key] : {});
		}else{
			return this.param;
		}
	}
	public setMessage(message:string){
		this.message = message;
	}
	public getMessage():string{
		return this.message;
	}
	public setTitle(title:string){
		this.title = title;
	}
	public getTitle():string{
		return this.title;
	}
	public getHTML():string{
		let classes = this.getParam('classes');
		if(classes){
			if(typeof classes === 'object'){
				classes = classes.join(' ');
			}
		}else{
			classes = '';
		}
		const code = `<div class="modal fade please-call-me ${classes}" id="notice-note-${this.getData('note')}" tabindex="-1" data-show="true" role="dialog" data-note="${this.getData('note')}">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
							${this.getParam('canEdit') == true ? `<a data-dismiss="alert" class="close edit" href="${Router.url(`userpanel/settings/notice/notes/edit/${this.getData('note')}`)}"><i class="fa fa-edit"></i></a>` : ''}
							<h4 class="modal-title">${this.getTitle()}</h4>
						</div>
						<div class="modal-body">
						${this.getMessage()}
						</div>
					</div>`;
		return code;
	}
}