/**
 * @version $Id$
 * @package DJ-MediaTools
 * @subpackage DJ-MediaTools galleryGrid layout
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license DJ-Extensions.com Proprietary Use License
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 */
!function(t){var s=window.DJImageGalleryGrid=window.DJImageGalleryGrid||function(t,s){this.options={transition:"swing",duration:250,delay:50},this.init(t,s)};s.prototype.init=function(s,i){var e=this;t.extend(e.options,i),e.grid=t("#"+s),e.grid.length&&(e.slides=e.grid.find(".dj-slide"),e.loaded=0,e.touch="ontouchstart"in window||navigator.MaxTouchPoints>0||navigator.msMaxTouchPoints>0,e.transition=e.support("transition"),e.transform="ifade"==e.options.effect?e.support("transform"):!1,e.slides.length&&(e.responsive(),e.setEffectsOptions(),e.setSlidesEffects(),e.loadSlides(),e.setGridEvents(),t(window).on("resize",e.responsive.bind(e)),t(window).on("load",e.responsive.bind(e))))},s.prototype.responsive=function(){var s=this,i=s.getSize(s.grid).x;i-=parseInt(s.grid.css("padding-left")),i-=parseInt(s.grid.css("padding-right")),i-=parseInt(s.grid.css("border-left-width")),i-=parseInt(s.grid.css("border-right-width"));var e=Math.ceil(i/(s.options.width+s.options.spacing)),o=Math.floor((i-1)/e-s.options.spacing);s.slides.css("width",o),s.slides.each(function(s){s%e==0?t(this).addClass("dj-first"):t(this).removeClass("dj-first")})},s.prototype.getSize=function(s){var i={};if(s.is(":hidden")){for(var e,o,n=s.parent();n.is(":hidden");)e=n,n=n.parent();o=n.width(),e&&(o-=parseInt(e.css("margin-left")),o-=parseInt(e.css("margin-right")),o-=parseInt(e.css("border-left-width")),o-=parseInt(e.css("border-right-width")),o-=parseInt(e.css("padding-left")),o-=parseInt(e.css("padding-right")));var a=s.clone();a.css({position:"absolute",visibility:"hidden","max-width":o}),t(document.body).append(a),i={x:a.width(),y:a.height()},a.remove()}else i={x:s.width(),y:s.height()};return i},s.prototype.setEffectsOptions=function(){var t=this;switch(t.options.effect){case"up":var s=Math.ceil(100*t.options.spacing/t.options.height);t.property="top",t.startEffect={top:100+s+"%"},t.endEffect={top:0};break;case"down":var s=Math.ceil(100*t.options.spacing/t.options.height);t.property="top",t.startEffect={top:-1*(100+s)+"%"},t.endEffect={top:0};break;case"left":var s=Math.ceil(100*t.options.spacing/t.options.width);t.property="left",t.startEffect={left:100+s+"%"},t.endEffect={left:0};break;case"right":var s=Math.ceil(100*t.options.spacing/t.options.width);t.property="left",t.startEffect={left:-1*(100+s)+"%"},t.endEffect={left:0};break;case"fade":default:t.property="opacity",t.startEffect={opacity:0},t.endEffect={opacity:1}}if(t.options.desc_effect)switch(t.options.desc_effect){case"up":var s=Math.ceil(100*t.options.spacing/t.options.height);t.desc_property="margin-bottom",t.desc_startEffect={marginBottom:100+s+"%"},t.desc_endEffect={marginBottom:0};break;case"down":var s=Math.ceil(100*t.options.spacing/t.options.height);t.desc_property="margin-bottom",t.desc_startEffect={marginBottom:-1*(100+s)+"%"},t.desc_endEffect={marginBottom:0};break;case"left":var s=Math.ceil(100*t.options.spacing/t.options.width);t.desc_property="margin-left",t.desc_startEffect={marginLeft:-1*(100+s)+"%"},t.desc_endEffect={marginLeft:0};break;case"right":var s=Math.ceil(100*t.options.spacing/t.options.width);t.desc_property="margin-left",t.desc_startEffect={marginLeft:100+s+"%"},t.desc_endEffect={marginLeft:0};break;case"fade":default:t.desc_property="opacity",t.desc_startEffect={opacity:0},t.desc_endEffect={opacity:1}}},s.prototype.setSlidesEffects=function(){var s=this;s.slides.each(function(){var i=t(this);if(i[0].fx=t(this).find(".dj-slide-in").first(),s.transition){i[0].fx.css(s.startEffect);var e=s.property+" "+s.options.duration+"ms "+s.options.css3transition;s.transform&&(e+=", "+s.transform+" "+s.options.duration+"ms "+s.options.css3transition,i[0].fx.css(s.transform,"scale(0.3)")),i[0].fx.css(s.transition,e),e="opacity "+s.options.duration+"ms ease-out "+s.options.delay+"ms",s.transform&&(e+=", "+s.transform+" "+s.options.duration+"ms "+s.options.css3transition+" "+s.options.delay+"ms"),i.css(s.transition,e)}else i[0].fx.css(s.startEffect);if(i[0].desc=i.find(".dj-slide-desc").first(),s.options.desc_effect&&i[0].desc.length&&(s.transition?(i[0].desc.css(s.desc_startEffect),i[0].desc.css(s.transition,s.desc_property+" "+s.options.duration+"ms "+s.options.css3transition+" "+s.options.delay+"ms")):i[0].desc.css(s.desc_startEffect)),s.touch){var o=i[0].fx.find("img.dj-image").first();o.on("click",function(e){s.options.desc_effect&&i[0].desc.length&&!i.hasClass("active")&&(s.slides.each(function(){var e=t(this);e.hasClass("active")&&e!=i&&s.hideItem(e)}),s.showItem(i),e.preventDefault(),e.stopPropagation(),s.grid.trigger("mouseenter"))})}else i.on("mouseenter",function(e){s.slides.each(function(){var e=t(this);e.hasClass("active")&&e!=i&&s.hideItem(e)}),s.showItem(i)}),i.on("mouseleave",s.hideItem.bind(s,i)),i.on("focus",function(e){var o=i.hasClass("active");s.slides.each(function(){var e=t(this);e.hasClass("active")&&e!=i&&s.hideItem(e)}),s.showItem(i),s.grid.trigger("mouseenter"),i.find(".showOnMouseOver").css("opacity",1);var n=i.find("a[href]").first();!o&&n&&n.focus()})})},s.prototype.showItem=function(t){var s=this;t.addClass("active"),s.transition?(t.css("opacity",1),s.transform&&t.css(s.transform,"scale(1.1)")):t.animate({opacity:1},s.options.duration,s.options.transition),s.options.desc_effect&&t[0].desc.length&&(s.transition?t[0].desc.css(s.desc_endEffect):t[0].desc.animate(s.desc_endEffect,s.options.duration,s.options.transition))},s.prototype.hideItem=function(t){var s=this;t.removeClass("active"),s.transition?(t.css("opacity",.3),s.transform&&t.css(s.transform,"scale(1.0)")):t.animate({opacity:.3},s.options.duration,s.options.transition),s.options.desc_effect&&t[0].desc.length&&(s.transition?t[0].desc.css(s.desc_startEffect):t[0].desc.animate(s.desc_startEffect,s.options.duration,s.options.transition))},s.prototype.loadSlide=function(s,i){var e=this;if(!e.slides[s].loaded){e.slides[s].loaded=!0,e.loaded++;var o=t(e.slides[s]).find("img.dj-image").first(),n=0;i&&(n=s*e.options.delay);var a=function(s,i,o){s.length>1&&(i=s[1],o=s[2],s=s[0]),setTimeout(function(){var o=t(e.slides[i]);o.css("background-image","none"),"opacity"!=e.property&&o[0].fx.css("opacity",1),e.transition?(o[0].fx.css(e.endEffect),e.transform&&o[0].fx.css(e.transform,"scale(1.0)")):("ifade"==e.options.effect&&(s.css("max-width","30%"),s.animate({maxWidth:"100%"},e.options.duration,e.options.transition)),o[0].fx.animate(e.endEffect,e.options.duration,e.options.transition))},o),s.off("load",a)}.bind(e,[o,s,n]);o.removeAttr("src"),o.on("load",a),o.data();var r=o.data("sizes"),c=o.data("srcset"),d=o.data("src");o.removeAttr("data-sizes data-srcset data-src"),r&&c?(o.attr("sizes",r),o.attr("srcset",c),picturefill({elements:[o[0]]})):o.attr("src",d)}},s.prototype.loadSlides=function(){var s=this,i=t(window).scrollTop()+t(window).height();s.slides.each(function(e){t(this).offset().top<i&&s.loadSlide(e,!0)});var e=function(){var i=t(window).scrollTop()+t(window).height();s.slides.each(function(e){t(this).offset().top<i&&s.loadSlide(e,!1)}),s.loaded==s.slides.length&&(t(window).off("scroll",e),t(window).off("resize",e))};t(window).on("scroll",e),t(window).on("resize",e),t(window).on("load",e)},s.prototype.setGridEvents=function(){var s=this;s.elementsToShow=s.grid.find(".showOnMouseOver"),s.elementsToShow.each(function(i){var i=t(this);i.css("opacity",0),s.transition&&i.css(s.transition,"opacity 200ms ease"),i.on("mouseenter",function(){s.transition?i.css("opacity",1):i.animate({opacity:1},200)}),i.on("mouseleave",function(){s.transition?i.css("opacity",.5):i.animate({opacity:.5},200)})}),s.grid.on("mouseenter",function(){s.slides.each(function(){var i=t(this);i.hasClass("active")||(s.transition?i.css("opacity",.3):i.animate({opacity:.3},200))}),s.elementsToShow.each(function(){var i=t(this);s.transition?i.css("opacity",.5):i.animate({opacity:.5},200)})}),s.grid.on("mouseleave",function(){s.slides.each(function(){var i=t(this);i.hasClass("active")&&s.hideItem(i)}),s.transition?s.slides.css("opacity",1):s.slides.animate({opacity:1},200),s.elementsToShow.each(function(){var i=t(this);s.transition?i.css("opacity",0):i.animate({opacity:0},200)})}),s.grid.find(".dj-gallery-end").first().on("focus",function(){s.grid.trigger("mouseleave")})},s.prototype.support=function(t){var s=document.body||document.documentElement,i=s.style;if("undefined"==typeof i)return!1;if("string"==typeof i[t])return t;v=["Moz","Webkit","Khtml","O","ms","Icab"],pu=t.charAt(0).toUpperCase()+t.substr(1);for(var e=0;e<v.length;e++)if("string"==typeof i[v[e]+pu])return"-"+v[e].toLowerCase()+"-"+t}}(jQuery);