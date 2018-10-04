/**
 * @version $Id: djselect.js 22 2014-05-07 00:55:19Z szymon $
 * @package DJ-MegaMenu
 * @copyright Copyright (C) 2013 DJ-Extensions.com, All rights reserved.
 * @license DJ-Extensions.com Proprietary Use License
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 */
(function($){var k=function(f,g,h){var j='';for(var i=0;i<h;i++){j+='- '}f.each(function(a){var b=a.getElement('> a');var c=a.getChildren('> .dj-subwrap > .dj-subwrap-in > .dj-subcol > .dj-submenu > li, > .dj-subtree > li');if(b&&b.getParent()==a){var d='';if(img=b.getElement('img')){d=img.get('alt')}else{d=b.get('html').replace(/(<small[^<]+<\/small>)/ig,"");d=d.replace(/(<([^>]+)>)/ig,"")}var e=new Element('option',{value:b.get('href'),text:j+d}).inject(g);if(!b.get('href')){e.set('disabled',true)}if(a.hasClass('active')){g.set('value',e.get('value'))}}if(c)k(c,g,h+1)})};window.addEvent('load',function(){$$('.dj-megamenu').each(function(a){var b=new Element('select',{id:a.get('id')+'select','class':'inputbox dj-select',events:{change:function(){if(this.get('value'))window.location=this.get('value')}}});var c=a.getChildren('li.dj-up');k(c,b,0);b.inject(a,'after')})})})(document.id);