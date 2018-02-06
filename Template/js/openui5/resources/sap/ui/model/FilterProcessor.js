/*!
 * UI development toolkit for HTML5 (OpenUI5)
 * (c) Copyright 2009-2017 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(['./Filter','jquery.sap.global',"jquery.sap.unicode"],function(F,q){"use strict";var a={};a.apply=function(d,f,g){if(!d){return[];}else if(!f||f.length==0){return d.slice();}var t=this,o={},b,c=[],G=false,e=true;q.each(f,function(j,h){if(h.sPath!==undefined){b=o[h.sPath];if(!b){b=o[h.sPath]=[];}}else{b=o["__multiFilter"];if(!b){b=o["__multiFilter"]=[];}}b.push(h);});q.each(d,function(i,r){e=true;q.each(o,function(p,b){if(p!=="__multiFilter"){G=false;q.each(b,function(j,h){var v=g(r,p),T=t.getFilterFunction(h);if(!h.fnCompare){v=t.normalizeFilterValue(v);}if(v!==undefined&&T(v)){G=true;return false;}});}else{G=false;q.each(b,function(j,h){G=t._resolveMultiFilter(h,r,g);if(G){return false;}});}if(!G){e=false;return false;}});if(e){c.push(r);}});return c;};a.normalizeFilterValue=function(v){if(typeof v=="string"){if(String.prototype.normalize&&(sap.ui.Device.browser.msie||sap.ui.Device.browser.edge)){v=v.normalize("NFD");}v=v.toUpperCase();if(String.prototype.normalize){v=v.normalize("NFC");}return v;}if(v instanceof Date){return v.getTime();}return v;};a._resolveMultiFilter=function(m,r,g){var t=this,M=!!m.bAnd,f=m.aFilters;if(f){q.each(f,function(i,o){var l=false;if(o._bMultiFilter){l=t._resolveMultiFilter(o,r,g);}else if(o.sPath!==undefined){var v=g(r,o.sPath),T=t.getFilterFunction(o);if(!o.fnCompare){v=t.normalizeFilterValue(v);}if(v!==undefined&&T(v)){l=true;}}if(l!==M){M=l;return false;}});}return M;};a.getFilterFunction=function(f){if(f.fnTest){return f.fnTest;}var v=f.oValue1,V=f.oValue2,c=f.fnCompare||F.defaultComparator;if(!f.fnCompare){v=this.normalizeFilterValue(v);V=this.normalizeFilterValue(V);}switch(f.sOperator){case"EQ":f.fnTest=function(b){return c(b,v)===0;};break;case"NE":f.fnTest=function(b){return c(b,v)!==0;};break;case"LT":f.fnTest=function(b){return c(b,v)<0;};break;case"LE":f.fnTest=function(b){return c(b,v)<=0;};break;case"GT":f.fnTest=function(b){return c(b,v)>0;};break;case"GE":f.fnTest=function(b){return c(b,v)>=0;};break;case"BT":f.fnTest=function(b){return(c(b,v)>=0)&&(c(b,V)<=0);};break;case"Contains":f.fnTest=function(b){if(b==null){return false;}if(typeof b!="string"){throw new Error("Only \"String\" values are supported for the FilterOperator: \"Contains\".");}return b.indexOf(v)!=-1;};break;case"StartsWith":f.fnTest=function(b){if(b==null){return false;}if(typeof b!="string"){throw new Error("Only \"String\" values are supported for the FilterOperator: \"StartsWith\".");}return b.indexOf(v)==0;};break;case"EndsWith":f.fnTest=function(b){if(b==null){return false;}if(typeof b!="string"){throw new Error("Only \"String\" values are supported for the FilterOperator: \"EndsWith\".");}var p=b.lastIndexOf(v);if(p==-1){return false;}return p==b.length-new String(f.oValue1).length;};break;default:q.sap.log.error("The filter operator \""+f.sOperator+"\" is unknown, filter will be ignored.");f.fnTest=function(b){return true;};}return f.fnTest;};return a;});
