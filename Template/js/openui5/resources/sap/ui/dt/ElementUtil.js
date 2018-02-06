/*!
 * UI development toolkit for HTML5 (OpenUI5)
 * (c) Copyright 2009-2017 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(['jquery.sap.global'],function(q){"use strict";var E={};E.sACTION_MOVE='move';E.sACTION_CUT='cut';E.sACTION_PASTE='paste';E.sREORDER_AGGREGATION='reorder_aggregation';E.iterateOverElements=function(e,c){if(e&&e.length){for(var i=0;i<e.length;i++){var o=e[i];if(o instanceof sap.ui.base.ManagedObject){c(o);}}}else if(e instanceof sap.ui.base.ManagedObject){c(e);}};E.iterateOverAllPublicAggregations=function(e,c){var a=e.getMetadata().getAllAggregations();var A=Object.keys(a);A.forEach(function(s){var o=a[s];var v=this.getAggregation(e,s);c(o,v);},this);};E.getElementInstance=function(e){if(typeof e==="string"){var o=sap.ui.getCore().byId(e);return o||sap.ui.getCore().getComponent(e);}else{return e;}};E.hasAncestor=function(e,a){a=this.fixComponentContainerElement(a);var p=this.fixComponentParent(e);while(p&&p!==a){p=p.getParent();p=this.fixComponentParent(p);}return!!p;};E.getClosestElementForNode=function(n){var c=q(n).closest("[data-sap-ui]");return c.length?sap.ui.getCore().byId(c.data("sap-ui")):undefined;};E.getClosestElementOfType=function(s,t){var e=s;while(e&&!this.isInstanceOf(e,t)){e=e.getParent();}return e;};E.fixComponentParent=function(e){if(this.isInstanceOf(e,"sap.ui.core.UIComponent")){var c=e.oContainer;if(c){return c.getParent();}}else{return e;}};E.fixComponentContainerElement=function(e){if(this.isInstanceOf(e,"sap.ui.core.ComponentContainer")){if(!e.getComponentInstance()){return;}return e.getComponentInstance().getRootControl();}else{return e;}};E.findAllPublicElements=function(e){var f=[];var i=function(e){e=this.fixComponentContainerElement(e);if(e){f.push(e);this.iterateOverAllPublicAggregations(e,function(a,v){this.iterateOverElements(v,i);}.bind(this));}}.bind(this);i(e);return f;};E.getDomRef=function(e){if(e){var d;if(e.getDomRef){d=e.getDomRef();}if(!d&&e.getRenderedDomRef){d=e.getRenderedDomRef();}return d;}};E.findAllPublicChildren=function(e){var f=this.findAllPublicElements(e);var i=f.indexOf(e);if(i>-1){f.splice(i,1);}return f;};E.hasSameRelevantContainer=function(o,r){var p=o.getParent();if(!r||!p.getElementInstance){return false;}while(p&&p.getElementInstance()!==r){p=p.getParent();if(!p.getElementInstance){return false;}}return!!p;};E.isElementFiltered=function(c,t){t=t||this.getControlFilter();var f=false;t.forEach(function(T){f=this.isInstanceOf(c,T);if(f){return false;}},this);return f;};E.findClosestControlInDom=function(n){if(n&&n.getAttribute("data-sap-ui")){return sap.ui.getCore().byId(n.getAttribute("data-sap-ui"));}else if(n.parentNode){this.findClosestControlInDom(n.parentNode);}else{return null;}};E.findAllSiblingsInContainer=function(e,c){var p=e.getParent();if(!p){return[];}if(p!==c){var P=E.findAllSiblingsInContainer(p,c);return P.map(function(p){return E.getAggregation(p,e.sParentAggregationName);}).reduce(function(a,b){return a.concat(b);},[]);}return E.getAggregation(p,e.sParentAggregationName);};E.getAggregationAccessors=function(e,a){var m=e.getMetadata();m.getJSONKeys();var A=m.getAggregation(a);if(A){var g=A._sGetter;if(A.altTypes&&A.altTypes.length&&e[A._sGetter+"Control"]){g=A._sGetter+"Control";}return{get:g,add:A._sMutator,remove:A._sRemoveMutator,insert:A._sInsertMutator,removeAll:A._sRemoveAllMutator};}else{return{};}};E.getAggregation=function(e,a){var v;var g=this.getAggregationAccessors(e,a).get;if(g){v=e[g]();}else{v=e.getAggregation(a);}v=v&&v.splice?v:(v?[v]:[]);return v;};E.getIndexInAggregation=function(e,p,a){return this.getAggregation(p,a).indexOf(e);};E.addAggregation=function(p,a,e){if(this.hasAncestor(p,e)){throw new Error("Trying to add an element to itself or its successors");}var A=this.getAggregationAccessors(p,a).add;if(A){p[A](e);}else{p.addAggregation("sAggregationName",e);}};E.removeAggregation=function(p,a,e,s){var A=this.getAggregationAccessors(p,a).remove;if(A){p[A](e,s);}else{p.removeAggregation(a,e,s);}};E.insertAggregation=function(p,a,e,i){if(this.hasAncestor(p,e)){throw new Error("Trying to add an element to itself or its successors");}if(this.getIndexInAggregation(e,p,a)!==-1){e.__bSapUiDtSupressParentChangeEvent=true;try{this.removeAggregation(p,a,e,true);}finally{delete e.__bSapUiDtSupressParentChangeEvent;}}var A=this.getAggregationAccessors(p,a).insert;if(A){p[A](e,i);}else{p.insertAggregation(a,e,i);}};E.isValidForAggregation=function(p,a,e){var A=p.getMetadata().getAggregation(a);if(this.hasAncestor(p,e)){return false;}if(A){var t=A.type;if(A.multiple===false&&this.getAggregation(p,a)&&this.getAggregation(p,a).length>0){return false;}return this.isInstanceOf(e,t)||this.hasInterface(e,t);}};E.getAssociationAccessors=function(e,a){var m=e.getMetadata();m.getJSONKeys();var A=m.getAssociation(a);if(A){return{get:A._sGetter,add:A._sMutator,remove:A._sRemoveMutator,insert:A._sInsertMutator,removeAll:A._sRemoveAllMutator};}else{return{};}};E.getAssociation=function(e,a){var v;var g=this.getAssociationAccessors(e,a).get;if(g){v=e[g]();}return v;};E.getAssociationInstances=function(e,a){var v=this.getAssociation(e,a);v=this.getElementInstance(v);if(v&&v.length){v=v.map(function(i){return this.getElementInstance(i);});}return v;};E.hasInterface=function(e,i){var I=e.getMetadata().getInterfaces();return I.indexOf(i)!==-1;};E.isInstanceOf=function(e,t){var i=q.sap.getObject(t);if(typeof i==="function"){return e instanceof i;}else{return false;}};E.loadDesignTimeMetadata=function(e){return e?e.getMetadata().loadDesignTime():Promise.resolve({});};E.executeActions=function(a){var t,m;for(var i=0;i<a.length;i++){var A=a[i];switch(A.changeType){case E.sACTION_MOVE:t=sap.ui.getCore().byId(A.target.parent);m=sap.ui.getCore().byId(A.element);E.insertAggregation(t,A.target.aggregation,m,A.target.index);break;case E.sACTION_CUT:t=sap.ui.getCore().byId(A.source.parent);m=sap.ui.getCore().byId(A.element);E.removeAggregation(t,A.source.aggregation,m);break;case E.sACTION_PASTE:t=sap.ui.getCore().byId(A.target.parent);m=sap.ui.getCore().byId(A.element);E.insertAggregation(t,A.target.aggregation,m,A.target.index);break;case E.sREORDER_AGGREGATION:t=sap.ui.getCore().byId(A.target.parent);var s=this.getAggregationAccessors(t,A.target.aggregation).removeAll;t[s]();var b=this.getAggregationAccessors(t,A.target.aggregation).add;for(var j=0;j<A.source.elements.length;j++){var e=sap.ui.getCore().byId(A.source.elements[j]);t[b](e);}break;default:}}};E.isVisible=function(e){var v=false;var c;for(var i=0,n=e.length;i<n;i++){c=e.eq(i);var f=c.css("filter").match(/opacity\(([^)]*)\)/);v=c.is(":visible")&&c.css("visibility")!=="hidden"&&c.css("opacity")>0&&(f?parseFloat(f[1])>0:true);if(v){break;}}return v;};return E;},true);
