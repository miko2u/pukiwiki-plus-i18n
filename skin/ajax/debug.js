// ****************************** debug.js
// original from http://homepage1.nifty.com/kuraman/js/debug.html
// modified by Kouichirou Eto
// print(variable): variable�̓��e���o�̓o�b�t�@�ɕۑ�
// flush(): �o�̓o�b�t�@�̓��e���f�o�b�O�E�B���h�E�ɏo��
// clear(): �o�̓o�b�t�@�̓��e���N���A
// setDebug(true | false): �f�o�b�O�����o�͂���(true)���o�͂��Ȃ�(false)����ݒ�
// inspect(obj): �I�u�W�F�N�g�̓��e���킩��₷��������ɂ���
// p(obj): inspect�������ʂ�\������

var debug = new debug();

function debug() {
  this.html = "";
  this.hWin = null;
  this.bDebug = true;

  this.setDebug = function(flag) {
    this.bDebug = flag;
  }

  this.clear = function() {
    this.html = "";
    this.flush();
  }

  this.flush = function() {
    if (false == this.bDebug) return;
    if (null == this.hWin || this.hWin.closed) {
      this.hWin = window.open("", "debug",
	"height=200,width=400,menubar=yes,scrollbars=yes,resizable=yes");
    }
    this.hWin.document.open("text/html", "replace");
    this.hWin.document.write(this.html);
    this.hWin.document.close();
    this.hWin.focus();
  }

  this.print = function(html) {
    this.html += ("<tt>" + html + "</tt><br>\n");
  }

  this.inspect = function(obj) {
    //var delimiter = ", ";
    var delimiter = ", <br>";
    if (typeof obj == "number") {
      return ""+obj;
    } else if (typeof obj == "string") {
      return "\""+obj+"\"";
    } else if (typeof obj == "function") {
      return ""+obj;
    } else if (typeof obj == "object") {
      var str = "{";
      var added = false;
      for (key in obj) {
	var value = obj[key];
	if (value) {
	  if (added) str += delimiter;
	  added = true;
	  if (typeof value == "number") {
	    str += ""+key+"=>"+value+"";
	  } else if (typeof value == "string") {
	    str += ""+key+"=>\""+value+"\"";
	  } else if (typeof value == "function") {
	    str += ""+key+"()";
	  } else if (typeof value == "object") {
	    str += ""+key+"=>"+value+"";
	  } else {
	    str += ""+key+"=><"+(typeof value)+":"+value+">";
	  }
	}
      }
      return str+"}";
    } else {
      return "<"+(typeof obj)+":"+obj+">";
    }
  }

  this.p = function(elem) {
    this.print(this.inspect(elem));
    this.flush();
  }
}
