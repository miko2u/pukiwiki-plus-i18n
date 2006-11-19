// BASE64 (RFC2045) Encode/Decode for string in JavaScript
// Version 1.2 Apr. 8 2004 written by MIZUTANI Tociyuki
// Copyright 2003-2004 MIZUTANI Tociyuki
//
// This code is free software; you can redistribute it and/or
// modify it under the terms of the GNU Library General Public
// License as published by the Free Software Foundation; either
// version 2 of the License, or (at your option) any later version.
//
// usage:
// base64 = base64encode(string)  Encode a string.
// string = base64decode(base64)  Decode a base64 string.
//
// caution:
// 1) Wide characters like japanese kanji are not supported. Use only in Latin-1.

var base64list = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';

function base64encode(s)
{
  var t = '', p = -6, a = 0, i = 0, v = 0, c;

  while ( (i < s.length) || (p > -6) ) {
    if ( p < 0 ) {
      if ( i < s.length ) {
        c = s.charCodeAt(i++);
        v += 8;
      } else {
        c = 0;
      }
      a = ((a&255)<<8)|(c&255);
      p += 8;
    }
    t += base64list.charAt( ( v > 0 )? (a>>p)&63 : 64 )
    p -= 6;
    v -= 6;
  }
  return t;
}

function base64decode(s)
{
  var t = '', p = -8, a = 0, c, d;

  for( var i = 0; i < s.length; i++ ) {
    if ( ( c = base64list.indexOf(s.charAt(i)) ) < 0 )
      continue;
    a = (a<<6)|(c&63);
    if ( ( p += 6 ) >= 0 ) {
      d = (a>>p)&255;
      if ( c != 64 )
        t += String.fromCharCode(d);
      a &= 63;
      p -= 8;
    }
  }
  return t;
}
