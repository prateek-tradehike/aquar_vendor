/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the NekloEULA that is bundled with this package in the file LICENSE.txt.
 *
 * It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt
 *
 * Copyright (c)  Neklo (http://store.neklo.com/)
 */

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
