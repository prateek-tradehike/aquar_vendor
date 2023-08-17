/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the NekloEULA that is bundled with this package in the file LICENSE.txt.
 *
 * It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt
 *
 * Copyright (c)  Neklo (http://store.neklo.com/)
 */

require(['prototype','NEKLOvendor'], function(){/**
 * Event.simulate(@element, eventName[, options]) -> Element
 *
 * - @element: element to fire event on
 * - eventName: name of event to fire (only MouseEvents and HTMLEvents interfaces are supported)
 * - options: optional object to fine-tune event properties - pointerX, pointerY, ctrlKey, etc.
 *
 *    $('foo').simulate('click'); // => fires "click" event on an element with id=foo
 *
 **/
;(function(){

    var eventMatchers = {
        'HTMLEvents': /^(?:load|unload|abort|error|select|change|submit|reset|focus|blur|resize|scroll|keyup)$/,
        'MouseEvents': /^(?:click|mouse(?:down|up|over|move|out))$/
    };
    var defaultOptions = {
        pointerX: 0,
        pointerY: 0,
        button: 0,
        ctrlKey: false,
        altKey: false,
        shiftKey: false,
        metaKey: false,
        bubbles: true,
        cancelable: true
    };

    Event.simulate = function(element, eventName) {
        var options = Object.extend(defaultOptions, arguments[2] || { });
        var oEvent, eventType = null;

        element = $(element);

        for (var name in eventMatchers) {
            if (eventMatchers[name].test(eventName)) { eventType = name; break; }
        }

        if (!eventType)
            throw new SyntaxError('Only HTMLEvents and MouseEvents interfaces are supported');

        if (document.createEvent) {
            oEvent = document.createEvent(eventType);
            if (eventType == 'HTMLEvents') {
                oEvent.initEvent(eventName, options.bubbles, options.cancelable);
            }
            else {
                oEvent.initMouseEvent(eventName, options.bubbles, options.cancelable, document.defaultView,
                    options.button, options.pointerX, options.pointerY, options.pointerX, options.pointerY,
                    options.ctrlKey, options.altKey, options.shiftKey, options.metaKey, options.button, element);
            }
            element.dispatchEvent(oEvent);
        }
        else {
            options.clientX = options.pointerX;
            options.clientY = options.pointerY;
            oEvent = Object.extend(document.createEventObject(), options);
            element.fireEvent('on' + eventName, oEvent);
        }
        return element;
    };

    if (Element.addMethods){
        Element.addMethods({ simulate: Event.simulate });
    } else if (jQuery) {
        console.log('Invalid install');
        // jQuery.fn.simulate = Event.simulate;
    } else {
        console.log('Invalid install');
    }
})();
(function (container, jQuery) {
    container.NEKLO = container.NEKLO || {};
    container.NEKLO.Connectors = {};

    /**
     * @class
     * @param options
     * @constructor
     */
    function Magento2Connector(options) {
        this.options = options || {};
    }

    Magento2Connector.prototype = {
        $getPage: function (pageCount, count, success, error) {
            var self = this;

            return new Ajax.Request(this.options.url + '?isAjax=true', {
                parameters: {
                    page: pageCount,
                    form_key: this.options.form_key,
                    count: count
                },
                onSuccess: function (response) {
                    var data = self.$parseResponse(response);
                    return success && typeof success === 'function' ? success(data) : data;
                },
                onFailure: function (err) {
                    return error && typeof error === 'function' ? error(err) : err;
                }
            });
        },

        $parseResponse: function (res) {
            return res.responseJSON;
        },

        $save: function (data) {
            return data;
        }
    };

    container.NEKLO.Connectors.Magento2Connector = Magento2Connector;

})(window, jQuery);
(function (container) {
  container.NEKLO = container.NEKLO || {};

  /**
   * @class
   * @constructor
   * @description Very simple event emitter.
   */
  function SimpleEventEmitter() {

  }

  SimpleEventEmitter.prototype = {
    /**
     * @method
     * @description subscribe to event
     * @param {string} event
     * @param {function} callback
     */
    on: function (event, callback) {
      this._events = this._events || {};
      this._events[event] = this._events[event] || [];
      this._events[event].push(callback);
    },

    /**
     * @method
     * @description unsubscribe from event
     * @param {string} event
     * @param {function} callback
     */
    off: function (event, callback) {
      this._events = this._events || {};
      if (event in this._events === false) {
        return;
      }
      this._events[event].splice(this._events[event].indexOf(callback), 1);
    },
    /**
     * @method
     * @description rise event. could receive any number of params. Params after first will passed to event subscriber funtion
     * @param {string} event
     * @param ...
     */
    emit: function (event /* , args... */) {
      this._events = this._events || {};
      if (event in this._events === false) {
        return;
      }
      for (var i = 0; i < this._events[event].length; i++) {
        this._events[event][i].apply(this, Array.prototype.slice.call(arguments, 1));
      }
    }
  };

  /**
   * @static
   * @method
   * @description add SimpleEventEmitter features to any class
   * @example SimpleEventEmitter.mixin(ClassFunctionName);
   * @param destObject
   * @return {*}
   */
  SimpleEventEmitter.mixin = function (destObject) {
    var props = ['on', 'off', 'emit'];
    for (var i = 0; i < props.length; i++) {
      if (typeof destObject === 'function') {
        destObject.prototype[props[i]] = SimpleEventEmitter.prototype[props[i]];
      } else {
        destObject[props[i]] = SimpleEventEmitter.prototype[props[i]];
      }
    }
    return destObject;
  };

  container.NEKLO.SimpleEventEmitter = SimpleEventEmitter;
})(window);
(function (container, document, _, jQuery) {
    container.NEKLO = container.NEKLO || {};

    /**
     * @class
     * @param setups
     * @param options
     * @param parent This is ul list element include all instances from this class
     * @constructor
     */
    function Element (setups, options, parent, onImageLoaded) {
        this.setups = setups;
        this.parent = parent;
        var element = document.createElement(setups.selector);
        var tmp = _.template(setups.template)(options);

        var image = new Image();
        image.src = options.image;
        image.onload = onImageLoaded;
        image.onerror = onImageLoaded;

        element.className = setups.className;
        element.style.width = setups.width;
        element.style.marginBottom = setups.margin;
        element.innerHTML = tmp;
        this.dataset = options;
        element.dataset.position = options.position;
        element.dataset.entity_id = options.entity_id;
        element.dataset.attached = options.attached === true || options.attached === 'true' ? true : false;
        element.dataset.isDirty = options.isDirty === true || options.isDirty === 'true' ? true : false;

        element.addEventListener('click', this.eventClick, false);
        addEvent(element, 'dragstart', this.eventDragStart);
        addEvent(element, 'dragenter', this.eventDragEnter);
        addEvent(element, 'dragover', this.eventDragOver);
        addEvent(element, 'drop', this.eventDrop);
        addEvent(element, 'dragend', this.eventDragEnd);

        element.$$instance = this;
        element.$$instance.getElement = function () {
            return element;
        };

        return element;
    }

    Element.prototype = {
        getParentInstance: function () {
            return this.getElement().$$instance.parent.$$instance;
        },
        isAttached: function () {
            var element = this.getElement();
            var attached = (element.dataset.attached === 'true') ? true : false;
            return attached;
        },
        searchElementSibling: function (element, prefix) {
            if (!element) {
                return
            }
            if (element.dataset.attached === 'true' || !element.classList.contains(this.setups.className)) {
                if (prefix === 'next') {
                    return this.searchElementSibling(element.nextSibling, prefix);
                } else {
                    return this.searchElementSibling(element.previousSibling, prefix);
                }
            }
            return element;
        },
        updateElementState: function (options, height) {
            var element = this.getElement();
            var states = this.setups.states;
            element.style.height = height + 'px';
            element.dataset.position = options.position;
            element.dataset.entity_id = options.entity_id;
            element.dataset.attached = options.attached === true || options.attached === 'true' ? true : false;
            element.dataset.isDirty = options.isDirty === true || options.isDirty === 'true' ? true : false;

            if (options.attached === true || options.attached === 'true') {
                element.classList.add(states.attached);
                element.setAttribute('draggable', false);
            } else {
                element.classList.remove(states.attached);
                element.setAttribute('draggable', true);
            }

            var leftSibling = this.searchElementSibling(element.previousSibling, 'previous');
            var rightSibling = this.searchElementSibling(element.nextSibling, 'next');

            if (!leftSibling) {
                element.classList.add(states.disableLeft);
            } else {
                element.classList.remove(states.disableLeft);
            }

            if (!rightSibling) {
                element.classList.add(states.disableRight);
            } else {
                element.classList.remove(states.disableRight);
            }
        },
        eventClick: function (e) {
            if (e.preventDefault) {
                e.preventDefault();
            }
            var target = e.target;
            var attr = target.getAttribute('btn') || target.parentNode.getAttribute('btn');

            if (attr === 'attached') {
                (this.$$instance.handlerAttach.bind(this))();
            }
            if (attr === 'toLeft') {
                (this.$$instance.handlerToLeft.bind(this))();
            }
            if (attr === 'toBegin') {
                (this.$$instance.handlerToBegin.bind(this))();
            }
            if (attr === 'toRight') {
                (this.$$instance.handlerToRight.bind(this))();
            }
            if (attr === 'toEnd') {
                (this.$$instance.handlerToEnd.bind(this))();
            }
            if (attr === 'toPage' && target.closest('.nd-list__item.attached') === null) {
                (this.$$instance.handlerToPage.bind(this))();
            }
            if (attr === 'goToPage') {
                (this.$$instance.handlerGoToPage.bind(this))();
            }
            if (target.tagName.toLowerCase() != 'input' && attr === null) {
                (this.$$instance.handlerToPageReset.bind(this))();
            }

            return false;
        },
        eventDragStart: function (e) {
            var parent = this.$$instance.getParentInstance();
            parent.setDragElement(this);
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', this.innerHTML);
            this.style.opacity = '0.5';
        },
        eventDragOver: function (e) {
            if (e.preventDefault) { e.preventDefault(); }
            e.dataTransfer.dropEffect = 'move';
            return false;
        },
        eventDragEnter: function (e) {
            if (e.preventDefault) { e.preventDefault(); }
            var parent = this.$$instance.getParentInstance();
            var items = parent.getListElements();
            var states = this.$$instance.setups.states;
            [].forEach.call(items, function (item) {
                item.classList.remove(states.over);
            });
            this.classList.add(states.over);
            return false;
        },
        eventDrop: function (e) {
            if (e.preventDefault) { e.preventDefault(); }
            if (e.stopPropagation) { e.stopPropagation(); }
            var parent = this.$$instance.getParentInstance();
            var dragElement = parent.getDragElement();
            var targetElement = this;
            this.$$instance.handlerMoving(dragElement, targetElement);

            return false;
        },
        eventDragEnd: function (e) {
            var parent = this.$$instance.getParentInstance();
            var items = parent.getListElements();
            var states = this.$$instance.setups.states;
            [].forEach.call(items, function (item) {
                item.classList.remove(states.over);
            });
            this.style.opacity = '1';
        },
        handlerToLeft: function () {
            var parent = this.$$instance.getParentInstance();
            var targetElement = this.$$instance.searchElementSibling(this.previousSibling, 'previous');
            if (!targetElement) {
                return;
            }
            var begin = +targetElement.dataset.position;
            var end = +this.dataset.position;

            parent.sortElements(begin, end, this, false);
        },
        handlerToRight: function () {
            var parent = this.$$instance.getParentInstance();
            var targetElement = this.$$instance.searchElementSibling(this.nextSibling, 'next');
            if (!targetElement) {
                return;
            }
            var begin = +this.dataset.position;
            var end = +targetElement.dataset.position;

            parent.sortElements(begin, end, this, true);
        },
        handlerToBegin: function () {
            var parent = this.$$instance.getParentInstance();
            var items = parent.getListElements();
            var targetElement = _.find(items, function(el){
                return el.dataset && el.dataset.attached == 'false';
            });
            if (!targetElement || targetElement === this) {
                return;
            }
            var begin = +targetElement.dataset.position;
            var end = +this.dataset.position;
            parent.sortElements(begin, end, this, false);
        },

        handlerToEnd: function () {
            var parent = this.$$instance.getParentInstance();
            var items = _.slice(parent.getListElements()).reverse();
            var targetElement = _.find(items, function(el){
                return el.dataset && el.dataset.attached == 'false';
            });
            if (!targetElement  || targetElement === this) {
                return;
            }
            var begin = +this.dataset.position;
            var end = +targetElement.dataset.position;

            parent.sortElements(begin, end, this, true);
        },

        //reset page jumping form for item
        handlerToPageReset: function () {
            jQuery(this).find('.to-page-frm').css({display:'none'});
            jQuery(this).find('.to-page-inp').prop('disabled', true);
            return false;

        },
        //initialize page load if newPage > pages loaded.
        handlerGoToPage: function (el) {
            var self = this;
            if (this.parent == undefined) {
                el = this;
                self = el.$$instance;
            }

            var value = jQuery(el).find('.to-page-inp').val();
            var parent = el.$$instance.getParentInstance();
            var sort = parent.sorter;

            if (value === '') return;
            value = parseInt(value);
            if (value >= 1) {
                if (sort.page < value) {
                    sort.pageSkip = true;
                    sort.uploadPage(self.handlerGoToPage.bind(el))
                } else {
                    sort.pageSkip = false;
                    var newPosition = (+sort.options.grid.cols * +sort.options.grid.rows * (value-1));
                    newPosition += 1;
                    var items = _.toArray(parent.getListElement().select('.nd-list__item')).slice(newPosition);
                    var targetElement = _.find(
                        items,
                        'dataset.attached',
                        'false'
                    );
                    if (!targetElement || targetElement === this) {
                        return;
                    }
                    var begin = +targetElement.dataset.position;
                    var end = +el.dataset.position;
                    var rev = false;
                    if (begin > end) {
                        newPosition = begin;
                        begin = end;
                        end = newPosition;
                        rev = true;
                    }
                    var toPageFrm = jQuery(el).find('.to-page-frm').css({display:'none'});
                    parent.sortElements(begin, end, el, rev);

                }
            }
        },
        handlerToPage: function () {
            var el = this;
            var self = this.$$instance;
            var parent = el.$$instance.getParentInstance();
            var sort = parent.sorter;
            var pages = (sort.totalItems/(+sort.options.grid.cols * +sort.options.grid.rows)).ceil();
            if (pages == 1) {
                alert('No page to move to');
                return;
            }
            var toPageFrm = jQuery(this).find('.to-page-frm');
            if (!jQuery(this).attr('my-initialized')) {

                var select = jQuery(this).find('.to-page-inp');
                select.click(function(e){
                    e.preventDefault();
                    return false;
                });

                if (select.length) {
                    self.addPageOptions(select, pages);
                }


                jQuery(this).attr('my-initialized', true)
            }
            jQuery(this).find('.to-page-inp').prop('disabled', false);
            toPageFrm.css({display:'block'});

        },

        addPageOptions: function(select, pageCount, active) {
            for(var i=1; i<= pageCount; i++) {
                select.append('<option value="'+i+'"'+(active===i ? ' selected="selected"' : '')+'>'+i+'</option>');
            }
        },

        handlerMoving: function (dragElement, targetElement) {
            if (dragElement == targetElement || targetElement.$$instance.isAttached()) {
                return false;
            }

            var parent = dragElement.$$instance.getParentInstance();
            var begin, end, reverse;

            if (+dragElement.dataset.position > +targetElement.dataset.position) {
                begin = +targetElement.dataset.position;
                end = +dragElement.dataset.position;
                reverse = false;
            } else {
                begin = +dragElement.dataset.position;
                end = +targetElement.dataset.position;
                reverse = true;
            }

            parent.sortElements(begin, end, dragElement, reverse);
        },
        handlerAttach: function () {
            var parent = this.$$instance.getParentInstance();
            this.dataset.isDirty = true;
            this.dataset.attached = (this.dataset.attached === 'true' || this.dataset.attached === true) ? false : true;
            parent.updateElementsState();
            parent.onListUpdated([this]);
        }
    };

    container.NEKLO.SorterElement = Element;

    /**
     * @class
     * @param setups
     * @constructor
     */
    function List (setups) {
        this.options = setups;
        this.separatorElements = [];
        this.dragElement = null;
        this.page = 1;
        var element = document.createElement(setups.list.selector);
        element.className = setups.list.className;
        element.style.marginLeft = setups.list.margin;
        element.style.width = setups.grid.cols * (parseInt(setups.item.width) + parseInt(setups.item.margin)) + 'px';
        element.$$instance = this;
        element.$$instance.getListElement = function () {
            return element;
        };

        return element;
    }

    List.prototype = {
        getOptions: function () {
            return this.options;
        },
        getListElements: function () {
            return this.getListElement().childNodes;
        },
        getDragElement: function () {
            return this.dragElement;
        },
        setDragElement: function (value) {
            this.dragElement = value;
        },
        addSeparators: function () {
            var options = this.getOptions();
            var setups = options.separator;
            var items = this.getListElements();
            var parent = this.getListElement();
            var length = items.length;
            var sizeOfPage = options.grid.cols * options.grid.rows;
            var insertPoints = [];

            var template = setups.template;

            for (var i = 0; i < length; i++) {
                if (i % sizeOfPage === 0 || i === 0) {
                    insertPoints.push(items[i]);
                }
            }

            _.forEach(insertPoints, function (element, key) {
                key++;
                var separator = document.createElement(setups.selector);
                separator.className = setups.className;
                separator.innerHTML = _.template(template)({value: key});

                parent.insertBefore(separator, element);
                parent.$$instance.separatorElements.push(separator);
            });
        },
        removeSeparators: function () {
            var parent = this.getListElement();
            _.forEach(parent.$$instance.separatorElements, function (separator) {
                parent.removeChild(separator);
            });
            parent.$$instance.separatorElements = [];
        },
        sortElements: function (begin, end, dragElement, reverse) {
            var self = this;
            var beginForItems = begin - 1;
            var endForItems = end - 1;
            this.removeSeparators();
            var position;
            var items = this.getListElements();
            items = _.slice(items, beginForItems, endForItems + 1);
            var oldItems = _.clone(items, true);

            if (reverse) {
                position = end;
                items = items.reverse();
            } else {
                position = begin;
            }

            items = this.sortElementsByOrder(items, dragElement, position);
            //setTimeout(function () {
            self.sortElementsInDOM(items, oldItems);
            self.updateElementsState();
            self.addSeparators();
            self.onListUpdated(items);
            //}, 10);
        },
        sortElementsByOrder: function (array, dragElement, position) {
            var needSortedElement;
            _.forEach(array, function (item) {
                if (item.$$instance.isAttached()) {
                    return;
                }
                item.dataset.isDirty = true;
                if (needSortedElement) {
                    needSortedElement.dataset.position = item.dataset.position;
                }

                needSortedElement = item;

                if (item.dataset.entity_id === dragElement.dataset.entity_id) {
                    item.dataset.position = position;
                }
            });

            array.sort(function (a, b) {
                return (+a.dataset.position) - (+b.dataset.position)
            });
            return array;
        },
        sortElementsInDOM: function (sortedElements, currentElements) {
            var parent = this.getListElement();
            var insertPoint = document.createElement('li');
            parent.insertBefore(insertPoint, currentElements[0]);

            _.forEach(currentElements, function (current) {
                parent.removeChild(current);
            });

            _.forEach(sortedElements, function (sorted) {
                parent.insertBefore(sorted, insertPoint);
            });

            parent.removeChild(insertPoint);
        },
        updateList: function (data, setups) {
            if (!data || (!Array.isArray(data))) {
                throw new Error('You can update list of elements with pass data like array');
            }
            if (!data.length) { return; }
            var list = this.createListItems(data, setups);
            return list;
        },
        createListItems: function (data, setups) {
            var self = this;
            var i = 0;
            var length = data.length;
            var list = this.getListElement();
            var imgCount = 0;

            for (i; i < length; i++) {
                var element = new Element (setups, data[i], list, onImageLoaded);
                list.appendChild(element);
            }

            function onImageLoaded () {
                ++imgCount;
                if (imgCount === length) { self.updateElementsState(); }
            };

            return list;
        },
        updateElementsState: function () {
            var separator = this.getOptions().separator;
            var elements = this.getListElements();
            var height = 0;
            _.forEach(elements, function (o) {
                if (o.clientHeight > height) {height = o.clientHeight}
            });
            _.forEach(elements, function (element) {
                if (element.classList.contains(separator.className)) { return; }
                element.$$instance.updateElementState(element.dataset, height);
            });
        }
    };

    container.NEKLO.SorterList = List;

    /**
     * @class
     * @param options
     * @constructor
     */
    function Button (options) {
        this.options = options;
        var setups = options.uploadButton;
        var button = document.createElement(setups.selector);
        button.className = setups.className;
        button.innerHTML = setups.label;
        button.$$instance = this;
        button.$$instance.getElement = function () {
            return button;
        };

        button.addEventListener('click', this.uploadPage, false);

        return button;
    }

    Button.prototype = {
        uploadPage: function (e) {
            e.preventDefault();
            this.$$instance.onUploadPage();
        },
        updateState: function (pageNumber, totalItemsLength) {
            var options = this.options;
            var cols = options.grid.cols;
            var rows = options.grid.rows;
            var currentItemsLength = pageNumber * rows * cols;

            if (currentItemsLength >= totalItemsLength) {
                var button = this.getElement();
                button.style.display = 'none';
            }
        },
        updateWidth: function (list) {
            var button = this.getElement();
        }
    };


    /**
     * @class
     * @extend SimpleEventEmitter
     * @constructor
     */
    function ProductSorter(options) {

        options = options || {};
        var self = this;

        this.dataStoring = [];
        this.totalItems = options.totalItems;
        this.page = 1;
        delete options.totalItems;
        this.options = _.merge(this.options, options);

        var count = +this.options.grid.cols * +this.options.grid.rows;
        jQuery(document).bind("myPageLoadedEvent", self.afterLoadPage);
        this.options.connector.$getPage(this.page, count, function (data) {
            var options = self.options;
            var targetDOM = options.targetDOM;

            if (!data.length) {
                return ProductSorter.createErrorElement(options);
            }

            var list = new List(options);
            var button = new Button(options);
            list.$$instance.sorter = self;

            targetDOM.appendChild(list);
            targetDOM.parentNode.appendChild(button);

            list.$$instance.updateList(data, options.item);
            list.$$instance.addSeparators();

            button.$$instance.updateState(self.page, self.totalItems);
            button.$$instance.updateWidth(list);

            targetDOM.$$instance = self;

            button.$$instance.onUploadPage = self.uploadPage.bind(self);
            self.onUploadedPage = function (data) {
                list.$$instance.removeSeparators();
                list.$$instance.updateList(data, options.item);
                list.$$instance.addSeparators();
                button.$$instance.updateState(self.page, self.totalItems);
                jQuery(document).trigger( "myPageLoadedEvent", [self]);
            };
            list.$$instance.onListUpdated = self.updateDataStoring.bind(self);
            jQuery(document).trigger( "myPageLoadedEvent", [self]);
        }, function (error) {
            ProductSorter.createErrorElement(options, error);
        });

        return this;
    }

    ProductSorter.createErrorElement = function (options, message) {
        var parent = options.targetDOM;
        var setups = options.error;
        var element = document.createElement(setups.selector);
        element.className = setups.className;
        element.innerHTML = message || setups.message;
        parent.appendChild(element);
    };

    ProductSorter.EVENTS = {
        ON_ITEMS_CHANGED: 'ProductSorter:ON_ITEMS_CHANGED'
    };

    ProductSorter.prototype = {
        EVENTS: ProductSorter.EVENTS,
        options: {
            targetDOM: document.querySelector('body'),
            grid: {
                cols: 5,
                rows: 2
            },
            uploadButton: {
                label: 'Upload Next Page',
                className: 'nd-btn upload',
                selector: 'button'
            },
            error: {
                className: 'nd-list__error',
                selector: 'div',
                message: 'Empty data'
            },
            list: {
                className: 'nd-list',
                selector: 'ul'
            },
            item: {
                className: 'nd-list__item',
                selector: 'li',
                width: '166px',
                margin: '20px',
                template: '<span>Say Hello</span>',
                states: {
                    attached: 'attached',
                    disableLeft: 'disableLeft',
                    disableRight: 'disableRight',
                    over: 'over'
                }
            },
            separator: {
                className: 'nd-list__separator',
                selector: 'li',
                template: ''
            }
        },
        getDataStoring: function () {
            return this.dataStoring;
        },
        updateDataStoring: function (elements) {
            var store = this.getDataStoring();
            var recentlyUpdated = [];

            _.forEach(elements, function (element) {
                if (element.dataset.isDirty !== 'true') {
                    return;
                }
                var item = element.dataset;
                var replacedItem = _.find(store, 'entity_id', item.entity_id);
                if (replacedItem) {
                    replacedItem = item;
                } else {
                    store.push(item);
                }
                recentlyUpdated.push(item);
            });

            if (this.emit) {
                this.emit(this.EVENTS.ON_ITEMS_CHANGED, recentlyUpdated);
            }
        },
        uploadPage: function (callback) {
            var self = this;
            var options = this.options;
            var cols = options.grid.cols;
            var rows = options.grid.rows;
            var count = +cols * +rows;

            options.connector.$getPage(++this.page, count, function(data) {
                self.onUploadedPage(data);
                if (callback) {
                    callback();
                }
            });
        },

        doSave: function () {
            var data = this.getDataStoring();
            var connector = this.options.connector;
            return connector.$save(data);
        },
        //method to handle product to page move
        afterLoadPage: function(event, self) {
            // ckeck if the page load was initiated by move to page
//        self.pageSkip = false;
        }
    };

    container.NEKLO.ProductSorter = container.NEKLO.SimpleEventEmitter ? container.NEKLO.SimpleEventEmitter.mixin(ProductSorter) : container.NEKLO.ProductSorter;
})(window, document, _, jQuery);
(function (container, document, _) {
    container.NEKLO = container.NEKLO || {};

    var Element = Class.create();

    Element.prototype = Object.assign(
        Object.create(container.NEKLO.SorterElement.prototype),
        {
            initialize: function (setups, options, parent, onImageLoaded) {
                this.el = container.NEKLO.SorterElement.call(this, setups, options, parent, onImageLoaded);
            },

            searchElementSibling: function (element, prefix) {
                if (!element) {
                    return
                }
                if (element.dataset.attached === 'true' || !element.classList.contains(this.setups.className)) {
                    if (prefix === 'next') {
                        return this.searchElementSibling(element.nextSibling, prefix);
                    } else {
                        return this.searchElementSibling(element.previousSibling, prefix);
                    }
                }
                return element;
              },

            //initialize page load if newPage > pages loaded.
            handlerGoToPage: function (el) {
                var self = this;
                if (this.parent == undefined) {
                    el = this;
                    self = el.$$instance;
                }

                var value = jQuery(el).find('.to-page-inp')[0].value;
                value = parseInt(value);
                if (!value) return;
                var parent = el.$$instance.getParentInstance();
                var sort = parent.sorter;
                sort.page;
                var pages = (sort.totalItems / sort.getPageSize()).ceil();

                var loading = [];
                var fnc = function(){
                    if (loading.length) {
                        loading.pop();
                    }
                    if (loading.length == 0) {
                        var newPosition = (sort.getPageSize() * (value - 1));
                        newPosition += 1;

                        var items = null;
                        var begin, end, reverse;

                        if (+el.dataset.position > newPosition) {
                            begin = newPosition;
                            end = +el.dataset.position;
                            reverse = false;
                        } else {
                            begin = +el.dataset.position;
                            end = newPosition;
                            reverse = true;
                        }
                        jQuery(el).find('.to-page-frm')[0].setStyle({display: 'none'});//hide page form
                        items = parent.sortData(begin, end, el, reverse);
                        parent.onListUpdated(items)
                        if (sort.emit) {
                            sort.emit(sort.EVENTS.ON_ITEMS_CHANGED, items);
                        }
                        parent.page = value;
                        parent.updateList(sort.getItemData(value), value, sort.options.item);
                        sort.pager.updateState(value, sort.totalItems);
                    }
                }
                if (value >= 1) {

                    //load page before and after
                    if (value > 1 && sort.loadedPages.indexOf(value-1) < 0) {
                        loading.push(1);
                        sort.uploadData(value-1, fnc.bind(this))
                    }
                    if (value < pages && sort.loadedPages.indexOf(value+1) < 0) {
                        loading.push(1);
                        sort.uploadData(value+1, fnc.bind(this))
                    }
                    if (sort.loadedPages.indexOf(value) < 0) {
                        loading.push(1);
                        sort.uploadData(value, fnc.bind(this))
                    } else {
                        fnc()
                    }
                }
            }

        });

    var List = Class.create();
    List.prototype = Object.assign(

        Object.create(container.NEKLO.SorterList.prototype), {

        initialize: function (setups) {
            this.el = container.NEKLO.SorterList.call(this, setups);
        },

        sortDataByOrder: function (array, dragElement, position) {
            var needSortedElement;
            var idx = false;
            _.forEach(array, function (item, index) {
                if (item.attached === 1 || item.attached === true || item.attached === 'true') {
                    return;
                } else if (idx === false) {
                    idx = index;
                }

                item.isDirty = true;
                if (needSortedElement) {
                    needSortedElement.position = item.position;
                }

                needSortedElement = item;

                if (item.entity_id === dragElement.dataset.entity_id) {
                    item.position = position + (isNaN(idx) ? 0 : idx);
                }
            });

            array.sort(function (a, b) {
              return (+a.position) - (+b.position)
            });
            return array;
          },

        sortData: function (begin, end, dragElement, reverse) {
            var self = this;
            var beginForItems = begin - 1;
            var endForItems = end - 1;
            var sort = this.sorter;
            var items = sort.pageItemsStorge;
            var position;
            items = _.slice(items, beginForItems, endForItems + 1);
            var oldItems = _.clone(items, true);

            if (reverse) {
                position = end;
                items = items.reverse();
            } else {
                position = begin;
            }

            items = this.sortDataByOrder(items, dragElement, position);
//            self.sortElementsInDOM(items, oldItems);
//            self.updateElementsState();
//            self.onListUpdated(items);
            return items;
        },

        sortElements: function (begin, end, dragElement, reverse) {

            var self = this;
            var beginForItems = begin - 1;
            var endForItems = end - 1;
            var sort = this.sorter;
            var offset = (this.page - 1) * (sort.getPageSize());
            beginForItems -= offset;
            endForItems -= offset;
            var position;
            var items = this.getListElements();
            items = _.slice(items, beginForItems, endForItems + 1);
            var oldItems = _.clone(items, true);

            if (reverse) {
                position = end;
                items = items.reverse();
            } else {
                position = begin;
            }

            items = this.sortElementsByOrder(items, dragElement, position);
            self.sortElementsInDOM(items, oldItems);
            self.updateElementsState();
            self.onListUpdated(items);
        },

        updateList: function (data, page, setups) {
            this.page = page;
            this.updateSetups = setups;
            container.NEKLO.SorterList.prototype.updateList.call(this, data, setups)
        },

        createListItems: function (data, setups) {
            var self = this;
            var i = 0;
            var length = data.length;
            var list = this.getListElement();
            list.innerHTML = '';
            var imgCount = 0;

            var onImageLoaded = function () {
                ++imgCount;
                if (imgCount === length) {
                    self.updateElementsState();
                }
            }
            for (i; i < length; i++) {
                if (typeof data[i].name === 'undefined') continue;
                var element = new Element(setups, data[i], list, onImageLoaded);
                list.appendChild(element.el);
            }


            return list;
        },

        updateElementsState: function () {
            var elements = this.getListElements();
            var height = 0;
            _.forEach(elements, function (o) {
                if (o.clientHeight > height) {
                    height = o.clientHeight
                }
            });
            _.forEach(elements, function (element) {
                element.$$instance.updateElementState(element.dataset, height);
            });
        }
    })


    var Pager = Class.create();

    Pager.prototype = {
        initialize: function (options) {
            this.options = options || {};
            var setups = options.separator;

            var template = setups.template;

            var button = document.createElement(setups.selector);
            button.className = setups.className;
            button.innerHTML = template;

            this.pageTemplate = $('template-paging-item').innerHTML;

            //        var setups = options.uploadButton;
            //        var button = document.createElement(setups.selector);
            button.$$instance = this;
            button.$$instance.getElement = function () {
                return jQuery(button);
            };
            this.el = button;
            //        button.addEventListener('click', this.uploadPage, false);
            return button;
        },

        updateState: function (page, totalItems) {

            var el;
            var sizeOfPage = this.options.grid.cols * this.options.grid.rows;
            this.pagesCount = Math.ceil(totalItems / sizeOfPage);
            this.page = page;
            if (page > this.pagesCount) {
                return;
            }
            if (this.pagesCount == 1) {
                $$('.nd-list__separator')[0].hide();
                return;
            }
            this._preparePages(page);

            var init = this.getElement().attr('initialized') * 1;
            //update clicker

            var btn = jQuery(this.getElement().find('.page-left')[0]);
            if (page > 1) {
                btn.removeClass('disabled');
                if (!init) {
                    addEvent(btn, 'click', this.updatePage.bind(this));
                }
            } else {
                btn.addClass('disabled');
            }
            if (!init) {
                addEvent(btn, 'click', this.updatePage.bind(this));
            }
            btn = jQuery(this.getElement().find('.page-right')[0]);
            if (page < this.pagesCount) {
                btn.removeClass('disabled');
                if (!init) {
                    addEvent(btn, 'click', this.updatePage.bind(this));
                }
            } else {
                btn.addClass('disabled');
            }

            el = jQuery(this.getElement().find('.page')[0]);
            if (!init) {
                Element.prototype.addPageOptions(el, this.pagesCount, page);
                addEvent(el, 'change', this.updatePage.bind(this));
            } else {
                el.find('option').removeAttr('selected');
                el.find('option[value="'+page+'"]').prop('selected', 'selected');
            }
            el.val(page);

            var html = this.getElement().find('.total-pages')[0];
            html.innerHTML = html.innerHTML.replace('%s', this.pagesCount);

            html = this.getElement().find('.total-items')[0];
            html.innerHTML = html.innerHTML.replace('%d', totalItems);
            this.getElement().attr('initialized', 1);
        },

        _preparePages: function (page) {
            var holder = this.getElement().find('.pager-holder')[0];
            var pages = this.pagesCount;
            holder.innerHTML = '';
            var el;
            var pageRange = 1;
            var rangeStart = page - pageRange;
            var rangeEnd = page + pageRange;

            if (rangeEnd > pages) {
                rangeEnd = pages;
                rangeStart = pages - pageRange * 2;
                rangeStart = rangeStart < 1 ? 1 : rangeStart;
            }

            if (rangeStart <= 1) {
                rangeStart = 1;
                rangeEnd = Math.min(pageRange * 2 + 1, pages);
            }
            //render items


            var i;
            // Page numbers
            if (rangeStart <= 3) {
                for (i = 1; i < rangeStart; i++) {
                    el = _.template(this.pageTemplate)({page: i});
                    holder.insert(el);
                    el = holder.children[holder.children.length - 1];
                    if (i == page) {
                        el.addClassName('active');
                    } else {
                        addEvent(el, 'click', this.uploadPage.bind(this));
                    }
                }
            } else {
                el = _.template(this.pageTemplate)({page: '1'});
                holder.insert(el);
                el = holder.children[holder.children.length - 1];
                addEvent(el, 'click', this.uploadPage.bind(this));

                el = _.template(this.pageTemplate)({page: '...'});
                holder.insert(el);
                el = holder.children[holder.children.length - 1];
                el.setStyle({cursor: 'default'})
            }

            // Main loop
            for (i = rangeStart; i <= rangeEnd; i++) {
                el = _.template(this.pageTemplate)({page: i});
                holder.insert(el);
                el = holder.children[holder.children.length - 1];
                if (i == page) {
                    el.addClassName('active');
                } else {
                    addEvent(el, 'click', this.uploadPage.bind(this));
                }
            }

            if (rangeEnd >= pages - 2) {
                for (i = rangeEnd + 1; i <= pages; i++) {
                    el = _.template(this.pageTemplate)({page: i});
                    holder.insert(el);
                    el = holder.children[holder.children.length - 1];
                    addEvent(el, 'click', this.uploadPage.bind(this));
                }
            } else {
                el = _.template(this.pageTemplate)({page: '...'});
                holder.insert(el);
                el = holder.children[holder.children.length - 1];
                el.setStyle({cursor: 'default'})

                el = _.template(this.pageTemplate)({page: pages});
                holder.insert(el);
                el = holder.children[holder.children.length - 1];
                addEvent(el, 'click', this.uploadPage.bind(this));
            }

        },

        updateWidth: function (list) {
            this.prototype.updateWidth.call(this, list);
        },
        updatePage: function (e) {
            var page = this.page;
            if (e.type == 'click') {
                e.preventDefault();
                var move = e.target.readAttribute('data-move') * 1;
                if (page == 1 && move < 0)
                    return;
                if (page == this.pagesCount && move > 0)
                    return;
                page += move;
            } else if (e.type == 'change') {
                page = e.target.value * 1;
            } else {
                if (e.charCode == 13) {
                    page = e.target.value * 1;
                }
            }
            this.onUploadPage(page);
        },

        uploadPage: function (e) {
            var self = this;
            e.preventDefault();
            var page = jQuery(e.target).attr('data-page') * 1;
            if (isNaN(page))
                return;

            this.onUploadPage(page);
        },
    }

    /**
     * @class
     * @extend SimpleEventEmitter
     * @constructor
     */
    var ProductPager = Class.create();

    ProductPager.prototype = {
        initialize: function (options) {

            options = options || {};
            var self = this;
            this.EVENTS = ProductPager.EVENTS;
            this.dataStoring = [];
            this.totalItems = options.totalItems;

            this.pageItemsStorge = this.initItems(options.categoryData);///where we have ouritem cache
            this.loadedPages = [];
            this.page = 1;

            delete options.totalItems;
            this.options = _.merge(this.options, options);
            var count = this.getPageSize();
            //call for data for the first page
            this.options.connector.$getPage(this.page, count, function (data) {
                var options = self.options;
                var targetDOM = options.targetDOM;
                if (!data.length) {
                    return ProductPager.createErrorElement(options);
                }
                self.loadedPages.push(self.page);
                self.addItemData(data, self.page);
                var list = new List(options);
                var pager = new Pager(options);

                self.lister = list;
                self.pager = pager;
                self.onUploadedPage = function (data) {
                    self.addItemData(data, self.page);
                    list.updateList(self.getItemData(self.page), self.page, options.item);
                    pager.updateState(self.page, self.totalItems);
                };

                list.sorter = self;
                targetDOM.appendChild(list.el);
                targetDOM.appendChild(pager.el);

                list.onListUpdated = self.updateDataStoring.bind(self);
                pager.onUploadPage = self.uploadPage.bind(self);
                list.updateList(self.getItemData(self.page), self.page, options.item);
                pager.updateState(self.page, self.totalItems);

//                pager.updateWidth(list);
                targetDOM.$$instance = self;

            }, function (error) {
                ProductPager.createErrorElement(options, error);
            });

            return this;
        },
        getPageSize: function () {
            return +this.options.grid.cols * +this.options.grid.rows;
        },
        addItemData: function (data, page) {
            var length = this.getPageSize();
            var offset = (page - 1) * length;
            var store = this.getDataStoring();
            for (var i = 0; i < data.length; i++) {
                this.pageItemsStorge[offset + i] = data[i];
            }
        },
        //get data for page
        getItemData: function (page) {
            var length = this.getPageSize();
            page = page * 1;
            if (page < 1) {
                page = 1;
            }
            var offset = (page - 1) * length;
            var out = [];
            for (var i = 0; i < length; i++) {
                if (this.pageItemsStorge[offset + i] != undefined) {
                    out.push(this.pageItemsStorge[offset + i]);
                }
            }
            return out;
        },
        options: container.NEKLO.ProductSorter.prototype.options,
        createErrorElement: container.NEKLO.ProductSorter.createErrorElement,

        getDataStoring: function () {
            return this.dataStoring;
        },

        updateDataStoring: function (elements) {

            var self = this;
            var store = this.getDataStoring();
            var recentlyUpdated = [];
            var length = this.getPageSize();
            var offset = (this.page - 1) * length;

            _.forEach(elements, function (element) {
                var data = element.dataset ? element.dataset : element;
                _.find(self.pageItemsStorge, function (item, index) {
                    if (+item.entity_id == +data.entity_id) {
                        item.position = +data.position;
                        item.attached = data.attached;
                        item.isDirty = data.isDirty;
                        return true;
                    }
                    return false;
                });

                if (data.isDirty !== 'true') {
                    return;
                }

                var replacedItem = _.find(store, 'entity_id', data.entity_id);

                if (replacedItem) {
                    replacedItem = Object.assign(replacedItem, data);
                } else {
                    store.push(data);
                }
                recentlyUpdated.push(data);
            });

            this.pageItemsStorge.sort(function (a, b) {
                return (a.position > b.position) ? 1 : ((b.position > a.position) ? -1 : 0);
            });

            if (this.emit) {
                this.emit(this.EVENTS.ON_ITEMS_CHANGED, recentlyUpdated);
            }
        },

        uploadData: function (page, callback) {
            var self = this;
            var options = this.options;
            var cols = options.grid.cols;
            var rows = options.grid.rows;
            var count = +cols * +rows;
            options.connector.$getPage(page, count, function (data) {
                self.addItemData(data, page);
                self.loadedPages.push(page);
                if (callback) {
                    callback();
                }
            });
        },

        uploadPage: function (page, callback) {
            var self = this;
            var options = this.options;
            var cols = options.grid.cols;
            var rows = options.grid.rows;
            var count = +cols * +rows;
            var offset = (page - 1) * count;
            if (this.loadedPages.indexOf(page) > -1) {
                if (this.pageSkip) {
                    if (callback) {
                        callback();
                    }
                } else {
                    self.page = page;
                    self.lister.updateList(self.getItemData(self.page), self.page, options.item);
                    self.pager.updateState(self.page, self.totalItems);
                }
            } else {
                options.connector.$getPage(page, count, function (data) {
                    self.page = page;
                    self.loadedPages.push(self.page);
                    self.onUploadedPage(data);
                    if (callback) {
                        callback();
                    }
                });
            }
        },

        initItems : function (categoryData) {
            var data = [], tmp;
            categoryData.position.each(function(item) {
                tmp = {
                    entity_id: item.key,
                    position: item.value,
                    attached: categoryData.attached.get(item.key) == '1' ? true : false
                }
                data.push(tmp)
            });
            data.sort(function (a, b) {
                return (a.position > b.position) ? 1 : ((b.position > a.position) ? -1 : 0);
            });
            return data;
        },

        doSave: function () {
            var data = this.getDataStoring();
            var connector = this.options.connector;
            return connector.$save(data);
        }
    }

    ProductPager.EVENTS = container.NEKLO.ProductSorter.EVENTS;

    container.NEKLO.ProductPager = container.NEKLO.SimpleEventEmitter ? container.NEKLO.SimpleEventEmitter.mixin(ProductPager) : container.NEKLO.ProductPager;
})(window, document, _);
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
})(window, document, _);});
