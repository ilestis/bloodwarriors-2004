/* ***** BEGIN LICENSE BLOCK *****
 * This file is part of DotClear.
 * Copyright (c) 2004 Olivier Meunier and contributors. All rights
 * reserved.
 *
 * DotClear is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * DotClear is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with DotClear; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * ***** END LICENSE BLOCK ***** */
 
function dcToolBar(textarea,format,img_path)
{
	this.btStrong		= function() {}
	this.btEm			= function() {}
	this.btUm			= function() {}
	this.btSt			= function() {}
	this.btQuote		= function() {}
	this.btImg			= function() {}
	this.btUrl			= function() {}
	this.btColor		= function() {}
	
	if (!document.createElement) {
		return;
	}
	
	if ((typeof(document["selection"]) == "undefined")
	&& (typeof(textarea["setSelectionRange"]) == "undefined")) {
		return;
	}
	
	var toolbar = document.createElement("div");
	toolbar.id = "dctoolbar";
	
	function getFormat() {
		return 'html';
	}
	
	function addButton(src, title, fn) {
		var i = document.createElement('img');
		i.src = src;
		i.title = title;
		i.onclick = function() { try { fn() } catch (e) { } return false };
		i.tabIndex = 400;
		toolbar.appendChild(i);
		addSpace(2);
	}
	
	function addSpace(w)
	{
		s = document.createElement('span');
		s.style.padding='0 '+w+'px 0 0';
		s.appendChild(document.createTextNode(' '));
		toolbar.appendChild(s);
	}
	
	function encloseSelection(prefix, suffix, fn) {
		textarea.focus();
		var start, end, sel, scrollPos, subst;
		
		if (typeof(document["selection"]) != "undefined") {
			sel = document.selection.createRange().text;
		} else if (typeof(textarea["setSelectionRange"]) != "undefined") {
			start = textarea.selectionStart;
			end = textarea.selectionEnd;
			scrollPos = textarea.scrollTop;
			sel = textarea.value.substring(start, end);
		}
		
		if (sel.match(/ $/)) { // exclude ending space char, if any
			sel = sel.substring(0, sel.length - 1);
			suffix = suffix + " ";
		}
		
		if (typeof(fn) == 'function') {
			var res = (sel) ? fn(sel) : fn('');
		} else {
			var res = (sel) ? sel : '';
		}
		
		subst = prefix + res + suffix;
		
		if (typeof(document["selection"]) != "undefined") {
			var range = document.selection.createRange().text = subst;
			textarea.caretPos -= suffix.length;
		} else if (typeof(textarea["setSelectionRange"]) != "undefined") {
			textarea.value = textarea.value.substring(0, start) + subst +
			textarea.value.substring(end);
			if (sel) {
				textarea.setSelectionRange(start + subst.length, start + subst.length);
			} else {
				textarea.setSelectionRange(start + prefix.length, start + prefix.length);
			}
			textarea.scrollTop = scrollPos;
		}
	}
	
	function draw(msg) {
		p = document.createElement('span');
		p.style.display='block';
		textarea.parentNode.insertBefore(p, textarea);
		textarea.parentNode.insertBefore(toolbar, textarea);
	}
	
	
	// ---
	function singleTag(ftag,ltag) {
		var stag = '['+ftag+']';
		var etag = '[/'+ltag+']';
		encloseSelection(stag,etag);
	}
	
	function btStrong(label) {
		addButton(img_path+'bt_bold.png',label,
		function() { singleTag('b','b'); });
	}
	
	function btEm(label) {
		addButton(img_path+'bt_em.png',label,
		function() { singleTag('i','i'); });
	}
	
	function btUm(label) {
		addButton(img_path+'bt_um.png',label,
		function() { singleTag('u','u'); });
	}
	
	function btSt(label) {
		addButton(img_path+'bt_st.png',label,
		function() { singleTag('s','s'); });
	}

	function btQuote(label) {
		addButton(img_path+'bt_quote.png',label,
		function() { singleTag('quote=Pseudo','quote'); });
	}

	function btImg(label) {
		addButton(img_path+'bt_img.png',label,
		function() { singleTag('img','img'); });
	}

	function btUrl(label) {
		addButton(img_path+'bt_url.png',label,
		function() { singleTag('url=""','url'); });
	}
	function btColor(label) {
		addButton(img_path+'bt_color.png',label,
		function() { singleTag('color=""','color'); });
	}
	

	
	// methods
	this.addButton		= addButton;
	this.addSpace		= addSpace;
	this.draw			= draw;
	this.btStrong		= btStrong;
	this.btEm			= btEm;
	this.btUm			= btUm;
	this.btSt			= btSt;
	this.btQuote		= btQuote;
	this.btImg			= btImg;
	this.btUrl			= btUrl;
	this.btColor		= btColor;
}

//pour les émoticons
function emoticon(textarea, text) 
{
	textarea.focus();
	var start, end, sel, scrollPos, subst;
	
	if (typeof(document["selection"]) != "undefined") {
		sel = document.selection.createRange().text;
	} else if (typeof(textarea["setSelectionRange"]) != "undefined") {
		start = textarea.selectionStart;
		end = textarea.selectionEnd;
		scrollPos = textarea.scrollTop;
		sel = textarea.value.substring(start, end);
	}
	
	if (sel.match(/ $/)) { // exclude ending space char, if any
		sel = sel.substring(0, sel.length - 1);
		text = text + " ";
	}
	
	if (typeof(fn) == 'function') {
		var res = (sel) ? fn(sel) : fn('');
	} else {
		var res = (sel) ? sel : '';
	}
	
	subst = text + res;
	
	if (typeof(document["selection"]) != "undefined") {
		var range = document.selection.createRange().text = subst;
		textarea.caretPos -= suffix.length;
	} else if (typeof(textarea["setSelectionRange"]) != "undefined") {
		textarea.value = textarea.value.substring(0, start) + subst +
		textarea.value.substring(end);
		if (sel) {
			textarea.setSelectionRange(start + subst.length, start + subst.length);
		} else {
			textarea.setSelectionRange(start + text.length, start + text.length);
		}
		textarea.scrollTop = scrollPos;
	}
}