// $Id: kanzaki.js,v 0.1.3 2004/10/13 13:17:36 miko Exp $
// Standard utility scripts
// Modified 2004-04-06, by M.Kanzaki
/* --------------------------------------------------------------
  This set of scripts is copyright (c) 2002-2004 by Masahide Kanzaki.
  You can redistribute it and/or modify it under the terms of 
  the GPL (GNU General Public License) .
  See <http://www.gnu.org/licenses/gpl.html> for detail of license.
  Japanese taranslation <http://www.opensource.jp/gpl/gpl.ja.html>
  ---------------------------------------------------------------- */

var gEnv = new getenv();
noframe();

function init(lv){
	if(!document.getElementById) return;
	initvars();
	//translink();
	if(lv == 'min') return;
	preptoc();
//	hrefp();
//	abac();
	notify('');
}

//---- general environment variables ----

//instantiated as gEnv
function getenv()
{
	var ua = navigator.userAgent;
	if(ua.match(/Opera/)) this.isOpera = true;
	else if(ua.match(/MSIE/)) {
		this.isIE = true;
		if(ua.match(/Mac/)) this.isMacIE = true;
		else if(ua.match(/Win/)) this.isWinIE = true;
	}
	else if(ua.match(/Safari/)) this.isSafari = true;
	else if(ua.match(/Gecko/)) this.isMozilla = true;
	else if(ua.match(/iCab/)) this.isIcab = true;
	this.usrLang = (navigator.userLanguage || navigator.language);
}

function initvars()
{
	gEnv.oH1 = document.getElementsByTagName("H1").item(0);
	if(gEnv.oH1.getAttribute("id")) {
		gEnv.topid = gEnv.oH1.getAttribute("id");
	} else {
		gEnv.topid = "header";
		gEnv.oH1.setAttribute("id",gEnv.topid);
	}
	gNumkeyLink[0] = gEnv.topid;
	gEnv.dbase = document.documentElement || document.body;
	gEnv.isHome = document.getElementById("mytopimg");
	gEnv.docLang = document.documentElement.getAttribute("lang"); //html
	gEnv.prv = (! document.getElementById("snavi"));
	gEnv.localhost = gEnv.prv ? "192" : "kanzaki";
//	gEnv.oAddr = addrelts();
//	gEnv.stInfo = stinfo();
	if ( document.location.href.lastIndexOf('?') == -1 ) {
		gEnv.script = document.location.href;
	} else {
		gEnv.script = document.location.href.substring(0,document.location.href.lastIndexOf('?'));
	}
	linkattrs(); 
	window.document.onkeypress = procKey;
}

function linkattrs()
{
	var x,link = document.getElementsByTagName("link");
	for(var i=0,n=link.length; i<n; i++) {
		x = link.item(i);
		switch(x.getAttribute("rel")) {
			case 'next':
				gEnv.next = x.getAttribute("href"); break;
			case 'prev':
			case 'previous':
				gEnv.prev = x.getAttribute("href"); break;
			case 'alternate':
				if(x.getAttribute("hreflang") == 'en') gEnv.hasEversion = true;
				break;
		}
	}
}

function addrelts()
{
	var ouri;
	var addrl = document.getElementsByTagName("ADDRESS");
	var addr = addrl.item(addrl.length - 1);
	addr.innerHTML = addr.innerHTML.replace(/MK<\/a>/i,"M.K<span class='cpr'>anzaki</span></a>");
	if(ouri = document.getElementById("orguri")) gEnv.orguri = ouri.firstChild.data;
	else{
		addr.innerHTML = addr.innerHTML.replace(/<!-- (Original URI is )(.*) -->/,"<cite class='cpr'><br/>$1$2</cite>");
		gEnv.orguri = RegExp.$2;
	}
	addr.innerHTML += "<span class='cpr'><br/>For non commercial use only. See http://www.kanzaki.com/info/disclaimer</span>";
	return addr;
}

// show update info on top
function stinfo()
{
	var x, y, z, c, si="";
	notify('prepating status info...');
	if(y = document.getElementById('pst')){
		//si = y.firstChild.data;
		si = nodeText(y);
	}
	if(x = document.getElementById('snavi')){
		if(! x.firstChild) return ""; //e.g. iCab
		if(si){x.firstChild.data = si;}
		if(y = document.getElementById('stinfo')){
			z = nodeText(y);
			if(c = document.getElementById('mycounter')) mct(x, c, z);
			else x.setAttribute('title', z);
		}
	}
	if(document.getElementsByTagName("head").item(0).getAttribute("profile").substring(0,34) == "http://www.w3.org/2003/g/data-view") grddl();
	return si;
}

//test to add GRDDL icon and link 2004-04-06
function grddl()
{
	var x, l;
	if(String(document.location).match(/kanzaki.com\/memo/)) return;
	if(x = document.getElementById('stinfo')){
		l = (gEnv.docLang == 'ja') ? document.location + "%3Futf8" : document.location;
		x.innerHTML += " <a href='http://www.w3.org/2000/06/webdata/xslt?xslfile=http://www.w3.org/2003/11/rdf-in-xhtml-processor&amp;xmlfile=" + l + "'><img src='/parts/grddl.png' alt='GRDDL' /> enhanced</a>.";
	}
}

function mct(x, c, z)
{
	var v = c.getAttribute('title').match(/[\d,]+/);
	z = z.replace("invaluable",v);
	x.setAttribute('title',z);
	if(v.toString().match(/000$/)) x.firstChild.data = ("Wow! " + v);
}

// an experimental link to babelfish J-E translation
function translink(){
	var navi, trns;
	if(gEnv.usrLang.match(/ja/i) || gEnv.docLang != 'ja' || gEnv.hasEversion) return;
	
	if(navi = document.getElementById("banner")){
		navi.innerHTML += (String(document.location).substr(0,12)=='http://babel') ?
			"<p class='note' style='font-weight:normal'>This page is translated by machine. Might be very odd, but hope to be of your help.</p>" :
			"<p class='dopo noprint' style='margin-bottom:0'><a href='http://babelfish.altavista.com/babelfish/urltrurl?url=" + document.location + "&amp;lp=ja_en' title='This is a trial link to machine translation of this document'>Babelfish J-&gt;E translation</a></p>";
	}
	
}

// abbr to acronym for Win ie
function abac()
{
	if(gEnv.isWinIE){
		var ab, abp, m;
		notify('preparing abbreviations...');
		ab = document.getElementsByTagName("abbr"); //For HPR3.01
		while(m = ab.length){
			abp = ab.item(m-1).parentNode;
			abp.innerHTML = abp.innerHTML.replace(/(<\/?)abbr/ig,'$1acronym');
		}
	}
	pflb();
}

function hrefp()
{
	if(! gEnv.isIE && ! gEnv.isOpera)return;
	var divlist, main, alist, a;
	notify('prepating href print...');
	divlist = document.getElementsByTagName("DIV");
	for(var i=0,n=divlist.length; i<n; i++){
		if(divlist.item(i).className == 'main'){
			main = divlist.item(i);
			break;
		}
	}
	if(main){
		alist = main.getElementsByTagName("A");
		for(var i=0,n=alist.length; i<n; i++){
			a = alist.item(i);
			if(a.getAttribute("href").substr(0,5) == "http:" && (a.getAttribute("href").indexOf(gEnv.localhost) == -1))
				a.innerHTML = "<img src='/parts/netref.gif' class='noprint' alt=''/>" + a.innerHTML + "<span class='hrefprint'> &lt;" + a.getAttribute("href") + "&gt; </span>";
		}
	}
}

// Pseudo Fixed Link Banner
function pflb()
{
	var pf,lb,path,subm;
	if((path = String(location.pathname)) == '/') return;
	(pf = document.createElement("a")).setAttribute('id','pflb');
	pf.setAttribute('href',findHome(path));
	pf.setAttribute('title','To toc page of this group of contents');
	(lb = document.createElement("img")).setAttribute('src','/parts/tp.gif');
	pf.appendChild(lb);
	gEnv.oAddr.appendChild(pf);
	pf.style.display='block';
	if(gEnv.isMozilla){
		pf.style.position ="fixed";
		if(subm = document.getElementById('smenu1')) subm.style.position ="fixed";
	}
}

function findHome(cp)
{
	if(cp.match(/\/docs\/(html|xml)\//)) return "/docs/htminfo.html";
	if(cp.match(/\/memo\//)) return "/memo/";
	if(cp.match(/\/music\/(mw|perf|cahier)/)) return "/music/";
	if(cp.match(/\/(info|test|w3c|art)\//)) return "/";
	return "./";
}

//misc
function noframe()
{
	if(top.frames.length > 0) top.window.location = self.window.location;
}

function nodeText(m)
{
	var res='';
	for(var i=0,n=m.childNodes;i<n.length;i++){
		if(n.item(i).nodeType == 3) res += n.item(i).data;
		else if(n.item(i).nodeType == 1) res += nodeText(n.item(i));
	}
	return res;
}

function h2d(h)
{
	return ("0123456789abcdef".indexOf(h.charAt(1),0) + "0123456789abcdef".indexOf(h.charAt(0),0) * 16);
}

function notify(str)
{
	if(gEnv.isMozilla) return;
	status = ((str == '') ? defaultStatus : str);
}


///// popup TOC test 2003-10-13
var gToc;

//---- preparation of popup TOC on init ----
function preptoc(){
	var lis;
	if(gEnv.isMacIE) return;// || gEnv.isIcab
	notify('prepating pop toc ...');
	if(lis = prepHdngs(document.getElementsByTagName('H2'),getTocList("UL"))) {
		genTocDiv(lis);
	}
}

//check if the page has TOC
function getTocList(tagName){
	var toc, tocl = document.getElementsByTagName(tagName);
	for(var i=0, n=tocl.length; i<n; i++){
		if(tocl.item(i).className == "toc") {toc = tocl.item(i); break;}
	}
	return toc;
}

//show icon at each heading, and returns TOC
function prepHdngs(hd, pageToc){
	var x, xid, lis;
//	var ptocImg = " <img src='/parts/ptoc.gif' width='1' height='1' class='tocpic' title='Table of Contents of this page'/>";
//	var ptocMsg = "Click heading, and Table of Contents will pop up";
	var ptocImg = '';
	var ptocMsg = '';
	var numH2 = hd.length;
	if(pageToc){
		for(var i = gEnv.isHome ? 3 : 0; i<numH2; i++){
			x = hd.item(i);
			xid = x.getAttribute("id") ? x.getAttribute("id") :
				(x.firstChild.getAttribute ?
					x.firstChild.getAttribute("id") : '');
			if(! xid) x.setAttribute("id",(xid = "genid"+i));
			prepHd(x, xid ,i, ptocImg, ptocMsg);
		}
		hd = document.getElementsByTagName('H3');
		for(var i=0, n=hd.length; i<n; i++){
			x = hd.item(i);
			if(x.getAttribute("id") || x.firstChild.nodeName=="A")
				x.innerHTML += ptocImg;
		}
		lis = pageToc.innerHTML;
		
	}else if(numH2 > 1){
		//if no TOC list and more than 2 <h2> present
		lis = genPseudoToc(hd, ptocImg, ptocMsg);
	}else if(numH2 == 0 && (hd = document.getElementsByTagName('DT')).length > 2){
		//if no h2 and more than 3 dt
		lis = genPseudoToc(hd, ptocImg, ptocMsg);
	}
	return lis;
}

//generate TOC from specified elements
function genPseudoToc(hd, ptocImg, ptocMsg){
	var x, xid, lis = "";
	for(var i=0, n=hd.length; i<n; i++){
		x = hd.item(i);
		if (x.parentNode.getAttribute("id") != 'menubar' && x.parentNode.getAttribute("id") != 'sidebar') {
			if(x.firstChild.getAttribute && x.firstChild.getAttribute("id"))
				xid = x.firstChild.getAttribute("id");
			else if(x.getAttribute("id"))
				xid = x.getAttribute("id");
			else
				x.setAttribute("id",(xid = "genid"+i));
			lis += "<li><a href='#" + xid + "'>" + nodeText(x) + "</a></li>";
			prepHd(x, xid, i, ptocImg, ptocMsg);
		}
	}
	return lis;
}

//set up each TOC item
var gNumkeyLink = new Array();

function prepHd(heading, xid, i, ptocImg, ptocMsg){
	heading.innerHTML += ptocImg;
	//heading.innerHTML = (i+1)+". "+heading.innerHTML+ptocImg;
	heading.setAttribute("title",ptocMsg);
	if(xid) gNumkeyLink[i+1] = xid;
}

//generate the popup TOC division
function genTocDiv(lis){
	(gToc = document.createElement("div")).setAttribute("id","poptoc");
	gToc.appendChild(document.createTextNode(""));
	document.body.appendChild(gToc); //dom html
	gToc.innerHTML = "<h2><a href='#" + gEnv.topid + "'>"
		+ gEnv.oH1.innerHTML.replace(/<img.*alt=\"([^\"]+)\"[^>]*>/i,"$1")
		+ "</a></h2><ol>"
		+ lis.replace(/href/g,"tabindex='1' href") + "</ol>"
		+ getNaviLink() + "<div class='nav'><a tabindex='1' href='" + gEnv.script + "'>TOP</a> - "
		+ "<a tabindex='1' href='" + gEnv.script + "?cmd=search'>Search</a> - <a tabindex='1' href='" + gEnv.script + "?cmd=help'>Help</a></div>";
	window.document.onclick = popToc;
	calcObj(gToc,300);
	// TOC at the bottom
//	gEnv.oAddr.innerHTML = "<img src='/parts/ptoc.gif' class='tocpic' style='float:right'/>" + gEnv.oAddr.innerHTML;
}

//if the page has prev/next link(s)...
function getNaviLink(){
	var navi="";
	if(gEnv.prev)
		navi = "&lt;&lt; <a href='" + gEnv.prev + "'>Prev page</a> ";
	if(gEnv.next){
		if(gEnv.prev) navi += "| ";
		navi += "<a href='" + gEnv.next + "'>Next page</a> &gt;&gt;";
	}
	return (navi ?  "<p>" + navi + "</p>" : "");
}

//determin the size of popup TOC
function calcObj(o, maxw){
	notify('prepating toc size ...'); //test
	var orgX = self.pageXOffset;
	var orgY = self.pageYOffset;
	o.style.visibility = "hidden";
	o.style.display = "block";
	o.width = o.offsetWidth;
	if(o.width > maxw){
		o.width = maxw;
		o.style.width = maxw + "px";
	}
	o.height = o.offsetHeight;
	o.style.display = "none";
	o.style.visibility = "visible";
	if(orgY) scroll(orgX,orgY);
}

//---- click event handlers ----
function popToc(ev){
	var tg;
	if(window.event){
		ev = event; tg = ev.srcElement;
	}else if(ev){
		tg = ev.target;
	}

	if(ev.altKey) dispToc(ev,tg,0);
	else if(tg.className=='tocpic') dispToc(ev,tg,2);
	//else if(ev.shiftKey) procSC(ev,tg);
	else{
		if(! tg.nodeName.substr(0,2).match(/(A|H[2-4])/)){
			tg = tg.parentNode; //Mozilla 1.2.1
			if(! tg.nodeName.substr(0,2).match(/(A|H[2-4])/)){
				hideToc(); return;
			}
		}
		if(tg.getAttribute("href")) hideToc();
		else if(tg.getAttribute("id")) dispToc(ev,tg,1);
		else hideToc();
	}
}

function procSC(ev,tg){
	//if(gEnv.prv && (tg.parentNode.getAttribute("id") == gEnv.topid || tg.getAttribute("id") == gEnv.topid)){
		//alert(gEnv.toSource());
	//}
	alert("e.y:"+ev.y+", body.clientHeight:"+document.body.clientHeight+", docEl.clientHeight:"+document.documentElement.clientHeight );
}

//display on mouseclick
function dispToc(ev,tg,type){
	var doc = new eventDocPos(ev);
	var scr = new eventScrPos(ev);
	gToc.style.top = ((scr.h - scr.y > gToc.height) ? doc.y + "px" :
		((scr.y > gToc.height) ? (doc.y - gToc.height) + "px" :
			((scr.y < scr.h/2) ? (doc.y - scr.y) + "px" :
				(doc.y + scr.h - scr.y - gToc.height) + "px")));
	gToc.style.left = ((scr.x < scr.w - gToc.width) ? doc.x + "px" :
		(doc.x - gToc.width) + "px");
	if(type) setCurPos(tg,type);
	gToc.style.display = "block";
}

//display on kbd request
function dispTocKey(ev){
	gToc.style.top = ((document.body.scrollTop + document.documentElement.scrollTop) | self.pageYOffset) + "px";
	//gToc.style.top = (gEnv.dbase.scrollTop | self.pageYOffset) + "px";
	gToc.style.left = 0;
	gToc.style.display = "block";
}

//find current heading and hilite
function setCurPos(tg,type){
	var tid = (type==1) ? tg.getAttribute("id") :
		(tg.parentNode.getAttribute("id") ? tg.parentNode.getAttribute("id") :
			(tg.parentNode.firstChild.getAttribute ? tg.parentNode.firstChild.getAttribute("id") :''));
		//(tg.parentNode ? tg.parentNode.firstChild.getAttribute("id") : '#');
	if(tid) hiliteHd(tid);
}

//hilite current heading
function hiliteHd(tid){
	var pat = "#" + tid + "\"";
	var rep = pat + " class=\"here\"";
	gToc.innerHTML = gToc.innerHTML.replace(pat,rep);
}

//close TOC and clear hilite
function hideToc(){
	gToc.style.display = "none"
	gToc.innerHTML = gToc.innerHTML.replace(/ class=\"?here\"?/,"");
}

//get event coordinates
function eventDocPos(e){
	if(gEnv.isOpera){
		this.x = e.clientX + document.body.scrollLeft;
		this.y = e.clientY + document.body.scrollTop;
	}else if(gEnv.isIE){// if(e.x){
		this.x = e.x + document.body.scrollLeft + document.documentElement.scrollLeft;
		this.y = e.y + document.body.scrollTop + document.documentElement.scrollTop;
	}else{
		this.x = e.pageX;
		this.y = e.pageY;
	}
	return this;
}

function eventScrPos(e){
	if(gEnv.isOpera){
		this.x = e.clientX;
		this.y = e.clientY;
		this.w = document.body.clientWidth;
		this.h = document.body.clientHeight;
	}else if(gEnv.isIE){//if(e.x){
		this.x = e.x;
		this.y = e.y;
		this.w = document.body.clientWidth;
		this.h = document.documentElement.clientHeight ? document.documentElement.clientHeight : document.body.clientHeight;
		//this.w = gEnv.dbase.clientWidth;
		//this.h = gEnv.dbase.clientHeight;
	}else if(gEnv.isSafari){
		this.x = e.screenX;
		this.y = self.innerHeight - self.screenY - e.screenY; //Safari doesn't seem to have location relative to current window. Tricky but Safari's screenY counts form the bottom of the screen (or window)
		this.w = self.innerWidth;
		this.h = self.innerHeight;
	}else{
		this.x = e.clientX;
		this.y = e.clientY;
		this.w = self.innerWidth;
		this.h = self.innerHeight;
	}
	return this;
}

//---- keyboard event handler ----
function procKey(e){
	var key, kl, tg;
	if(e){
		key = e.which; tg = e.target;
	}else{
		key = event.keyCode; tg = event.srcElement;
	}
	//if(key == 35){ aler("testing");treturn; }//End key
	if(tg.nodeName.match(/(INPUT|TEXTAREA)/i)) return;
	kl = String.fromCharCode(key).toLowerCase();
	if(kl == '?'){
		if(! location.href.match("/?cmd=help")){
			if(confirm("Go to help page ?"))
				location.href= "/?cmd=help";
		} else {
			alert("This key should bring you our help, i.e. this page :-)");
		}
		return false;
	}else if(gToc){
		if(gToc.style.display == 'block'){
			if(key == 27 || key == 47) hideToc(); //Esc, slash
			else if(key >= 48 && key <=57){ //0-9
				key -= 48;
				if(gNumkeyLink[key]){
					location.href = "#" + gNumkeyLink[key];
					hideToc();
				}
			}
		}else{
			if(key == 47) dispTocKey();
		}
	}
}

var __kanzaki_onload_save = window.onload;
window.onload = function() {
	if (__kanzaki_onload_save) __kanzaki_onload_save();
	init();
}
