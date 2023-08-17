/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the NekloEULA that is bundled with this package in the file LICENSE.txt.
 *
 * It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt
 *
 * Copyright (c)  Neklo (http://store.neklo.com/)
 */

(function (container){
  container.NEKLO = container.NEKLO || {};
  container.NEKLO.Connectors = {};

  /**
   * @class
   * @param options
   * @constructor
   */
  function FakeConnector (options) {
    this.options = options || {};
  }

  FakeConnector.prototype = {
    $getPage: function (pageCount, count, success, error) {
      var data = [];
      var i = (pageCount - 1) * count + 1;
      var length = pageCount * count + 1;
      for (i; i < length; i++) {
        data.push({
          entity_id: i,
          position: i,
          attached: false,
          name: 'Name ' + i,
          sku: 'sku ' + i,
          price: '$' + i,
          image: ''
        });
      }

      setTimeout(success(data), 1000);
    },
    $save: function (data) {
      return data;
    }
  };

  container.NEKLO.Connectors.FakeConnector = FakeConnector;
})(window);
