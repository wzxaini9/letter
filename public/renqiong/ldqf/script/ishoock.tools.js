//author: C, @ishoock group
//www.ishoock.com
//2016-08-23 a.m.
$.prototype.in = function () {
    this.addClass("in");
};
$.prototype.removeIn = function () {
    this.removeClass("in");
};
$.prototype.out = function () {
    this.addClass("out");
};
$.prototype.removeOut = function () {
    this.removeClass("out");
};
//获取地址栏参数
//query: query string
location.getQuery = function (query) {
    var reg = new RegExp("(^|&)" + query + "=([^&]*)(&|$)");
    var r = decodeURI(window.location.href.split("?")[1]).match(reg);
    if (r != null) return unescape(r[2]); return null;
}

//加载v0.2
//Progress:function handler(progress){//使用当前进度},
//Complete:function handler(){//加载完成后执行},
//imglist:[uri1,uri2,uri3,....],
//delay:nubmer
function Load(Progress, Complete, imglist, delay, cdn) {
    var complete = 0;
    var count = imglist.length;
    var currentProgress = 0;
    _images = [];
    _base64images = {};
    function _animate() {
        var completeProgres = complete / count;
        currentProgress = (currentProgress + 0.01) < completeProgres ? (currentProgress + 0.01) : completeProgres;
        if (Math.floor(currentProgress * 100) < 100) {
            Progress(currentProgress);
            setTimeout(_animate, delay);
        } else {
            setTimeout(function () {
                Progress(1);
                setTimeout(function () {
                    Complete();
                }, 200);
            }, delay);
        }
    }
    this.Start = function () {
        for (var i = 0; i < count; i++) {
            var img = new Image();
            if (imglist[i].indexOf("+") == 0) {
                _images[_images.length] = img;
                imglist[i] = imglist[i].replace("+", "");
            }
            img.onload = function () {
                complete++;
            };
            var uri = cdn ? (cdn + imglist[i]) : imglist[i];
            if (/json$/g.test(imglist[i])) {
                $.get(uri, function (data) {
                    var base64img = new Image();
                    base64img.src = data.url;
                    _base64images[data.name] = base64img;
                    complete++;
                });
            } else {
                img.src = uri;
            }
        }
        _animate();
    }
}

//手势v0.2.2
//*element:dom,
//option:{
//  pointcount:1|2(default:1),
//  direction:'x'|'y'|'both'(default:'y'),p.s. pointcount:1 only,
//  step:number(default:50),  
//  delay:ms(default:1000),
// click:function,
//},
//complete:funtion
function Gesture(element, option, complete) {
    var element = element ? element : document.body;
    var pointcount = option.pointcount ? (option.pointcount > 2 ? 2 : option.pointcount) : 1;
    var direction = option.direction ? option.direction : "y";
    var step = option.step ? option.step : 50;
    var delay = option.delay ? option.delay : 0;
    var click = option.click;
    var points = [];
    var spacing = 0;
    var action = false;
    var delaying = false;
    var result=0;
    function _Start() {
        action = true;
        result = 0;

        for (var i = 0; i < pointcount; i++) {
            points[i] = { x: event.touches[0].clientX, y: event.touches[0].clientY };
        }
        if (pointcount == 2) {
            var x = Math.abs(points[0].x - points[1], x);
            var y = Math.abs(points[0].y - points[1], y);
            spacing = Math.sqrt(x * x + y * y);
        }
    }
    function _Move() {
        event.preventDefault();
        event.stopPropagation();
        if (action && !delaying) {
            var _points = [];
            for (var i = 0; i < pointcount; i++) {
                _points[i] = { x: event.touches[0].clientX, y: event.touches[0].clientY };
            }
            result = 0;
            if (pointcount == 1) {
                var move;
                if (direction == "both") {
                    if (Math.abs(points[0].x - _points[0].x) > Math.abs(points[0].y - _points[0].y)) {
                        move = points[0].x - _points[0].x;
                        if (move > step) {
                            result = 1;
                        } else if (move < -step) {
                            result = -1;
                        }
                        result = { "code": result, "direction": "x" };
                    } else {
                        move = points[0].y - _points[0].y;
                        if (move > step) {
                            result = 1;
                        } else if (move < -step) {
                            result = -1;
                        }
                        result = { "code": result, "direction": "y" };
                    }
                } else {
                    move = direction == "x" ? points[0].x - _points[0].x : points[0].y - _points[0].y;
                    if (move > step) {
                        result = 1;
                    } else if (move < -step) {
                        result = -1;
                    }
                }
            } else if (pointcount == 2) {
                var x = Math.abs(_points[0].x - _points[1].x);
                var y = Math.abs(_points[0].y - _points[1].y);
                var _spacing = Math.sqrt(x * x + y * y);
                var change = spacing - _spacing;
                if (change < -step) {
                    result = 1;
                } else if (change > step) {
                    result = -1;
                }
            }
            if (direction != "both") {
                if (result != 0) {
                    delaying = true;
                    action = false;
                    complete(result,this);
                    setTimeout(function () {
                        delaying = false;
                    }, delay);
                }
            } else {
                if (result.code != 0) {
                    delaying = true;
                    action = false;
                    complete(result,this);
                    setTimeout(function () {
                        delaying = false;
                    }, delay);
                }
            }
        }
        return false;
    }
    function _End() {
        action = false;
        if (click && result == 0) {
            click(event);
        }
    }

    this.Init = function () {
        $(element).on("touchstart", _Start);
        $(element).on("touchmove", _Move);
        $(element).on("touchend", _End);
        return this;
    };
    this.Kill = function () {
        $(element).off("touchstart");
        $(element).off("touchmove");
        $(element).off("touchend");
    }
}


// 限制字符串长度（区分大小写）；
String.prototype.Limit = function (max) {
    var _length = 0, len = this.length, charCode = -1;
    var str = "";
    for (var i = 0; i < len; i++) {
        charCode = this.charCodeAt(i);
        if (charCode >= 0 && charCode <= 128) {
            _length += 1;
        }
        else {
            _length += 2;
        }
        if (_length <= max) {
            str += this[i];
        } else {
            break;
        }
    }
    return str;
}
//字符串真实长度
String.prototype.Length= function () {
    var _length = 0, len = this.length, charCode = -1;
    var str = "";
    for (var i = 0; i < len; i++) {
        charCode = this.charCodeAt(i);
        if (charCode >= 0 && charCode <= 128) {
            _length += 1;
        }
        else {
            _length += 2;
        }
    }
    return _length;
}

function SetStorage(name, value) {
    setCookie(name, value);
    localStorage.setItem(name, value);
}
function GetStorage(name) {
    return localStorage.getItem(name) ? localStorage.getItem(name) : getCookie(name);
}
//设置cookies 
function setCookie(name, value) {
    var Days = 30;
    var exp = new Date();
    exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
    document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString();
}
//读取cookies 
function getCookie(name) {
    var arr, reg = new RegExp("(^| )" + name + "=([^;]*)(;|$)");
    if (arr = document.cookie.match(reg))
        return unescape(arr[2]);
    else
        return null;
}
//获取手机系统
function GetDevice() {
        var u = navigator.userAgent;
        if (u.indexOf('Android') > -1 || u.indexOf('Linux') > -1) {
            return "android";
        } else if (u.indexOf('iPhone') > -1) {
            return "iphone";
        } else if (u.indexOf('Windows Phone') > -1) {
            return "windowsphone";
        } else if (u.indexOf('iPad') > -1) {
            return "ipad";
        } else {
            return "other";
        }
}



var base64DecodeChars = new Array(
      -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
      -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1,
      -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, -1, 62, -1, -1, -1, 63,
      52, 53, 54, 55, 56, 57, 58, 59, 60, 61, -1, -1, -1, -1, -1, -1,
      -1,  0,  1,  2,  3,  4,  5,  6,  7,  8,  9, 10, 11, 12, 13, 14,
      15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, -1, -1, -1, -1, -1,
      -1, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38, 39, 40,
      41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, -1, -1, -1, -1, -1);
  //编码的方法
  function base64encode(str) {
      var out, i, len;
      var c1, c2, c3;
      len = str.length;
      i = 0;
      out = "";
      while(i < len) {
      c1 = str.charCodeAt(i++) & 0xff;
      if(i == len)
      {
          out += base64EncodeChars.charAt(c1 >> 2);
          out += base64EncodeChars.charAt((c1 & 0x3) << 4);
          out += "==";
          break;
      }
      c2 = str.charCodeAt(i++);
      if(i == len)
      {
          out += base64EncodeChars.charAt(c1 >> 2);
          out += base64EncodeChars.charAt(((c1 & 0x3)<< 4) | ((c2 & 0xF0) >> 4));
          out += base64EncodeChars.charAt((c2 & 0xF) << 2);
          out += "=";
          break;
      }
      c3 = str.charCodeAt(i++);
      out += base64EncodeChars.charAt(c1 >> 2);
      out += base64EncodeChars.charAt(((c1 & 0x3)<< 4) | ((c2 & 0xF0) >> 4));
      out += base64EncodeChars.charAt(((c2 & 0xF) << 2) | ((c3 & 0xC0) >>6));
      out += base64EncodeChars.charAt(c3 & 0x3F);
      }
      return out;
  }
  //解码的方法
  function base64decode(str) {
      var c1, c2, c3, c4;
      var i, len, out;
      len = str.length;
      i = 0;
      out = "";
      while(i < len) {
      
      do {
          c1 = base64DecodeChars[str.charCodeAt(i++) & 0xff];
      } while(i < len && c1 == -1);
      if(c1 == -1)
          break;
      
      do {
          c2 = base64DecodeChars[str.charCodeAt(i++) & 0xff];
      } while(i < len && c2 == -1);
      if(c2 == -1)
          break;
      out += String.fromCharCode((c1 << 2) | ((c2 & 0x30) >> 4));
      
      do {
          c3 = str.charCodeAt(i++) & 0xff;
          if(c3 == 61)
          return out;
          c3 = base64DecodeChars[c3];
      } while(i < len && c3 == -1);
      if(c3 == -1)
          break;
      out += String.fromCharCode(((c2 & 0XF) << 4) | ((c3 & 0x3C) >> 2));
      
      do {
          c4 = str.charCodeAt(i++) & 0xff;
          if(c4 == 61)
          return out;
          c4 = base64DecodeChars[c4];
      } while(i < len && c4 == -1);
      if(c4 == -1)
          break;
      out += String.fromCharCode(((c3 & 0x03) << 6) | c4);
      }
      return out;
  }
  function utf16to8(str) {
      var out, i, len, c;
      out = "";
      len = str.length;
      for(i = 0; i < len; i++) {
      c = str.charCodeAt(i);
      if ((c >= 0x0001) && (c <= 0x007F)) {
          out += str.charAt(i);
      } else if (c > 0x07FF) {
          out += String.fromCharCode(0xE0 | ((c >> 12) & 0x0F));
          out += String.fromCharCode(0x80 | ((c >>  6) & 0x3F));
          out += String.fromCharCode(0x80 | ((c >>  0) & 0x3F));
      } else {
          out += String.fromCharCode(0xC0 | ((c >>  6) & 0x1F));
          out += String.fromCharCode(0x80 | ((c >>  0) & 0x3F));
      }
      }
      return out;
  }
  function utf8to16(str) {
      var out, i, len, c;
      var char2, char3;
      out = "";
      len = str.length;
      i = 0;
      while(i < len) {
      c = str.charCodeAt(i++);
      switch(c >> 4)
      { 
        case 0: case 1: case 2: case 3: case 4: case 5: case 6: case 7:
          // 0xxxxxxx
          out += str.charAt(i-1);
          break;
        case 12: case 13:
          // 110x xxxx   10xx xxxx
          char2 = str.charCodeAt(i++);
          out += String.fromCharCode(((c & 0x1F) << 6) | (char2 & 0x3F));
          break;
        case 14:
          // 1110 xxxx  10xx xxxx  10xx xxxx
          char2 = str.charCodeAt(i++);
          char3 = str.charCodeAt(i++);
          out += String.fromCharCode(((c & 0x0F) << 12) |
                         ((char2 & 0x3F) << 6) |
                         ((char3 & 0x3F) << 0));
          break;
      }
      }
      return out;
  }
  //设置cookie
function setCookie(name, value, day) {
	var date = new Date();
	date.setDate(date.getDate() + day);
	document.cookie = name + '=' + value + ';expires=' + date;
};
//获取cookie
function getCookie(name) {
	var reg = RegExp(name + '=([^;]+)');
	var arr = document.cookie.match(reg);
	if(arr) {
		return arr[1];
	} else {
		return '';
	}
};
//删除cookie
function delCookie(name) {
	setCookie(name, null, -1);
};