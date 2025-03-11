/*!
 * JxTag v1.00b (https://jxtag.jas.ru/)
 */

class JxTag{
	constructor(options){
		this.options = options;
		this.ArrayTags = [];
		this.Controller = new AbortController();
		this.Timer;
		
		if(this.options.name){
			this.JxOrignalInput = document.getElementById(this.options.name);
		}else{
			this.JxOrignalInput = document.querySelector('.' + this.options.class);
		}

		this.JxTagDiv = document.createElement('div');
		if (typeof this.options.name !== 'undefined') {
			this.JxTagDiv.setAttribute("id", this.options.name+'_');
		}
		this.JxTagWindow = document.createElement('div');
		this.JxTagInput = document.createElement('input');

		this.Enabled = 1;

		if (typeof this.options.new_tags === 'undefined') {
			this.options.new_tags = 1;
		}
		if (typeof this.options.text_tags === 'undefined') {
			this.options.text_tags = 0;
		}
		
		//Режимы работы
		//normal - без вложений
		//parent - с вложениями, но не жёстко
		//bond - с вложениями, жёстко

		this.init(this);
		this.initEvents(this);
	}

	init(tags){
		tags.JxTagDiv.append(tags.JxTagInput);
        tags.JxTagDiv.classList.add("JxTag");
		
		tags.JxTagWindow.classList.add("JxTagWindow");
		tags.JxTagWindow.classList.add("hidden");

        tags.JxOrignalInput.setAttribute('hidden', 'true');
        tags.JxOrignalInput.parentNode.insertBefore(tags.JxTagDiv, tags.JxOrignalInput);

		tags.JxOrignalInput.parentNode.insertBefore(tags.JxTagWindow, tags.JxOrignalInput);
    }

	initEvents(tags){
        tags.JxTagDiv.addEventListener('click' ,function(){
			tags.JxTagInput.focus();
        });

		tags.JxTagDiv.addEventListener('focusin' ,function(){
			if(tags.JxTagInput.value != ''){
				tags.JxTagWindow.style.top=tags.#getOffsetTop()+'px';
				tags.JxTagWindow.style.left=tags.#getOffsetLeft()+'px';
				tags.JxTagWindow.classList.remove("hidden");
			}
        });

		document.onclick = function (event) {
			if((event.target !== tags.JxTagWindow)){
				tags.JxTagWindow.classList.add("hidden");
			}
	 	}
        
        tags.JxTagInput.addEventListener('keydown' , function(e){
			let str = tags.JxTagInput.value.trim();

			if(e.key == 'Enter'){
				e.preventDefault();
				tags.JxTagInput.value = "";
				
				if(e.ctrlKey && (tags.options.mode == 'parent' || tags.options.mode == 'bond')){	
					if(str != ""){
						tags.AddTag(str,{new: 1, parent: ''});
						tags.JxTagWindow.classList.add("hidden");
						tags.AddBond();//создали связь
					}
				}else if(e.altKey){
					if(str != ""){
						if(tags.ArrayTags.length > 0){
							tags.AddTag(tags.ArrayTags[0].title, {id: tags.ArrayTags[0].id});
							tags.JxTagWindow.classList.add("hidden");
						}
					}
				}else{
					if(str != ""){
						if(tags.options.new_tags == 0 && tags.options.text_tags == 0){
							//запрет на создание новых тегов
							if(tags.ArrayTags.length > 0){
								tags.AddTag(tags.ArrayTags[0].title, {id: tags.ArrayTags[0].id});
								tags.JxTagWindow.classList.add("hidden");
							}
						}else if(tags.options.new_tags == 0 && tags.options.text_tags == 1){
							//текстовые теги
							tags.AddTag(str, {new: 0, text: 1});
							tags.JxTagWindow.classList.add("hidden");
						}else if(str == ','){
							tags.AddSeparator(str);
							tags.UpdateTagsField();
						}else{
							tags.AddTag(str);
							tags.JxTagWindow.classList.add("hidden");
						}
					}
				}
			}else if(e.key == 'ArrowLeft'){
				var tagInput = this;
				if(tagInput.value == ''){
					var tag = tagInput.previousElementSibling;
					if(tag){
						tags.JxTagDiv.insertBefore(tagInput, tag);
						tagInput.focus();
					}
				}
			}else if(e.key == 'ArrowRight'){
				var tagInput = this;
				if(tagInput.value == '' && tagInput.nextElementSibling){
					var tag = tagInput.nextElementSibling.nextElementSibling;
					tags.JxTagDiv.insertBefore(tagInput, tag);
					tagInput.focus();
				}
			}else if(e.key == 'Delete'){
				var tagInput = this;
				if(tagInput.value == ''){
					var tag = tagInput.nextElementSibling;
					if(tag){
						tags.DeleteTag(tag);
					}
				}
			}else if(e.key == 'Backspace'){
				if(tags.JxTagInput.value == ''){
					var tagInput = this;
					var tag = tagInput.previousElementSibling;
					if(tagInput.value == '' && tag != null){
						tags.DeleteTag(tag);
					}
				}else{
					clearTimeout(this.Timer);
					let tagsRequestTime = 1000;
					if(tags.options.request_time){
						tagsRequestTime = tags.options.request_time;
					}

					this.Timer = setTimeout(function () {
						if(tags.JxTagInput.value != ''){
							tags.LoadData();
						}else{
							tags.JxTagWindow.classList.add("hidden");
						}
					}, tagsRequestTime);
				}
			}else if(e.key == 'Escape'){
				if(tags.JxTagInput.previousSibling.className == 'bond'){
					tags.JxTagInput.previousSibling.remove();
				}
            }else{
				if(!e.ctrlKey && e.key != 'Alt' && e.key != 'Shift' && e.key != 'Home' && e.key != 'ArrowLeft' && e.key != 'ArrowRight' && e.key != 'NumLock' && e.key != 'CapsLock'){
					clearTimeout(this.Timer);
					let tagsRequestTime = 1000;
					if(tags.options.request_time){
						tagsRequestTime = tags.options.request_time;
					}

					this.Timer = setTimeout(function () {
						if(tags.JxTagInput.value != ''){
							tags.LoadData();
						}else{
							tags.JxTagWindow.classList.add("hidden");
						}
					}, tagsRequestTime);
				}
			}

        });
    }

	#getOffsetTop(){
		let offset=parseInt(parseInt(this.JxTagDiv.offsetTop)+parseInt(this.JxTagInput.offsetTop+20));
		return offset;
	}

	#getOffsetLeft(){
		let offset=parseInt(parseInt(this.JxTagDiv.offsetLeft)+parseInt(this.JxTagInput.offsetLeft));
		return offset;
	}

	async LoadData(){
		this.ArrayTags=[];
		let ArrayTags=this.ArrayTags;
		let varFormData;
		if (typeof this.options.form !== 'undefined') {
			varFormData=jQuery('#'+this.options.form).serialize();
		}

		let response;
		let response_=this.options.url;
		let response_string = this.JxTagInput.value;

		if(this.options?.data?.length > 0){
			this.ArrayTags = this.options.data.filter(el => el.title.search(this.JxTagInput.value) == 0);
		}else{
			let response_flag=0;
			if(this.options.request_min  !== 'undefined'){
				if(response_string.length >= this.options.request_min){
					response_flag=1;
				}
			}else{
				response_flag=1;
			}

			if(response_flag){
				this.Controller.abort();
				this.Controller = new AbortController();

				if(this.options.url.indexOf('?') != -1){response_+='&';}else{response_+='?';}
				if(this.options.mode == 'bond'){
					if(this.JxTagDiv.childNodes[this.JxTagDiv.childNodes.length-3].getAttribute("id") && this.JxTagDiv.childNodes[this.JxTagDiv.childNodes.length-2].className == 'bond'){
						response = await fetch(response_+'title='+this.JxTagInput.value+'&id='+this.JxTagDiv.childNodes[this.JxTagDiv.childNodes.length-3].getAttribute("id")+'', {
							method: 'GET',
							signal: this.Controller.signal
						});
					}
				}else{
					if(this.options.request_title){
						try {
							response = await fetch(response_+''+this.options.request_title+'='+this.JxTagInput.value, {
								method: 'GET',
								signal: this.Controller.signal
							});
						} catch(err) {
							if (err.name == 'AbortError') {
								return false;
							} else {
								throw err;
							}
						}
					}else{
						try {
							response = await fetch(response_+'title='+this.JxTagInput.value, {
								method: 'GET',
								signal: this.Controller.signal
							});
						} catch(err) {
							if (err.name == 'AbortError') {
								return false;
							} else {
								throw err;
							}
						}
					}
				}
				
				if(response.ok){
					//let json = await response.json();//Сделать для получение JSON данных
					let xml = await response.text();
					
					//
					var parseXml;
					if (typeof window.DOMParser != "undefined") {
						parseXml = function(xml) {
							return ( new window.DOMParser() ).parseFromString(xml, "text/xml");
						};
					} else if (typeof window.ActiveXObject != "undefined" &&
						new window.ActiveXObject("Microsoft.XMLDOM")) {
						parseXml = function(xml) {
							var xmlDoc = new window.ActiveXObject("Microsoft.XMLDOM");
							xmlDoc.async = "false";
							xmlDoc.loadXML(xml);
							return xmlDoc;
						};
					} else {
						throw new Error("No XML parser found");
					}
					//

					var xml_ = parseXml(xml);
					for(let j=0; j<=xml_.getElementsByTagName("rec").length-1; j++){

						let value1 = '';
						let value2 = '';
						if(this.options.response_id){
							value1 = xml_.getElementsByTagName("rec")[j].getElementsByTagName(""+this.options.response_id+"")[0].innerHTML;
						}else{
							value1 = xml_.getElementsByTagName("rec")[j].getElementsByTagName("rec_id")[0].innerHTML;
						}

						if(this.options.response_title){
							value2= xml_.getElementsByTagName("rec")[j].getElementsByTagName(""+this.options.response_title+"")[0].innerHTML;
						}else{
							value2 = xml_.getElementsByTagName("rec")[j].getElementsByTagName("rec_title")[0].innerHTML;
						}

						let response_='ArrayTags.push({';
						response_+='id: "'+value1+'",';
						response_+='title: "'+value2+'"';
						response_+='});';

						eval(response_);
					}
				}else{
					console.error("Ошибка HTTP: " + response.status);
				}
			}
		}

		//вывод окна или его закрытие
		if(this.ArrayTags.length > 0){
			this.JxTagWindow.innerHTML="";
			
			this.JxTagWindow.style.top=this.#getOffsetTop()+'px';
			this.JxTagWindow.style.left=this.#getOffsetLeft()+'px';
			this.JxTagWindow.classList.remove("hidden");
			
			for(let i=0; i<=this.ArrayTags.length-1; i++){
				let tag_ = document.createElement('div');
				tag_.classList.add("tag");
				tag_.innerText = this.ArrayTags[i].title;

				tag_.addEventListener("click", (e) => {
					e.preventDefault(true);
					if(this.options.mode != 'normal' && e.ctrlKey){
						if(this.JxTagDiv.childNodes.length > 1){
							if(this.JxTagDiv.childNodes[this.JxTagDiv.childNodes.length-2].className != 'bond'){
								this.AddSeparator();
							}
						}
						this.AddTag(this.ArrayTags[i].title, {bond: 1, id: this.ArrayTags[i].id});
						this.AddBond();
					}else{
						this.AddTag(this.ArrayTags[i].title, {id: this.ArrayTags[i].id});
					}
					this.JxTagWindow.classList.add("hidden");
					this.JxTagInput.value = "";

					if(this.options.tag_add_function){
						eval(this.options.tag_add_function+'();');
					}

					//только для режимов с вложенностью
					this.JxTagInput.focus();
				}, false);

				if(typeof this.options.max_tags_win !== 'undefined'){
					if(i < this.options.max_tags_win){
						this.JxTagWindow.append(tag_);
					}
				}else{
					this.JxTagWindow.append(tag_);
				}
			}
		}else{
			this.JxTagWindow.classList.add("hidden");
		}
		//вывод окна или его закрытие

	}

	Disable(){
		this.Enabled = 0;
		this.JxTagInput.disabled = true;
		this.JxTagDiv.classList.add("disabled");
	}

	Enable(){
		this.Enabled = 1;
		this.JxTagInput.disabled = false;
		this.JxTagDiv.classList.remove("disabled");
	}

	AddBond(){
		let bond = document.createElement('div');
		bond.classList.add("bond");
		bond.innerText = "→";

		this.JxTagDiv.insertBefore(bond, this.JxTagInput);
	}

	AddSeparator(){
		let separator = document.createElement('div');
		separator.classList.add("separator");
		separator.innerText = ",";

		this.JxTagDiv.insertBefore(separator, this.JxTagInput);
	}

	AddTag(string, options){
		//по умолчанию
		let id_=undefined;
		let tagId;
		let tagDisabled=0;
		let tagNew=1;
		let tagText=0;
		let tagParent=0;
		let tagBond=0;

		//
		for(let i=0; i<=this.ArrayTags.length-1; i++){
			if(this.ArrayTags[i].title == string){
				id_=this.ArrayTags[i].id;
			}
		}
		if(id_ !== undefined){tagId=id_;}
		//
		
		//опции тега
		if(typeof options !== 'undefined'){
			if(typeof options.id !== 'undefined'){
				tagId=options.id;
			}
			if(typeof options.disabled !== 'undefined'){
				tagDisabled=options.disabled;
			}
			if(typeof options.new !== 'undefined'){
				tagNew=options.new;
			}
			if(typeof options.text !== 'undefined'){
				tagText=options.text;
			}
			if(typeof options.parent !== 'undefined'){
				tagParent=options.parent;
			}
			if(typeof options.bond !== 'undefined'){
				tagBond=options.bond;
			}
		}
		//опции тега
		
		if(this.options.mode != 'normal'){
			if(tagParent){
				if(tagBond != 1){
					this.AddBond();
				}
			}else{
				if(this.JxTagDiv.childNodes.length > 1){
					if(this.JxTagDiv.childNodes[this.JxTagDiv.childNodes.length-2].className != 'bond'){
						if(this.JxTagDiv.childNodes[this.JxTagDiv.childNodes.length-2].className != 'separator'){
							this.AddSeparator();
						}
					}
				}
			}
		}

		var tagInput = this;

		var tag = document.createElement('div');
		tag.classList.add("tag");
		
		if(this.options.mode != 'normal'){
			if(tagParent){
				tag.setAttribute("parent", tagParent);
			}
			if(this.JxTagDiv.childNodes.length > 1){
				if(this.JxTagDiv.childNodes[this.JxTagDiv.childNodes.length-2].className == 'bond'){
					tag.setAttribute("parent", this.JxTagDiv.childNodes[this.JxTagDiv.childNodes.length-3].getAttribute("id"));
				}
			}
		}

		if(typeof tagId !== 'undefined'){
			tag.setAttribute('id', tagId);	
		}else{
			if(tagNew == 1){
				tag.classList.add("new");
			}
			if(tagText == 1){
				tag.classList.add("text");
			}
		}

		if(tagDisabled == 1){
			tag.classList.add("disabled");
		}

		tag.innerText = string;

		var deleteTagIcon = document.createElement('a');
		deleteTagIcon.innerHTML = '&times;';
		deleteTagIcon.addEventListener('click' , function(e){
			e.preventDefault();
			var tag = this.parentNode;

			for(let i=0; i < tagInput.JxTagDiv.childNodes.length; i++){
				if(tagInput.JxTagDiv.childNodes[i] == tag){
					tagInput.DeleteTag(tag, i);
				}
			}
		})

		if(tagDisabled != 1){
			tag.appendChild(deleteTagIcon);
		}

		//проверка на существования тега в списке
		let tag_exist=0;
		for(let i=0; i <= this.JxTagDiv.childNodes.length-1; i++){
			if(this.JxTagDiv.childNodes[i].getAttribute('id') != null){
				if(tagId == this.JxTagDiv.childNodes[i].getAttribute('id')){
					tag_exist=1;
				}
			}
		}
		//проверка на существования тега в списке

		if(!tag_exist){
			let max_tags_flag=1;
			if(this.options.max_tags !== 'undefined'){
				if((parseInt(this.JxTagDiv.childNodes.length) - 1) == this.options.max_tags){
					max_tags_flag=0;
				}
			}

			if(max_tags_flag){
				this.JxTagDiv.insertBefore(tag, this.JxTagInput);
				this.UpdateTagsField();
			}
		}
	}
	
	DeleteTag(tag, number ){
		let ArrayDeleteNode=[];
		let tagPosition;
		let startFlag=0;
		let lastFlag=0;

		if(this.Enabled == 1){
			for(let i=0; i <= this.JxTagDiv.childNodes.length-1; i++){
				if(startFlag == 0 && this.JxTagDiv.childNodes[i] == tag){startFlag=1;tagPosition=i;}
				if(startFlag == 1 && (this.JxTagDiv.childNodes[i].nodeName == 'INPUT' || this.JxTagDiv.childNodes[i].className == 'separator')){break;}
				if(startFlag == 1){ArrayDeleteNode.push(i);}
			}

			if(tag?.nextSibling?.className == 'bond'){
				if(this.JxTagDiv.childNodes[ArrayDeleteNode[ArrayDeleteNode.length-1]].nextSibling.nodeName == 'INPUT'){
					lastFlag=1;
				}

				let j=0;
				for(let i=0; i <= ArrayDeleteNode.length-1; i++){
					this.JxTagDiv.childNodes[ArrayDeleteNode[i-j]].remove();
					j++;
				}

				if(lastFlag){this.JxTagDiv.childNodes[this.JxTagDiv.childNodes.length-2].remove();}
				if(tagPosition == 0){this.JxTagDiv.childNodes[0].remove();}
				if(lastFlag == 0 && tagPosition != 0){this.JxTagDiv.childNodes[tagPosition-1].remove();}
			}else{
				if(tagPosition != 0 && tag.previousSibling.className == 'bond'){
					tag.previousSibling.remove();
				}else if(tagPosition != 0 && (tag.previousSibling.className == 'separator' && tag.nextSibling.nodeName == 'INPUT')){
					tag.previousSibling.remove();
				}else if(tag?.nextSibling?.className == 'separator'){
					tag.nextSibling.remove();
				}
				tag.remove();
			}

			this.UpdateTagsField();
		}
	}

	UpdateTagsField(){
		let allTags='';
		if(this.options.mode != 'normal'){
			let startTag=0;

			for(let i=0; i <= this.JxTagDiv.childNodes.length-1; i++){
				if(this.JxTagDiv.childNodes[i].className.substr(0,3) == 'tag'){
					if(startTag == 0){allTags+='[';startTag=1;}

					if(this.JxTagDiv.childNodes[i].getAttribute('id') != null){
						allTags+=' '+this.JxTagDiv.childNodes[i].getAttribute('id')+' ';
					}else{
						let typeTag = 'new';
						if(this.options.new_tags == 0){typeTag = 'text';}
						allTags+=' {``' + typeTag + '``: ``' + this.JxTagDiv.childNodes[i].innerText.substring(0, this.JxTagDiv.childNodes[i].innerText.length - 1) + '``} ';
					}

					if(allTags && this.JxTagDiv.childNodes[i+1]?.className == 'separator'){allTags+=']';allTags+=',';startTag=0;}
				}
				if(i == this.JxTagDiv.childNodes.length-1){allTags+=']';}
			}
		}else{
			for(let i=0; i <= this.JxTagDiv.childNodes.length-1; i++){
				if(this.JxTagDiv.childNodes[i].className.substr(0,3) == 'tag'){
					if(allTags){allTags+=',';}
					if(this.JxTagDiv.childNodes[i].getAttribute('id') != null){
						allTags+='[ '+this.JxTagDiv.childNodes[i].getAttribute('id')+' ]';
					}else{
						let typeTag = 'new';
						if(this.options.new_tags == 0){typeTag = 'text';}
						allTags+='[ {``' + typeTag + '``: ``'+this.JxTagDiv.childNodes[i].innerText.substring(0, this.JxTagDiv.childNodes[i].innerText.length - 1)+'``} ]';
					}
				}
			}
		}
		this.JxOrignalInput.value=allTags;
	}
}

/*-------	JxTag	-------*/