/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the NekloEULA that is bundled with this package in the file LICENSE.txt.
 *
 * It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt
 *
 * Copyright (c)  Neklo (http://store.neklo.com/)
 */

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
