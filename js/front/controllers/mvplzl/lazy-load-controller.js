import { decode } from 'blurhash';

import ApplicationController from '../../../common/application-controller';

export default class extends ApplicationController {
  static targets = ['canvas', 'image'];

  static values = {
    componentsX: Number,
    componentsY: Number,
    blurhash: String,
    src: String,
    imageWidth: Number,
    imageHeight: Number,
  };

  #intersectionObserver;

  connect() {
    super.connect();

    this.later(() => {
      this.setupCanvas();
      this.setupObserver();
    });
  }

  disconnect() {
    super.disconnect();

    this.tearDownObserver();
  }

  get canvasContext() {
    return this.canvasTarget.getContext('2d');
  }

  get imageRatio() {
    return this.imageWidthValue / this.imageHeightValue;
  }

  setupCanvas() {
    const pixels = decode(this.blurhashValue, 32, 32);
    const imageData = this.canvasContext.createImageData(32, 32);

    imageData.data.set(pixels);
    this.canvasContext.putImageData(imageData, 0, 0);
  }

  setupObserver() {
    this.#intersectionObserver = new IntersectionObserver(
      this.handleIntersection,
      { rootMargin: '20% 0px', threshold: 0 }
    );

    this.#intersectionObserver.observe(this.element);
  }

  tearDownObserver() {
    if (this.#intersectionObserver) {
      this.#intersectionObserver.disconnect();
      this.#intersectionObserver = null;
    }
  }

  handleIntersection = (entries) => {
    entries.forEach(({ target, isVisible, isIntersecting }) => {
      if (target === this.element && (isVisible || isIntersecting)) {
        this.startLazyLoad();
      }
    });
  };

  // TODO: !!!
  // TODO: Try loading the image off-canvas to see if it improves scrolling performance on Chrome
  // TODO: !!!
  startLazyLoad() {
    this.tearDownObserver();
    this.imageTarget.src = this.srcValue;
  }

  loaded() {
    this.data.set('status', 'loaded');
  }
}
