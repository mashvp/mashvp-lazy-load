import { Controller } from 'stimulus';
import { publish, subscribe, unsubscribe } from 'pubsub-js';
import camelCase from 'lodash.camelcase';

class ApplicationController extends Controller {
  constructor(...args) {
    super(...args);

    this.__timeouts = [];
    this.__events = [];
    this.__subscriptions = [];

    this.element[this.controllerName] = this;
  }

  /**
   * Don't forget to call `super.disconnect()` if you override the disconnect hook.
   */
  disconnect() {
    this.clearAllTimeouts();

    this.__events.forEach(({ receiver, event, callback }) => {
      receiver.removeEventListener(event, callback);
    });

    this.__subscriptions.forEach((subscription) => {
      unsubscribe(subscription);
    });

    this.element[this.controllerName] = null;
  }

  /**
   * The controller name in camel came
   */
  get camelCaseIdentifier() {
    return camelCase(this.identifier);
  }

  /**
   * The controller name formatted as {name}Controller.
   */
  get controllerName() {
    return `${this.camelCaseIdentifier}Controller`;
  }

  /**
   * Executes a callback function after some delay.\
   * Same as window.setTimeout, but timeouts are cancelled automatically on disconnect.
   * - Don't forget to call `super.disconnect()` if you override the disconnect hook.
   *
   * @param {function} callback
   * @param {number} [delay=0]
   */
  later(callback, delay = 0) {
    this.__timeouts.push(setTimeout(callback, delay));
  }

  /**
   * Clears all registered timeouts without waiting for controller disconnect.
   */
  clearAllTimeouts() {
    this.__timeouts.forEach((id) => clearTimeout(id));
    this.__timeouts = [];
  }

  /**
   * Binds the given callback to the receiver's event.\
   * Same as HTMLElement.addEventListener, but listeners are removed automatically on disconnect.
   * - Don't forget to call `super.disconnect()` if you override the disconnect hook.
   * - If events is a space-separated list of events, each events gets bound.
   *
   * @param {HTMLElement} receiver
   * @param {string} events
   * @param {function} callback
   * @param {boolean | AddEventListenerOptions} [options]
   */
  bind(receiver, events, callback, options) {
    const listeners = events.split(' ').map((event) => {
      const data = { receiver, event, callback };

      receiver.addEventListener(event, callback, options);
      this.__events.push(data);

      return data;
    });

    if (listeners.length === 1) {
      return listeners[0];
    }

    return listeners;
  }

  /**
   * Unbinds all listeners of the given event types.
   *
   * @param {string[]} events
   */
  unbind(...events) {
    const matchingEvents = this.__events.filter(({ event }) =>
      events.includes(event)
    );

    const rest = this.__events.filter(({ event }) => !events.includes(event));

    matchingEvents.forEach(({ receiver, event, callback }) => {
      receiver.removeEventListener(event, callback);
    });

    this.__events = rest;
  }

  /**
   * Unbinds all given listeners.
   *
   * @param {Object[]} listeners
   */
  unbindListeners(...listeners) {
    listeners.forEach((listener) => {
      const { receiver, event, callback } = listener;

      receiver.removeEventListener(event, callback);
      this.__events.pop(listener);
    });
  }

  /**
   * Publishes a PubSubJS event. Helper function.
   *
   * @param {string} message Message descriptor
   * @param {*} data User data
   */
  publish(message, data) {
    return publish(message, data);
  }

  /**
   * Subscribes to a PubSub event.\
   * Same as the native PubSubJS subscribe function, but unsubscribes automatically on disconnect.
   * - Don't forget to call `super.disconnect()` if you override the disconnect hook.
   *
   * @param {string} message
   * @param {SubscriptionCallback} func
   */
  subscribe(message, func) {
    const subscription = subscribe(message, func);

    this.__subscriptions.push(subscribe);

    return subscription;
  }

  /**
   * Unsubscribes from a PubSub event.
   *
   * @param {Subscription} subscription
   */
  unsubscribe(subscription) {
    unsubscribe(subscription);

    this.__subscriptions = this.__subscriptions.filter(
      (sub) => sub !== subscription
    );
  }
}

export default ApplicationController;
