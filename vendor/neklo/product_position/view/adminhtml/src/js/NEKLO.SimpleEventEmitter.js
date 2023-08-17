/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the NekloEULA that is bundled with this package in the file LICENSE.txt.
 *
 * It is also available through the world-wide-web at this URL: http://store.neklo.com/LICENSE.txt
 *
 * Copyright (c)  Neklo (http://store.neklo.com/)
 */

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
