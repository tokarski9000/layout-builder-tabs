(function (Drupal, once) {
  'use strict';

  function LayoutTabs(element) {
    this.element = element;
    this.baseClass = 'layout-tabs';
    this.nav = element.querySelector('.' + this.baseClass + '--nav');
    this.init();
  }

  LayoutTabs.prototype.init = function () {
    this.setOverlap();
  }

  LayoutTabs.prototype.setOverlap = function () {
    if(this.nav.classList.contains( this.baseClass + '--overlap')){
      const navHeight = this.nav.clientHeight;
      this.nav.setAttribute('style', 'margin-top: -' + this.nav.clientHeight + 'px;'+ this.baseClass +'-overlap-height: ' + this.nav.clientHeight + 'px;');
      this.nav.setAttribute('style', `margin-top: -${navHeight}px; --${this.baseClass}-overlap-height: ${navHeight}px;`);
    }
  }

  Drupal.behaviors.layout_tabs_script = {
    attach: function (context) {
      once('layout-tabs', '.layout-tabs-container', context).forEach(function (element, context) {
        new LayoutTabs(element);
      });
    }
  }
})(Drupal, once);
