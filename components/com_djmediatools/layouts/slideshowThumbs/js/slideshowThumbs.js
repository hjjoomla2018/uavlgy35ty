/**
 * @version $Id$
 * @package DJ-MediaTools
 * @subpackage DJ-MediaTools slideshowThumbs layout
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license DJ-Extensions.com Proprietary Use License
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 */
!function(t){var i=window.DJImageSlideshowThumbs=window.DJImageSlideshowThumbs||function(t,i){this.options={autoplay:0,transition:"linear",duration:800,delay:3e3,ifade_multiplier:2,wcag:1},this.init(t,i)};i.prototype=Object.create(DJImageSlideshow.prototype),i.prototype.init=function(t,i){var o=this;DJImageSlideshow.prototype.init.call(o,t,i),o.focusTimer=null},i.prototype.setIndicators=function(){var i=this;DJImageSlideshow.prototype.setIndicators.call(this),i.indicators.length&&(i.indicatorBox=i.slider.find(".dj-indicators-in").first(),i.indicators.each(function(o){indicator=t(this),indicator.on("focus",function(t){i.focusTimer&&clearTimeout(i.focusTimer),i.focusTimer=setTimeout(function(){i.centerIndicator(o)},50)})}))},i.prototype.setCurrentSlide=function(t){var i=this;i.indicators.length&&i.centerIndicator(t),DJImageSlideshow.prototype.setCurrentSlide.call(i,t)},i.prototype.centerIndicator=function(i){var o=this;o.thumb_width=t(o.indicators[0]).outerWidth(!0);var e=o.getSize(o.slider.find("div").first()).x,n=-i*o.thumb_width+(e-o.thumb_width)/2;t(o.indicators[i]).position().left<e/2||o.indicators.length*o.thumb_width<e?n=0:t(o.indicators[i]).position().left+o.thumb_width>o.indicators.length*o.thumb_width-e/2&&(n=-o.indicators.length*o.thumb_width+e-t(o.indicators[0]).position().left),o.indicatorBox.animate({left:n},{queue:!1,duration:o.options.duration})}}(jQuery);