/*!
* JxChart v1.00e (https://jxchart.jas.ru/)
*/

class JxChart{
	constructor(options, ArrayTypes = [], ArrayData = [], hashPercentage = [], hashChart = [], hashChartDisabled = [], hashTotal = [], hashShow = [], hashFilter = [], hashFilterValue = [], varChartType, JxChartPatterns = [], JxChartColors = []){
		this.options=options;
		this.ArrayFields = [];
		this.ArrayChartTypes = [];
		this.ArrayData=ArrayData;

		this.ArrayTypes=ArrayTypes;
		if (typeof this.options.types !== 'undefined') {
			this.ArrayTypes = this.options.types;
		}

		this.hashPercentage=hashPercentage;
		this.hashTotal=hashTotal;
		this.hashChart=hashChart;
		this.hashShow=hashShow;
		this.hashChartDisabled=hashChartDisabled;
		this.hashFilter=hashFilter;
		this.hashFilterValue=hashFilterValue;

		this.varChartType=varChartType;
		this.varChartTitle='';
		if (typeof this.options.width !== 'undefined') {
			this.varChartWidth=this.options.width;
		}else{
			let computedStyle;
			let elementChartWidth = document.getElementById(this.options.field_chart).clientWidth;
			if(getComputedStyle){
				computedStyle = getComputedStyle(document.getElementById(this.options.field_chart));	
				elementChartWidth -= parseFloat(computedStyle.paddingLeft) + parseFloat(computedStyle.paddingRight);
			}
			//this.varChartWidth=$("#"+this.options.field_chart).width();//jQuery
			this.varChartWidth=elementChartWidth;
		}

		if (typeof this.options.height !== 'undefined') {
			this.varChartHeight=this.options.height;
		}else{
			let computedStyle;
			document.getElementById(this.options.field_chart).style.height = 'auto';
			//$("#"+this.options.field_chart).height('auto');

			let elementChartHeight = document.getElementById(this.options.field_chart).clientHeight;
			if(getComputedStyle){
				computedStyle = getComputedStyle(document.getElementById(this.options.field_chart));	
				elementChartHeight -= parseFloat(computedStyle.paddingTop) + parseFloat(computedStyle.paddingBottom);
			}
			//this.varChartHeight=$("#"+this.options.field_chart).height();
			this.varChartHeight=elementChartHeight;
		}

		this.varChartExpandCollapse=0;

		this.JxChartPatterns = JxChartPatterns;
		this.JxChartColors = ['#64b71a','#049ce9','#ffc600','#00e4ff','#847bd9','#c83922','#4aaa31','#e66b21','#0073b5'];
	
		//Ищем текущий тип отчета
		let CurrentReportType;
		if(typeof this.options.current_type !== 'undefined'){
			//выбираем из options
			for(let i=0; i<=this.ArrayTypes.length-1; i++){
				if(this.ArrayTypes[i].id == this.options.current_type){
					CurrentReportType=i;
					this.varChartTitle=this.ArrayTypes[i].title;
				}
			}
			this.CurrentReportType=CurrentReportType;
		}else{
			//ищем из выбора поля
			if(typeof document.querySelector("#"+this.options.form+" #"+this.options.type+"") !== "undefined" && document.querySelector("#"+this.options.form+" #"+this.options.type+"") !== null){
				for(let i=0; i<=this.ArrayTypes.length-1; i++){
				
					if(this.ArrayTypes[i].id == parseInt(document.querySelector("#"+this.options.form+" #"+this.options.type+"").value)){
						CurrentReportType=i;
						this.varChartTitle=this.ArrayTypes[i].title;
					}
				}
			}
			if(!CurrentReportType){
				CurrentReportType = 0;				
			}
			this.CurrentReportType=CurrentReportType;			
		}

		//Ищем текущий тип отчета
		
		//Считываем текущее для фильтров
		// let ArrayFilters=ArrayTypes[this.CurrentReportType].filters;
		// ArrayFilters=ArrayFilters.replace(/`/gim, '\"');
		// let TempFiltersData='';
		// if(ArrayFilters){
		// 	eval('var ArrayFilters='+ArrayFilters+';');
			
		// 	for(let i=0; i<=ArrayFilters.length-1; i++){
		// 		var Temp=ArrayFilters[i].field;
		// 		TempArrayValues[i]=$('#'+Temp+'').val();
		// 	}
		// }
		//Считываем текущее для фильтров
	
		//Поля
		let ArrayFields=this.ArrayTypes[this.CurrentReportType].fields;
		//Поля

		//Типы графиков
		this.ArrayChartTypes=this.ArrayTypes[this.CurrentReportType].chart_types;
		//Типы графиков

		//
		for(let i=0; i<=ArrayFields.length-1; i++){
			if(ArrayFields[i].show){
				this.hashChart[ArrayFields[i].name]=1;
			}
		}
		//
		this.ArrayFields=ArrayFields;

		if(this.options.url){
			this.LoadDataURL();
		}else if(this.options.data){
			this.LoadData();
			//console.log(this.options.data);
		}
		const debounce = (func, wait, immediate) => {
			let timeout;
			return () => {
				const context = this, args = arguments;
				const later = function() {
					timeout = null;
					if (!immediate) func.apply(context, args);
				};
				const callNow = immediate && !timeout;
				clearTimeout(timeout);
				timeout = setTimeout(later, wait);
				if (callNow) func.apply(context, args);
			};
		};
		window.addEventListener('resize', debounce(() => this.ShowChart('resize'), 500, false), false);
	}

	LoadData(TempAction){
		let ArrayFields=this.ArrayFields;
		let ArrayChartTypes=this.ArrayChartTypes;
		this.ArrayData=[];
		let ArrayData=this.ArrayData;
		let ArrayTypes=this.ArrayTypes;
		this.ArrayData = this.options.data;

		this.#ControlLoading(1);

		if (typeof this.options.current_type === 'undefined' && TempAction == 'load') {
			//проверка на изменение типа графика
			let CurrentReportType;
			for(let i=0; i<=ArrayTypes.length-1; i++){
				if(ArrayTypes[i].id == parseInt(document.querySelector("#"+this.options.form+" #"+this.options.type+"").value)){
					CurrentReportType=i;
					this.varChartTitle=ArrayTypes[i].title;
				}
			}
			this.CurrentReportType=CurrentReportType;
	
			//Поля
			let ArrayFields=ArrayTypes[this.CurrentReportType].fields;
			//Поля
	
			//Типы графиков
			this.ArrayChartTypes=ArrayChartTypes=ArrayTypes[this.CurrentReportType].chart_types;
			//Типы графиков
	
			//
			for(let i=0; i<=ArrayFields.length-1; i++){
				if(ArrayFields[i].show){
					this.hashChart[ArrayFields[i].name]=1;
				}
			}
			//
			this.ArrayFields=ArrayFields;
		}

		this.#ControlLoading(0);

		if(ArrayTypes[this.CurrentReportType].reverse == "1"){
			ArrayData.reverse();
		}
				
		if(this.options.table == 1){
			this.ShowTable();
		}

		if(this.options.chart == 1){
			this.varChartType=this.ArrayChartTypes[0];
			this.ShowChart('load');
		}
	}

	async LoadDataURL(TempAction){
		let ArrayFields=this.ArrayFields;
		let ArrayChartTypes=this.ArrayChartTypes;
		this.ArrayData=[];
		let ArrayData=this.ArrayData;
		let ArrayTypes=this.ArrayTypes;
		let varFormData;
		if (typeof this.options.form !== 'undefined') {
			varFormData=jQuery('#'+this.options.form).serialize();
		}
	
		if (typeof this.options.current_type === 'undefined' && TempAction == 'load') {
			//проверка на изменение типа графика
			let CurrentReportType;
			for(let i=0; i<=ArrayTypes.length-1; i++){
				if(ArrayTypes[i].id == parseInt(document.querySelector("#"+this.options.form+" #"+this.options.type+"").value)){
					CurrentReportType=i;
					this.varChartTitle=ArrayTypes[i].title;
				}
			}
			this.CurrentReportType=CurrentReportType;
	
			//Поля
			let ArrayFields=ArrayTypes[this.CurrentReportType].fields;
			//Поля
	
			//Типы графиков
			this.ArrayChartTypes=ArrayTypes[this.CurrentReportType].chart_types;
			//Типы графиков
	
			//
			for(let i=0; i<=ArrayFields.length-1; i++){
				if(ArrayFields[i].show){
					this.hashChart[ArrayFields[i].name]=1;
				}
			}
			//
			this.ArrayFields=ArrayFields;
		}
		
		this.#ControlLoading(1);
	
		let response;
		if(this.options.url.indexOf('?') != -1){
			response = await fetch(this.options.url+'&'+varFormData, {method: 'GET'});
		}else{
			response = await fetch(this.options.url+'?'+varFormData, {method: 'GET'});
		}
		if(response.ok){
			//let json = await response.json();//Сделать для получение JSON данных
			let xml = await response.text();
			this.#ControlLoading(0);

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

			var xml_ = parseXml(xml);
			for(let j=0; j<=xml_.getElementsByTagName("rec").length-1; j++){
				let arr_='';
				for(let i=0; i<=ArrayFields.length-1; i++){
					if(i>0){arr_+=',';}
					let value= xml_.getElementsByTagName("rec")[j].getElementsByTagName("rec_"+ArrayFields[i].name+"")[0].innerHTML;
					arr_+=''+ArrayFields[i].name+':"'+value+'"';
				}
				eval('ArrayData.push({'+arr_+'});');
			}
			
			if(ArrayTypes[this.CurrentReportType].reverse == "1"){
				ArrayData.reverse();
			}
					
			if(this.options.table == 1){
				this.ShowTable();
			}
	
			if(this.options.chart == 1){
				this.varChartType=this.ArrayChartTypes[0];
				this.ShowChart('load');
			}
		}else{
			console.error("Ошибка HTTP: " + response.status);
		}
	}

	ShowTable(){
		let TempContent='';
				
		let TempCounterTitle='';
		let TempItemsNum='';
		let TempArrayValues=[];
		let flagChart=false;
						
		//Ищем текущий тип отчета
		// let CurrentReportType;
		// for(let i=0; i<=ArrayTypes.length-1; i++){
		// 	if(ArrayTypes[i].id == parseInt($("#JCMSReportType").val())){
		// 		CurrentReportType=i;
		// 	}
		// }
		//Ищем текущий тип отчета
		
		//Считываем текущее для фильтров
		// let ArrayFilters=ArrayTypes[CurrentReportType].filters;
		// ArrayFilters=ArrayFilters.replace(/`/gim, '\"');
		// var TempFiltersData='';
		// if(ArrayFilters){
		// 	eval('var ArrayFilters='+ArrayFilters+';');
			
		// 	for(let i=0; i<=ArrayFilters.length-1; i++){
		// 		var Temp=ArrayFilters[i].field;
		// 		//TempFiltersData+='&'+ArrayFilters[i].field+'='+$('#'+Temp+'').val()+'';
		// 		TempArrayValues[i]=$('#'+Temp+'').val();
		// 	}
		// }
		//Считываем текущее для фильтров
			
		//Считываем настройки отчета
		if(this.ArrayTypes[this.CurrentReportType].period == 1){
			if(typeof document.getElementById('period') !== "undefined" && document.getElementById('period') !== null){
				document.getElementById('period').setAttribute('display', 'true');
			}
		}else{
			if(typeof document.getElementById('period') !== "undefined" && document.getElementById('period') !== null){
				document.getElementById('period').setAttribute('display', 'false');
			}
		}
		
		let TempSpecification = '';
		if(this.ArrayTypes[this.CurrentReportType].specification == 1){
			if(typeof document.getElementById('specification') !== "undefined" && document.getElementById('specification') !== null){
				document.getElementById('specification').setAttribute('display', 'true');
				TempSpecification = document.getElementById('specification').value;
			}
		}else{
			if(typeof document.getElementById('specification') !== "undefined" && document.getElementById('specification') !== null){
				document.getElementById('specification').setAttribute('display', 'false');
			}
		}
		//Считываем настройки отчета
		
		// if(this.ArrayFilters){
		// 	//Дополнительные фильтры
		// 	$("div[id^='filter_']").remove();
				
		// 	for(let i=0; i<=ArrayFilters.length-1; i++){
		// 		var TempFiltersContent='';
				
		// 		TempFiltersContent+='<div class="table_rubricator_l_item" id="filter_'+i+'">';
		// 		TempFiltersContent+=''+ArrayFilters[i].title+': ';
		// 		TempFiltersContent+='<select class="jcms_select" style="width: 200px;" name="'+ArrayFilters[i].field+'" id="'+ArrayFilters[i].field+'">';
		// 		var TempValues=ArrayFilters[i].values;
		// 		var TempValues=TempValues.split(';');
		// 		for(j=0; j<=TempValues.length-1; j++){
		// 			if(TempValues[j]){
		// 				var TempValue=TempValues[j].split('|');
						
		// 				if(TempArrayValues[i] ==  TempValue[1]){
		// 					TempFiltersContent+='<option selected value="'+TempValue[1]+'">'+TempValue[0]+'</option>';
		// 				}else{
		// 					TempFiltersContent+='<option value="'+TempValue[1]+'">'+TempValue[0]+'</option>';
		// 				}
						
		// 			}
		// 		}
		// 		TempFiltersContent+='</select>';
		// 		TempFiltersContent+='</div>';
		// 		$(TempFiltersContent).appendTo($("#table_rubricator"));
		// 	}
		// 	//Дополнительные фильтры
		// }else{
		// 	$("div[id^='filter_']").remove();
		// }
		
		// var TDate=$("#JCMSReportPeriodDate_from").val();
		// var ADate=TDate.split('-');
		// var TempDateFrom = new Date(ADate[0],parseInt(parseInt(ADate[1])-1),ADate[2]);
			
		// var TDate=$("#JCMSReportPeriodDate_to").val();
		// var ADate=TDate.split('-');
		// var TempDateTo = new Date(ADate[0],parseInt(parseInt(ADate[1])-1),ADate[2]);
	
		// if($("#JCMSReportPeriodDate_from").val() || $("#JCMSReportPeriodDate_to").val()){
			TempContent+='<table>';
			TempContent+='<tr>';
	
			for(let i=0; i<=this.ArrayFields.length-1; i++){
				if(typeof this.ArrayFields[i].width == "undefined"){
					TempContent+='<th>';
				}else{
					TempContent+='<th style=\"width: '+this.ArrayFields[i].width+'%;\">';
				}
				TempContent+=''+this.ArrayFields[i].title+'';
					
				if(this.ArrayFields[i].percentage == 1 || this.ArrayFields[i].filter == 1 || this.ArrayFields[i].chart == 1){
					TempContent+='<br /><nobr>';
				}
							
				if(this.ArrayFields[i].filter == 1){
					if(typeof this.hashFilterValue[this.ArrayFields[i].name] == "undefined"){
						this.hashFilterValue[this.ArrayFields[i].name]=0;
					}
								
					if(this.hashFilter[this.ArrayFields[i].name] == 1){
						TempContent+='<input id="filter_'+this.options.field_table+'_'+this.ArrayFields[i].name+'" value="'+this.hashFilterValue[this.ArrayFields[i].name]+'" type="number" size="5" />';
						TempContent+='<div class="filter select '+this.ArrayFields[i].name+' '+this.options.field_table+'ControlTable" title="Фильтр"></div>';
					}else{
						TempContent+='<input id="filter_'+this.options.field_table+'_'+this.ArrayFields[i].name+'" value="'+this.hashFilterValue[this.ArrayFields[i].name]+'" type="number" size="5" />';
						TempContent+='<div class="filter '+this.ArrayFields[i].name+' '+this.options.field_table+'ControlTable" title="Фильтр"></div>';
					}
				}
								
				if(this.ArrayFields[i].percentage == 1){
					if(this.hashPercentage[this.ArrayFields[i].name] == 1){
						TempContent+='<div class="percentage select '+this.ArrayFields[i].name+' '+this.options.field_table+'ControlTable" title="Абсолютные значения / Проценты"></div>';
					}else{
						TempContent+='<div class="percentage '+this.ArrayFields[i].name+' '+this.options.field_table+'ControlTable" title="Абсолютные значения / Проценты"></div>';
					}
				}
										
				if(this.ArrayFields[i].chart == 1 && this.options.chart){
					if(this.hashChart[this.ArrayFields[i].name] == 1){
						this.varChartType=this.ArrayChartTypes[0];
						flagChart=true;
						TempContent+='<div class="chart select '+this.ArrayFields[i].name+' '+this.options.field_table+'ControlTable" title="График"></div>';
					}else{
						TempContent+='<div class="chart '+this.ArrayFields[i].name+' '+this.options.field_table+'ControlTable" title="График"></div>';
					}
				}
								
				if(this.ArrayFields[i].percentage == 1 || this.ArrayFields[i].filter == 1 || this.ArrayFields[i].chart == 1){
					TempContent+='</nobr>';
				}
								
				TempContent+='</th>';
			}
			TempContent+='</tr>';

			let flagTotal = false;
			let flagFilter = false;
			this.hashTotal=[];
	
			for(let j=0; j<=this.ArrayData.length-1; j++){
				this.hashShow['rec_'+j]=1;
	
				for(let i=0; i<=this.ArrayFields.length-1; i++){
					let val_=eval('this.ArrayData['+j+'].'+this.ArrayFields[i].name+';');
					
					if(
						(!this.ArrayFields[i].filter) || 
						(
							(this.ArrayFields[i].filter == 1) && 
							(
								!this.hashFilter[this.ArrayFields[i].name] || 
								this.hashFilter[this.ArrayFields[i].name] == 0 || 
								(
									this.hashFilter[this.ArrayFields[i].name] == 1 && 
									parseInt(val_) >= parseInt(this.hashFilterValue[this.ArrayFields[i].name])
								)
							)
						)
					)
					{			
										
					}else{
						this.hashShow['rec_'+j]=0;
					}
					
					if(this.ArrayFields[i].filter == 1){
						flagFilter=true;
					}
						
					if(this.ArrayFields[i].total == 1){
						flagTotal=true;
					}
				}
	
				if(this.hashShow['rec_'+j] == 1){
					for(let i=0; i<=this.ArrayFields.length-1; i++){
						let valt_=eval('this.ArrayData['+j+'].'+this.ArrayFields[i].totalnum+';');
						let val_=eval('this.ArrayData['+j+'].'+this.ArrayFields[i].name+';');
	
						if(this.hashTotal[this.ArrayFields[i].name]){
							if(this.ArrayFields[i].totalnum){
								if(parseFloat(valt_).toFixed(5) > 0){
									this.hashTotal[this.ArrayFields[i].name]=parseFloat(this.hashTotal[this.ArrayFields[i].name]).toFixed(2)*1+parseFloat(val_).toFixed(2)*1*parseFloat(valt_).toFixed(5);
								}
							}else{
								this.hashTotal[this.ArrayFields[i].name]=parseFloat(this.hashTotal[this.ArrayFields[i].name]).toFixed(2)*1+parseFloat(val_).toFixed(2)*1;
							}
						}else{
							if(this.ArrayFields[i].totalnum){
								if(parseFloat(valt_).toFixed(5) > 0){
									this.hashTotal[this.ArrayFields[i].name]=parseFloat(val_).toFixed(2)*1*parseFloat(valt_).toFixed(5);
								}
							}else{
								this.hashTotal[this.ArrayFields[i].name]=parseFloat(val_).toFixed(2)*1;
							}
						}
					}
				}
			}
			
			let tempChartTitleName;
			for(let i=0; i<=this.ArrayFields.length-1; i++){
				if(this.ArrayFields[i].chart_title == 1){
					tempChartTitleName=this.ArrayFields[i].name;
				}
			}
	
			//Отображение записей
			for(let j=0; j<=this.ArrayData.length-1; j++){
				if(this.hashShow['rec_'+j] == 1){
					// if($(this).find('rec_categorytitle').text()){
					// 	TempContent+='<tr class="title">';				
					// 	TempContent+='<td colspan="'+parseInt(ArrayFields.length)+'">'+$(this).find('rec_categorytitle').text()+'</td>';
					// 	TempContent+='</tr>';
					// }
					//
					TempContent+='<tr>';
											
					for(let i=0; i<=this.ArrayFields.length-1; i++){
						let TempPercentage;
						let val_=eval('this.ArrayData['+j+'].'+this.ArrayFields[i].name+';');
						let valm_=eval('this.ArrayData['+j+'].'+tempChartTitleName+';');
						
						if(this.ArrayFields[i].percentage == 1 && parseFloat(val_) > 0){
							TempPercentage=parseFloat(val_/(parseInt(this.hashTotal[this.ArrayFields[i].name])/100)).toFixed(2);
						}else{
							TempPercentage='0.00';
						}
						
						if(
							this.ArrayFields[i].fixed == 1 && 
							(
								(this.ArrayFields[i].percentage == 1 && !this.hashPercentage[this.ArrayFields[i].name]) ||
								!this.ArrayFields[i].percentage
							)
						){
							TempContent+='<td>'+parseFloat(val_).toFixed(2)+'</td>';
						}else if(
							this.ArrayFields[i].percentage == 1 && this.hashPercentage[this.ArrayFields[i].name] == 1){
			
							TempContent+='<td>'+TempPercentage+'%</td>';
						}else{
							if(this.ArrayFields[i].dayofweek == 1 && (TempSpecification == 'day' || TempSpecification == '')){
								let TDate=val_;
								let ADate=TDate.split('-');
								let TempDate = new Date(ADate[0],parseInt(parseInt(ADate[1])-1),ADate[2]);
								
								if(TempDate.getDay() == 0 || TempDate.getDay() == 6){
									TempContent+='<td><font color="#e66b21">'+val_+'</font></td>';
								}else{
									TempContent+='<td>'+val_+'</td>';
								}
							}else if(this.ArrayFields[i].parameters == 1){
									TempContent+='<td>';
									var l=0;
									$(Recs).find(this).find('parameter').each(function(){
										if(l>0){TempContent+='; ';}
										var m=0;
										TempContent+='<strong>'+$(this).find('parameter_title').text()+'</strong>: ';
										$(Recs).find(this).find('parameter_values').find('parameter_value').each(function(){
											if(m>0){TempContent+=', ';}
											TempContent+=''+$(this).find('title').text()+'';
											m++;
										});
										
										l++;
									});
									TempContent+='</td>';
								}else{
									TempContent+='<td>';
									var TempStr=val_;
									if(TempStr == 'Прямой заход'){
										TempContent+='<strong>Прямой заход</strong>';
									}else{
										if(this.ArrayFields[i].url == 1){TempContent+='<a href="'+val_+'">';}
										
										if(TempStr.length > 176){
											TempStr = TempStr.slice(0, 176);
											TempContent+=''+TempStr.trim()+'...';
										}else{
											TempContent+=''+val_+'';
										}
										if(this.ArrayFields[i].url == 1){TempContent+='</a>';}
										// if($(this).find('rec_'+ArrayFields[i].name+'_comment').text()){
										// 	TempContent+=' <span class="small_font">('+$(this).find('rec_'+ArrayFields[i].name+'_comment').text()+')</font>';
										// }
									}
									TempContent+='</td>';
								}
							}
					}
					TempContent+='</tr>';
					//
				}
			}
			//Отображение записей
			
			if(flagChart){
				//ShowChart();
			}
	
			if(flagTotal){
				TempContent+='<tr class=\"total\">';
				TempContent+='<td>Итого</td>';
				
				for(let i=0; i<=this.ArrayFields.length-1; i++){
					if(this.ArrayFields[i].total == 1){
						if(this.ArrayFields[i].fixed == 1){
							TempContent+='<td>'+parseFloat(this.hashTotal[this.ArrayFields[i].name]).toFixed(2)+'</td>';
						}else{
							TempContent+='<td>';
							if(this.hashTotal[this.ArrayFields[i].name] != undefined){
								TempContent+=''+this.hashTotal[this.ArrayFields[i].name]+'';
							}
							TempContent+='</td>';
						}
					}else if(this.ArrayFields[i].total == 0){
						
					}else{
						TempContent+='<td></td>';
					}
				}
				TempContent+='</tr>';
			}
	
			TempContent+='</table>';
	
			document.getElementById(this.options.field_table).innerHTML=TempContent;
		// }else{
		// 	TempContent+='<span class="help_string">Введите параметры поиска.</span>';
		// 	document.getElementById(this.options.field_table).innerHTML=TempContent;
		// }

		//создаем listeners
		let inputs = document.querySelectorAll('.'+this.options.field_table+'ControlTable');
		for(let i=0; i<inputs.length; i++){
			let str=inputs[i].className;
			let str_=str.split(' ');
			let val;
			let name;
			if(str_[1] == 'select'){
				val=0;
				name=str_[2];
			}else{
				val=1;
				name=str_[1];
			}

			inputs[i].addEventListener("click", () => { this.#ControlTable(str_[0],name,val); }, false);
		}
		//создаем listeners
	}

	ShowChart(TempAction){
		//Изменение размеров окна
		if(TempAction == 'resize'){
			if (typeof this.options.width === 'undefined') {
				//this.varChartWidth=$("#"+this.options.field_chart).width();
				let computedStyle;
				let elementChartWidth = document.getElementById(this.options.field_chart).clientWidth;
				if(getComputedStyle){
					computedStyle = getComputedStyle(document.getElementById(this.options.field_chart));	
					elementChartWidth -= parseFloat(computedStyle.paddingLeft) + parseFloat(computedStyle.paddingRight);
				}
				//this.varChartWidth=$("#"+this.options.field_chart).width();//jQuery
				this.varChartWidth=elementChartWidth;
			}
			if (typeof this.options.height === 'undefined') {
				let elementChartHeight = document.getElementById(this.options.field_chart).clientHeight;
				if(getComputedStyle){
					computedStyle = getComputedStyle(document.getElementById(this.options.field_chart));	
					elementChartHeight -= parseFloat(computedStyle.paddingTop) + parseFloat(computedStyle.paddingBottom);
				}
				//this.varChartHeight=$("#"+this.options.field_chart).height();
				this.varChartHeight=elementChartHeight;
			}
		}
		//Изменение размеров окна

		if(this.varChartHeight == 0){//Высота могла не успеть отрисоваться
			let elementChartHeight = document.getElementById(this.options.field_chart).clientHeight;
			if(getComputedStyle){
				computedStyle = getComputedStyle(document.getElementById(this.options.field_chart));	
				elementChartHeight -= parseFloat(computedStyle.paddingTop) + parseFloat(computedStyle.paddingBottom);
			}
			//this.varChartHeight=$("#"+this.options.field_chart).height();
			this.varChartHeight=elementChartHeight;
			this.varChartHeight=$("#"+this.options.field_chart).height();
		}

		//Значения по умолчанию
		let varChartGridLineColor='#e5e5e5';
		let varChartGridTextColor='#000';
		let varChartGridLineWidth='1';
		let varChartLegend=false;
		let varChartItemMaxSteps=10;//Максимальное кол-во шагов
		let varChartMaxPieItems=60;
		//Значения по умолчанию

		if (typeof this.options.grid_line_color !== 'undefined') {varChartGridLineColor=this.options.grid_line_color;}
		if (typeof this.options.grid_text_color !== 'undefined') {varChartGridTextColor=this.options.grid_text_color;}
		if (typeof this.options.grid_line_width !== 'undefined') {varChartGridLineWidth=this.options.grid_line_width;}
		if (typeof this.options.grid_max_steps !== 'undefined') {varChartItemMaxSteps=this.options.grid_max_steps;}
		if (typeof this.options.chart_legend !== 'undefined') {varChartLegend=this.options.chart_legend;}
		if (typeof this.options.chart_max_pie_items !== 'undefined') {varChartMaxPieItems=this.options.chart_max_pie_items;}

		let tempChartTitleName;
		for(let i=0; i<=this.ArrayFields.length-1; i++){
			if(this.ArrayFields[i].chart_title == 1){
				tempChartTitleName=this.ArrayFields[i].name;
			}
		}

		//Количество полей
		let varChartNumFields=0;
		for(let j=0; j<=this.ArrayFields.length-1; j++){
			if(this.ArrayFields[j].chart == 1 && this.hashChart[this.ArrayFields[j].name] == 1){
				varChartNumFields++;
			}
		}
		//Количество полей

		//данные графиков
		let ArrayChart=[];
		if (typeof this.ArrayData !== 'undefined') {
			if(varChartNumFields > 1 && (this.varChartType == 'pie' || this.varChartType == 'donut')){//для круговой/кольцевой другие данные
				for(let i=0; i<=this.ArrayFields.length-1; i++){
					let total_val=0;
					for(let j=0; j<=this.ArrayData.length-1; j++){
						if((this.hashShow['rec_'+j] == 1 && this.options.table == 1) || this.options.table == 0){
							let val_=eval('this.ArrayData['+j+'].'+this.ArrayFields[i].name+';');					
							if(this.hashChart[this.ArrayFields[i].name]){
								total_val=total_val+parseFloat(val_);
							}
						}	
					}
					if(this.hashChart[this.ArrayFields[i].name]){
						ArrayChart.push({title: this.ArrayFields[i].title, value: total_val, chart: this.ArrayFields[i].name});
					}
				}
			}else{
				for(let j=0; j<=this.ArrayData.length-1; j++){
					if((this.hashShow['rec_'+j] == 1 && this.options.table == 1) || this.options.table == 0){
						for(let i=0; i<=this.ArrayFields.length-1; i++){
							let val_=eval('this.ArrayData['+j+'].'+this.ArrayFields[i].name+';');
							let valm_=eval('this.ArrayData['+j+'].'+tempChartTitleName+';');
							
							if(this.hashChart[this.ArrayFields[i].name]){
								ArrayChart.push({title: valm_, value: val_, chart: this.ArrayFields[i].name});
								this.hashChart[this.ArrayFields[i].name]=1;
							}
						}
					}
				}
			}
		}
		//данные графиков
						
		if(this.ArrayTypes[this.CurrentReportType].reverse == "1"){//Реверс
			ArrayChart.reverse();
		}

		let varChartLegendWidth=0;
		let varChartLegendMaxWidth=0;
		let varChartItemsValueNum=0;
		let TempChartTitle='';
		for(let i=0; i<=this.ArrayFields.length-1; i++){
			if(this.hashChart[this.ArrayFields[i].name]){
				if(TempChartTitle){TempChartTitle+=', ';}
				TempChartTitle+=this.ArrayFields[i].title;
				let t=this.#getTextWidth(this.ArrayFields[i].title.toString());
				if(varChartLegendMaxWidth < t){
					varChartLegendMaxWidth=t;
				}
				varChartLegendWidth=varChartLegendWidth+parseFloat(t)+30;//размеры квадрата
			}
	
			if(this.ArrayFields[i].chart == 1 && this.hashChart[this.ArrayFields[i].name] == 1){
				varChartItemsValueNum++;
			}
		}
		if(this.varChartTitle){
			TempChartTitle=this.varChartTitle;
		}
		varChartLegendMaxWidth=varChartLegendMaxWidth+50;//Добавляем ширину квадрата с отступом к максимальному размеру текста легенды
		
		if(varChartItemsValueNum > 0){
			let TempContent='<h3>'+TempChartTitle+'</h3>';
			
			if (typeof this.options.chart_expand_collapse !== 'undefined') {
				TempContent+='<div ';
				if(this.varChartExpandCollapse){
					TempContent+='class="collapse"';
				}else{
					TempContent+='class="expand"';
				}
				TempContent+=' id="'+ this.options.field_chart + 'ControlSizeChart"></div>';
			}

			if(this.ArrayChartTypes.length > 1){
				for(let j=0; j<=this.ArrayChartTypes.length-1; j++){
					if(this.ArrayChartTypes[j]){
						let TempChartTypeTitle = '';
						if(this.ArrayChartTypes[j] == 'histogram'){TempChartTypeTitle='Гистограмма';}
						if(this.ArrayChartTypes[j] == 'histogram-accumulation'){TempChartTypeTitle='Гистограмма с накоплением';}
						if(this.ArrayChartTypes[j] == 'histogram-normalized'){TempChartTypeTitle='Гистограмма номированная с накоплением';}
						if(this.ArrayChartTypes[j] == 'bar'){TempChartTypeTitle='Линейчатая';}
						if(this.ArrayChartTypes[j] == 'bar-accumulation'){TempChartTypeTitle='Линейчатая с накоплением';}
						if(this.ArrayChartTypes[j] == 'bar-normalized'){TempChartTypeTitle='Линейчатая номированная с накоплением';}
						if(this.ArrayChartTypes[j] == 'line'){TempChartTypeTitle='Линии';}
						if(this.ArrayChartTypes[j] == 'bezier'){TempChartTypeTitle='Кривые Безье';}
						if(this.ArrayChartTypes[j] == 'line-fill'){TempChartTypeTitle='Линии с заливкой';}
						if(this.ArrayChartTypes[j] == 'bezier-fill'){TempChartTypeTitle='Кривые Безье с заливкой';}
						if(this.ArrayChartTypes[j] == 'pie'){TempChartTypeTitle='Круговая';}
						if(this.ArrayChartTypes[j] == 'donut'){TempChartTypeTitle='Колцевая';}

						if(this.varChartType == this.ArrayChartTypes[j]){
							TempContent+='<div class="'+this.ArrayChartTypes[j]+' select '+this.options.field_chart+'ControlChart" title="'+TempChartTypeTitle+'"></div>';
						}else{
							TempContent+='<div class="'+this.ArrayChartTypes[j]+' '+this.options.field_chart+'ControlChart" title="'+TempChartTypeTitle+'"></div>';
						}
					}
				}
			}
						
			//ПАРАМЕТРЫ
			let varChartItemMinValue;

			let varChartItemFontHeight=8;//Высота шрифта
			let varChartTopPadding=varChartItemFontHeight;//Отступ сверху
			let varChartBottomPadding=20;//Отступ снизу
			let varChartLeftPadding=100;//Отступ слева
			let varChartRightPadding=0;//Отступ справа
			let varChartAnimation=true;//Отступ справа

			if (typeof this.options.chart_top_padding !== 'undefined') {varChartTopPadding=this.options.chart_top_padding;}
			if (typeof this.options.chart_bottom_padding !== 'undefined') {varChartBottomPadding=this.options.chart_bottom_padding;}
			if (typeof this.options.chart_left_padding !== 'undefined') {varChartLeftPadding=this.options.chart_left_padding;}
			if (typeof this.options.chart_right_padding !== 'undefined') {varChartRightPadding=this.options.chart_right_padding;}
			if (typeof this.options.chart_animation !== 'undefined') {varChartAnimation=this.options.chart_animation;}
								
			//минимальные ширина и высота
			let varChartItemMinWidth=3;
			let varChartItemMinHeight=3;
			if (typeof this.options.chart_item_min_width !== 'undefined') {varChartItemMinWidth=this.options.chart_item_min_width;}
			if (typeof this.options.chart_item_min_height !== 'undefined') {varChartItemMinHeight=this.options.chart_item_min_height;}

			let varChartItemStrokeWidth=1;
			let varChartItemStrokeColor='#fff';
			let varChartItemStrokeRadius=0;
			if (typeof this.options.chart_item_stroke_width !== 'undefined') {varChartItemStrokeWidth=this.options.chart_item_stroke_width;}
			if (typeof this.options.chart_item_stroke_color !== 'undefined') {varChartItemStrokeColor=this.options.chart_item_stroke_color;}
			if (typeof this.options.chart_item_stroke_radius !== 'undefined') {varChartItemStrokeRadius=this.options.chart_item_stroke_radius;}

			let varChartItemMaxWidth;
			let varChartItemMaxHeight;
			let varChartItemMaxValue;
			let varChartItemAllValue=0;
			let varChartItemCoefValueH=0;
			let varChartItemCoefValueW=0;
			//ПАРАМЕТРЫ
			
			//ПАРАМЕТРЫ ЛЕГЕНДЫ
			let varChartLegendStrokeColor='#fff';
			let varChartLegendStrokeRadius=0;
			let varChartLegendStrokeWidth=1;
			let varChartLegendRectWidth=20;
			let varChartLegendRectHeight=20;

			if (typeof this.options.chart_legend_rect_width !== 'undefined') {varChartLegendRectWidth=this.options.chart_legend_rect_width;}
			if (typeof this.options.chart_legend_rect_height !== 'undefined') {varChartLegendRectHeight=this.options.chart_legend_rect_height;}
			if (typeof this.options.chart_legend_stroke_color !== 'undefined') {varChartLegendStrokeColor=this.options.chart_legend_stroke_color;}
			if (typeof this.options.chart_legend_stroke_radius !== 'undefined') {varChartLegendStrokeRadius=this.options.chart_legend_stroke_radius;}
			if (typeof this.options.chart_legend_stroke_width !== 'undefined') {varChartLegendStrokeWidth=this.options.chart_legend_stroke_width;}

			if(varChartLegend){
				//добавляем отступ под легенду
				varChartBottomPadding=varChartBottomPadding+30*Math.ceil(varChartLegendWidth/this.varChartWidth);
				//добавляем отступ под легенду
			}
			//ПАРАМЕТРЫ ЛЕГЕНДЫ
			
			//
			TempContent+='<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="'+this.varChartWidth+'" height="'+this.varChartHeight+'" shape-rendering="auto">';
			//timelineBegin="onStart" x="0px" y="0px" xml:space="preserve" viewBox="0 0 '+this.varChartWidth+' '+this.varChartHeight+'"
			
			//ПАЛИТРЫ
			TempContent+='<defs>';
			//пользовательские палитры
			for(let j=0; j<=this.ArrayFields.length-1; j++){
				if(this.ArrayFields[j].color && this.ArrayFields[j].pattern){
					if(this.ArrayFields[j].pattern == 'grid'){
						TempContent+='<pattern id="pattern-grid-'+this.ArrayFields[j].name+'" patternUnits="userSpaceOnUse" width="4" height="4">';
						TempContent+='<rect x="0" y="0" width="1" height="1" fill="'+this.ArrayFields[j].color+'" rx="0" ry="0"></rect>';
						TempContent+='<rect x="0" y="2" width="1" height="1" fill="'+this.ArrayFields[j].color+'" rx="0" ry="0"></rect>';
						TempContent+='<rect x="1" y="1" width="1" height="1" fill="'+this.ArrayFields[j].color+'" rx="0" ry="0"></rect>';
						TempContent+='<rect x="1" y="3" width="1" height="1" fill="'+this.ArrayFields[j].color+'" rx="0" ry="0"></rect>';
						TempContent+='<rect x="2" y="0" width="1" height="1" fill="'+this.ArrayFields[j].color+'" rx="0" ry="0"></rect>';
						TempContent+='<rect x="2" y="2" width="1" height="1" fill="'+this.ArrayFields[j].color+'" rx="0" ry="0"></rect>';
						TempContent+='<rect x="3" y="1" width="1" height="1" fill="'+this.ArrayFields[j].color+'" rx="0" ry="0"></rect>';
						TempContent+='<rect x="3" y="3" width="1" height="1" fill="'+this.ArrayFields[j].color+'" rx="0" ry="0"></rect>';
						TempContent+='</pattern>';
					}else if(this.ArrayFields[j].pattern == 'stroke'){
						TempContent+='<pattern id="pattern-stroke-'+this.ArrayFields[j].name+'" patternUnits="userSpaceOnUse" width="4" height="4">';
						TempContent+='<path d="M-1,1 l2,-2 M0,4 l4,-4 M3,5 l2,-2" stroke="'+this.ArrayFields[j].color+'" stroke-width="2" fill="transparent"></path>';
						TempContent+='</pattern>';
					}else{
						TempContent+='<pattern id="pattern-full-'+this.ArrayFields[j].name+'" patternUnits="userSpaceOnUse" width="1" height="1"><rect x="0" y="0" width="1" height="1" fill="'+this.ArrayFields[j].color+'" rx="0" ry="0"></rect></pattern>';
					}
				}
			}
			//пользовательские палитры

			TempContent+='<pattern id="pattern-full-disabled" patternUnits="userSpaceOnUse" width="1" height="1"><rect x="0" y="0" width="1" height="1" fill="#E2E0D7" rx="0" ry="0"></rect></pattern>';

			for(let i=0; i<this.JxChartColors.length; i++){
				TempContent+='<pattern id="pattern-full-'+i+'" patternUnits="userSpaceOnUse" width="1" height="1">';
				TempContent+='<rect x="0" y="0" width="1" height="1" fill="'+this.JxChartColors[i]+'" rx="0" ry="0"></rect>';
				TempContent+='</pattern>';
			}
			
			TempContent+='<pattern id="pattern-grid-disabled" patternUnits="userSpaceOnUse" width="4" height="4">';
			TempContent+='<rect x="0" y="0" width="1" height="1" fill="#E2E0D7" rx="0" ry="0"></rect>';
			TempContent+='<rect x="0" y="2" width="1" height="1" fill="#E2E0D7" rx="0" ry="0"></rect>';
			TempContent+='<rect x="1" y="1" width="1" height="1" fill="#E2E0D7" rx="0" ry="0"></rect>';
			TempContent+='<rect x="1" y="3" width="1" height="1" fill="#E2E0D7" rx="0" ry="0"></rect>';
			TempContent+='<rect x="2" y="0" width="1" height="1" fill="#E2E0D7" rx="0" ry="0"></rect>';
			TempContent+='<rect x="2" y="2" width="1" height="1" fill="#E2E0D7" rx="0" ry="0"></rect>';
			TempContent+='<rect x="3" y="1" width="1" height="1" fill="#E2E0D7" rx="0" ry="0"></rect>';
			TempContent+='<rect x="3" y="3" width="1" height="1" fill="#E2E0D7" rx="0" ry="0"></rect>';
			TempContent+='</pattern>';
			for(let i=0; i<this.JxChartColors.length; i++){
				TempContent+='<pattern id="pattern-grid-'+i+'" patternUnits="userSpaceOnUse" width="4" height="4">';
				TempContent+='<rect x="0" y="0" width="1" height="1" fill="'+this.JxChartColors[i]+'" rx="0" ry="0"></rect>';
				TempContent+='<rect x="0" y="2" width="1" height="1" fill="'+this.JxChartColors[i]+'" rx="0" ry="0"></rect>';
				TempContent+='<rect x="1" y="1" width="1" height="1" fill="'+this.JxChartColors[i]+'" rx="0" ry="0"></rect>';
				TempContent+='<rect x="1" y="3" width="1" height="1" fill="'+this.JxChartColors[i]+'" rx="0" ry="0"></rect>';
				TempContent+='<rect x="2" y="0" width="1" height="1" fill="'+this.JxChartColors[i]+'" rx="0" ry="0"></rect>';
				TempContent+='<rect x="2" y="2" width="1" height="1" fill="'+this.JxChartColors[i]+'" rx="0" ry="0"></rect>';
				TempContent+='<rect x="3" y="1" width="1" height="1" fill="'+this.JxChartColors[i]+'" rx="0" ry="0"></rect>';
				TempContent+='<rect x="3" y="3" width="1" height="1" fill="'+this.JxChartColors[i]+'" rx="0" ry="0"></rect>';
				TempContent+='</pattern>';
			}

			TempContent+='<pattern id="pattern-stroke-disabled" patternUnits="userSpaceOnUse" width="4" height="4"><path d="M-1,1 l2,-2 M0,4 l4,-4 M3,5 l2,-2" stroke="#E2E0D7" stroke-width="2" fill="transparent"></path></pattern>';
			for(let i=0; i<this.JxChartColors.length; i++){
				TempContent+='<pattern id="pattern-stroke-'+i+'" patternUnits="userSpaceOnUse" width="4" height="4">';
				TempContent+='<path d="M-1,1 l2,-2 M0,4 l4,-4 M3,5 l2,-2" stroke="'+this.JxChartColors[i]+'" stroke-width="2" fill="transparent"></path>';
				TempContent+='</pattern>';
			}
			TempContent+='</defs>';
			//ПАЛИТРЫ
			
			//
			let varChartItemTempStep;
			let varChartItemStep;
			let varChartItemYMinStep;
			let varChartItemYMaxStep;
			let varChartItemXMinStep;
			let varChartItemXMaxStep;
			let varChartAccumulationMaxValue=0;
			let varChartAccumulationMinValue=0;//
			//
			
			for(let i=0; i<=ArrayChart.length-1; i++){
				if(varChartItemMaxValue == undefined || varChartItemMaxValue < parseFloat(ArrayChart[i].value)){varChartItemMaxValue=parseFloat(ArrayChart[i].value);}
				if(varChartItemMinValue == undefined || parseFloat(ArrayChart[i].value) <= varChartItemMinValue){varChartItemMinValue=parseFloat(ArrayChart[i].value);}
				varChartItemAllValue=varChartItemAllValue+Math.abs(parseFloat(ArrayChart[i].value));
			}
						
			if((this.varChartType == 'histogram-accumulation' || this.varChartType == 'bar-accumulation') && varChartItemsValueNum != 1){
				//расчёт максимального значения при накоплении
				for(let j=0; j<=this.ArrayFields.length-1; j++){
					if(this.ArrayFields[j].chart == 1 && this.hashChart[this.ArrayFields[j].name] == 1){
						for(let i=0; i<=ArrayChart.length-1; i++){
							if(ArrayChart[i].chart == this.ArrayFields[j].name){
								let varAccumulation=0;
								for(let i_=0; i_<=ArrayChart.length-1; i_++){
									if(ArrayChart[i_].title == ArrayChart[i].title){
										varAccumulation=varAccumulation+parseFloat(ArrayChart[i_].value);
									}
								}
								if(varChartAccumulationMaxValue < varAccumulation){
									varChartAccumulationMaxValue=varAccumulation;
								}
								if(varChartAccumulationMinValue == undefined){
									varChartAccumulationMinValue=varAccumulation;
								}
								if(varChartAccumulationMinValue > varAccumulation){
									varChartAccumulationMinValue=varAccumulation;
								}
							}
						}
					}
				}
				//расчёт максимального значения при накоплении
				varChartItemTempStep=(varChartAccumulationMaxValue-varChartAccumulationMinValue)/varChartItemMaxSteps;
				varChartItemStep=Math.pow(10,Math.ceil(this.#mathLog10(varChartItemTempStep)));//больше 100.000 делает дофига шагов

				varChartItemXMinStep=0;
				varChartItemXMaxStep=varChartItemStep*Math.ceil(varChartAccumulationMaxValue/varChartItemStep);
				varChartItemYMinStep=0;
				varChartItemYMaxStep=varChartItemStep*Math.ceil(varChartAccumulationMaxValue/varChartItemStep);
			}else if(this.varChartType == 'histogram-normalized' || this.varChartType == 'bar-normalized'){
				varChartItemStep=10;
				varChartItemXMinStep=0;
				varChartItemXMaxStep=100;
				varChartItemYMinStep=0;
				varChartItemYMaxStep=100;
			}else{
				if(varChartItemMinValue < 0){
					varChartItemTempStep=(varChartItemMaxValue-Math.abs(varChartItemMinValue))/varChartItemMaxSteps;
				}else{
					varChartItemTempStep=(varChartItemMaxValue-varChartItemMinValue)/varChartItemMaxSteps;
				}
				// varChartItemTempStep=(varChartItemMaxValue-Math.abs(varChartItemMinValue))/varChartItemMaxSteps;

				varChartItemStep=Math.pow(10,Math.ceil(this.#mathLog10(varChartItemTempStep)));
				varChartItemXMinStep=0;
				varChartItemXMaxStep=varChartItemStep*Math.ceil(varChartItemMaxValue/varChartItemStep);
				varChartItemYMinStep=0;
				// varChartItemYMinStep=varChartItemStep*Math.floor(varChartItemMinValue/varChartItemStep);
				
				varChartItemYMaxStep=varChartItemStep*Math.ceil(varChartItemMaxValue/varChartItemStep);
			}

			let ChartTitlesArray= [];
			if(varChartItemsValueNum == 1){
				for(let i=0; i<=ArrayChart.length-1; i++){ChartTitlesArray.push(ArrayChart[i].title);}
			}else{
				ChartTitlesArray = [...new Set(ArrayChart.map(ArrayChart_ => ArrayChart_.title))];//группируем по заголовку
			}
			
			if(this.varChartType == 'bar' || this.varChartType == 'bar-overlay' || this.varChartType == 'bar-accumulation' || this.varChartType == 'bar-normalized'){
				let varChartMaxTitleWidth=0;
				for(let i=0; i<=ChartTitlesArray.length-1; i++){
					if(varChartMaxTitleWidth < this.#getTextWidth(ChartTitlesArray[i].toString())){
						varChartMaxTitleWidth=this.#getTextWidth(ChartTitlesArray[i].toString());
					}
				}
				varChartLeftPadding=parseInt(5)+varChartMaxTitleWidth;

				if(typeof this.options.grid_horizontal_text_max_width !== 'undefined'){
					varChartLeftPadding=parseInt(5)+(this.varChartWidth/100)*this.options.grid_horizontal_text_max_width;
				}
			}else{
				varChartLeftPadding=parseInt(15)+parseInt(this.#getTextWidth(varChartItemYMaxStep.toString()));
			}
			
			let varChartItemsValue=parseInt(ChartTitlesArray.length);
			varChartItemMaxWidth=parseFloat((this.varChartWidth-varChartLeftPadding)/varChartItemsValue);
			varChartItemMaxHeight=parseFloat((this.varChartHeight-varChartBottomPadding)/varChartItemsValue);
						
			if(this.varChartType == 'bar' || this.varChartType == 'bar-overlay' || this.varChartType == 'bar-accumulation' || this.varChartType == 'bar-normalized'){
				//добавляем половину ширины значения элемента
				varChartRightPadding=varChartRightPadding+parseFloat(this.#getTextWidth(varChartItemXMaxStep))/2;
			}
			
			if(varChartItemYMaxStep){
				varChartItemCoefValueH=(this.varChartHeight-varChartTopPadding-varChartBottomPadding)/varChartItemYMaxStep;//Коэфициент по высоте
			}else{
				varChartItemCoefValueH=0;
			}
			varChartItemCoefValueW=(this.varChartWidth-varChartLeftPadding-varChartRightPadding)/varChartItemXMaxStep;//Коэфициент по ширине

			let varChartItemsWidth=this.varChartWidth-varChartRightPadding-varChartLeftPadding;

			//ПРОВЕРКА НА ШИРИНУ
			let varChartItemsWidth_=0;
			for(let i=varChartItemXMinStep; i<=varChartItemXMaxStep; i=i+varChartItemStep){
				//ширина значения элемента
				varChartItemsWidth_=varChartItemsWidth_+parseFloat(this.#getTextWidth(i.toString()));
			}
						
			//
			if(varChartItemsWidth_ > varChartItemsWidth){
				let n=0;
				while (n < 10) {
					if(varChartItemsWidth_ > varChartItemsWidth){
						for(let j=0; j<=this.ArrayFields.length-1; j++){
							if(this.ArrayFields[j].chart == 1 && this.hashChart[this.ArrayFields[j].name] == 1){
								for(let i=0; i<=ArrayChart.length-1; i++){
									if(ArrayChart[i].chart == this.ArrayFields[j].name){
										let varAccumulation=0;
										for(let i_=0; i_<=ArrayChart.length-1; i_++){
											if(ArrayChart[i_].title == ArrayChart[i].title){
												varAccumulation=varAccumulation+parseFloat(ArrayChart[i_].value);
											}
										}
										if(varChartAccumulationMaxValue < varAccumulation){
											varChartAccumulationMaxValue=varAccumulation;
										}
										if(varChartAccumulationMinValue == undefined){
											varChartAccumulationMinValue=varAccumulation;
										}
										if(varChartAccumulationMinValue > varAccumulation){
											varChartAccumulationMinValue=varAccumulation;
										}
									}
								}
							}
						}
						//ширина легенды превышает суммарную ширину текста легенды
						varChartItemStep=varChartItemStep+parseInt(varChartItemStep*0.5);
						//не хватает пересчёта коэффициентов
						varChartItemXMaxStep=varChartItemStep*Math.ceil(varChartAccumulationMaxValue/varChartItemStep);
						//varChartRightPadding=varChartRightPadding+parseFloat(this.#getTextWidth(varChartItemXMaxStep))/2;
						varChartItemCoefValueW=(this.varChartWidth-varChartLeftPadding-varChartRightPadding)/varChartItemXMaxStep;//Коэфициент по ширине

						//пересчёт значений
						varChartItemsWidth_=0;
						varChartItemsWidth=this.varChartWidth-varChartRightPadding-varChartLeftPadding;
						for(let i=varChartItemXMinStep; i<=varChartItemXMaxStep; i=i+varChartItemStep){
							//ширина значения элемента
							varChartItemsWidth_=varChartItemsWidth_+parseFloat(this.#getTextWidth(i.toString()));
						}
					}
					n++;
				}
				//
			}
			//ПРОВЕРКА НА ШИРИНУ
			
			//СЕТКА
			if(this.varChartType != 'pie' && this.varChartType != 'donut' && this.varChartType != 'bar' && this.varChartType != 'bar-overlay' && this.varChartType != 'bar-accumulation' && this.varChartType != 'bar-normalized'){
				//ГОРИЗОНТАЛЬНАЯ СЕТКА
				TempContent+='<g>';
				if(isNaN(varChartItemYMaxStep)){
					let varChartItemPosX1=varChartLeftPadding;
					let varChartItemPosX2=this.varChartWidth-varChartRightPadding;
					let varChartItemPosY_1=varChartTopPadding;
					let varChartItemPosY_2=this.varChartHeight-varChartBottomPadding;

					TempContent+='<line x1="'+varChartItemPosX1+'" y1="'+varChartItemPosY_1+'" x2="'+varChartItemPosX2+'" y2="'+varChartItemPosY_1+'" stroke-width="'+varChartGridLineWidth+'" stroke="' + varChartGridLineColor + '" />';
					TempContent+='<line x1="'+varChartItemPosX1+'" y1="'+varChartItemPosY_2+'" x2="'+varChartItemPosX2+'" y2="'+varChartItemPosY_2+'" stroke-width="'+varChartGridLineWidth+'" stroke="' + varChartGridLineColor + '" />';
				}else{
					// console.log(varChartItemStep);
					// console.log(varChartItemYMinStep);
					// console.log(varChartItemYMaxStep);
					// console.log(varChartItemAllValue);

					for(let i=varChartItemYMinStep; i<=varChartItemYMaxStep; i=i+varChartItemStep){
						let varChartItemPosX1=varChartLeftPadding;
						let varChartItemPosX2=this.varChartWidth-varChartRightPadding;
						let varChartItemPosY1=varChartTopPadding+i*varChartItemCoefValueH;
						let varChartItemPosY2=varChartTopPadding+i*varChartItemCoefValueH;
						
						let varChartItemPosTitleX1=0;
						let varChartItemPosTitleY1=parseInt(varChartItemFontHeight/2)+varChartItemPosY1;
						
						if(varChartItemPosY1>=this.varChartHeight){varChartItemPosY1--;}
						if(varChartItemPosY2>=this.varChartHeight){varChartItemPosY2--;}
						
						TempContent+='<line x1="'+varChartItemPosX1+'" y1="'+varChartItemPosY1+'" x2="'+varChartItemPosX2+'" y2="'+varChartItemPosY2+'" stroke-width="'+varChartGridLineWidth+'" stroke="'+varChartGridLineColor+'" />';
						if(this.varChartType == 'histogram-normalized'){
							TempContent+='<text x="'+varChartItemPosTitleX1+'" y="'+varChartItemPosTitleY1+'" fill="'+varChartGridTextColor+'">'+(varChartItemYMaxStep-i)+'%</text>';
						}else{
							TempContent+='<text x="'+varChartItemPosTitleX1+'" y="'+varChartItemPosTitleY1+'" fill="'+varChartGridTextColor+'">'+(varChartItemYMaxStep-i)+'</text>';
						}
					}
				}
				TempContent+='</g>';
				//ГОРИЗОНТАЛЬНАЯ СЕТКА
			
				//ВЕРТИКАЛЬНАЯ СЕТКА
				TempContent+='<g>';
				let varChartItemTitleFlag=true;
				for(let i=0; i<=varChartItemsValue; i++){
					let varChartItemPosX1=varChartLeftPadding+varChartItemMaxWidth*i;
					let varChartItemPosX2=varChartLeftPadding+varChartItemMaxWidth*i;
					
					if(varChartItemPosX1>=(this.varChartWidth-varChartLeftPadding)){varChartItemPosX1--;}
					if(varChartItemPosX2>=(this.varChartWidth-varChartLeftPadding)){varChartItemPosX2--;}
					
					if(i!=varChartItemsValue){
						let varChartItemWidth=varChartItemMaxWidth-parseInt(this.#chartPadding(varChartItemMaxWidth)/2)*2;
						let varChartItemTitleWidth=this.#getTextWidth(ChartTitlesArray[i].toString());
						
						if(parseInt(varChartItemTitleWidth) >= parseInt(varChartItemWidth)){
							varChartItemTitleFlag=false;
						}
					}
				}

				for(let i=0; i<=varChartItemsValue; i++){
					let varChartItemPosX1=varChartLeftPadding+varChartItemMaxWidth*i;
					let varChartItemPosX2=varChartLeftPadding+varChartItemMaxWidth*i;
					let varChartItemPosY1=this.varChartHeight-varChartBottomPadding;
					let varChartItemPosY2=varChartTopPadding;
					
					if(varChartItemPosX1>=(this.varChartWidth-varChartLeftPadding)){varChartItemPosX1--;}
					if(varChartItemPosX2>=(this.varChartWidth-varChartLeftPadding)){varChartItemPosX2--;}
					
					TempContent+='<line x1="'+varChartItemPosX1+'" y1="'+varChartItemPosY1+'" x2="'+varChartItemPosX2+'" y2="'+varChartItemPosY2+'" stroke-width="'+varChartGridLineWidth+'" stroke="'+varChartGridLineColor+'" />';
					if(i!=varChartItemsValue){
						let varChartItemTitlePosY1=this.varChartHeight-varChartBottomPadding+varChartItemFontHeight*2;
						let varChartItemTitlePosX1;
						
						let varChartItemWidth=varChartItemMaxWidth-parseInt(this.#chartPadding(varChartItemMaxWidth)/2)*2;
						let varChartItemTitleWidth=this.#getTextWidth(ChartTitlesArray[i].toString());
						
						if(varChartItemTitleFlag){
							varChartItemTitlePosX1=varChartLeftPadding+varChartItemMaxWidth*i+this.#chartPadding(varChartItemMaxWidth)/2+(parseInt(varChartItemWidth)-parseInt(varChartItemTitleWidth))/2;
							TempContent+='<text x="'+varChartItemTitlePosX1+'" y="'+varChartItemTitlePosY1+'" fill="'+varChartGridTextColor+'">'+ChartTitlesArray[i]+'</text>';
						}
					}
				}
				TempContent+='</g>';
				//ВЕРТИКАЛЬНАЯ СЕТКА

				if(varChartLegend){
					//ЛЕГЕНДА
					let varChartLegendWidth_=0;
					let varChartLegendStep=0;
					let k=0;
					for(let i=0; i<=this.ArrayFields.length-1; i++){
						if(this.hashChart[this.ArrayFields[i].name]){
							let varChartLegendTextX;
							let varChartLegendTextY;
							let varChartLegendRectX;
							let varChartLegendRectY;
							
							if((k*20+(varChartLegendWidth_+parseFloat(this.#getTextWidth(this.ArrayFields[i].title.toString()))+15)) > this.varChartWidth){
								varChartLegendStep++;
								varChartLegendWidth_=0;
								k=0;

								varChartLegendTextY=parseInt(this.varChartHeight+varChartTopPadding-varChartBottomPadding+20+13)+varChartLegendStep*25;
								varChartLegendTextX=30;
								varChartLegendRectY=parseInt(this.varChartHeight+varChartTopPadding-varChartBottomPadding+20)+varChartLegendStep*25;
								varChartLegendRectX=0;
							}else{
								varChartLegendTextY=parseInt(this.varChartHeight+varChartTopPadding-varChartBottomPadding+20+13)+varChartLegendStep*25;
								varChartLegendTextX=parseInt(k*20+varChartLegendWidth_+30);
								varChartLegendRectY=parseInt(this.varChartHeight+varChartTopPadding-varChartBottomPadding+20)+varChartLegendStep*25;
								varChartLegendRectX=parseInt(k*20+varChartLegendWidth_);
							}

							if(varChartLegendRectWidth < 20){
								varChartLegendRectX=varChartLegendRectX + 20/2-varChartLegendRectWidth/2;
							}
							if(varChartLegendRectHeight < 20){
								varChartLegendRectY=varChartLegendRectY + 20/2-varChartLegendRectHeight/2;
							}
							

							let varChartItemPattern='url(#pattern-full-'+k+')';
							if(this.ArrayFields[i].color && this.ArrayFields[i].pattern){
								varChartItemPattern='url(#pattern-'+this.ArrayFields[i].pattern+'-'+this.ArrayFields[i].name+')';
							}
							if(this.hashChartDisabled[this.ArrayFields[i].name]){
								varChartItemPattern='url(#pattern-full-disabled)';
							}

							TempContent+='<rect x="'+varChartLegendRectX+'" y="'+varChartLegendRectY+'" width="'+varChartLegendRectWidth+'" height="'+varChartLegendRectHeight+'" stroke="'+varChartLegendStrokeColor+'" rx="'+varChartLegendStrokeRadius+'" ry="'+varChartLegendStrokeRadius+'" stroke-width="'+varChartLegendStrokeWidth+'" fill="'+varChartItemPattern+'"></rect>';
							
							TempContent+='<text ';
							if(!this.options.chart_legend_clickable_disabled){
								TempContent+='style="cursor: pointer;" ';
							}
							TempContent+='class="'+this.ArrayFields[i].name+' '+this.options.field_chart+'ControlLegend" x="'+varChartLegendTextX+'" y="'+varChartLegendTextY+'" ';
							if(this.hashChartDisabled[this.ArrayFields[i].name]){
								TempContent+='fill="'+varChartItemPattern+'';
							}else{
								TempContent+='fill="'+varChartGridTextColor+'';
							}
							TempContent+='">'+this.ArrayFields[i].title.toString()+'</text>';

							varChartLegendWidth_=(varChartLegendWidth_+parseFloat(this.#getTextWidth(this.ArrayFields[i].title.toString()))+15);
							
							k++;
						}
					}
					TempContent+='</g>';
					//ЛЕГЕНДА
				}
			}else if(this.varChartType == 'bar' || this.varChartType == 'bar-overlay' || this.varChartType == 'bar-accumulation' || this.varChartType == 'bar-normalized'){
				//ГОРИЗОНТАЛЬНАЯ СЕТКА
				TempContent+='<g>';
				if(varChartItemsValue > 0){
					for(let i=0; i<=varChartItemsValue; i++){
						let varChartItemPosY1=varChartTopPadding+varChartItemMaxHeight*i;
						let varChartItemPosY2=varChartTopPadding+varChartItemMaxHeight*i;
						
						let varChartItemPosX1=this.varChartWidth-varChartRightPadding;
						let varChartItemPosX2=varChartLeftPadding;
						
						if(varChartItemPosY1>=(this.varChartHeight-varChartTopPadding)){varChartItemPosY1=varChartItemPosY1-1;}
						if(varChartItemPosY2>=(this.varChartHeight-varChartTopPadding)){varChartItemPosY2=varChartItemPosY2-1;}
						
						//!!!
						TempContent+='<line x1="'+varChartItemPosX1+'" y1="'+varChartItemPosY1+'" x2="'+varChartItemPosX2+'" y2="'+varChartItemPosY2+'" stroke-width="'+varChartGridLineWidth+'" stroke="'+varChartGridLineColor+'" />';
						if(i != varChartItemsValue){
							let varChartItemTitlePosX1=0;
							let varChartItemTitlePosY1=varChartTopPadding+varChartItemMaxHeight*i+varChartItemMaxHeight/2+varChartItemFontHeight/2;
							
							if(typeof this.options.grid_horizontal_text_max_width !== 'undefined'){
								let varChartItemTitleWidth=this.#getTextWidth(ChartTitlesArray[i]);//ширина заголовка
								let varChartItemWidth=(this.varChartWidth/100)*this.options.grid_horizontal_text_max_width;
								
								if(parseInt(varChartItemTitleWidth) < parseInt(varChartItemWidth)){
									TempContent+='<text x="'+varChartItemTitlePosX1+'" y="'+varChartItemTitlePosY1+'" fill="'+varChartGridTextColor+'">'+ChartTitlesArray[i]+'</text>';
								}else{
									let varChartTitleText=ChartTitlesArray[i];
									let varChartTitleTextArray=varChartTitleText.split('').reverse();
									
									let varChartItemTitleWidth_=this.#getTextWidth(varChartTitleTextArray.join('').toString());
									let n=0;
									while (parseInt(varChartItemTitleWidth_) > parseInt(varChartItemWidth) && n < 1000) {
										varChartTitleTextArray.shift();
										let Temp=varChartTitleTextArray.join('').toString();
										varChartItemTitleWidth_=this.#getTextWidth(Temp);
										n++;
									}
									varChartTitleTextArray=varChartTitleTextArray.reverse();
									TempContent+='<text x="'+varChartItemTitlePosX1+'" y="'+varChartItemTitlePosY1+'" fill="'+varChartGridTextColor+'">'+varChartTitleTextArray.join('').toString()+'...<title>'+ChartTitlesArray[i]+'</title></text>';
								}
							}else{
								TempContent+='<text x="'+varChartItemTitlePosX1+'" y="'+varChartItemTitlePosY1+'" fill="'+varChartGridTextColor+'">'+ChartTitlesArray[i]+'</text>';
							}
						}

					}
				}
				TempContent+='</g>';
				//ГОРИЗОНТАЛЬНАЯ СЕТКА
												
				//ВЕРТИКАЛЬНАЯ СЕТКА
				TempContent+='<g>';
				for(let i=varChartItemXMinStep; i<=varChartItemXMaxStep; i=i+varChartItemStep){
					let varChartItemPosY1=varChartTopPadding;
					let varChartItemPosY2=this.varChartHeight+varChartTopPadding-varChartBottomPadding;//+(varChartItemsValue)
					let varChartItemPosX1=varChartLeftPadding+i*varChartItemCoefValueW;
					let varChartItemPosX2=varChartLeftPadding+i*varChartItemCoefValueW;
										
					let varChartItemPosTitleY1=this.varChartHeight-varChartBottomPadding+20;
					let varChartItemPosTitleX1=varChartItemPosX1-parseFloat(this.#getTextWidth(i.toString()))/2;
					
					if(varChartItemPosX1>=this.varChartWidth){varChartItemPosX1=varChartItemPosX1-1;}
					if(varChartItemPosX2>=this.varChartWidth){varChartItemPosX2=varChartItemPosX2-1;}
										
					TempContent+='<line x1="'+varChartItemPosX1+'" y1="'+varChartItemPosY1+'" x2="'+varChartItemPosX2+'" y2="'+varChartItemPosY2+'" stroke-width="'+varChartGridLineWidth+'" stroke="'+varChartGridLineColor+'" />';

					if(this.varChartType == 'bar-normalized'){
						varChartItemPosTitleX1=varChartItemPosTitleX1-parseFloat(this.#getTextWidth('%'))/2;
						if(i == varChartItemXMaxStep){varChartItemPosTitleX1=varChartItemPosTitleX1-parseFloat(this.#getTextWidth('%'))/2;}
						TempContent+='<text x="'+varChartItemPosTitleX1+'" y="'+varChartItemPosTitleY1+'" fill="'+varChartGridTextColor+'">'+parseInt(i)+'%</text>';
					}else{
						//сделать проверку на длину текста
						TempContent+='<text x="'+varChartItemPosTitleX1+'" y="'+varChartItemPosTitleY1+'" fill="'+varChartGridTextColor+'">'+parseInt(i)+'</text>';
					}
				}
				TempContent+='</g>';
				//ВЕРТИКАЛЬНАЯ СЕТКА

				//ЛЕГЕНДА
				TempContent+='<g>';
				let varChartLegendWidth_=0;
				let k=0;
				for(let i=0; i<=this.ArrayFields.length-1; i++){
					if(this.hashChart[this.ArrayFields[i].name]){
						let varChartItemPattern='url(#pattern-full-'+k+')';
						if(this.ArrayFields[i].color && this.ArrayFields[i].pattern){
							varChartItemPattern='url(#pattern-'+this.ArrayFields[i].pattern+'-'+this.ArrayFields[i].name+')';
						}
						if(this.hashChartDisabled[this.ArrayFields[i].name]){
							varChartItemPattern='url(#pattern-full-disabled)';
						}

						TempContent+='<rect x="'+parseInt(k*20+varChartLegendWidth_)+'" y="'+parseInt(this.varChartHeight+varChartTopPadding-varChartBottomPadding+20)+'" width="20" height="20" stroke="'+varChartLegendStrokeColor+'" rx="'+varChartLegendStrokeRadius+'" ry="'+varChartLegendStrokeRadius+'" stroke-width="'+varChartLegendStrokeWidth+'" fill="'+varChartItemPattern+'"></rect>';

						TempContent+='<text ';
						if(!this.options.chart_legend_clickable_disabled){
							TempContent+='style="cursor: pointer;" ';
						}
						TempContent+='class="'+this.ArrayFields[i].name+' '+this.options.field_chart+'ControlLegend" x="'+parseInt(k*20+varChartLegendWidth_+30)+'" y="'+parseInt(this.varChartHeight+varChartTopPadding-varChartBottomPadding+20+13)+'" ';
						if(this.hashChartDisabled[this.ArrayFields[i].name]){
							TempContent+='fill="'+varChartItemPattern+'';
						}else{
							TempContent+='fill="'+varChartGridTextColor+'';
						}
						TempContent+='">'+this.ArrayFields[i].title.toString()+'</text>';

						varChartLegendWidth_=varChartLegendWidth_+parseFloat(this.#getTextWidth(this.ArrayFields[i].title.toString()))+30;
						k++;
					}
				}
				TempContent+='</g>';
				//ЛЕГЕНДА
			}
			//СЕТКА

			///line-fill//bezier-fill
			TempContent+='<g>';
			if(this.varChartType == 'line-fill' || this.varChartType == 'bezier-fill'){
				//Линии//Кривые Безье

				let valnum=0;
				for(let j=0; j<=this.ArrayFields.length-1; j++){
					if(this.ArrayFields[j].chart == 1 && this.hashChart[this.ArrayFields[j].name] == 1){
						let pos=0;
						let varChartItemPosPrevX;
						let varChartItemPosPrevY;
	
						let numItemsInChart=0;
						for(let i=0; i<=ArrayChart.length-1; i++){
							if(ArrayChart[i].chart == this.ArrayFields[j].name){
								numItemsInChart++;
							}
						}

						//
						let varChartItemPadding=parseInt(this.#chartPadding(varChartItemMaxWidth)/2);
						let varChartItemWidth=varChartItemMaxWidth-varChartItemPadding*2;
						//
						
						for(let i=0; i<=ArrayChart.length-1; i++){
							if(ArrayChart[i].chart == this.ArrayFields[j].name){
								//ширина и x
								let varChartItemPosX;
								let varChartItemRectPosX;
								if(pos == 0){
									varChartItemPosX=varChartItemWidth/2+varChartItemWidth*pos+(pos+1)*(varChartItemPadding+varChartLeftPadding);
									varChartItemRectPosX=varChartItemWidth*pos+(pos+1)*(varChartItemPadding+varChartLeftPadding);
								}else{
									varChartItemPosX=varChartItemWidth/2+varChartLeftPadding+varChartItemWidth*pos+(pos+1)*(varChartItemPadding)*2-varChartItemPadding;
									varChartItemRectPosX=varChartLeftPadding+varChartItemWidth*pos+(pos+1)*(varChartItemPadding)*2-varChartItemPadding;
								}
								
								//высота и y
								let varChartItemHeight;
								varChartItemHeight=ArrayChart[i].value*varChartItemCoefValueH;
								if(ArrayChart[i].value == 0){
									varChartItemHeight=varChartItemMinHeight;
								}
								let varChartItemPosY=this.varChartHeight-varChartItemHeight-varChartBottomPadding;
								let varChartItemRectPosY=0;
	
								//
								let varChartItemPattern='url(#pattern-full-'+valnum+')';
								if(this.ArrayFields[j].color && this.ArrayFields[j].pattern){
									varChartItemPattern='url(#pattern-'+this.ArrayFields[j].pattern+'-'+this.ArrayFields[j].name+')';
								}
								if(this.hashChartDisabled[this.ArrayFields[j].name]){
									varChartItemPattern='url(#pattern-full-disabled)';
								}
								//
								
								let varChartItemAnimatePosY1=this.varChartHeight-varChartBottomPadding;

								if(pos>0){
									if(this.varChartType == 'bezier-fill'){
										let koef=varChartItemMaxWidth/2;
										TempContent+=`<path d="M ${varChartItemPosPrevX} ${varChartItemPosPrevY} C ${varChartItemPosPrevX + koef} ${varChartItemPosPrevY} ${varChartItemPosX - koef} ${varChartItemPosY} ${varChartItemPosX} ${varChartItemPosY}" fill="url(#pattern-${this.ArrayFields[j].pattern}-${this.ArrayFields[j].name})" stroke-width="0" opacity="0.5" stroke="${varChartItemPattern}">`;
										TempContent+='</path>';

										//TempContent+='<polygon points="'+varChartItemPosPrevX+','+varChartItemPosPrevY+' '+varChartItemPosPrevX+','+varChartItemAnimatePosY1+' '+varChartItemPosX+','+varChartItemAnimatePosY1+' '+varChartItemPosX+','+varChartItemPosY+'" stroke-width="0" opacity="0.5" fill="url(#pattern-'+this.ArrayFields[j].pattern+'-'+this.ArrayFields[j].name+')" />';
									}else{
										TempContent+='<polygon points="'+varChartItemPosPrevX+','+varChartItemPosPrevY+' '+varChartItemPosPrevX+','+varChartItemAnimatePosY1+' '+varChartItemPosX+','+varChartItemAnimatePosY1+' '+varChartItemPosX+','+varChartItemPosY+'" stroke-width="0" opacity="0.5" fill="url(#pattern-'+this.ArrayFields[j].pattern+'-'+this.ArrayFields[j].name+')" />';
									}

									if(pos == parseInt(numItemsInChart-1)){
										let varChartItemPosLLX=this.varChartWidth-varChartRightPadding-3;
										if(this.varChartType != 'bezier-fill'){
											TempContent+='<polygon points="'+varChartItemPosX+','+varChartItemPosY+' '+varChartItemPosLLX+','+varChartItemPosY+' '+varChartItemPosLLX+','+varChartItemAnimatePosY1+' '+varChartItemPosX+','+varChartItemAnimatePosY1+'" stroke-width="0" opacity="0.5" fill="url(#pattern-'+this.ArrayFields[j].pattern+'-'+this.ArrayFields[j].name+')" />';
										}
									}
								}else{
									let varChartItemPosFLX=varChartLeftPadding+3;
									if(this.varChartType != 'bezier-fill'){
										TempContent+='<polygon points="'+varChartItemPosFLX+','+varChartItemPosY+' '+varChartItemPosX+','+varChartItemPosY+' '+varChartItemPosX+','+varChartItemAnimatePosY1+' '+varChartItemPosFLX+','+varChartItemAnimatePosY1+'" stroke-width="0" opacity="0.5" fill="url(#pattern-'+this.ArrayFields[j].pattern+'-'+this.ArrayFields[j].name+')" />';
									}
								}
					
								varChartItemPosPrevX=varChartItemPosX;
								varChartItemPosPrevY=varChartItemPosY;
								pos++;
	
							}
						}
						valnum++;
					}
				}
				//Линии//Кривые Безье
			}
			TempContent+='</g>';
			///line-fill//bezier-fill
			
			TempContent+='<g>';
			if(this.varChartType == 'line-fill' || this.varChartType == 'bezier-fill' || this.varChartType == 'line' || this.varChartType == 'bezier'){
				//Линии//Кривые Безье

				let valnum=0;
				for(let j=0; j<=this.ArrayFields.length-1; j++){
					if(this.ArrayFields[j].chart == 1 && this.hashChart[this.ArrayFields[j].name] == 1){
						let pos=0;
						let varChartItemPosPrevX;
						let varChartItemPosPrevY;
	
						let numItemsInChart=0;
						for(let i=0; i<=ArrayChart.length-1; i++){
							if(ArrayChart[i].chart == this.ArrayFields[j].name){
								numItemsInChart++;
							}
						}

						//
						let varChartItemPadding=parseInt(this.#chartPadding(varChartItemMaxWidth)/2);
						let varChartItemWidth=varChartItemMaxWidth-varChartItemPadding*2;
						//
						
						for(let i=0; i<=ArrayChart.length-1; i++){
							if(ArrayChart[i].chart == this.ArrayFields[j].name){
								//ширина и x
								let varChartItemPosX;
								let varChartItemRectPosX;
								if(pos == 0){
									varChartItemPosX=varChartItemWidth/2+varChartItemWidth*pos+(pos+1)*(varChartItemPadding+varChartLeftPadding);
									varChartItemRectPosX=varChartItemWidth*pos+(pos+1)*(varChartItemPadding+varChartLeftPadding);
								}else{
									varChartItemPosX=varChartItemWidth/2+varChartLeftPadding+varChartItemWidth*pos+(pos+1)*(varChartItemPadding)*2-varChartItemPadding;
									varChartItemRectPosX=varChartLeftPadding+varChartItemWidth*pos+(pos+1)*(varChartItemPadding)*2-varChartItemPadding;
								}
								
								//высота и y
								let varChartItemHeight;
								varChartItemHeight=ArrayChart[i].value*varChartItemCoefValueH;
								if(ArrayChart[i].value == 0){
									varChartItemHeight=varChartItemMinHeight;
								}
								let varChartItemPosY=this.varChartHeight-varChartItemHeight-varChartBottomPadding;
								let varChartItemRectPosY=0;
	
								//
								let varChartItemPattern='url(#pattern-full-'+valnum+')';
								if(this.ArrayFields[j].color && this.ArrayFields[j].pattern){
									varChartItemPattern='url(#pattern-'+this.ArrayFields[j].pattern+'-'+this.ArrayFields[j].name+')';
								}
								if(this.hashChartDisabled[this.ArrayFields[j].name]){
									varChartItemPattern='url(#pattern-full-disabled)';
								}
								//
								
								let varChartItemAnimatePosY1=this.varChartHeight-varChartBottomPadding;

								if(pos>0){
									if(this.varChartType == 'bezier' || this.varChartType == 'bezier-fill'){
										let koef=varChartItemMaxWidth/2;
										TempContent+=`<path d="M ${varChartItemPosPrevX} ${varChartItemPosPrevY} C ${varChartItemPosPrevX + koef} ${varChartItemPosPrevY} ${varChartItemPosX - koef} ${varChartItemPosY} ${varChartItemPosX} ${varChartItemPosY}" fill="none" stroke-width="2" stroke="${varChartItemPattern}">`;

										if(TempAction == 'load'){
											TempContent+=`<animate attributeName="d" from="M ${varChartItemPosPrevX} ${varChartItemPosPrevY} C ${varChartItemPosPrevX} ${varChartItemPosPrevY} ${varChartItemPosX} ${varChartItemPosY} ${varChartItemPosX} ${varChartItemPosY}" to="M ${varChartItemPosPrevX} ${varChartItemPosPrevY} C ${varChartItemPosPrevX + koef} ${varChartItemPosPrevY} ${varChartItemPosX - koef} ${varChartItemPosY} ${varChartItemPosX} ${varChartItemPosY}" dur="0.3s" />`;
										}

										TempContent+='</path>';
									}else{
										TempContent+='<line x1="'+varChartItemPosPrevX+'" y1="'+varChartItemPosPrevY+'" x2="'+varChartItemPosX+'" y2="'+varChartItemPosY+'" stroke-width="2" stroke="'+varChartItemPattern+'">';
										
										if(TempAction == 'load'){
											
											TempContent+='<animate attributeName="y1" from="'+varChartItemAnimatePosY1+'" to="'+varChartItemPosPrevY+'" dur="0.3s" />';
											TempContent+='<animate attributeName="y2" from="'+varChartItemAnimatePosY1+'" to="'+varChartItemPosY+'" dur="0.3s" />';
										}
										TempContent+='</line>';
									}

									if(pos == parseInt(numItemsInChart-1)){
										let varChartItemPosLLX=this.varChartWidth-varChartRightPadding-3;
										let varChartItemPosLLY=varChartItemPosY;
										TempContent+='<line x1="'+varChartItemPosX+'" y1="'+varChartItemPosY+'" x2="'+varChartItemPosLLX+'" y2="'+varChartItemPosLLY+'" stroke-width="2" stroke-dasharray="2,2" stroke="'+varChartItemPattern+'" />';
									}
								}else{
									let varChartItemPosFLX=varChartLeftPadding+3;
									let varChartItemPosFLY=varChartItemPosY;
									TempContent+='<line x1="'+varChartItemPosFLX+'" y1="'+varChartItemPosFLY+'" x2="'+varChartItemPosX+'" y2="'+varChartItemPosY+'" stroke-width="2" stroke-dasharray="2,2" stroke="'+varChartItemPattern+'" />';
								}
								TempContent+='<circle id="circle_'+this.options.field_chart+'_'+valnum+'_'+pos+'" cx="'+varChartItemPosX+'" cy="'+varChartItemPosY+'" r="4" stroke="transparent" stroke-width="0" fill="transparent"></circle>';
	
								if(valnum == 0){
									let TempChartTitleItem='';
									for(let i_=0; i_<=this.ArrayFields.length-1; i_++){
										if(this.hashChart[this.ArrayFields[i_].name]){
											TempChartTitleItem+='<br />'+this.ArrayFields[i_].title+': ';
											if(varChartItemsValueNum > 1){
												for(let i__=0; i__<=ArrayChart.length-1; i__++){
													if(ArrayChart[i__].chart == this.ArrayFields[i_].name && ArrayChart[i__].title == ArrayChart[i].title){
														TempChartTitleItem+='<strong>'+ArrayChart[i__].value+'</strong>';
													}
												}
											}else{
												TempChartTitleItem+='<strong>'+ArrayChart[i].value+'</strong>';
											}
										}
									}
									TempContent+='<rect class="'+this.options.field_chart+'ItemTooltip" title="<strong>'+ArrayChart[i].title+'</strong>'+TempChartTitleItem+'" circlesover="';

									let m_=0;
									for(let m=0; m<=this.ArrayFields.length-1; m++){
										if(this.ArrayFields[m].chart == 1){
											let varChartPatternCircle='url(#pattern-full-'+m_+')';
											if(this.ArrayFields[m].color && this.ArrayFields[m].pattern){
											 	varChartPatternCircle='url(#pattern-'+this.ArrayFields[m].pattern+'-'+this.ArrayFields[m].name+')';
											}
											if(this.hashChartDisabled[this.ArrayFields[m].name]){
												varChartPatternCircle='url(#pattern-full-disabled)';
											}
											TempContent+='document.getElementById(\'circle_'+this.options.field_chart+'_'+m_+'_'+pos+'\').setAttribute(\'fill\', \''+varChartPatternCircle+'\');';
											
											m_++;
										}
									}
									TempContent+='"';
									TempContent+=' circlesout="';
									m_=0;
									for(let m=0; m<=this.ArrayFields.length-1; m++){
										if(this.ArrayFields[m].chart == 1){
											TempContent+='document.getElementById(\'circle_'+this.options.field_chart+'_'+m_+'_'+pos+'\').setAttribute(\'fill\', \'transparent\');';

											m_++;
										}
									}
									TempContent+='"';
									TempContent+=' x="'+varChartItemRectPosX+'" y="'+varChartItemRectPosY+'" width="'+varChartItemWidth+'" height="'+parseFloat(this.varChartHeight-varChartBottomPadding)+'" stroke="transparent" stroke-width="0" fill="transparent"></rect>';
									
								}
					
								varChartItemPosPrevX=varChartItemPosX;
								varChartItemPosPrevY=varChartItemPosY;
								pos++;
	
							}
						}
						valnum++;
					}
				}
				//Линии//Кривые Безье
			}else if(this.varChartType == 'pie' || this.varChartType == 'donut'){
				//КРУГОВАЯ - pie//КОЛЦЕВАЯ - donut
				if(ArrayChart.length > varChartMaxPieItems && (this.varChartType == 'pie' || this.varChartType == 'donut')){//слишком много значений
					ArrayChart.sort((a, b) => b.value - a.value);
					let varChartItemMaxPieValue=0;
					for(let i=0; i<=varChartMaxPieItems-1; i++){
						varChartItemMaxPieValue=varChartItemMaxPieValue+parseFloat(ArrayChart[i].value);
					}
					ArrayChart.splice(varChartMaxPieItems,ArrayChart.length);
					ArrayChart.push({title: 'Остальное', value: parseFloat(varChartItemAllValue)-parseFloat(varChartItemMaxPieValue), chart: ArrayChart[varChartMaxPieItems-1].chart});
				}else{
					ArrayChart.reverse();
				}
					
				let varPieRadius=0;
				//вычисляем размеры радиуса графика
				if(this.varChartWidth > this.varChartHeight){
					varPieRadius=parseInt(this.varChartHeight/2);
				}else{
					varPieRadius=parseInt(this.varChartWidth/2);
				}
				//отступ снизу для графиков pie и donut
				varPieRadius=varPieRadius-varChartBottomPadding;

				if(typeof this.options.chart_legend_text_max_width !== 'undefined'){
					varChartLegendMaxWidth=(this.varChartWidth/100)*this.options.chart_legend_text_max_width;
				}

				if(varPieRadius*2+varChartLegendMaxWidth > this.varChartWidth){
				 	varPieRadius=(varPieRadius*2-varChartLegendMaxWidth)/2;
				}
				//вычисляем размеры радиуса графика
				if(varChartItemAllValue == 0){
					TempContent+='<circle cx="'+varPieRadius+'" cy="'+varPieRadius+'" r="'+parseInt(varPieRadius)+'" fill="url(#pattern-full-disabled)" stroke-width="1" stroke="url(#pattern-full-disabled)" />';
				}

				let varPieItemCoef=parseFloat(360/varChartItemAllValue);
	
				this.#generateChartPatterns();
								
				let varPieItemStartArc=0;
				let varPieCenterMass=this.#mathCenterMass(varPieRadius);
				if(this.varChartType == 'donut'){
					varPieCenterMass=varPieCenterMass+this.#mathCenterMass((varPieRadius/4)*3);//центр сектора
				}
				
				let j=0;
				for(let i=0; i<=ArrayChart.length-1; i++){
					let varChartPieItemArc;
					
					if(ArrayChart[i].value == 0){varChartPieItemArc=0;}else{varChartPieItemArc=parseFloat(ArrayChart[i].value)*parseFloat(varPieItemCoef);}
					let varChartItemPattern=this.#getChartPattern();
					let TempChartTitleItem;

					for(let k=0; k<=this.ArrayFields.length-1; k++){
						if(ArrayChart[i].chart == this.ArrayFields[k].name){
							TempChartTitleItem=this.ArrayFields[k].title;
							if(this.ArrayFields[k].color && this.ArrayFields[k].pattern){
								varChartItemPattern='url(#pattern-'+this.ArrayFields[k].pattern+'-'+this.ArrayFields[k].name+')';
							}
							if(varChartItemsValueNum == 1){
								if(this.hashChartDisabled[this.ArrayFields[k].name+''+i]){
									varChartItemPattern='url(#pattern-full-disabled)';
								}
							}else{
								if(this.hashChartDisabled[this.ArrayFields[k].name]){
									varChartItemPattern='url(#pattern-full-disabled)';
								}
							}
						}
					}
					
					let varPercent;
					if(varChartItemAllValue > 0){varPercent=parseFloat((parseInt(ArrayChart[i].value)/(varChartItemAllValue/100))).toFixed(2);}else{varPercent=0;}

					if(varChartPieItemArc == 360){
						TempContent+='<circle cx="'+varPieRadius+'" cy="'+varPieRadius+'" r="'+parseInt(varPieRadius)+'" fill="'+varChartItemPattern+'" stroke-width="0" />';
					}else{
						TempContent+='<path title="<strong>'+ArrayChart[i].title+'</strong><br />'+TempChartTitleItem+': <strong>'+ArrayChart[i].value+' ('+varPercent+'%)</strong>" class="pie '+this.options.field_chart+'ItemTooltip" d="'+this.#mathDescribeArc(varPieRadius,varPieRadius,varPieRadius,varPieItemStartArc,varPieItemStartArc+varChartPieItemArc)+'" stroke="'+varChartItemPattern+'" stroke-width="0" fill="'+varChartItemPattern+'">';
						if(TempAction == 'load'){
							TempContent+='<animate attributeName="d" from="'+this.#mathDescribeArc(varPieRadius,varPieRadius,parseInt(varPieRadius/2),varPieItemStartArc,varPieItemStartArc+varChartPieItemArc)+'" to="'+this.#mathDescribeArc(varPieRadius,varPieRadius,varPieRadius,varPieItemStartArc,varPieItemStartArc+varChartPieItemArc)+'" dur="0.3s" />';
						}
						TempContent+='</path>';
						varPieItemStartArc=varPieItemStartArc+varChartPieItemArc;
					}
					
					let varPieItemTextWidth=this.#getTextWidth(parseInt(varPercent).toString())/2+this.#getTextWidth('%')/2;
					let varPieItemTextHeight=19/4;//только под этот шрифт

					if(typeof this.options.chart_legend_text_percents !== 'undefined'){
						let varPieItemText=this.#mathPolarToCartesian(varPieRadius, varPieRadius, varPieCenterMass, varPieItemStartArc-varChartPieItemArc+varChartPieItemArc/2);
						if(parseInt(varPercent) != 0 && varChartPieItemArc > 19){
							TempContent+='<text x="'+(varPieItemText.x-varPieItemTextWidth)+'" y="'+(varPieItemText.y+varPieItemTextHeight)+'" fill="#fff">'+parseInt(varPercent)+'%</text>';
						}
						//TempContent+='<circle cx="'+(varPieItemText.x)+'" cy="'+(varPieItemText.y)+'" r="2" fill="#fff" stroke-width="0" />';//тестовый кружочек//TODO: для больих масштабов увелечение шрифта
					}
	
					//ЛЕГЕНДА
					let varChartPieItemStep=0;
					let varChartPiePaddingTop=0;
					TempContent+='<rect x="'+parseInt(varPieRadius*2+15+100*varChartPieItemStep)+'" y="'+parseInt(varChartPiePaddingTop+30*j)+'" width="20" height="20" stroke="'+varChartLegendStrokeColor+'" rx="'+varChartLegendStrokeRadius+'" ry="'+varChartLegendStrokeRadius+'" stroke-width="'+varChartLegendStrokeWidth+'" fill="'+varChartItemPattern+'"></rect>';
					TempContent+='<text ';
					
					if(!this.options.chart_legend_clickable_disabled){
						TempContent+='style="cursor: pointer;" ';
					}
					
					TempContent+='class="'+ArrayChart[i].chart+'';

					if(varChartItemsValueNum == 1){TempContent+=''+i+'';}
					TempContent+=' '+this.options.field_chart+'ControlLegend" x="'+parseInt(varPieRadius*2+50+100*varChartPieItemStep)+'" y="'+parseInt(varChartPiePaddingTop+15+varChartPieItemStep+30*j)+'" fill="'+varChartGridTextColor+'">';
					
					if(typeof this.options.chart_legend_text_max_width !== 'undefined'){
						let varChartItemWidth=(this.varChartWidth/100)*this.options.chart_legend_text_max_width;
						let varChartItemTitleWidth=this.#getTextWidth(ArrayChart[i].title.toString());//ширина заголовка
						
						if(parseInt(varChartItemTitleWidth) < parseInt(varChartItemWidth)){
							TempContent+=ArrayChart[i].title.toString();
						}else{
							let varChartTitleText=ArrayChart[i].title.toString();
							let varChartTitleTextArray=varChartTitleText.split('').reverse();

							let varChartItemTitleWidth_=this.#getTextWidth(varChartTitleTextArray.join('').toString());
							let n=0;
							while (parseInt(varChartItemTitleWidth_) > parseInt(varChartItemWidth) && n < 1000) {
								varChartTitleTextArray.shift();
								let Temp=varChartTitleTextArray.join('').toString();
								varChartItemTitleWidth_=this.#getTextWidth(Temp);
								n++;
							}
							varChartTitleTextArray=varChartTitleTextArray.reverse();

							TempContent+=''+varChartTitleTextArray.join('').toString()+'...';
							TempContent+='<title>'+ArrayChart[i].title.toString()+'</title>';
						}
					}else{
						TempContent+=ArrayChart[i].title.toString();
					}

					TempContent+='</text>';
					
					if(i > 6){
						varChartPieItemStep++;
					}
					//ЛЕГЕНДА
	
					j++;
				}
				if(this.varChartType == 'donut'){
					TempContent+='<circle cx="'+varPieRadius+'" cy="'+varPieRadius+'" r="'+parseInt(varPieRadius/2)+'" fill="#fff" stroke="none" />';
				}
				//КРУГОВАЯ - pie//КОЛЦЕВАЯ - donut
			}else if(this.varChartType == 'bar' || this.varChartType == 'bar-overlay' || this.varChartType == 'bar-accumulation' || this.varChartType == 'bar-normalized'){
				//ЛИНЕЙЧАТАЯ - bar
				let valnum=0;
				let arrAccumulation=[];
				for(let l=0; l<=varChartItemsValue-1;l++){
					arrAccumulation.push(0);
				}

				for(let j=0; j<=this.ArrayFields.length-1; j++){
					if(this.ArrayFields[j].chart == 1 && this.hashChart[this.ArrayFields[j].name] == 1){
						let pos=0;
						for(let i=0; i<=ArrayChart.length-1; i++){
							if(ArrayChart[i].chart == this.ArrayFields[j].name){
								let varChartItemPadding=parseInt(this.#chartPadding(varChartItemMaxHeight)/2);
								let varChartItemWidth;
								let varChartItemHeight;
								let varChartItemPosX;
								let varChartItemPosY;

								//accumulation//normalized
								if(this.varChartType == 'bar-accumulation' || this.varChartType == 'bar-normalized'){
									varChartItemHeight=(varChartItemMaxHeight-varChartItemPadding*2);//ВЫСОТА ЭЛЕМЕНТА
									if(pos == 0){
										varChartItemPosY=varChartItemHeight*pos+(pos+1)*(varChartItemPadding+varChartTopPadding);
									}else{
										varChartItemPosY=varChartTopPadding+varChartItemHeight*pos+(pos+1)*(varChartItemPadding)*2-varChartItemPadding;
									}
								}else if(this.varChartType == 'bar-overlay'){
									varChartItemHeight=(varChartItemMaxHeight-varChartItemPadding*2);//ВЫСОТА ЭЛЕМЕНТА
									if(pos == 0){
										varChartItemPosY=varChartItemHeight*pos+(pos+1)*(varChartItemPadding+varChartTopPadding);
									}else{
										varChartItemPosY=varChartTopPadding+varChartItemHeight*pos+(pos+1)*(varChartItemPadding)*2-varChartItemPadding;
									}
								}else{
									varChartItemHeight=(varChartItemMaxHeight-varChartItemPadding*2)/varChartItemsValueNum;//ВЫСОТА ЭЛЕМЕНТА
									if(pos == 0){
										varChartItemPosY=varChartItemHeight*varChartItemsValueNum*pos+(pos+1)*(varChartItemPadding+varChartTopPadding);
									}else{
										varChartItemPosY=varChartTopPadding+varChartItemHeight*varChartItemsValueNum*pos+(pos+1)*(varChartItemPadding)*2-varChartItemPadding;
									}
									varChartItemPosY=varChartItemPosY+varChartItemHeight*(valnum);
								}

								let varAccumulation=0;
								if(this.varChartType == 'bar-normalized'){
									for(let j_=0; j_<=this.ArrayFields.length-1; j_++){
										if(this.ArrayFields[j_].chart == 1 && this.hashChart[this.ArrayFields[j_].name] == 1){
											for(let i_=0; i_<=ArrayChart.length-1; i_++){
												if(ArrayChart[i_].chart == this.ArrayFields[j_].name && ArrayChart[i_].title == ArrayChart[i].title){
													varAccumulation=parseFloat(varAccumulation)+parseFloat(ArrayChart[i_].value);
												}
											}
										}
									}
								}
								//accumulation//normalized

								if(this.varChartType == 'bar-normalized'){
									varChartItemWidth=ArrayChart[i].value/(varAccumulation/(this.varChartWidth-varChartLeftPadding-varChartRightPadding));
									varChartItemPosX=varChartLeftPadding+arrAccumulation[pos]/(varAccumulation/(this.varChartWidth-varChartLeftPadding-varChartRightPadding));
								}else{
									varChartItemWidth=ArrayChart[i].value*varChartItemCoefValueW;//Ширина элемента
									if(ArrayChart[i].value == 0){
										varChartItemWidth=varChartItemMinWidth;
									}
									varChartItemPosX=varChartLeftPadding+arrAccumulation[pos]*varChartItemCoefValueW;//Позиция элемениа по X
								}

								if(this.varChartType == 'bar-accumulation' || this.varChartType == 'bar-normalized'){
									arrAccumulation[pos]=parseFloat(arrAccumulation[pos])+parseFloat(ArrayChart[i].value);
								}
	
								let varChartItemPattern='url(#pattern-full-'+valnum+')';
								if(this.ArrayFields[j].color && this.ArrayFields[j].pattern){
									varChartItemPattern='url(#pattern-'+this.ArrayFields[j].pattern+'-'+this.ArrayFields[j].name+')';
								}
								if(this.hashChartDisabled[this.ArrayFields[j].name]){
									varChartItemPattern='url(#pattern-full-disabled)';
								}
								
								TempContent+='<rect fill="'+varChartItemPattern+'" title="'+ArrayChart[i].title+'<br />'+this.ArrayFields[j].title+': <strong>'+ArrayChart[i].value+'</strong>" class="bar '+this.options.field_chart+'ItemTooltip" x="'+varChartItemPosX+'" y="'+varChartItemPosY+'" width="'+varChartItemWidth+'" height="'+varChartItemHeight+'" rx="'+varChartItemStrokeRadius+'" stroke="'+varChartItemStrokeColor+'" stroke-width="'+varChartItemStrokeWidth+'">';
								if(TempAction == 'load'){
									if(varChartAnimation){
										TempContent+='<animate attributeName="width" from="0" to="'+varChartItemWidth+'" dur="0.3s" />';
									}
								}
								TempContent+='</rect>';
								pos++;
							}
						}
						valnum++;
					}
				}
				//ЛИНЕЙЧАТАЯ - bar
			}else if(this.varChartType == 'histogram' || this.varChartType == 'histogram-accumulation' || this.varChartType == 'histogram-normalized'){
				//ГИСТОГРАММА - histogram
				let valnum=0;
				let arrAccumulation=[];
				for(let l=0; l<=varChartItemsValue-1;l++){
					arrAccumulation.push(0);
				}
				
				for(let j=0; j<=this.ArrayFields.length-1; j++){
					if(this.ArrayFields[j].chart == 1 && this.hashChart[this.ArrayFields[j].name] == 1){
						let pos=0;
						for(let i=0; i<=ArrayChart.length-1; i++){
							if(ArrayChart[i].chart == this.ArrayFields[j].name){
								let varChartItemPadding=parseInt(this.#chartPadding(varChartItemMaxWidth)/2);//отступы
								let varChartItemWidth;
								let varChartItemHeight;
								let varChartItemPosX;
								let varChartItemPosY;

								//accumulation//normalized
								if(this.varChartType == 'histogram-accumulation' || this.varChartType == 'histogram-normalized'){
									varChartItemWidth=(varChartItemMaxWidth-varChartItemPadding*2);

									if(pos == 0){
										varChartItemPosX=varChartItemWidth*pos+(pos+1)*(varChartItemPadding+varChartLeftPadding);
									}else{
										varChartItemPosX=varChartLeftPadding+varChartItemWidth*pos+(pos+1)*(varChartItemPadding)*2-varChartItemPadding;
									}
								}else{
									varChartItemWidth=(varChartItemMaxWidth-varChartItemPadding*2)/varChartItemsValueNum;

									if(pos == 0){
										varChartItemPosX=varChartItemWidth*varChartItemsValueNum*pos+(pos+1)*(varChartItemPadding+varChartLeftPadding);
									}else{
										varChartItemPosX=varChartLeftPadding+varChartItemWidth*varChartItemsValueNum*pos+(pos+1)*(varChartItemPadding)*2-varChartItemPadding;
									}
									varChartItemPosX=varChartItemPosX+varChartItemWidth*(valnum);
								}
								
								let varAccumulation=0;
								if(this.varChartType == 'histogram-normalized'){
									for(let j_=0; j_<=this.ArrayFields.length-1; j_++){
										if(this.ArrayFields[j_].chart == 1 && this.hashChart[this.ArrayFields[j_].name] == 1){
											for(let i_=0; i_<=ArrayChart.length-1; i_++){
												if(ArrayChart[i_].chart == this.ArrayFields[j_].name && ArrayChart[i_].title == ArrayChart[i].title){
													varAccumulation=parseFloat(varAccumulation)+parseFloat(ArrayChart[i_].value);
												}
											}
										}
									}
								}
								//accumulation//normalized

								if(this.varChartType == 'histogram-normalized'){
									varChartItemHeight=ArrayChart[i].value/(varAccumulation/(this.varChartHeight-varChartBottomPadding-varChartTopPadding));
									varChartItemPosY=this.varChartHeight-varChartItemHeight-varChartBottomPadding-arrAccumulation[pos]/(varAccumulation/(this.varChartHeight-varChartBottomPadding-varChartTopPadding));
								}else{
									varChartItemHeight=ArrayChart[i].value*varChartItemCoefValueH;
									if(ArrayChart[i].value == 0){
										varChartItemHeight=varChartItemMinHeight;
									}
									varChartItemPosY=this.varChartHeight-varChartItemHeight-varChartBottomPadding-arrAccumulation[pos]*varChartItemCoefValueH;
								}
								
								if(this.varChartType == 'histogram-accumulation' || this.varChartType == 'histogram-normalized'){
									arrAccumulation[pos]=parseFloat(arrAccumulation[pos])+parseFloat(ArrayChart[i].value);
								}
								
								let varChartItemPattern='url(#pattern-full-'+valnum+')';
								if(this.ArrayFields[j].color && this.ArrayFields[j].pattern){
									varChartItemPattern='url(#pattern-'+this.ArrayFields[j].pattern+'-'+this.ArrayFields[j].name+')';
								}
								if(this.hashChartDisabled[this.ArrayFields[j].name]){
									varChartItemPattern='url(#pattern-full-disabled)';
								}

								//дублируем поверх прозранчый слой
								if(this.options.chart_tooltip_histogram_full){
									let varChartTooltipFull=this.varChartHeight-varChartBottomPadding-varChartTopPadding-1;
									let varChartTooltipFullY=varChartTopPadding+1;
									TempContent+='<rect x="'+varChartItemPosX+'" y="'+varChartTooltipFullY+'" width="'+varChartItemWidth+'" height="'+varChartTooltipFull+'" fill="transparent" title="'+ArrayChart[i].title+'<br />'+this.ArrayFields[j].title+': <strong>'+ArrayChart[i].value+'</strong>" class="histogram '+this.options.field_chart+'ItemTooltip" stroke="#fff" stroke-width="1">';
									TempContent+='</rect>';
								}
								//дублируем поверх прозранчый слой

								TempContent+='<rect x="'+varChartItemPosX+'" y="'+varChartItemPosY+'" width="'+varChartItemWidth+'" height="'+varChartItemHeight+'" fill="'+varChartItemPattern+'" title="'+ArrayChart[i].title+'<br />'+this.ArrayFields[j].title+': <strong>'+ArrayChart[i].value+'</strong>" class="histogram '+this.options.field_chart+'ItemTooltip" rx="'+varChartItemStrokeRadius+'" stroke="'+varChartItemStrokeColor+'" stroke-width="'+varChartItemStrokeWidth+'">';
								
								if(TempAction == 'load'){
									if(varChartAnimation){
										let varChartItemAnimatePosY=varChartItemPosY+varChartItemHeight;
										TempContent+='<animate attributeName="y" from="'+varChartItemAnimatePosY+'" to="'+varChartItemPosY+'" dur="0.3s" />';
										TempContent+='<animate attributeName="height" from="0" to="'+varChartItemHeight+'" dur="0.3s" />';
									}
								}
								TempContent+='</rect>';

								pos++;
							}
						}
						valnum++;
					}
				}
				//ГИСТОГРАММА
			}
			TempContent+='</g>';

			TempContent+='Ошибка SVG!';
			TempContent+='</svg>';
			
			//$("#"+this.options.field_chart).show();
			document.getElementById(this.options.field_chart).style.visibility = 'visible';
			document.getElementById(this.options.field_chart).innerHTML=TempContent;
		}else{
			document.getElementById(this.options.field_chart).innerHTML='';
		}

		//создаем listeners
		let inputs = document.querySelectorAll('.'+this.options.field_chart+'ControlChart');
		for(let i=0; i < inputs.length; i++){
			let str=inputs[i].className;
			let str_=str.split(' ');

			inputs[i].addEventListener("click", () => { this.ControlChart(str_[0]); }, false);
		}

		let items = document.querySelectorAll('.'+this.options.field_chart+'ItemTooltip');
		for(let i=0; i < items.length; i++){
			items[i].addEventListener("mousemove", (event) => { this.#ControlTooltip(items[i], true); }, false);
			items[i].addEventListener("mouseout", (event) => { this.#ControlTooltip(items[i], false); }, false);
		}

		if(!this.options.chart_legend_clickable_disabled){
			let inputs_ = document.querySelectorAll('.'+this.options.field_chart+'ControlLegend');
			for(let i=0; i < inputs_.length; i++){
				let str=inputs_[i].className;
				str=str.animVal;
				let str_=str.split(' ');
				inputs_[i].addEventListener("click", () => { this.#ControlLegend(str_[0]); }, false);
			}
		}

		if(this.options.chart_expand_collapse){
			let input_ = document.getElementById(this.options.field_chart+'ControlSizeChart');
			input_.addEventListener("click", () => { this.#ControlSizeChart(); }, false);
		}
		//document.addEventListener('DOMContentLoaded', this.myFunction);
		//создаем listeners
	}

	#ControlTable(TempAction,TempName,TempValue){
		if(TempAction == 'percentage'){//таблица в проценты
			this.hashPercentage[TempName]=TempValue;
			this.ShowTable();
		}
		if(TempAction == 'chart'){
			// for (var key in hashChart) {
			// 	hashChart[key]='';
			// }
			///!!!!!!!!!
			this.hashChart[TempName]=TempValue;
	
			this.ShowChart();
			this.ShowTable();
		}
		if(TempAction == 'filter'){//таблица фильтрация
			this.hashFilter[TempName]=TempValue;
			this.hashFilterValue[TempName]=document.querySelector("#filter_"+this.options.field_table+"_"+TempName+"").value;
			this.ShowTable();
			if(this.options.chart){
				this.ShowChart();
			}
		}
	}

	#ControlTooltip(TempItem, TempShow) {
		if(document.getElementById("JxChartTooltip") != null){
			let varTooltipStyle = document.getElementById("JxChartTooltip").style;
			if(TempShow){
				if(typeof TempItem.attributes.circlesover !== 'undefined'){
					eval(TempItem.attributes.circlesover.value);
				}

				let ChartTooltipEvent=document.onmousemove;

				let TempWidth = 0;
				if(typeof document.getElementById('JxChartTooltip') !== "undefined" && document.getElementById('JxChartTooltip') !== null){
					let computedStyle;
					TempWidth = document.getElementById("JxChartTooltip").clientWidth;
					if(getComputedStyle){
						computedStyle = getComputedStyle(document.getElementById("JxChartTooltip"));	
						TempWidth -= parseFloat(computedStyle.paddingLeft) + parseFloat(computedStyle.paddingRight);
					}
				}
				
				let x;
				let y;
				if(document.all){ 
					x = event.clientX + document.body.scrollLeft; 
					y = event.clientY + document.body.scrollTop; 
				} else   { 
					x = event.pageX; // Координата X курсора
					y = event.pageY; // Координата Y курсора
				}
				
				//Справа от курсора 
				if((x + TempWidth + 30) < document.body.clientWidth){ 
					varTooltipStyle.left = x + 'px';
				//Слева от курсора
				} else { 
					varTooltipStyle.left = x - TempWidth + 'px';
				}
				varTooltipStyle.top = y + 20 + 'px';

				document.getElementById("JxChartTooltip").innerHTML = TempItem.attributes.title.value;
				varTooltipStyle.display = "block";
			}else{
				if(typeof TempItem.attributes.circlesout !== 'undefined'){
					eval(TempItem.attributes.circlesout.value);
				}

				varTooltipStyle.display = "none";
			}
		}
	}
	
	ControlChart(TempValue){
		this.varChartType=TempValue;
		this.ShowChart();
	}

	#ControlSizeChart(){
		var str=document.getElementById(this.options.field_chart+'ControlSizeChart').className;
		if(str == 'expand'){
			this.varChartExpandCollapse=1;
			this.varChartHeight=window.innerHeight-100;
			document.getElementById(this.options.field_chart).classList.add("fullscreen");
		}else{
			this.varChartExpandCollapse=0;
			this.varChartHeight=this.options.height;
			document.getElementById(this.options.field_chart).classList.remove("fullscreen");
		}

		this.ShowChart('resize');
	}

	#ControlLegend(TempValue){
		if(this.hashChartDisabled[TempValue]){
			this.hashChartDisabled[TempValue]=0;
		}else{
			this.hashChartDisabled[TempValue]=1;
		}

		if(this.options.chart_legend_function){
			//внешняя функция
			eval(this.options.chart_legend_function+'(\''+this.options.name+'\',\''+TempValue+'\');');
		}

		this.ShowChart();
	}

	#ControlLoading(TempValue){
		if(TempValue){
			if(this.options.table == 1){
				document.getElementById(this.options.field_table).innerHTML='';
				document.getElementById(this.options.field_table).classList.add("loading");
			}
			if(this.options.chart == 1){
				document.getElementById(this.options.field_chart).innerHTML='';
				document.getElementById(this.options.field_chart).classList.add("loading");
			}
		}else{
			if(this.options.table == 1){
				document.getElementById(this.options.field_table).classList.remove("loading");
				document.getElementById(this.options.field_table).innerHTML='';
			}
			if(this.options.chart == 1){
				document.getElementById(this.options.field_chart).classList.remove("loading");
				document.getElementById(this.options.field_chart).innerHTML='';
			}
		}
	}

	#generateChartPatterns(){
		let k=0;
		for(let j=0; j<=2; j++){
			for(let i=0; i<this.JxChartColors.length; i++){
				this.JxChartPatterns[k]=0;
				k++;
			}
		}
	}

	#mathLog10(val){
		return Math.log(val)/Math.LN10;
	}

	#mathDescribeArc(x, y, radius, startAngle, endAngle){
		let start = this.#mathPolarToCartesian(x, y, radius, endAngle);
		let end = this.#mathPolarToCartesian(x, y, radius, startAngle);
		let arcSweep = endAngle - startAngle <= 180 ? "0" : "1";
			
		let d = [
			"M", start.x, start.y, 
			"A", radius, radius, 0, arcSweep, 0, end.x, end.y,
			"L", x,y,
			"L", start.x, start.y
		].join(" ");
		
		return d;
	}

	#mathCenterMass(radius) {
		return (4*radius)/(3*Math.PI);
	}

	#mathPolarToCartesian(centerX, centerY, radius, angleInDegrees) {
		let angleInRadians = (angleInDegrees-90) * Math.PI / 180.0;
		
		return {
		  x: centerX + (radius * Math.cos(angleInRadians)),
		  y: centerY + (radius * Math.sin(angleInRadians))
		};
	}
	
	#getChartPattern(){
		let num;
	
		let j=0;
		for(let key in this.JxChartPatterns){
			if(this.JxChartPatterns[key] == 0){
				num=j;
				break;
			}
			j++;
		}
		this.JxChartPatterns[num]=1;
	
		if(num >=9 && num <= 17){
			return 'url(#pattern-grid-'+parseInt(num-9)+')';
		}else if(num >=18 && num <= 26){
			return 'url(#pattern-stroke-'+parseInt(num-18)+')';
		}else{
			return 'url(#pattern-full-'+num+')';
		}
	}

	#chartPadding(TempValue){
		TempValue=20*(TempValue/100);
		
		return TempValue;
	}

	#getTextWidth(TempValue){
		let width = 0;
	
		let box = document.createElement('span');
		box.innerHTML = TempValue;
		box.style.top = "-1000px";
		box.style.left = "-1000px";
		document.body.appendChild(box);
		//width = box.offsetWidth;
		width = box.getBoundingClientRect().width;
		box.style.display = "none";
		
		return width;
	}
}

/*-------	JxChart	-------*/