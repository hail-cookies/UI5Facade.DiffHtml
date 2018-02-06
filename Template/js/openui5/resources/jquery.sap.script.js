/*!
 * UI development toolkit for HTML5 (OpenUI5)
 * (c) Copyright 2009-2017 SAP SE or an SAP affiliate company.
 * Licensed under the Apache License, Version 2.0 - see LICENSE.txt.
 */
sap.ui.define(['jquery.sap.global'],function(q){"use strict";var I=0;q.sap.uid=function uid(){return"id-"+new Date().valueOf()+"-"+I++;};q.sap.hashCode=function(s){var i=s.length,h=0;while(i--){h=(h<<5)-h+s.charCodeAt(i);h=h&h;}return h;};q.sap.delayedCall=function delayedCall(d,o,m,p){return setTimeout(function(){if(q.type(m)=="string"){m=o[m];}m.apply(o,p||[]);},d);};q.sap.clearDelayedCall=function clearDelayedCall(d){clearTimeout(d);return this;};q.sap.intervalCall=function intervalCall(i,o,m,p){return setInterval(function(){if(q.type(m)=="string"){m=o[m];}m.apply(o,p||[]);},i);};q.sap.clearIntervalCall=function clearIntervalCall(i){clearInterval(i);return this;};var U=function(u){this.mParams={};var Q=u||window.location.href;if(Q.indexOf('#')>=0){Q=Q.slice(0,Q.indexOf('#'));}if(Q.indexOf("?")>=0){Q=Q.slice(Q.indexOf("?")+1);var p=Q.split("&"),P={},a,n,v;for(var i=0;i<p.length;i++){a=p[i].split("=");n=decodeURIComponent(a[0]);v=a.length>1?decodeURIComponent(a[1].replace(/\+/g,' ')):"";if(n){if(!Object.prototype.hasOwnProperty.call(P,n)){P[n]=[];}P[n].push(v);}}this.mParams=P;}};U.prototype={};U.prototype.get=function(n,a){var v=Object.prototype.hasOwnProperty.call(this.mParams,n)?this.mParams[n]:[];return a===true?v:(v[0]||null);};q.sap.getUriParameters=function getUriParameters(u){return new U(u);};q.sap.unique=function(a){var l=a.length;if(l>1){a.sort();var j=0;for(var i=1;i<l;i++){if(a[i]!==a[j]){a[++j]=a[i];}}if(++j<l){a.splice(j,l-j);}}return a;};q.sap.equal=function(a,b,m,c,d){if(typeof m=="boolean"){c=m;m=undefined;}if(!d){d=0;}if(!m){m=10;}if(d>m){return false;}if(a===b){return true;}if(Array.isArray(a)&&Array.isArray(b)){if(!c&&a.length!==b.length){return false;}if(a.length>b.length){return false;}for(var i=0;i<a.length;i++){if(!q.sap.equal(a[i],b[i],m,c,d+1)){return false;}}return true;}if(typeof a=="object"&&typeof b=="object"){if(!a||!b){return false;}if(a.constructor!==b.constructor){return false;}if(!c&&Object.keys(a).length!==Object.keys(b).length){return false;}if(a.nodeName&&b.nodeName&&a.namespaceURI&&b.namespaceURI){return a.isEqualNode(b);}if(a instanceof Date){return a.valueOf()===b.valueOf();}for(var i in a){if(!q.sap.equal(a[i],b[i],m,c,d+1)){return false;}}return true;}return false;};q.sap.each=function(o,c){var a=Array.isArray(o),l,i;if(a){for(i=0,l=o.length;i<l;i++){if(c.call(o[i],i,o[i])===false){break;}}}else{for(i in o){if(c.call(o[i],i,o[i])===false){break;}}}return o;};q.sap.forIn=function(o,c){for(var n in o){if(c(n,o[n])===false){return;}}};q.sap.arraySymbolDiff=function(o,n,s){var S={},O=[],N=[],a,v,b,c=0,d=0,e,f,g,h,D=[];if(o===n||q.sap.equal(o,n)){return D;}s=s||function(V){if(typeof V!=="string"){V=JSON.stringify(V)||"";}return q.sap.hashCode(V);};for(var i=0;i<n.length;i++){v=s(n[i]);b=S[v];if(!b){b=S[v]={iNewCount:0,iOldCount:0};}b.iNewCount++;N[i]={symbol:b};}for(var i=0;i<o.length;i++){v=s(o[i]);b=S[v];if(!b){b=S[v]={iNewCount:0,iOldCount:0};}b.iOldCount++;b.iOldLine=i;O[i]={symbol:b};}for(var i=0;i<N.length;i++){b=N[i].symbol;if(b.iNewCount===1&&b.iOldCount===1){N[i].line=b.iOldLine;O[b.iOldLine].line=i;}}for(var i=0;i<N.length-1;i++){a=N[i].line;if(a!==undefined&&a<O.length-1){if(O[a+1].symbol===N[i+1].symbol){O[a+1].line=i+1;N[i+1].line=a+1;}}}for(var i=N.length-1;i>0;i--){a=N[i].line;if(a!==undefined&&a>0){if(O[a-1].symbol===N[i-1].symbol){O[a-1].line=i-1;N[i-1].line=a-1;}}}while(c<o.length||d<n.length){f=O[c]&&O[c].line;e=N[d]&&N[d].line;if(c<o.length&&(f===undefined||f<d)){D.push({index:d,type:"delete"});c++;}else if(d<n.length&&(e===undefined||e<c)){D.push({index:d,type:"insert"});d++;}else if(d===f){d++;c++;}else{h=f-d;g=e-c;if(h<=g){D.push({index:d,type:"insert"});d++;}else{D.push({index:d,type:"delete"});c++;}}}return D;};q.sap.arrayDiff=function(o,n,c,u){c=c||function(v,V){return q.sap.equal(v,V);};var O=[];var N=[];var m=[];for(var i=0;i<n.length;i++){var a=n[i];var f=0;var t;if(u&&c(o[i],a)){f=1;t=i;}else{for(var j=0;j<o.length;j++){if(c(o[j],a)){f++;t=j;if(u||f>1){break;}}}}if(f==1){var M={oldIndex:t,newIndex:i};if(m[t]){delete O[t];delete N[m[t].newIndex];}else{N[i]={data:n[i],row:t};O[t]={data:o[t],row:i};m[t]=M;}}}for(var i=0;i<n.length-1;i++){if(N[i]&&!N[i+1]&&N[i].row+1<o.length&&!O[N[i].row+1]&&c(o[N[i].row+1],n[i+1])){N[i+1]={data:n[i+1],row:N[i].row+1};O[N[i].row+1]={data:O[N[i].row+1],row:i+1};}}for(var i=n.length-1;i>0;i--){if(N[i]&&!N[i-1]&&N[i].row>0&&!O[N[i].row-1]&&c(o[N[i].row-1],n[i-1])){N[i-1]={data:n[i-1],row:N[i].row-1};O[N[i].row-1]={data:O[N[i].row-1],row:i-1};}}var d=[];if(n.length==0){for(var i=0;i<o.length;i++){d.push({index:0,type:'delete'});}}else{var b=0;if(!O[0]){for(var i=0;i<o.length&&!O[i];i++){d.push({index:0,type:'delete'});b=i+1;}}for(var i=0;i<n.length;i++){if(!N[i]||N[i].row>b){d.push({index:i,type:'insert'});}else{b=N[i].row+1;for(var j=N[i].row+1;j<o.length&&(!O[j]||O[j].row<i);j++){d.push({index:i+1,type:'delete'});b=j+1;}}}}return d;};q.sap._createJSTokenizer=function(){var a,b,e={'"':'"','\'':'\'','\\':'\\','/':'/',b:'\b',f:'\f',n:'\n',r:'\r',t:'\t'},t,d=function(m){throw{name:'SyntaxError',message:m,at:a,text:t};},n=function(c){if(c&&c!==b){d("Expected '"+c+"' instead of '"+b+"'");}b=t.charAt(a);a+=1;return b;},f=function(){var f,s='';if(b==='-'){s='-';n('-');}while(b>='0'&&b<='9'){s+=b;n();}if(b==='.'){s+='.';while(n()&&b>='0'&&b<='9'){s+=b;}}if(b==='e'||b==='E'){s+=b;n();if(b==='-'||b==='+'){s+=b;n();}while(b>='0'&&b<='9'){s+=b;n();}}f=+s;if(!isFinite(f)){d("Bad number");}else{return f;}},s=function(){var c,i,s='',k,u;if(b==='"'||b==='\''){k=b;while(n()){if(b===k){n();return s;}if(b==='\\'){n();if(b==='u'){u=0;for(i=0;i<4;i+=1){c=parseInt(n(),16);if(!isFinite(c)){break;}u=u*16+c;}s+=String.fromCharCode(u);}else if(typeof e[b]==='string'){s+=e[b];}else{break;}}else{s+=b;}}}d("Bad string");},g=function(){var g='',c=function(b){return b==="_"||b==="$"||(b>="0"&&b<="9")||(b>="a"&&b<="z")||(b>="A"&&b<="Z");};if(c(b)){g+=b;}else{d("Bad name");}while(n()){if(b===' '){n();return g;}if(b===':'){return g;}if(c(b)){g+=b;}else{d("Bad name");}}d("Bad name");},w=function(){while(b&&b<=' '){n();}},h=function(){switch(b){case't':n('t');n('r');n('u');n('e');return true;case'f':n('f');n('a');n('l');n('s');n('e');return false;case'n':n('n');n('u');n('l');n('l');return null;}d("Unexpected '"+b+"'");},v,j=function(){var j=[];if(b==='['){n('[');w();if(b===']'){n(']');return j;}while(b){j.push(v());w();if(b===']'){n(']');return j;}n(',');w();}}d("Bad array");},o=function(){var k,o={};if(b==='{'){n('{');w();if(b==='}'){n('}');return o;}while(b){if(b>="0"&&b<="9"){k=f();}else if(b==='"'||b==='\''){k=s();}else{k=g();}w();n(':');if(Object.hasOwnProperty.call(o,k)){d('Duplicate key "'+k+'"');}o[k]=v();w();if(b==='}'){n('}');return o;}n(',');w();}}d("Bad object");};v=function(){w();switch(b){case'{':return o();case'[':return j();case'"':case'\'':return s();case'-':return f();default:return b>='0'&&b<='9'?f():h();}};function p(c,i){var r;t=c;a=i||0;b=' ';r=v();if(isNaN(i)){w();if(b){d("Syntax error");}return r;}else{return{result:r,at:a-1};}}return{array:j,error:d,getIndex:function(){return a-1;},getCh:function(){return b;},init:function(c,i){t=c;a=i||0;b=' ';},name:g,next:n,number:f,parseJS:p,setIndex:function(i){if(i<a-1){throw new Error("Must not set index "+i+" before previous index "+(a-1));}a=i;n();},string:s,value:v,white:w,word:h};};q.sap.parseJS=q.sap._createJSTokenizer().parseJS;q.sap.extend=function(){var s,c,a,n,o,b,t=arguments[0]||{},i=1,l=arguments.length,d=false;if(typeof t==="boolean"){d=t;t=arguments[i]||{};i++;}if(typeof t!=="object"&&!q.isFunction(t)){t={};}for(;i<l;i++){o=arguments[i];for(n in o){s=t[n];a=o[n];if(t===a){continue;}if(d&&a&&(q.isPlainObject(a)||(c=Array.isArray(a)))){if(c){c=false;b=Array.isArray(s)?s:[];}else{b=s&&q.isPlainObject(s)?s:{};}t[n]=q.sap.extend(d,b,a);}else{t[n]=a;}}}return t;};return q;});
