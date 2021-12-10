function Class(){}function capitalize(e){return e.charAt(0).toUpperCase()+e.slice(1)}function isNumeric(e){return!isNaN(parseFloat(e))&&isFinite(e)}Class.prototype.construct=function(){},Class.__asMethod__=function(i,n){return function(){var e=this.$;this.$=n;var t=i.apply(this,arguments);return this.$=e,t}},Class.extend=function(e){function t(){arguments[0]!==Class&&this.construct.apply(this,arguments)}var i,n=new this(Class),a=this.prototype;for(i in e){var r=e[i];r instanceof Function&&(r=Class.__asMethod__(r,a)),n[i]=r}return n.$=a,t.prototype=n,t.extend=this.extend,t},$.fn.highlight=function(e){return this.length&&e&&e.length?this.each(function(){!function e(t,i){var n=0;if(3==t.nodeType){var a,r,o=t.data.toUpperCase().indexOf(i);0<=(o-=t.data.substr(0,o).toUpperCase().length-t.data.substr(0,o).length)&&((a=document.createElement("span")).className="highlight",(r=t.splitText(o)).splitText(i.length),o=r.cloneNode(!0),a.appendChild(o),r.parentNode.replaceChild(a,r),n=1)}else if(1==t.nodeType&&t.childNodes&&!/(script|style)/i.test(t.tagName))for(var s=0;s<t.childNodes.length;++s)s+=e(t.childNodes[s],i);return n}(this,e.toUpperCase())}):this},$.fn.searchAble=function(n,a){var r=0,o=this,s=o.next(".glyphicon-remove");s.click(function(){o.val(""),o.trigger("keyup")}),o.on("keyup",function(e){r++;var t=o.val(),i=r;""==t?s.hide():s.show(),e.keyCode!=keyCode.ENTER?setTimeout(function(){i===r&&n(t)},void 0===a?500:a):n(t)})},$.fn.serializeObject=function(){var e={},t=this.serializeArray();return $.each(t,function(){void 0!==e[this.name]?(e[this.name].push||(e[this.name]=[e[this.name]]),e[this.name].push(this.value||"")):e[this.name]=this.value||""}),e};var keyCode={BACKSPACE:8,COMMA:188,DELETE:46,DOWN:40,END:35,ENTER:13,ESCAPE:27,HOME:36,LEFT:37,CTRL:17,PAGE_DOWN:34,PAGE_UP:33,PERIOD:190,RIGHT:39,SPACE:32,TAB:9,UP:38,SHIFT:16,S:83,COMMAND:91},KikCmsClass=Class.extend({baseUri:null,translations:{},errorMessages:{},isDev:!1,maxFileUploads:null,maxFileSize:null,maxFileSizeString:null,windowManager:null,renderables:{},init:function(){"undefined"!=typeof moment&&moment.locale($("html").attr("lang"));var e,t=JSON.parse($("#kikCmsJsSettings").text());for(e in t)this[e]=t[e];$("body").on("mouseover",".tt-suggestion",function(){$(".tt-suggestion").removeClass("tt-cursor"),$(this).addClass("tt-cursor")}),"undefined"!=typeof WindowManager&&(this.windowManager=new WindowManager)},initRenderables:function(n){var a=this;n=void 0!==n?n:null,$("[data-renderable]").each(function(){var e,i,t=$(this);"true"!=t.attr("data-rendered")&&(e=$.parseJSON(t.attr("data-renderable")),i=e.properties.renderableInstance,a.renderables[i]=new window[e.class],$.each(e.properties,function(e,t){a.renderables[i][e]=t}),n&&(a.renderables[i].parent=n),a.renderables[i].init(),t.attr("data-rendered",!0))})},action:function(e,t,n,i,a,r){var o=!1,s=this,l=0;r=void 0!==r?r:null,setTimeout(function(){0==o&&KikCMS.showLoader()},250);var c=$("html").attr("lang");c&&(t.activeLangCode=c);var d={url:e,type:"post",dataType:"json",data:t,cache:!1,success:function(e,t,i){o=!0,s.hideLoader(),n(e,t,i),s.initRenderables(r)},error:function(e){if(0==e.readyState&&0==e.status&&l<2)return l++,void u();o=!0,s.showError(e,i)}};void 0!==a&&a&&(d.cache=!1,d.contentType=!1,d.processData=!1,d.xhr=a);var u=function(){$.ajax(d)};u()},getSecurityToken:function(t){KikCMS.action("/cms/generate-security-token",{},function(e){t(e)})},showError:function(e,t){void 0!==t&&t(),this.hideLoader(),this.isDev&&440!=e.status?$("#ajaxDebugger").html(e.responseText).show():alert(e.responseJSON.title+"\n\n"+e.responseJSON.description)},showLoader:function(){this.getLoader().addClass("show")},hideLoader:function(){this.getLoader().removeClass("show")},getLoader:function(){return $("#cmsLoader")},removeExtension:function(e){return e.replace(/\.[^/.]+$/,"")},tl:function(e,t){var i=this.translations[e];return $.each(t,function(e,t){i=i.replace(new RegExp(":"+e,"g"),t)}),i},toSlug:function(e){e=e.replace(/^\s+|\s+$/g,"").toLowerCase();for(var t="àáäâèéëêìíïîòóöôùúüûñç·/_,:;",i=0,n=t.length;i<n;i++)e=e.replace(new RegExp(t.charAt(i),"g"),"aaaaeeeeiiiioooouuuunc------".charAt(i));return e=e.replace(/[^a-z0-9 -]/g,"").replace(/\s+/g,"-").replace(/-+/g,"-")}}),KikCMS=new KikCmsClass;$(function(){KikCMS.init(),KikCMS.initRenderables()});var WebForm=Class.extend({renderableInstance:null,renderableClass:null,parent:null,actionPreview:function(e,t,i,n){var a=e.find(".preview"),r=e.find(".preview .thumb"),o=e.find(".buttons .pick"),s=e.find(".buttons .delete"),l=e.find(".filename");i.dimensions?(r.css("width",i.dimensions[0]/2),r.css("height",i.dimensions[1]/2)):(r.css("width","auto"),r.css("height","auto")),a.removeClass("hidden"),r.html(i.preview),l.html("("+i.name+")"),e.find(" > input[type=hidden].fileId").val(t),o.addClass("hidden"),s.removeClass("hidden"),void 0!==n&&n()},createFilePicker:function(t){var i=this;return new FilePicker(this.renderableInstance,this.getWebForm(),function(e){i.onPickFile(e,t)})},init:function(){this.initAutocompleteFields(),this.initDateFields(),this.initFileFields(),this.initWysiwyg(),this.initPopovers(),this.initCsrf()},initAutocompleteFields:function(){var n=this;this.getWebForm().find(".autocomplete").each(function(){var t=$(this),e=t.attr("data-field-key"),i=t.attr("data-route"),e={field:e,renderableInstance:n.renderableInstance,renderableClass:n.renderableClass};KikCMS.action(i,e,function(e){n.initAutocompleteData(e,t)})})},initAutocompleteData:function(e,t){var a;t.typeahead({hint:!0,highlight:!0},{limit:10,source:(a=e,function(e,t){var i=[],n=new RegExp(e,"i");$.each(a,function(e,t){n.test(t)&&i.push(t)}),t(i)})})},initCsrf:function(){var n=this;setTimeout(function(){KikCMS.action("/webform/token/",{},function(e){var t=e[0],i='<input type="hidden" name="'+t+'" value="'+e[1]+'" />',e=n.getWebForm().find("form");e.find("input[name="+t+"]").length||e.append(i)})},1500)},initDateFields:function(){this.getWebForm().find(".type-date input").each(function(){var e,t,i=$(this);i.datetimepicker({format:i.attr("data-format"),locale:moment.locale(),useCurrent:!1}),i.attr("data-default-date")&&(e=i.val(),t=moment(i.attr("data-default-date"),i.attr("data-format")),i.datetimepicker("defaultDate",t),e||i.val(""))})},initFileFields:function(){var s=this;this.getWebForm().find(".type-file").each(function(){var e=$(this),t=e.find(".file-picker"),i=e.find(".btn.upload"),n=e.find(".btn.delete"),a=e.find(".btn.pick"),r=e.find(".btn.preview"),o=e.find(".btn.pick, .btn.preview");s.initUploader(e),n.click(function(){e.find(".filename").html(""),e.find(" > input[type=hidden].fileId").val(""),a.removeClass("hidden"),n.addClass("hidden"),r.find("img").remove(),r.addClass("hidden")}),o.click(function(){if(0!=$(this).attr("data-finder")){if(1<=t.find(".finder").length)return t.slideToggle(),void i.toggleClass("disabled");s.filePicker=s.createFilePicker(e),s.filePicker.open()}})})},initPopovers:function(){this.getWebForm().find('[data-toggle="popover"]').each(function(){var e=$(this).attr("data-content");$(this).popover({placement:"auto bottom",html:!0,content:e,container:"body"})})},initTinyMCE:function(){var t=this;tinymce.init({selector:this.getWysiwygSelector(),setup:function(e){e.on("change",function(){tinymce.triggerSave()})},language:KikCMS.tl("system.langCode"),relative_urls:!1,remove_script_host:!0,branding:!1,elementpath:!1,document_base_url:KikCMS.baseUri,toolbar:"undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | outdent indent | bullist numlist",plugins:["advlist autolink lists link image charmap print preview hr anchor pagebreak searchreplace visualblocks","visualchars code insertdatetime media nonbreaking save table directionality template paste","textpattern codesample toc"],image_advtab:!0,content_css:["/cmsassets/css/tinymce_content.css"],link_list:this.getLinkListUrl(),file_picker_callback:function(e){t.getFilePicker(e)}})},initUploader:function(t){var i=this;new FileUploader({$container:t,action:"/cms/webform/uploadAndPreview",addParametersBeforeUpload:function(e){return e.append("folderId",t.find(".btn.upload").attr("data-folder-id")),e.append("renderableInstance",i.renderableInstance),e.append("renderableClass",i.renderableClass),e},onSuccess:function(e){e.fileId&&i.actionPreview(t,e.fileId,e)}}).init()},initWysiwyg:function(){var e,t=this;0!=$(this.getWysiwygSelector()).length&&("undefined"==typeof tinymce?(e="https://cdn.tiny.cloud/1/"+KikCMS.tinyMceApiKey+"/tinymce/5",$.getScript(e+"/tinymce.min.js",function(){window.tinymce.dom.Event.domLoaded=!0,tinymce.baseURL=e,tinymce.suffix=".min",t.initTinyMCE()})):this.initTinyMCE())},getFilePicker:function(i){function t(t){var e=t.attr("data-id");KikCMS.action("/cms/file/url/"+e,{},function(e){i(e.url,{alt:t.find(".name span").text()}),n.close()})}var e=this.getWindowHeight()<768?this.getWindowHeight()-130:768,n=tinymce.activeEditor.windowManager.openUrl({title:"Image Picker",url:"/cms/filePicker",width:952,height:e,buttons:[{type:"cancel",text:"Close",onclick:"close"},{text:"Insert",type:"custom",onclick:function(){var e=$(n.$el).find("iframe")[0].contentWindow.$(".filePicker").find(".file.selected");if(!e.length)return!1;t(e)}}]});$(".tox-navobj iframe").on("load",function(){this.contentWindow.$(".filePicker").on("pick",".file",function(){t($(this))})})},getLinkListUrl:function(){var e="/cms/getTinyMceLinks/";return this.parent&&this.parent.getWindowLanguageCode()?e+this.parent.getWindowLanguageCode()+"/":e},getWebForm:function(){return $("[data-instance="+this.renderableInstance+"]")},getWindowHeight:function(){return $(window).height()},getWysiwygSelector:function(){return"#"+this.getWebForm().attr("id")+" textarea.wysiwyg"},getUploadButtonForFileField:function(e){return e.find(".btn.upload")},onPickFile:function(e,t){e.removeClass("selected"),this.pickFile(e.data("id"),t)},pickFile:function(t,i){var n=this;this.getUploadButtonForFileField(i).removeClass("disabled"),KikCMS.action("/cms/webform/filepreview/"+t,{},function(e){n.actionPreview(i,t,e)})},removeExtension:function(e){return e.replace(/\.[^/.]+$/,"")}});