import {Router} from "webuilder";
type thisType = "success" | "warning" | "fatal" | "notice";
export default class Error{
	public readonly SUCCESS = 'success';
	public readonly WARNING = 'warning';
	public readonly FATAL = 'fatal';
	public readonly NOTICE = 'notice';
	protected code:string;
	protected data:any;
	protected param:any;
	protected title:string;
	protected message:string;
	protected type = this.FATAL;
	public setCode(code:string){
		this.code = code;
	}
	public getCode():string{
		return this.code;
	}
	public setData(data:any, key?:string){
		if(key){
			this.data[key] = data;
		}else{
			this.data = data;
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
	public setType(type:thisType){
		this.type = type;
	}
	public getType(){
		return this.type;
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
	public getHtml():string{
		const alert = {
			type: this.getType(),
			txt: this.getMessage(),
			title: this.getTitle(),
			data: this.getData(),
			params: this.getParam(),
			content: this.getMessage(),
			icon: ''
		};
		switch(alert['type']){
			case(this.FATAL):
				alert['type'] = 'danger';
				if(!alert['title']){
					alert['title'] = 'خطا';
				}
				alert['icon'] = '<i class="fa fa-times-circle"></i>'
				break;
			case(this.WARNING):
				alert['type'] = 'warning';
				if(!alert['title']){
					alert['title'] = 'هشدار';
				}
				alert['icon'] = '<i class="fa fa-exclamation-triangle"></i>'
				break;
			case(this.NOTICE):
				alert['type'] = 'info';
				if(!alert['title']){
					alert['title'] = 'توجه';
				}
				alert['icon'] = '<i class="fa fa-info-circle"></i>'
				break;
			case(this.SUCCESS):
				alert['type'] = 'success';
				if(!alert['title']){
					alert['title'] = 'موفق';
				}
				alert['icon'] = '<i class="fa fa-check-square-o"></i>'
				break;
		}
		alert['classes'] = this.getParam('classes');
		if(alert['classes']){
			if(typeof alert['classes'] === 'object'){
				alert['classes'] = alert['classes'].join(' ');
			}
		}else{
			alert['classes'] = '';
		}
		const code = `<div class="alert alert-${alert['type']} ${alert['classes']}" data-note="${this.getData('note')}">
						<button data-dismiss="alert" class="close" type="button">&times;</button>
						${(this.getParam('canEdit')) == true ? `<a data-dismiss="alert" class="close edit" href="${Router.url(`userpanel/settings/notice/notes/edit/${this.getData('note')}`)}"><i class="fa fa-edit"></i></a>` : ''}
						<h4 class="alert-heading">${alert['icon']} ${alert['title']}</h4> 
						<p>${alert['content']}</p>
					</div>
					`;
		return code;
	}
}