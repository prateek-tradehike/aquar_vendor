/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the NekloEULA that is bundled with this package in the file LICENSE.txt.
 *
 * It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt
 *
 * Copyright (c)  Neklo (http://store.neklo.com/)
 */

/**
 * @method
 * @param container
 * @param document
 * @param {_} _ - lodash library, https://lodash.com/docs
 */
(function (container, document, _) {

  function dnd_page_scroll (options) {
    var options = options || {};
    var defaults = {
      topId: 'top_scroll_page',
      bottomId: 'bottom_scroll_page',
      height: '20px'
    };
    options = _.extend(options, defaults);

    var $el_top = document.createElement('div');
    $el_top.setAttribute('id', options.topId);
    $el_top.innerHTML = '&nbsp;';
    var $el_bottom = document.createElement('div');
    $el_bottom.setAttribute('id', options.bottomId);
    $el_bottom.innerHTML = '&nbsp;';
    var $body = document.querySelector('body');
    var $both_el = [];
    $both_el.push($el_top, $el_bottom);

    // When DnD occurs over a scroll area - scroll the page!
    var lastTop;
    var lastBottom;

    var handlers = {
      dragEnter: function (e) {
        if (e.preventDefault) {e.preventDefault(); }
        var direction = (this.getAttribute('id') === options.topId) ? 'up' : 'down';
        return false;
      },
      dragOver: function (e) {
        if (e.preventDefault) {e.preventDefault(); }
        var scrollTop = container.scrollY;
        var direction = (this.getAttribute('id') === options.topId) ? -1 : 1;
        var last = (direction === -1) ? lastTop : lastBottom;
        var current = (direction === -1) ? scrollTop : $body.clientHeight - (scrollTop + container.screen.height);

        if (last != undefined && last == current && current > 0) {
          var newScrollTop = scrollTop + direction * 50;
          container.scrollTo(0, newScrollTop);
        }

        if (direction == -1) lastTop = current; else lastBottom = current;
        return false;
      },
      hide: function (e) {
        _.forEach($both_el, function ($element) { $element.style.display = 'none' });
        return true;
      },
      show: function (e) {
        _.forEach($both_el, function ($element) { $element.style.display = 'block' });
        return true;
      }
    };

    _.forEach($both_el, function ($element) {
      $body.insertBefore($element, $body.childNodes[0]);
      $element.style.display = 'none';
      $element.style.position = 'fixed';
      $element.style.left = '0px';
      $element.style.right = '0px';
      $element.style.zIndex = 99999;
      $element.style.height = options.height;

      addEvent($element, 'dragenter', handlers.dragEnter);
      addEvent($element, 'dragover', handlers.dragOver);
      addEvent($element, 'mouseover', handlers.hide);
    });

    $el_top.style.top = '0px';
    $el_bottom.style.bottom = '0px';

    addEvent(document, 'dragstart', handlers.show);
    addEvent(document, 'dragend', handlers.hide);
  };

  container.dnd_page_scroll = dnd_page_scroll;
})(window, document, _);
