var render;

window.onload = function() {
	render = new RssRenderer();

	try {
		var list = new Rss();
		list.load("http://192.168.135.2/pwutf8/?cmd=ajaxrss");

		// フォーム作成
		var frm = document.createElement('form');
		frm.setAttribute('id' , 'rsslist');
		var sel = document.createElement('select');

		for( var i = 0 ; i < list.items.length ; i++ ){
			var opt = document.createElement('option');
			opt.setAttribute('value', list.items[i].link );
			opt.appendChild( document.createTextNode( list.items[i].title ) );
			sel.appendChild(opt);
		}
		frm.appendChild(sel);

		var btn = document.createElement('input');
		btn.setAttribute('type','button');
		btn.setAttribute('value','表示');
		frm.appendChild(btn);
		
		document.getElementById('ajaxrss').appendChild(frm);

		// RSS読み込みイベント用クロージャ
		btn.onclick = function(){
			var rss = new Rss();
			window.document.body.style.cursor = 'wait';
			try{
				rss.load(sel.value);
				render.write(rss, document.getElementById('ajaxrss'));
			}
			catch(e){
				window.alert(e.message);
			}
			window.document.body.style.cursor = 'auto';
		}
	}
	catch(e) {
		window.alert(e.message);
	}
}
