/*!
 * jQuery Countdown plugin v1.0
 * http://www.littlewebthings.com/projects/countdown/
 *
 * Copyright 2010, Vassilis Dourdounis
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
(function(a){var f="diffSecs",c="timer",b="id",g="omitWeeks",d="callback";a.fn.countDown=function(e){var c=this;config={};a.extend(config,e);diffSecs=c.setCountDown(config);config.onComplete&&a.data(a(c)[0],d,config.onComplete);config.omitWeeks&&a.data(a(c)[0],g,config.omitWeeks);a("#"+a(c).attr(b)+" .digit").html('<div class="top"></div><div class="bottom"></div>');a(c).doCountDown(a(c).attr(b),diffSecs,500);return c};a.fn.stopCountDown=function(){clearTimeout(a.data(this[0],c))};a.fn.startCountDown=function(){this.doCountDown(a(this).attr(b),a.data(this[0],f),500)};a.fn.setCountDown=function(b){var c=new Date;if(b.targetDate)c=new Date(b.targetDate.month+"/"+b.targetDate.day+"/"+b.targetDate.year+" "+b.targetDate.hour+":"+b.targetDate.min+":"+b.targetDate.sec+(b.targetDate.utc?" UTC":""));else if(b.targetOffset){c.setFullYear(b.targetOffset.year+c.getFullYear());c.setMonth(b.targetOffset.month+c.getMonth());c.setDate(b.targetOffset.day+c.getDate());c.setHours(b.targetOffset.hour+c.getHours());c.setMinutes(b.targetOffset.min+c.getMinutes());c.setSeconds(b.targetOffset.sec+c.getSeconds())}var d=new Date;diffSecs=Math.floor((c.valueOf()-d.valueOf())/1e3);a.data(this[0],f,diffSecs);return diffSecs};a.fn.doCountDown=function(j,h,i){var k=1200,b=60;$this=a("#"+j);if(h<=0){h=0;a.data($this[0],c)&&clearTimeout(a.data($this[0],c))}secs=h%b;mins=Math.floor(h/b)%b;hours=Math.floor(h/b/b)%24;if(a.data($this[0],g)==true){days=Math.floor(h/b/b/24);weeks=Math.floor(h/b/b/168)}else{days=Math.floor(h/b/b/24)%7;weeks=Math.floor(h/b/b/168)}$this.dashChangeTo(j,"seconds_dash",secs,i?i:800);$this.dashChangeTo(j,"minutes_dash",mins,i?i:k);$this.dashChangeTo(j,"hours_dash",hours,i?i:k);$this.dashChangeTo(j,"days_dash",days,i?i:k);$this.dashChangeTo(j,"weeks_dash",weeks,i?i:k);a.data($this[0],f,h);if(h>0){e=$this;t=setTimeout(function(){e.doCountDown(j,h-1)},1e3);a.data(e[0],c,t)}else(cb=a.data($this[0],d))&&a.data($this[0],d)()};a.fn.dashChangeTo=function(h,e,d,g){$this=a("#"+h);for(var c=$this.find("."+e+" .digit").length-1;c>=0;c--){var f=d%10;d=(d-f)/10;$this.digitChangeTo("#"+$this.attr(b)+" ."+e+" .digit:eq("+c+")",f,g)}};a.fn.digitChangeTo=function(b,f,d){var e=" div.bottom",c=" div.top";if(!d)d=800;if(a(b+c).html()!=f+""){a(b+c).css({display:"none"});a(b+c).html(f?f:"0").fadeOut(d);a(b+e).animate({height:""},d,function(){a(b+e).html(a(b+c).html());a(b+e).css({display:"block",height:""});a(b+c).hide().slideUp(10)})}}})(jQuery);

/*!
 * jQuery Cookies
 */

jQuery.cookie=function(d,b,a){if(arguments.length>1&&String(b)!=="[object Object]"){a=jQuery.extend({},a);if(b===null||b===undefined)a.expires=-1;if(typeof a.expires==="number"){var g=a.expires,e=a.expires=new Date;e.setDate(e.getDate()+g)}b=String(b);return document.cookie=[encodeURIComponent(d),"=",a.raw?b:encodeURIComponent(b),a.expires?"; expires="+a.expires.toUTCString():"",a.path?"; path="+a.path:"",a.domain?"; domain="+a.domain:"",a.secure?"; secure":""].join("")}a=b||{};var c,f=a.raw?function(a){return a}:decodeURIComponent;return(c=new RegExp("(?:^|; )"+encodeURIComponent(d)+"=([^;]*)").exec(document.cookie))?f(c[1]):null};