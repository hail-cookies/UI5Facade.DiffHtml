/*!
 * UI development toolkit for HTML5 (OpenUI5)
 * (c) Copyright 2009-2017 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(["jquery.sap.global","./library","sap/ui/core/Control","sap/ui/Device","sap/m/PDFViewerRenderManager","sap/m/MessageBox"],function(q,l,C,D,P,M){"use strict";var a=Object.freeze(["application/pdf","application/x-google-chrome-pdf"]);function i(m){var f=a.indexOf(m);return f>-1;}var b=C.extend("sap.m.PDFViewer",{metadata:{library:"sap.m",properties:{height:{type:"sap.ui.core.CSSSize",group:"Dimension",defaultValue:"100%"},width:{type:"sap.ui.core.CSSSize",group:"Dimension",defaultValue:"100%"},source:{type:"sap.ui.core.URI",group:"Misc",defaultValue:null},errorMessage:{type:"string",group:"Misc",defaultValue:null,deprecated:true},errorPlaceholderMessage:{type:"string",group:"Misc",defaultValue:null},popupHeaderTitle:{type:"string",group:"Misc",defaultValue:null,deprecated:true},title:{type:"string",group:"Misc",defaultValue:null},showDownloadButton:{type:"boolean",group:"Misc",defaultValue:true}},aggregations:{errorPlaceholder:{type:"sap.ui.core.Control",multiple:false},popupButtons:{type:"sap.m.Button",multiple:true,singularName:"popupButton"}},events:{loaded:{},error:{},sourceValidationFailed:{}}}});b._isPdfPluginEnabled=function(){var I=true;if(D.browser.firefox){return I;}if(D.browser.internet_explorer){try{new ActiveXObject("AcroPDF.PDF");}catch(e){I=false;}return I;}var m=navigator.mimeTypes;I=a.some(function(A){var o=m.namedItem(A);return o!==null;});return I;};b.prototype.init=function(){this._objectsRegister={};this._bIsPopupOpen=false;this._initPopupControl();this._initPopupDownloadButtonControl();this._initPlaceholderMessagePageControl();this._initToolbarDownloadButtonControl();this._initOverflowToolbarControl();this._initControlState();};b.prototype._initControlState=function(){this._bRenderPdfContent=true;this._bOnBeforeUnloadFired=false;};b.prototype.setWidth=function(w){this.setProperty("width",w,true);var d=this.$();if(d===null){return this;}d.css("width",this._getRenderWidth());return this;};b.prototype.setHeight=function(h){this.setProperty("height",h,true);var d=this.$();if(d===null){return this;}d.css("height",this._getRenderHeight());return this;};b.prototype.onBeforeRendering=function(){this._bOnBeforeUnloadFired=false;};b.prototype.onAfterRendering=function(){var I=function(){var o=this._getIframeDOMElement();var c=q(o.get(0).contentWindow);if(D.browser.internet_explorer){c.on("beforeunload",this._onBeforeUnloadListener.bind(this));c.on("readystatechange",this._onReadyStateChangeListener.bind(this));o.on("load",this._onLoadIEListener.bind(this));}else{o.on("load",this._onLoadListener.bind(this));}o.on("error",this._onErrorListener.bind(this));var p=this.getSource();var d=this.getSource().indexOf("#");if(d>-1){p=p.substr(0,d);}p+="#view=FitH";if(!q.sap.validateUrl(p)){p=encodeURI(p);}if(q.sap.validateUrl(p)){o.attr("src",p);}else{this._fireErrorEvent();}}.bind(this);try{this.setBusy(true);I();}catch(e){this.setBusy(false);}};b.prototype._fireErrorEvent=function(){this._renderErrorState();this.fireEvent("error",{},true);};b.prototype._renderErrorState=function(){var d=this._objectsRegister.getToolbarDownloadButtonControl();d.setEnabled(false);var d=this._objectsRegister.getPopupDownloadButtonControl();d.setEnabled(false);this.setBusy(false);this._bRenderPdfContent=false;C.prototype.invalidate.call(this);};b.prototype._fireLoadedEvent=function(){this._bRenderPdfContent=true;this.setBusy(false);try{this._getIframeDOMElement().removeClass("sapMPDFViewerLoading");}catch(e){q.log.fatal("Iframe not founded in loaded event");q.log.fatal(e);}this.fireEvent("loaded");};b.prototype._onLoadListener=function(e){try{var t=q(e.target),c=true;var s="application/pdf";try{var E=t[0].contentWindow.document.embeds;c=!!E&&E.length===1;if(c){s=E[0].attributes.getNamedItem("type").value;}}catch(d){if(!D.browser.firefox&&this.fireEvent("sourceValidationFailed",{},true)){this._showMessageBox();return;}}if(c&&i(s)){this._fireLoadedEvent();}else{this._fireErrorEvent();}}catch(d){q.sap.log.fatal(false,"Fatal error during the handling of load event happened.");q.sap.log.fatal(false,d.message);}};b.prototype._onErrorListener=function(){this._fireErrorEvent();};b.prototype._onReadyStateChangeListener=function(e){var I="interactive";var c="complete";switch(e.target.readyState){case I:case c:this._fireLoadedEvent();break;}};b.prototype._onBeforeUnloadListener=function(){if(this._bOnBeforeUnloadFired){this._fireErrorEvent();return;}this._bOnBeforeUnloadFired=true;};b.prototype._onLoadIEListener=function(e){try{var c=e.currentTarget.contentWindow.document.mimeType;}catch(d){return;}if(!i(c)){this._fireErrorEvent();}};b.prototype.downloadPDF=function(){var w=window.open(this.getSource());w.focus();};b.prototype._onSourceValidationErrorMessageBoxCloseListener=function(c){if(c===M.Action.CANCEL){this._renderErrorState();}else{this._fireLoadedEvent();}};b.prototype._onAfterPopupClose=function(e){var p=this._objectsRegister.getPopup();p.removeAllContent();this._bIsPopupOpen=false;};b.prototype._shouldRenderPdfContent=function(){return b._isPdfPluginEnabled()&&this._bRenderPdfContent&&this.getSource()!==null;};b.prototype._isSourceValidToDisplay=function(){var s=this.getSource();return s!==null&&s!==""&&typeof s!=="undefined";};b.prototype.invalidate=function(o){this._initControlState();C.prototype.invalidate.call(this,o);};b.prototype.open=function(){if(!this._isSourceValidToDisplay()){return;}if(this._isEmbeddedModeAllowed()){this._openOnDesktop();}else{this._openOnMobile();}};b.prototype._openOnDesktop=function(){var p=this._objectsRegister.getPopup();if(this._bIsPopupOpen){return;}this._initControlState();this._preparePopup(p);p.addContent(this);this._bIsPopupOpen=true;p.open();};b.prototype._openOnMobile=function(){var w=window.open(this.getSource());w.focus();};b.prototype._getIframeDOMElement=function(){var I=this.$().find("iframe");if(I.length===0){throw Error("Underlying iframe was not found in DOM.");}if(I.length>1){q.sap.log.fatal("Initialization of iframe fails. Reason: the control somehow renders multiple iframes");}return I;};b.prototype._isEmbeddedModeAllowed=function(){return D.system.desktop;};b.prototype._getLibraryResourceBundle=function(){return sap.ui.getCore().getLibraryResourceBundle("sap.m");};b.prototype._getMessagePageErrorMessage=function(){return this.getErrorPlaceholderMessage()?this.getErrorPlaceholderMessage():this._getLibraryResourceBundle().getText("PDF_VIEWER_PLACEHOLDER_ERROR_TEXT");};b.prototype._getRenderWidth=function(){return this._bIsPopupOpen?'100%':this.getWidth();};b.prototype._getRenderHeight=function(){return this._bIsPopupOpen?'100%':this.getHeight();};b.prototype._showMessageBox=function(){M.show(this._getLibraryResourceBundle().getText("PDF_VIEWER_SOURCE_VALIDATION_MESSAGE_TEXT"),{icon:M.Icon.WARNING,title:this._getLibraryResourceBundle().getText("PDF_VIEWER_SOURCE_VALIDATION_MESSAGE_HEADER"),actions:[M.Action.OK,M.Action.CANCEL],defaultAction:M.Action.CANCEL,id:this.getId()+"-validationErrorSourceMessageBox",styleClass:"sapUiSizeCompact",contentWidth:'100px',onClose:this._onSourceValidationErrorMessageBoxCloseListener.bind(this)});};b.prototype.exit=function(){q.each(this._objectsRegister,function(I,g){var o=g(true);if(o){o.destroy();}});};P.extendPdfViewer(b);return b;});
