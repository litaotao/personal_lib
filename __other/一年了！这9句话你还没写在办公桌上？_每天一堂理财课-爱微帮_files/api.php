(function(){	//原型部分img
	var tuiFixed = new Function();
	tuiFixed.prototype = {
		init : function () {
			if (!this.oBox) {
				document.write("<div id='tuiFixedTemp_" + this['request']['aid'] + "'></div>");
				var tempDom = document.getElementById('tuiFixedTemp_' + this.request.aid)
					this.oBox = tempDom.parentNode;
				this.oBox.removeChild(tempDom);
			};
			//若宽度自适应，计算父容器宽度
			if (this['set']['style']['style_width'] == 0) {
				var pl = parseInt(getEyeJsStyle(this.oBox, 'paddingLeft')) || 0;
				var pr = parseInt(getEyeJsStyle(this.oBox, 'paddingRight')) || 0;
				var oWidth = parseInt(this.oBox.offsetWidth) - pl - pr;
				this['width'] = oWidth;
				var pic_col = Math.floor((oWidth - 172) / (Number(this['set']['pic']['pic_width']) + 23)) || 1;
				this['style_pic_col'] = pic_col;
			} else {
				this['width'] = this['set']['style']['style_width'];
				this['style_pic_col'] = this['set']['style']['style_pic_col'];
			};
			function getEyeJsStyle(obj, styleName) {
				if (obj.currentStyle) { //ie
					return obj.currentStyle[styleName];
				} else { //ff
					var arr = window.getComputedStyle(obj, null)[styleName];
					return arr;
				};
			};
			//若高度自适应，计算内容高度
			if (this['set']['style']['style_length'] == 0) {
				//??
			} else {
				this['height'] = this['set']['style']['style_length'];
			};
			//分页
			this.page = 1;
			//每页数据
			//内容高度
			this.boxHeight = (parseInt(this['set']['pic']['pic_length']) + 2 + 25) * parseInt(this['set']['style']['style_pic_row']) + (parseInt(this['set']['style']['style_pic_row']) - 1) * 10;
			//文字行数
            this.txtCol = Math.floor(this.boxHeight / Number(this['set']['txt']['txt_body_margin']));
            if(this.isMier == 1){
                this.txtCol -=  3;
            }
			//每页数据量
			this.perPage = this['style_pic_col'] * this['set']['style']['style_pic_row'] + this.txtCol;

			/***默认数据***/
			if (!this['set']['slide']) {
				this['set']['slide'] = {
					"slide_type" : "0", //切换类型 0 = 关闭 1 = 换一换 2 = 幻灯
					"change_title" : "1", //换一换 文字 0 = 未开启 1 = 开启
					"change_title_txt" : "换一换", //换一换 文字
					"change_title_size" : "12", //换一换 字体大小
					"change_title_bold" : "0", //换一换 字体粗细	0=无 1=有
					"change_title_family" : "0", //换一换 字体
					"change_title_color" : "333333", //换一换 字体颜色
					"change_icon" : "1", //换一换 图标 0 = 未开启 1 = 开启
					"change_icon_type" : "1", //换一换 标志 0 = 白色 1 = 橙色
					"change_background_color" : "0", //换一换 背景 0 = 灰色 1 = 白色透明
					"change_show_type" : "0" //换一换 形式 0 = 无动画 1 = 左右 2 = 渐隐渐现
				};
			};
			//数据总数
			this.total = this.data.length;
			//总页数
			this.maxPage = Math.floor(this.total / this.perPage);
			//console.log (this.data.length,this.txtCol,this.perPage,this.maxPage)
			this.maxPage = Math.min(this.maxPage, 3);
			this.maxPage = Math.max(this.maxPage, 1);
            
            //创建iframe
			var iframe = document.createElement('iframe');
			iframe.setAttribute('allowTransparency', 'true');
			iframe.setAttribute('frameBorder', '0');
			iframe.setAttribute('scrolling', 'no');
			iframe.style.cssText = 'float:none;display:block;overflow:hidden;z-index:2147483646;margin:0;padding:0;border:0 none;background:none;';
			iframe.style.height = this['height'] + 'px';
			iframe.style.width = this['width'] + 'px';
			this.oBox.appendChild(iframe);
			if (/msie/i.test(navigator.userAgent)) {
				var that = this;
				try {
					iframe.contentWindow.document;
					this.o = iframe;
					//创建内容
					this.createHtml();
				} catch (e) {
					var base = document.getElementsByTagName('base');
					if (base && base.length > 0) {
						var baseTarget = {};
						for (var i=0;i<base.length;i++) {
							baseTarget[base[i]] = base[i].target;
							if (base[i].target == '_self') {
								continue;
							};
							base[i].target = '_self';
						};
					};
					
					iframe.src = 'javascript:void((function(){document.open();document.domain="' + document.domain + '";document.close()})())';
					if (!window.XMLHttpRequest) {
						setTimeout(function () {
							that.o = iframe;
							that.createHtml();
						}, 0);
					} else {
						this.o = iframe
							//创建内容
							this.createHtml();
					};
					
					if (base && base.length > 0) {
						for (var i=0;i<base.length;i++) {
							if (base[i].target != '_self') {
								continue;
							};
							base[i].target = baseTarget[base[i]];
						};
					};
				}
			} else {
				this.o = iframe
					//创建内容
					this.createHtml();
			};
			//请求
			if (!this.demo) {
				this.funcQuery();
			};

		},
		createHtml : function () {
			var that = this;
			this.oDoc = this.o.contentWindow.document;
            this.oDoc.open();
            var _border = this.isMier==1?"":"1px solid #ddd;";
			this.oDoc.write("<!doctype html><html><head><meta charset='utf-8'><title>云推荐</title><style type='text/css'>body,div,ul,li,em,span,a,p,img{padding:0;margin:0;}em{font-weight:normal;font-style:normal;}ol,ul{list-style:none;}table{border-collapse:collapse;border-spacing:0;}.tui{overflow:hidden;border-width:1px;border-style:solid;position:relative;}.title{overflow:hidden;position:relative;}.tools_0{position:absolute;top:0;right:5px;overflow:hidden;}.tools_1{position:relative;height:30px;line-height:30px;overflow:hidden;right:5px;}.logo{float:left;}.logo a{display:block;width:18px;height:12px;overflow:hidden;text-indent:-999em;cursor:pointer;position:absolute;left:10px;top:50%;margin-top:-6px;}.logo span{float:left;padding-left:33px;}.change{height:20px;overflow:hidden;line-height:20px;display:none;position:relative;right:0;top:50%;margin-top:-10px;}.change{float:right;}.change a{height:20px;overflow:hidden;font-size:12px;float:left;color:#333;text-decoration:none;}.change a:hover{background:none;}.change,.change em,.change b,.change span,.change i{cursor:pointer;}.change em{float:left;height:20px;position:relative;z-index:2;font-style:normal;overflow:hidden;}.change b{display:block;text-indent:-999em;width:10px;height:20px;float:left;}.change span{float:left;height:20px;}.change i{float:left;height:20px;width:20px;background-repeat:no-repeat;}.change i.i_1{background-position:0 0;}.change i.i_0{background-position:0 -20px;_margin-top:-20px;}.change a.a_0 em{background:#eee;}.change a.a_0 b.b_0{background-position:0 0;_margin-top:0;}.change a.a_0 b.b_1{background-position:0 -30px;_margin-top:-30px;}.change a.a_0:hover b.b_0{background-position:0 -60px;_margin-top:-60px;}.change a.a_0:hover b.b_1{background-position:0 -90px;_margin-top:-90px;}.change a.a_0:hover em{background-color:#e1e1e1;}.change a.a_1 em{background-position:0 -240px;background-repeat:repeat-x;_filter:progid:DXImageTransform.Microsoft.gradient(enabled='true',startColorstr='#33FFFFFF',endColorstr='#33FFFFFF');_background:none;}.change a.a_1 b.b_0{background-position:0 -120px;_margin-top:-120px;}.change a.a_1 b.b_1{background-position:0 -150px;_margin-top:-150px;}.change a.a_1:hover b.b_0{background-position:0 -180px;_margin-top:-180px;}.change a.a_1:hover b.b_1{background-position:0 -210px;_margin-top:-210px;}.change a.a_1:hover em{background-position:0 -270px;background-repeat:repeat-x;_background:none;_filter:progid:DXImageTransform.Microsoft.gradient(enabled='true',startColorstr='#7FFFFFFF',endColorstr='#7FFFFFFF');}.link{float:right;display:none;}#link a,#foot a{color:#969696;font-size:12px;text-decoration:none;}#link a:hover,#foot a:hover{text-decoration:underline;}.content{overflow:hidden;margin:0 10px;}.box{width:9999em;overflow:hidden;}.hot{overflow:hidden;margin-top:10px;}.hot ul{overflow:hidden;*zoom:1;}.hot li{float:left;word-wrap:normal;word-break:keep-all;padding-left:10px;}.foot{height:30px;line-height:30px;text-align:right;font-size:12px;}.focus{height:16px;line-height:16px;overflow:hidden;float:right;display:none;position:relative;right:0;top:50%;margin-top:-8px;}.focus li{float:left;width:16px;height:16px;text-align:center;padding-left:5px;}.focus a{display:block;width:16px;height:16px;overflow:hidden;text-decoration:none;font-size:12px;}.img img{display:block;overflow:hidden;border:0 none}.img a{display:block;width:100%;}.img a em{display:block;overflow:hidden;cursor:pointer;}.img a span{display:block;overflow:hidden;padding-top:5px;cursor:pointer;}.img{margin-left:10px;float:left;border-right:"+_border+"padding-right:10px;}.img_0{margin-left:0;}.img li{margin-top:10px;}.img li.i_0{margin-top:0;}.box_inn{margin-top:10px;overflow:hidden;float:left;}.txt{float:left;margin-left:10px;}</style></head><body><div class='tui' id='tui'><div id='title' class='title'><div class='logo' id='logo'><a href='' target='_blank' hidefocus='true' title='云推荐'>云推荐</a><span></span></div></div><div id='tools'><div id='change' class='change'><a href='javascript:;' hidefocus='true'><b class='b_0'></b><em><i></i><span></span></em><b class='b_1'></b></a></div><div id='link' class='link'><a href='' target='_blank' title='云推荐'>云推荐</a></div><ol id='focus' class='focus'></ol></div><div id='content' class='content'><div class='box' id='box'></div><div id='hot' class='hot'></div><div id='foot' class='foot'><a href='' target='_blank' title='云推荐'>云推荐</a></div></div></div>" + this.funcStyle() + "</body></html>");
			this.oDoc.close();
			//定义容器
			this.oTui = this.oDoc.getElementById('tui'); //外容器
			this.oTitle = this.oDoc.getElementById('title'); //标题栏
			this.oHot = this.oDoc.getElementById('hot'); //热词容器
			this.oFoot = this.oDoc.getElementById('foot'); //底部	链接
			this.oLink = this.oDoc.getElementById('link'); //头部 链接
			this.oChange = this.oDoc.getElementById('change'); //换一换
			this.oFocus = this.oDoc.getElementById('focus'); //轮播
			this.oContent = this.oDoc.getElementById('content'); //内容
			this.oCon = this.oDoc.getElementById('box'); //图文容器
			this.oLogo = this.oDoc.getElementById('logo'); //logo
			this.oTools = this.oDoc.getElementById('tools'); //tools
			//标题
			var ts = this.oLogo.getElementsByTagName('span')[0];
			var ta = this.oLogo.getElementsByTagName('a')[0];
			if (this['set']['txt']['txt_title_icon'] == 0 && this['set']['txt']['txt_title'] == 0) {
				this.oTitle.style.display = 'none';
				this.oTools.className = "tools_1";
			} else {
				this.oTools.className = "tools_0";
				if (this['set']['txt']['txt_title_icon'] == 1) {
					ta.href = this.tuiUrl + '?pd=logo';
					if (this['set']['logo']['logo_background_user'] != 2) {
						ta.className = 'a_' + this['set']['logo']['logo_background_user'];
					} else {
						ta.style.backgroundImage = 'url(' + this['set']['logo']['logo_background_img'] + ')';
						ta.style.backgroundPosition = '0 0';
						ta.style.backgroundRepeat = 'no-repeat';
					};
				} else {
					ts.style.paddingLeft = '10px';
					ta.style.display = 'none';
				};
				if (this['set']['txt']['txt_title'] == 1) {
					ts.innerHTML = this['set']['txt']['txt_title_txt'];
				};
			};
			//tools是否出现
			if ((this['set']['slide']['slide_type'] == 0 || this.maxPage == 1) && this['set']['logo']['logo_position'] != 1) {
				this.oTools.style.display = "none";
			} else {
				//换一换
				if (this['set']['slide']['slide_type'] != 1 || this.maxPage == 1) {
					this.oChange.style.display = "none";
				} else {
					this.oChange.style.display = "block";
					var cs = this.oChange.getElementsByTagName("span")[0]; //文字
					var ca = this.oChange.getElementsByTagName("a")[0]; //按钮背景
					var ci = this.oChange.getElementsByTagName("i")[0]; //图标
					this.oChange.title = this['set']['slide']['change_title_txt'] || '换一换';
					if (this['set']['slide']['change_title'] == 1) {
						cs.innerHTML = this['set']['slide']['change_title_txt'];
					} else {
						cs.style.display = "none";
					};
					if (this['set']['slide']['change_icon'] == 1) {
						ci.className = "i_" + this['set']['slide']['change_icon_type'];
					} else {
						ci.style.display = "none";
					};
					ca.className = "a_" + this['set']['slide']['change_background_color'];
					this.oChange.onclick = function () {
						that.funcChange();
					};
				};
				//轮播
				clearInterval(that.autoTime);
				if (this['set']['slide']['slide_type'] != 2 || this.maxPage == 1) {
					this.oFocus.style.display = "none";
				} else if (this['set']['slide']['slide_type'] == 2) {
					clearInterval(that.autoTime);
					var focusHtml = '';
					for (var i = 1; i <= this.maxPage; i++) {
						focusHtml += '<li><a href="javascript:;">' + i + '</a></li>';
					};
					this.oFocus.innerHTML = focusHtml;
					this.oFocus.style.display = "block";
					var focusA = this.oFocus.getElementsByTagName('a');
					focusA[0].className = 'active';
					for (var i = 0; i < focusA.length; i++) {
						focusA[i].index = i;
						focusA[i].onclick = function () {
							clearInterval(that.autoTime)
							showFocus(this.index);
						};
					};
					function showFocus(index, boo) {
						removeAllClass();
						var node = focusA[index] || focusA[0];
						node.className = 'active';
						var page = index + 1;
						if (boo) {
							that.funcChange();
						} else {
							that.funcChange(1, page);
						};

					};
					function autoFocus(pages) {
						pages = pages || 1;
						var timer = that['set']['slide']['slide_timer'] || 5;
						timer = timer * 1000;
						that.autoTime = setInterval(
								function () {
								showFocus(pages, 1);
								pages == that.maxPage ? pages = 1 : pages++;
							},
								timer);
					};
					autoFocus();
					this.oDoc.onmouseout = function () {
						for (var i = 0; i < focusA.length; i++) {
							if (focusA[i].className == 'active') {
								autoFocus(i + 1);
								break;
							};
						};
					};
					this.oDoc.onmouseover = function () {
						clearInterval(that.autoTime);
					};
					function removeAllClass() {
						for (var i = 0; i < focusA.length; i++) {
							focusA[i].className = '';
						};
					};
				};
				//云推荐位置
				if (this['set']['logo']['logo_position'] && this['set']['logo']['logo_position'] == 1) {
					this.oFoot.style.display = "none";
					this.oLink.style.display = "block";
				} else {
					this.oFoot.style.display = "block";
					this.oLink.style.display = "none";
				};
			};
			this.oFoot.getElementsByTagName('a')[0].href = this.tuiUrl + '?pd=PowerBy';
			this.oLink.getElementsByTagName('a')[0].href = this.tuiUrl + '?pd=PowerBy';
			//内容
			var ihtml = '';
			var i = 0;
			for (var j = 0; j < this.maxPage; j++) {
				var dataLength = this['data'].length - this.perPage * j;
				var target = '_blank';
				if (this['set']['txt']['txt_link_target'] == 1 && !this.demo) {
					target = '_parent';
				};
				var imgLength = Math.min(this['style_pic_col'] * this['set']['style']['style_pic_row'], dataLength);
				var trueimg = 0,
				defaultimg = 0,
				itelimg = 0,
				totalimg = 0;
				if (!this['data'][i]) {
					break;
				};
				ihtml += '<div class="box_inn">';
				for (var x = 0; x < this['style_pic_col']; x++) {
					if (x == 0) {
						ihtml += '<ul class="img img_0">';
					} else {
						ihtml += '<ul class="img">';
					};
					for (var y = 0; y < this['set']['style']['style_pic_row']; y++) {
						
						if (!this['data'][i] || !this['data'][i]['title']) {
							break;
						};
						if (y == 0) {
							ihtml += '<li class="i_0">';
						} else {
							ihtml += '<li>';
						};
						var has_thumb = this['data'][i]['has_thumb'] || 'false';
						var is_smart_thumb = this['data'][i]['is_smart_thumb'] || 'false';
						ihtml += "<a href='" + this['data'][i]['url'] + "' target='" + target + "' title='" + this['data'][i]['title'] + "'><em><img src='" + this['imgLoad'] + "' alt='" + this['data'][i]['thumbnail'] + "' title='" + this['data'][i]['title'] + "' hidefocus='true' jsdata='" + has_thumb + "' userimg='" + this['data'][i]['algId'] + "' data-img='" + is_smart_thumb + "'></em><span>" + this['data'][i]['title'] + "</span></a></li>";
					    i++;
					};
					ihtml += '</ul>';
				};
				ihtml += '<ul class="txt">';
                //米尔的用户头部加粗
                if(this.isMier == 1){
                    ihtml += "<li>";
                    ihtml += "<b><a style='font-size:18px;' href='" + this['data'][i]['url'] + "' target='" + target + "' title='" + this['data'][i]['title'] + "' hidefocus='true'>" + this['data'][i]['title'] + "</a></b></li>";
                    if(this['data'][i]['mainbody']){
                        ihtml += "<li style='font-size:12px'>"+this['data'][i]['mainbody']+"</li>";
                    }
                    ihtml += "<li style='height:12px'><div style='border-bottom: 1px dashed #cccccc;height: 6px;'></div></li>";
                    i++;
                    
                }
                for (var z = 0; z < this.txtCol; z++) {
				    if(z == Math.floor((this.txtCol)/2)&&this.isMier == "1"){
                        ihtml +="<li style='height:"+(this['set']['txt']['txt_body_margin']-12)+"px'></li>";
                    }	
					if (!this['data'][i] || !this['data'][i]['title']) {
						break;
					};
					if (this['set']['txt']['txt_focus'] == 1) {
						ihtml += "<li>&bull;&nbsp;";
					} else if (this['set']['txt']['txt_focus'] == 2) {
						ihtml += "<li>▪&nbsp;";
					} else {
						ihtml += "<li>";
					};
					ihtml += "<a href='" + this['data'][i]['url'] + "' target='" + target + "' title='" + this['data'][i]['title'] + "' hidefocus='true'>" + this['data'][i]['title'] + "</a></li>";
				    i++;
				};
				ihtml += '</ul>';
				ihtml += '</div>';
			};
			this.oCon.innerHTML = ihtml;

			var Img = this.oCon.getElementsByTagName('img');
			for (var i = 0; i < Img.length; i++) {
				loadImg(Img[i]);
			};
			//图片功能
			function loadImg(oBj) {
				if (Sys().ie == '6.0' || Sys().ie == '7.0') {
					var a = oBj.parentNode.parentNode;
					a.onclick = function () {
						if (that['set']['txt']['txt_link_target'] == 1 && !that.demo) {
							window.location.href = a.href;
						} else {
							window.open(a.href);
						};
						return false;
					};
				};
				tryImg(oBj, 0)
			};
			function tryImg(oBj, index) {
				var src = oBj.getAttribute('alt');
				var jsdata = oBj.getAttribute('jsdata');
				var userimg = oBj.getAttribute('userimg'); //stick
				var size = Math.max(Number(that['set']['pic']['pic_width']), Number(that['set']['pic']['pic_length']));
				var is_smart_thumb = oBj.getAttribute('data-img');
				var img = new Image();
				var ErrorNum = that.errorNum || 7;
				
				if (jsdata == 'false') {
					userimg = 'false';
				};
				
				if (userimg == 'stick' || window.location.href.indexOf('meishichina.com') != -1) {
					if (index == 1) {
						index = 2;
						src = that.errorDir + Math.ceil(Math.random() * ErrorNum) + '.jpg';
					};
				} else {
					if ((jsdata == 'false' || index >= 2) && !that.demo) {
						src = that.errorDir + Math.ceil(Math.random() * ErrorNum) + '.jpg';
					} else if (index == 0) {
						if (userimg == 'stick') {
							src = src;
						} else if (size > 96 && !that.demo) {
							src = src + '_b';
						};
					} else if (index == 1) {
						if (size <= 96 && !that.demo) {
							src = src + '_b';
						};
					};
				};

				img.onload = function () {
					totalimg++
					if (jsdata == 'false' || index >= 2) {
						defaultimg++;
					} else if (is_smart_thumb != 'false') {
						itelimg++;
					} else {
						trueimg++;
					};
					imgStatus();
					loadFunc(this, oBj, that['set']['pic']['pic_scale'], src);
				};
				img.onerror = img.onabort = function () {
					if (index < 2) {
						index++;
						tryImg(oBj, index);
					};
				};
				img.src = src;
			};
			function imgStatus() {
				if (totalimg == Img.length && !that.demo) {
					var url = '&' + encodeURIComponent(String.fromCharCode(1)) + '&ch=wprdsp&l=img&hid=' + that['request']['hid'] + '&trueimg=' + trueimg + '&defaultimg=' + defaultimg + '&itelimg=' + itelimg;
					questImg(url);
				};
			};
			function loadFunc(img, oBj, type, src) {
				var w = img.width;
				var h = img.height;
				var w0 = Number(that['set']['pic']['pic_width']);
				var h0 = Number(that['set']['pic']['pic_length']);
				if (!type || type == 0) {
					if (oBj) {
						oBj.style.height = h0 + 'px';
						oBj.style.width = w0 + 'px';
					};
				} else if (type == 1) {
					if (w * h0 >= w0 * h) {
						var h1 = Math.ceil(w0 * h / w);
						if (oBj) {
							oBj.style.width = w0 + 'px';
							oBj.style.height = h1 + 'px';
							oBj.style.marginTop = (h0 - h1) / 2 + 'px';
						};
					} else {
						var w1 = Math.ceil(w * h0 / h);
						if (oBj) {
							oBj.style.width = w1 + 'px';
							oBj.style.height = h0 + 'px';
							oBj.style.marginLeft = (w0 - w1) / 2 + 'px';
						};
					};
				} else if (type == 2) {
					if (w * h0 >= w0 * h) {
						var w1 = Math.ceil(w * h0 / h);
						if (oBj) {
							oBj.style.height = h0 + 'px';
							oBj.style.width = w1 + 'px';
						};
					} else {
						var h1 = Math.ceil(w0 * h / w);
						if (oBj) {
							oBj.style.width = w0 + 'px';
							oBj.style.height = h1 + 'px';
						};
					};
				};
				if (oBj)
					oBj.setAttribute('src', src);
			};
			//热词
			if (this['set']['hot']['data_hot'] != 0 && this['set']['hot']['data_hot_txt'] != '') {
				var hhtml = '<ul>';
				var hotLength = this['set']['hot']['data_hot_num'];
				var hotList = this['set']['hot']['data_hot_txt'].split(',');
				
				if (hotLength == 0) {
					hotLength = Math.max(5, hotList.length)
				};
				
				//标签热词
				var hotReco = [];
				if (typeof(aliyun_recommend_opts) == 'object' && aliyun_recommend_opts['tags']) {
					var hotRecoTmp = aliyun_recommend_opts['tags'].split(',');
					for (var i=0;i<hotRecoTmp.length;i++) {
						if (!hotRecoTmp[i]) {
							continue;
						};
						hotReco.push(hotRecoTmp[i]);
					};
				};
				//用户热词
				var hotUserNum = this['set']['hot']['data_hot_txt_user'] || 0;
				if (hotUserNum == 0) {
					hotLength = Math.min(hotLength, hotList.length + hotReco.length);
					if (hotReco[0]) {
						for (var i = 0; i < Math.min(hotReco.length,hotLength); i++) {
							hhtml += "<li><a href='" + this.searchUrl + "?kw=" + encodeURIComponent(hotReco[i]) + "&site=" + (this.request.sid || '') + "&ip=" + (this.ip || '') + "&pui=czb&cok=" + (this.Rcookie || '') + "&vr=1&hid=" + (this.request.hid || '') + "&bkt=" + (this.request.bkt || '') + "&ch=kwrdc&l=click&ft=" + this['ft'] + "&ps=" + i + "&wd=" + encodeURIComponent(hotReco[i]) + "&aid=" + this['request']['aid'] + "&sid=" + this['request']['aid'] + "' target='_blank' title='" + hotReco[i] + "' hidefocus='true'>" + hotReco[i] + "</a></li>";
						};
					};
					hotList.sort(function () {
						return 0.5 > Math.random();
					});
					for (var i = 0; i < hotLength - Math.min(hotReco.length, hotLength); i++) {
						if (hotList[i]) {
							hhtml += "<li><a href='" + this.searchUrl + "?kw=" + encodeURIComponent(hotList[i]) + "&site=" + (this.request.sid || '') + "&ip=" + (this.ip || '') + "&pui=czb&cok=" + (this.Rcookie || '') + "&vr=1&hid=" + (this.request.hid || '') + "&bkt=" + (this.request.bkt || '') + "&ch=kwrdc&l=click&ft=" + this['ft'] + "&ps=" + i + "&wd=" + encodeURIComponent(hotList[i]) + "&aid=" + this['request']['aid'] + "&sid=" + this['request']['aid'] + "' target='_blank' title='" + hotList[i] + "' hidefocus='true'>" + hotList[i] + "</a></li>";
						};
					};
				} else {
					var hotUser = [];
					var hotAgg = [];
					for (var i = 0; i < hotLength; i++) {
						if (i < hotUserNum) {
							hotUser.push(hotList[i]);
						} else {
							hotAgg.push(hotList[i])
						};
					};
					hotList = hotReco.concat(hotAgg);
					if (hotUser[0]) {
						for (var i = 0; i < Math.min(hotUser.length,hotLength); i++) {
							hhtml += "<li><a href='" + this.searchUrl + "?kw=" + encodeURIComponent(hotUser[i]) + "&site=" + (this.request.sid || '') + "&ip=" + (this.ip || '') + "&pui=czb&cok=" + (this.Rcookie || '') + "&vr=1&hid=" + (this.request.hid || '') + "&bkt=" + (this.request.bkt || '') + "&ch=kwrdc&l=click&ft=" + this['ft'] + "&ps=" + i + "&wd=" + encodeURIComponent(hotUser[i]) + "&aid=" + this['request']['aid'] + "&sid=" + this['request']['aid'] + "' target='_blank' title='" + hotUser[i] + "' hidefocus='true'>" + hotUser[i] + "</a></li>";
						};
					};
					hotList.sort(function () {
						return 0.5 > Math.random();
					});
					for (var i = 0; i < (hotLength - Math.min(hotUser.length, hotLength)); i++) {
						if (hotList[i]) {
							hhtml += "<li><a href='" + this.searchUrl + "?kw=" + encodeURIComponent(hotList[i]) + "&site=" + (this.request.sid || '') + "&ip=" + (this.ip || '') + "&pui=czb&cok=" + (this.Rcookie || '') + "&vr=1&hid=" + (this.request.hid || '') + "&bkt=" + (this.request.bkt || '') + "&ch=kwrdc&l=click&ft=" + this['ft'] + "&ps=" + i + "&wd=" + encodeURIComponent(hotList[i]) + "&aid=" + this['request']['aid'] + "&sid=" + this['request']['aid'] + "' target='_blank' title='" + hotList[i] + "' hidefocus='true'>" + hotList[i] + "</a></li>";
						};
					};
				};
				
				hhtml += '</ul>';
				this.oHot.innerHTML = hhtml;
			} else {
				this.oHot.style.display = 'none';
			};
			//边框
			if (this['set']['txt']['txt_border'] == 1) {
				this.oTxt.className += " bor";
			};
			//无间隔滚动
			if ((this['set']['slide']['change_show_type'] == 1 && this['set']['slide']['slide_type'] == 1) || (this['set']['slide']['focus_show_type'] == 1 && this['set']['slide']['slide_type'] == 2)) {
				var nodeTmp = this.oDoc.createElement('div');
				nodeTmp.innerHTML = this.oCon.getElementsByTagName('div')[0].innerHTML;
				nodeTmp.className = 'box_inn';
				this.oCon.appendChild(nodeTmp);
				var img_1 = nodeTmp.getElementsByTagName('img');
				for (var i = 0; i < img_1.length; i++) {
					loadImg(img_1[i]);
				};
			};
		},
		funcChange : function (boo, page) {
			var that = this;
			var oTmp = this.oCon.childNodes;
			var oDiv = [];
			for (var i = 0; i < oTmp.length; i++) {
				if (oTmp[i].className == 'box_inn') {
					oDiv.push(oTmp[i]);
				};
			};
			//无效果
			if ((this['set']['slide']['change_show_type'] == 0 && this['set']['slide']['slide_type'] == 1) || (this['set']['slide']['focus_show_type'] == 0 && this['set']['slide']['slide_type'] == 2)) {
				if (!boo) {
					if (this.page == this.maxPage) {
						this.page = 1;
					} else {
						this.page++;
					};
				} else {
					this.page = page;
				};
				for (var i = 0; i < oDiv.length; i++) {
					if (i == that.page - 1) {
						oDiv[i].style.display = 'block';
					} else {
						oDiv[i].style.display = 'none';
					};
				};
			};
			//左右
			if ((this['set']['slide']['change_show_type'] == 1 && this['set']['slide']['slide_type'] == 1) || (this['set']['slide']['focus_show_type'] == 1 && this['set']['slide']['slide_type'] == 2)) {
				if (!boo) {
					if (this.page - this.maxPage == 1) {
						that.oCon.style.marginLeft = 0;
						this.page = 1;
					};
					this.page++;
				} else {
					this.page = page;
				};
				clearTimeout(that.scrollTime);
				var w = this['width'] - 12;
				var t = 0 - w * this.page + w;
				this.scrollTime = setInterval(
						function () {
						var left = parseInt(that.oCon.style.marginLeft || 0);
						var step = (t - left) / 10;
						step = step > 0 ? Math.ceil(step) : Math.floor(step);
						if (left == t) {
							clearInterval(that.scrollTime);
						} else {
							that.oCon.style.marginLeft = left + step + 'px';
						};
					}, 10);
			};
			//渐现
			if ((this['set']['slide']['change_show_type'] == 2 && this['set']['slide']['slide_type'] == 1) || (this['set']['slide']['focus_show_type'] == 2 && this['set']['slide']['slide_type'] == 2)) {
				if (!boo) {
					if (this.page == this.maxPage) {
						this.page = 1;
					} else {
						this.page++;
					};
				} else {
					this.page = page;
				};
				clearTimeout(that.fadeTime);
				var t = 0;
				for (var i = 0; i < oDiv.length; i++) {
					if (i == that.page - 1) {
						oDiv[i].style.display = 'block';
					} else {
						oDiv[i].style.display = 'none';
					};
				};
				var node = that.oCon;
				if (!document.documentMode && (Sys().ie == "6.0" || Sys().ie == "7.0") || Sys().ie == "8.0") {
					node = that.oContent;
				};
				this.fadeTime = setInterval(
						function () {
						if (t > 100) {
							clearInterval(that.timer);
						} else {
							setOpacity(node, t);
							t += 1;
						};

					}, 15);
				function setOpacity(elem, level) {
					if (elem.filters) {
						elem.style.filter = "alpha(opacity=" + level + ")";
						elem.style.zoom = 1;
					} else {
						elem.style.opacity = level / 100;
					};
				};
			};
			if (!this.demo) {
				var hid = this['request']['hid'];
				var bkt = this['request']['bkt'];
				var la = encodeURIComponent(String.fromCharCode(1));
				var lb = encodeURIComponent(String.fromCharCode(2));
				var url = '';
				url = '&' + la + '&ch=wprdsp&l=flush&pg=' + this.page + '&hid=' + hid + '&bkt=' + bkt;
				questImg(url);
			};
		},
		funcStyle : function () {
			//计算图片间距 MM=( W - (MW+4)*COL - 22) / (COL-1)
			var W = Number(this['width']);
			var MW = Number(this['set']['pic']['pic_width']);
			var MH = Number(this['set']['pic']['pic_length']);
			var MCOL = Number(this['style_pic_col']);
			var MROW = Number(this['set']['style']['style_pic_row']);
			var MM = Math.floor((W - MW * MCOL - 4 * MCOL - 22) / (MCOL - 1));
			//文字宽度 TW=(W - COL*20 + 18 )/COL
			var TCOL = Number(this['set']['style']['style_txt_col']);
			var TW = Math.floor((W - 22 - TCOL * 10) / TCOL);
			//热词行高
			var HLH = Number(this['set']['hot']['hot_body_margin']);
			//标题行高
			var TLH = Number(this['set']['txt']['txt_title_margin']);
			//文字行高
			var BLH = Number(this['set']['txt']['txt_body_margin'])
				//各种配置
				var style = "<style type='text/css'>";
			style += ".tui{width:" + (W - 2) + "px;height:" + (this['set']['style']['style_length'] - 2) + "px;background:#" + this['set']['style']['style_background_color'] + ";border-color:#" + this['set']['style']['style_border_color'] + ";}";
			var font_family = ['arial', 'tahoma', 'sans-serif', 'SimSun', 'SimHei', 'Microsoft YaHei'];
			var bold = 400;
			if (this['set']['txt']['txt_title_bold'] == 1) {
				bold = 700;
			};
			style += ".title {height:" + this['set']['txt']['txt_title_margin'] + "px;line-height:" + this['set']['txt']['txt_title_margin'] + "px;font-size:" + this['set']['txt']['txt_title_size'] + "px;font-weight:" + bold + ";font-family:" + font_family[this['set']['txt']['txt_title_family']] + ";color:#" + this['set']['txt']['txt_title_color'] + ";}";
			style += ".tools_0 {height:" + this['set']['txt']['txt_title_margin'] + "px;line-height:" + this['set']['txt']['txt_title_margin'] + "px;}"
			if (this["set"]["txt"]["txt_title_background"] == 1) {
				style += ".title {background:url(" + this["set"]["txt"]["txt_title_bgimage"] + ") 0 0 repeat;}";
			} else {
				style += ".title {background-color:#" + this["set"]["txt"]["txt_title_bgcolor"] + ";}";
			};
			style += ".content{width:" + (W - 22) + "px;}";
			style += ".bor{background:url(" + this.imgDir + "border.png) left top repeat-x;}";
			style += ".logo a.a_1{background:url(" + this.imgDir + "logo_pink.png) 0 0 no-repeat;_filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true,sizingMethod=noscale,src='" + this.imgDir + "logo_pink.png');_background:none;}";
			style += ".logo a.a_0{background:url(" + this.imgDir + "logo_white.png) 0 0 no-repeat;_filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true,sizingMethod=noscale,src='" + this.imgDir + "logo_white.png');_background:none;}";
			style += ".change b{background:url(" + this.imgDir + "change_btn.png) 0 0 no-repeat;_filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true,sizingMethod=noscale,src='" + this.imgDir + "change_btn.png');_background:none;}";
			style += ".change i{background-image:url(" + this.imgDir + "change_ico.png);_filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true,sizingMethod=noscale,src='" + this.imgDir + "change_ico.png');_background:none;_margin-right:6px;_height:40px;}";
			style += ".change a.a_1 em {background-image:url(" + this.imgDir + "change_btn.png) !important;_background-image:url(" + this.imgDir + "change_btn_8.png)}";
			var bold = 400;
			if (this['set']['slide']['change_title_bold'] == 1) {
				bold = 700;
			};
			style += ".change span {font-size:" + this['set']['slide']['change_title_size'] + "px;color:#" + this['set']['slide']['change_title_color'] + ";font-weight:" + bold + ";font-family:" + font_family[this['set']['slide']['change_title_family']] + "}";
			var tw = W - 2 - (Number(this['set']['pic']['pic_width']) + 2 + 21) * Number(this['style_pic_col']) - 10;
			var bold = 400;
			if (this['set']['txt']['txt_body_bold'] == 1) {
				bold = 700;
			};
			style += ".box_inn {width:" + (W - 12) + "px;height:" + this.boxHeight + "px;font-weight:"+ bold +"}";
			style += ".img li {width:" + (MW + 2) + "px;font-size:" + this['set']['txt']['txt_body_size'] + "px;font-weight:" + bold + ";font-family:" + font_family[this['set']['txt']['txt_body_family']] + "}";
			style += ".img a:hover {background-color:#" + this['set']['style']['style_hover_color'] + ";}";
			style += ".img a span {height:20px;line-height:20px;text-align:center;}";
			style += ".img em {width:" + this['set']['pic']['pic_width'] + "px;height:" + MH + "px;}";
			style += ".txt {width:" + tw + "px;color:#" + this['set']['txt']['txt_default_color'] + ";}";
			style += ".txt li{height:" + this['set']['txt']['txt_body_margin'] + "px;line-height:" + this['set']['txt']['txt_body_margin'] + "px;font-size:" + this['set']['txt']['txt_body_size'] + "px;font-weight:" + bold + ";font-family:" + font_family[this['set']['txt']['txt_body_family']] + ";padding-right:10px;overflow:hidden;word-break:break-all;}";
			style += ".box li a:link{color:#" + this['set']['txt']['txt_default_color'] + ";}";
			style += ".box li a:visited{color:#" + this['set']['txt']['txt_clicked_color'] + ";}";
			style += ".box li a:hover{color:#" + this['set']['txt']['txt_hover_color'] + ";}";
			style += ".box li a:active{color:#" + this['set']['txt']['txt_click_color'] + ";}";
			if (this['set']['txt']['txt_default_underline'] == 0) {
				style += ".box li a:link{text-decoration:none;}";
			} else {
				style += ".box li a:link{text-decoration:underline;}";
			};
			if (this['set']['txt']['txt_clicked_underline'] == 0) {
				style += ".box li a:visited{text-decoration:none;}";
			} else {
				style += ".box li a:visited{text-decoration:underline;}";
			};
			if (this['set']['txt']['txt_hover_underline'] == 0) {
				style += ".box li a:hover{text-decoration:none;}";
			} else {
				style += ".box li a:hover{text-decoration:underline;}";
			};
			if (this['set']['txt']['txt_click_underline'] == 0) {
				style += ".box li a:active{text-decoration:none;}";
			} else {
				style += ".box li a:active{text-decoration:underline;}";
			};
			var bold = 400;
			if (this['set']['hot']['hot_body_bold'] == 1) {
				bold = 700;
			};
			style += ".hot{background-color:#" + this['set']['hot']['hot_body_background'] + ";height:" + this['set']['hot']['hot_body_margin'] + "px;line-height:" + this['set']['hot']['hot_body_margin'] + "px;font-size:" + this['set']['hot']['hot_body_size'] + "px;font-weight:" + bold + ";font-family:" + font_family[this['set']['hot']['hot_body_family']] + "}";
			style += ".hot li a:link{color:#" + this['set']['hot']['hot_default_color'] + ";}";
			style += ".hot li a:visited{color:#" + this['set']['hot']['hot_clicked_color'] + ";}";
			style += ".hot li a:hover{color:#" + this['set']['hot']['hot_hover_color'] + ";}";
			style += ".hot li a:active{color:#" + this['set']['hot']['hot_click_color'] + ";}";
			if (this['set']['hot']['hot_default_underline'] == 0) {
				style += ".hot li a:link{text-decoration:none;}";
			} else {
				style += ".hot li a:link{text-decoration:underline;}";
			};
			if (this['set']['hot']['hot_clicked_underline'] == 0) {
				style += ".hot li a:visited{text-decoration:none;}";
			} else {
				style += ".hot li a:visited{text-decoration:underline;}";
			};
			if (this['set']['hot']['hot_hover_underline'] == 0) {
				style += ".hot li a:hover{text-decoration:none;}";
			} else {
				style += ".hot li a:hover{text-decoration:underline;}";
			};
			if (this['set']['hot']['hot_click_underline'] == 0) {
				style += ".hot li a:active{text-decoration:none;}";
			} else {
				style += ".hot li a:active{text-decoration:underline;}";
			};
			if (this['set']['slide']['change_title'] == 0 || this['set']['slide']['change_title'] == '') {
				style += ".change b {display:none}#change a em {background:none;}"
			};
			style += ".focus a {color:#" + this['set']['slide']['focus_txt_color'] + ";}";
			style += ".focus a {font-family:" + font_family[this['set']['slide']['focus_txt_family']] + ";}";
			style += ".focus a {background-color:#" + this['set']['slide']['focus_hover_background'] + ";}";
			style += ".focus a.active {background-color:#" + this['set']['slide']['focus_txt_background'] + ";}";
			style += ".foot a {font-family:'Microsoft YaHei'}";
			if (this['set']['pic']['pic_border'] == 1) {
				style += ".img a em {border:1px solid #ddd;}";
			} else {
				style += ".img a em {padding:1px;}";
			};
			style += '</style>';
			return (style);
		},
		funcQuery : function () {
			var that = this;
			var hid = this['request']['hid'];
			var bkt = this['request']['bkt'];
			var la = encodeURIComponent(String.fromCharCode(1));
			var lb = encodeURIComponent(String.fromCharCode(2));
			var dspsize = Math.min(this.perPage, this.data.length);
			//打开页面
			var url = '';
			url = '&' + la + '&ch=wprdsp&l=view&pg=1&hid=' + hid + '&bkt=' + bkt + '&dspsize=' + dspsize;
			//热词
			if (this['set']['hot']['data_hot'] != 0) {
				if (this['set']['hot']['data_hot_txt'] != '') {
					url += '&' + lb + '&has=true&ch=hkwrdsp&l=view&hid=' + hid + '&bkt=' + bkt;
				} else {
					url += '&' + lb + '&has=false&ch=hkwrdsp&l=view&hid=' + hid + '&bkt=' + bkt;
				};
			};
			questImg(url);

			//若不在第一屏
			var ot = getElemPos(this.o).y || 0;
			var tt;
			if (document.compatMode == 'BackCompat') {
				tt = document.body.clientHeight;
			} else {
				tt = document.documentElement.clientHeight;
			};
			var seenCount = 0;
			function seeOnce() {
				if (seenCount == 0) {
					var st = Math.max(document.body.scrollTop, document.documentElement.scrollTop, 0);
					if (tt + st > ot) {
						var url = '&' + la + '&ch=wprdsp&l=action&act=001&hid=' + hid + '&bkt=' + bkt;
						questImg(url);
						seenCount++;
					};
				};
			};
			if (ot > tt) {
				addEvent(window, 'scroll', function () {
					seeOnce();
				});
			} else {
				seeOnce();
			};

			//鼠标第一次经过
			var mouseCount = 0;
			this.o.onmouseover = function () {
				if (mouseCount == 0) {
					var url = '&' + la + '&ch=wprdsp&l=action&act=002&hid=' + hid + '&bkt=' + bkt;
					questImg(url);
					mouseCount++;
				};
			};

			//热词点击
			if (this['set']['hot']['data_hot'] != 0 && this['set']['hot']['data_hot_txt'] != '') {
				var a = this.oHot.getElementsByTagName('a');
				for (var i = 0; i < a.length; i++) {
					a[i].index = i;
					a[i].onclick = function () {
						var url = '';
						url = '&' + la + '&ch=kwrdc&l=click&ps=' + this.index + '&wd=' + encodeURIComponent(this.innerHTML) + '&hid=' + hid + '&bkt=' + bkt;
						questImg(url);
					};
				}
			};
			var jumpUrl = this.jumpUrl;
			var jumpAid = this.request.aid;
			var jumpFt = 0;
			var jumpRef = window.location.href || parent.location.href;
			//链接
			var a = this.oCon.getElementsByTagName('a');
			for (var i = 0; i < a.length; i++) {
				a[i].index = i;
				a[i].onclick = function () {
					var isimg = 0;
					if (this.parentNode.parentNode.className != 'txt') {
						isimg = 1;
					};
					var urltemp = '&' + la + '&ch=wprc&l=click&ps=' + this.index + '&hid=' + hid + '&bkt=' + bkt + '&isimg=' + isimg + '&curl=' + encodeURIComponent(this.href);
					questImg(urltemp);
					if (jumpUrl) {
						window.open(jumpUrl + '&url=' + encodeURIComponent(this.href) + '&ref=' + encodeURIComponent(jumpRef) + '&aid=' + jumpAid + '&ft=' + jumpFt);
						return false;
					};
				};
			};

		}
	};
	//原型结束
	function Sys() {
		var Sys = {};
		var ua = navigator.userAgent.toLowerCase();
		var s;
		(s = ua.match(/msie ([\d.]+)/)) ? Sys.ie = s[1] :
			(s = ua.match(/firefox\/([\d.]+)/)) ? Sys.firefox = s[1] :
			(s = ua.match(/chrome\/([\d.]+)/)) ? Sys.chrome = s[1] :
			(s = ua.match(/opera.([\d.]+)/)) ? Sys.opera = s[1] :
			(s = ua.match(/version\/([\d.]+).*safari/)) ? Sys.safari = s[1] : 0;
		return Sys;
	};	var tuiFixedRun = new tuiFixed();
	//*********配置参数***************************
	tuiFixedRun.oBox = document.getElementById("aliyun_cnzz_tui_1000074377");
	tuiFixedRun.demo = false;
	
	tuiFixedRun.set = {"logo":{"logo_background_user":0,"logo_background_img":"","logo_position":1},"style":{"style_length":326,"style_width":602,"style_pic_col":2,"style_pic_row":2,"style_txt_col":0,"style_txt_row":0,"style_background_color":"ffffff","style_hover_color":"ffffff","style_border_color":"FFFFFF"},"pic":{"pic_length":100,"pic_width":150,"pic_scale":2,"pic_summary":1,"pic_summary_row":0,"pic_border":0},"hot":{"data_hot":0,"data_hot_num":"10","data_hot_txt":"\u4eba\u4f53\u827a\u672f,\u820c\u5c16\u4e0a\u7684\u6ce1\u9762,\u7cbd\u5b50\u7684\u5305\u6cd5,\u6559\u5e08\u5de5\u8d44\u6539\u9769\u65b9\u68482014,\u4eca\u65e5\u5173\u6ce8,2014\u6559\u5e08\u5de5\u8d44\u6539\u9769\u65b9\u6848,\u5218\u6c49,\u4eba\u4f53\u827a\u672f,\u820c\u5c16\u4e0a\u7684\u6ce1\u9762,\u7f8e\u5973\u56fe\u7247,\u9b54\u8b66\u7535\u5f71,X\u6218\u8b663,\u65e5\u672c\u519b\u673a,\u4e2d\u4fc4\u519b\u6f14,\u82cd\u4e95\u7a7a\u4e0d\u80fd\u751f\u80b2\u539f\u56e0,iPhone6","hot_body_background":"97CDA5","hot_body_size":"12","hot_body_bold":0,"hot_body_margin":"30","hot_body_family":3,"hot_default_color":"ffffff","hot_default_underline":0,"hot_hover_color":"000000","hot_hover_underline":0,"hot_click_color":"ff6600","hot_click_underline":0,"hot_clicked_color":"ff6600","hot_clicked_underline":0},"txt":{"txt_title_icon":"0","txt_title_txt":"\u731c\u4f60\u559c\u6b22","txt_title":1,"txt_title_size":12,"txt_title_bold":"1","txt_title_margin":"38","txt_title_background":0,"txt_title_bgcolor":"FFFFFF","txt_title_bgimage":"http:\/\/tui.cnzz.net\/templates\/images\/fix_txt_img\/51\/title.png","txt_title_color":"000000","txt_title_family":3,"txt_body_size":12,"txt_body_bold":0,"txt_body_margin":"24","txt_body_family":3,"txt_default_color":"535353","txt_hover_color":"0a8cd2","txt_click_color":"535353","txt_clicked_color":"535353","txt_default_underline":"0","txt_hover_underline":"0","txt_click_underline":"0","txt_clicked_underline":"0","txt_focus":2,"txt_border":0,"txt_link_target":0},"locat":{"locat_left_right":0,"locat_float":0,"locat_mark":0,"locat_color":0,"locat_txt_color":null,"locat_txt":null,"locat_background":null,"locat_float_hide":0},"slide":{"slide_type":0,"slide_timer":5,"change_icon":1,"change_title":1,"change_title_txt":"\u6362\u4e00\u6362","change_background_color":"0","change_icon_type":1,"change_title_size":"12","change_title_bold":0,"change_title_family":"arial","change_title_color":"333333","change_show_type":1,"focus_show_type":1,"focus_txt_background":"ff9010","focus_hover_background":"b4b4b4","focus_txt_family":0,"focus_txt_color":"ffffff"},"search":{"search_show":0,"search_high":0,"search_high_color":null},"keywords":{"images":null,"node":null,"txt_color":null,"underline":0,"number":0,"bold":0,"italic":0,"count":0,"node_text":null},"summary":{"txt_body_size":0,"txt_body_bold":0,"txt_body_family":0,"txt_default_color":null,"txt_hover_color":null,"txt_click_color":null,"txt_clicked_color":null,"txt_default_underline":0,"txt_hover_underline":0,"txt_click_underline":0,"txt_clicked_underline":0},"wap":{"auto_show":0},"base":{"cloud_id":"10088487","r_name":"\u6587\u7ae0\u5e95\u90e8\u63a8\u8350","r_type":"1","r_style_id":"51","r_style_name":"\u6a59\u8272\u5f8b\u52a8\u6807\u51c6\u7248","r_status":"1","data_type":"1","nyn_host":"4","domain_source":null,"r_radius":"0","img_type":"1","cnzz_code_id":"0","sf_deploy":0,"yn_host":"4"}};
	//列表数据
	tuiFixedRun.data = [{"url":"http:\/\/www.aiweibang.com\/yuedu\/2884920.html","title":"\uff1e\u793e\u4f1a\u4e07\u8c61: \u4ee5\u524d\u7684\u5973\u4eba\u8863\u670d\u7a7f\u5c11\u4e86.....\u592aTM\u7ecf\u5178\u4e86","algId":"2-hot","unigramlist":"","pagetime":"","from2hot":"true","thumbnail":"http:\/\/recimg.cn-hangzhou.oss.aliyun-inc.com\/26245F0D7F29475F49407C05597F34571D7E2840","has_thumb":"true","is_smart_thumb":"true","cluster_name":"hot_redis"},{"url":"http:\/\/www.aiweibang.com\/yuedu\/1337355.html","title":"\u7f8e\u5973: \u4f60\u88e4\u5b50\u91cc\u6709, \u800c\u6211\u7684\u88d9\u5b50\u91cc\u6ca1\u6709\u662f\u4ec0\u4e48?","algId":"2-hot","unigramlist":"","pagetime":"","from2hot":"true","thumbnail":"http:\/\/recimg.cn-hangzhou.oss.aliyun-inc.com\/2861415E6D5B515E0B40646109605E0C3E236700","has_thumb":"true","cluster_name":"hot_redis"},{"url":"http:\/\/www.aiweibang.com\/yuedu\/all?page=2&sort=2","title":"\u81ea\u5a92\u4f53\u4eba\u7684\u597d\u5e2e\u624b","algId":"2-hot","unigramlist":"","pagetime":"","from2hot":"true","thumbnail":"http:\/\/recimg.cn-hangzhou.oss.aliyun-inc.com\/720E0767501A1E764A005D023A505F27420F0840","has_thumb":"true","is_smart_thumb":"true","cluster_name":"hot_redis"},{"url":"http:\/\/www.aiweibang.com\/yuedu\/news\/2736610.html","title":"\u868c\u57e0\u4e00\u4f4d\u5c0f\u76c6\u53cb\u5929\u5929\u770b\u300a\u65b0\u95fb\u8054\u64ad\u300b, \u7ed3\u679c\u4ed6\u4f5c\u6587\u5199\u6210\u8fd9\u6837\u2026\u2026","algId":"2-hot","unigramlist":"","pagetime":"","from2hot":"true","thumbnail":"http:\/\/recimg.cn-hangzhou.oss.aliyun-inc.com\/4C3D690E1B366D562C006F70507A085616750340","cluster_name":"hot_redis"},{"url":"http:\/\/www.aiweibang.com\/yuedu\/nvren\/5920743.html","title":"\u2611\u4eac\u57ce\u8f89\u5b50\u54e5\u5f15\u53d1\u7684\u4e8b\u4ef6\u3002\u3002\u3002","algId":"2-hot","unigramlist":"","pagetime":"","from2hot":"true","thumbnail":"http:\/\/recimg.cn-hangzhou.oss.aliyun-inc.com\/6C29464D55057714220052245A5F675845723B00","has_thumb":"true","is_smart_thumb":"true","cluster_name":"hot_redis"},{"url":"http:\/\/www.aiweibang.com\/yuedu\/3122858.html","title":"\u4e2d\u7eaa\u59d4\u53c8\u67e5\u51fa\u4e00\u53ea\u66f4\u5927\u7684\u8001\u864e","algId":"2-hot","unigramlist":"","pagetime":"","from2hot":"true","thumbnail":"http:\/\/recimg.cn-hangzhou.oss.aliyun-inc.com\/17061E7D4A785727554005633E0124660F046840","has_thumb":"true","is_smart_thumb":"true","cluster_name":"hot_redis"},{"url":"http:\/\/www.aiweibang.com\/yuedu\/8010994.html","title":"\u6da8\u59ff\u52bf: \u5168\u7403\u5341\u5927\u6700\u5371\u9669\u822a\u7a7a\u516c\u53f8VS\u5168\u7403\u5341\u5927\u6700\u5b89\u5168\u822a\u7a7a\u516c\u53f8(\u7ec4\u56fe)","algId":"2-hot","unigramlist":"","pagetime":"","from2hot":"true","thumbnail":"http:\/\/recimg.cn-hangzhou.oss.aliyun-inc.com\/4A065A303024220921405108317B69150D0B0900","has_thumb":"true","cluster_name":"hot_redis"},{"url":"http:\/\/www.aiweibang.com\/yuedu\/7800129.html","title":"\u4e03\u65a4\u54e5\u8bde\u751f\u4e86, \u6311\u6218\u4e03\u65a4\u56fd\u7a96\u767d\u9152\uff01\u6a2a\u626b\u5168\u56fd\uff01(\u5b8c\u6574\u7248)","algId":"2-hot","unigramlist":"","pagetime":"","from2hot":"true","thumbnail":"http:\/\/recimg.cn-hangzhou.oss.aliyun-inc.com\/3D6F686C0521605015006B092722372C5F071200","has_thumb":"true","is_smart_thumb":"true","cluster_name":"hot_redis"},{"url":"http:\/\/www.aiweibang.com\/yuedu\/2181516.html","title":"\u59b9\u5b50, \u628a\u88e4\u5b50\u7a7f\u6210\u8fd9\u6837, \u662f\u6211\u4e0d\u61c2\u65f6\u5c1a\u5417\uff1f","algId":"2-hot","unigramlist":"","pagetime":"","from2hot":"true","thumbnail":"http:\/\/recimg.cn-hangzhou.oss.aliyun-inc.com\/4B750629530227633740341D261A5837671F7E40","has_thumb":"true","cluster_name":"hot_redis"},{"url":"http:\/\/www.aiweibang.com\/yuedu\/1406799.html","title":"\u5feb\u89c6\u9891\u4e28\u9ad8\u5927\u4e0a\u2014\u2014\u88f8\u4f53\u5b83\u662f\u4e00\u79cd\u827a\u672f, \u8ba9\u4e16\u754c\u540d\u753b\u6d3b\u8fc7\u6765","algId":"2-hot","unigramlist":"","pagetime":"","from2hot":"true","thumbnail":"http:\/\/recimg.cn-hangzhou.oss.aliyun-inc.com\/2E11341A53400F5F114048177A19522B565D5B40","has_thumb":"true","cluster_name":"hot_redis"},{"url":"http:\/\/www.aiweibang.com\/yuedu\/news\/597673.html","title":"\u7f8e\u5973\u88f8\u4f53\u505a\u753b, \u6b64\u5973\u8fd9\u662f\u8981\u706b\u554a","algId":"2-hot","unigramlist":"","pagetime":"","from2hot":"true","thumbnail":"http:\/\/recimg.cn-hangzhou.oss.aliyun-inc.com\/5D14545A5B4E0162054067002E235C2338526E00","has_thumb":"true","cluster_name":"hot_redis"},{"url":"http:\/\/www.aiweibang.com\/yuedu\/1384780.html","title":"\u563f\uff01\u59b9\u5b50\u522b\u8d70\uff01\u59b9\u5b50\uff01\u59b9......\u4f60\u59b9\uff01","algId":"2-hot","unigramlist":"","pagetime":"","from2hot":"true","thumbnail":"http:\/\/recimg.cn-hangzhou.oss.aliyun-inc.com\/5120223C1E4970652800025530351C5A461E2D40","has_thumb":"true","cluster_name":"hot_redis"},{"url":"http:\/\/www.aiweibang.com\/yuedu\/2116662.html","title":"\u7f8e\u5973\u8db4\u7740\u65e5\u5149\u6d74, \u8272\u72d7\u5c45\u7136\u8dd1\u6765\u3002\u3002\u3002\u3002\u3002\u3002","algId":"2-hot","unigramlist":"","pagetime":"","from2hot":"true","thumbnail":"http:\/\/recimg.cn-hangzhou.oss.aliyun-inc.com\/6C32740B7D521B3C73005225621D733831516640","has_thumb":"true","cluster_name":"hot_redis"},{"url":"http:\/\/www.aiweibang.com\/yuedu\/1664671.html","title":"\u88f8\u4f53\u7684\u79d8\u5bc6\u4f60\u518d\u4e0d\u770b\u5c31\u665a\u4e86","algId":"2-hot","unigramlist":"","pagetime":"","from2hot":"true","thumbnail":"http:\/\/recimg.cn-hangzhou.oss.aliyun-inc.com\/0A12303212482721234027095825137E4C027140","has_thumb":"true","cluster_name":"hot_redis"},{"url":"http:\/\/www.aiweibang.com\/yuedu\/516172.html","title":"\u7f8e\u5973\u5185\u88e4\u90fd\u88abPS\u6389\u4e86, \u7537\u4eba\u4e0d\u770b\u540e\u6094\u554a","algId":"2-hot","unigramlist":"","pagetime":"","from2hot":"true","thumbnail":"http:\/\/recimg.cn-hangzhou.oss.aliyun-inc.com\/3A212C250F6F141B4240782D4B5D673A0E7B7640","has_thumb":"true","cluster_name":"hot_redis"}];
	//处理热词
	tuiFixedRun.set.hot.data_hot_txt = "";
	//自定义热词数量
	tuiFixedRun.set.hot.data_hot_txt_user = 0;
	//图片loading
	tuiFixedRun.imgLoad = "http://img.cnzz.net/adt/cnzz_tui/vip/loading.gif";
	//错误图片
	tuiFixedRun.errorDir = "http://img.cnzz.net/adt/cnzz_tui/vip/error/error_";
	//logo 点击地址
	tuiFixedRun.tuiUrl = "http://tui.cnzz.com";
	//统计地址
	tuiFixedRun.tongjiUrl = "http://log.so.cnzz.net/stat.php?";
	tuiFixedRun.errorNum = 35;
	//搜索地址
	tuiFixedRun.searchUrl = "http://s.cnzz.net/";
	//图片路径
	tuiFixedRun.imgDir = "http://img.cnzz.net/adt/cnzz_tui/vip/";
	//ip
	tuiFixedRun.ip = "222.44.84.4";
	//cookie
	tuiFixedRun.Rcookie = "6df4dbf59423f6298ee2b0423a8fb37a";
	//jumpUrl
    tuiFixedRun.jumpUrl = "http://tui.cnzz.net/redirect.php?pf=t";
    //是否米尔用户
    tuiFixedRun.isMier  = "0";
	//公用方法
	function getEyeJsStyle(oBj, styleName) {
		if (oBj.currentStyle) {
			return oBj.currentStyle[styleName];
		} else {
			return getComputedStyle(oBj, null)[styleName];
		};
	};
	function addEvent(Elem, type, handle) {
		if (Elem.addEventListener) {
			Elem.addEventListener(type, handle, false);
		} else if (Elem.attachEvent) {
			Elem.attachEvent("on" + type, handle);
		};
	};
	function getElemPos(obj) {
		var pos = {
			"top" : 0,
			"left" : 0
		};
		if (obj.offsetParent) {
			while (obj.offsetParent) {
				pos.top += obj.offsetTop;
				pos.left += obj.offsetLeft;
				obj = obj.offsetParent;
			}
		} else if (obj.x) {
			pos.left += obj.x;
		} else if (obj.x) {
			pos.top += obj.y;
		}
		return {
			x : pos.left,
			y : pos.top
		};
	};
	if (tuiFixedRun["set"]["hot"]["data_hot"]!=0 && tuiFixedRun["set"]["hot"]["data_hot_txt"]!="") {
		tuiFixedRun.ft = "block_kw";
	} else{
		tuiFixedRun.ft = "block_s";
	};
	tuiFixedRun.request = {
		"common" : tuiFixedRun.tongjiUrl+"ip="+tuiFixedRun.ip+"&pui=czb&cok="+tuiFixedRun.Rcookie+"&vr=1&aid=1000074377&sid=aiweibang.com&img=" + tuiFixedRun["set"]["base"]["data_type"] + "&so=t&ft=" + tuiFixedRun.ft + "",
		"sid" : "aiweibang.com",
		"aid" : "1000074377",
		"hid" : "b1be1afef3fc2bac3744b1a2887c254d",
		"bkt" : "0",
		"so" : "t"
	};
	function questImg(url) {
		var Img = new Image();
		var d = new Date();
		Img.onload = Img.onabort = Img.onerror = function () {
			Img = null;
		};
		Img.src = tuiFixedRun.request.common + url + "&"+ encodeURIComponent(String.fromCharCode(1)) + "&oref=" + encodeURIComponent(document.referrer) + "&purl=" + encodeURIComponent(window.location.href) +"&_rnd=" + (Date.parse(d) + "." + d.getMilliseconds());
	};
	function checkData() {
		return true;
		var t = 0;		//总数
		var dt = 0;		//总需
		var r = false;	//结果
		var n = 0.6;	//良品率
		var set = tuiFixedRun.set;
		//计算总需
		if (set.base.data_type == 0) {
			dt = Number(set.style.style_txt_col) * Number(set.style.style_txt_row);
		}else if (set.base.data_type == 2) {
			dt = Number(set.style.style_pic_col) * Number(set.style.style_pic_row);
		}else {
			dt = Number(set.style.style_pic_col) * Number(set.style.style_pic_row) + Number(set.style.style_txt_col) * Number(set.style.style_txt_row);
		};
		if (tuiFixedRun.data.length < dt * n){
			return false;
		}else {
			//计算良好数据
			for (var i=0;i<tuiFixedRun.data.length;i++) {
				if (tuiFixedRun.data[i].title) {
					t++;
				};
			};
			var l = t / dt;
			l < n ? r = false : r = true;
			return r;
		};
	};
	//*********************************
	if (!tuiFixedRun.demo) {
		//运行之
		if (tuiFixedRun.data && tuiFixedRun.data[0]) {
			if (checkData()) {
				tuiFixedRun.init();
			}else {
				var url =  "&" + encodeURIComponent(String.fromCharCode(1)) + "&has=false&ch=wprdsp&l=view&good=false";
				questImg(url)
			};
		} else {
			var url =  "&" + encodeURIComponent(String.fromCharCode(1)) + "&has=false&ch=wprdsp&l=view";
			questImg(url)
		};
	};
	//*********配置参数***************************
	
(function(){
	function setMyAcookie(){
		var taobaoImg = new Image(), id = "img" + +new Date;
		window.loog = window.loog || {};
		taobaoImg.id = id;
		window.loog[id] = taobaoImg;
		taobaoImg.onload = taobaoImg.onerror = taobaoImg.onabort = function () {
			window.loog[id] = undefined;
		}
		window.loog[id] = taobaoImg;
		taobaoImg.src = "http://cnzz.mmstat.com/9a.gif";
	}		
	window.setTimeout(setMyAcookie,0);	
})();
})();