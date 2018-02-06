/*!
 * UI development toolkit for HTML5 (OpenUI5)
 * (c) Copyright 2009-2017 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(['sap/ui/core/Control','./library','jquery.sap.global','sap/ui/core/ResizeHandler'],function(C,l,q,R){"use strict";var B=C.extend("sap.ui.layout.BlockLayout",{metadata:{library:"sap.ui.layout",properties:{background:{type:"sap.ui.layout.BlockBackgroundType",group:"Appearance",defaultValue:"Default"}},defaultAggregation:"content",aggregations:{content:{type:"sap.ui.layout.BlockLayoutRow",multiple:true}},designTime:true}});B.CONSTANTS={SIZES:{S:600,M:1024,L:1440,XL:null}};B.prototype.init=function(){this._currentBreakpoint=null;};B.prototype.onBeforeRendering=function(){this._detachResizeHandler();};B.prototype.onAfterRendering=function(){this._onParentResize();this._notifySizeListeners();};B.prototype.setBackground=function(n){var c=this.getBackground(),o=C.prototype.setProperty.apply(this,["background"].concat(Array.prototype.slice.call(arguments)));if(this.hasStyleClass("sapUiBlockLayoutBackground"+c)){this.removeStyleClass("sapUiBlockLayoutBackground"+c,true);}n=n?n:"Default";this.addStyleClass("sapUiBlockLayoutBackground"+n,true);this.invalidate();return o;};B.prototype._onParentResize=function(){var p,d=this.getDomRef(),w=d.clientWidth,s=B.CONSTANTS.SIZES;this._detachResizeHandler();if(w>0){this._removeBreakpointClasses();for(p in s){if(s.hasOwnProperty(p)&&(s[p]===null||s[p]>w)){if(this._currentBreakpoint!=p){this._currentBreakpoint=p;this._notifySizeListeners();}this.addStyleClass("sapUiBlockLayoutSize"+p,true);break;}}}this._attachResizeHandler();};B.prototype._notifySizeListeners=function(){var t=this;this.getContent().forEach(function(r){r._onParentSizeChange(t._currentBreakpoint);});};B.prototype._removeBreakpointClasses=function(){var s=B.CONSTANTS.SIZES;for(var p in s){if(s.hasOwnProperty(p)){this.removeStyleClass("sapUiBlockLayoutSize"+p,true);}}};B.prototype._attachResizeHandler=function(){if(!this._parentResizeHandler){this._parentResizeHandler=R.register(this,this._onParentResize.bind(this));}};B.prototype._detachResizeHandler=function(){if(this._parentResizeHandler){R.deregister(this._parentResizeHandler);this._parentResizeHandler=null;}};B.prototype.exit=function(){this._detachResizeHandler();};return B;});
