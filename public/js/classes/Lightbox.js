export default class Lightbox {
  constructor(el) {
    this._el = el;
    this._imgBox = this._el.querySelector('.lightbox__container');
    this._img = this._imgBox.getElementsByTagName('img')[0];
    this._magnifiedImg = this._imgBox.querySelector('.large-img');
    this._thumbnails = this._el.querySelector('.lightbox__thumbnails');
    this._stampInfos = document.querySelectorAll('[data-js-id]');

    this.init();
  }

  init = () => {
    for (let stampInfo of this._stampInfos) {
      if (this._img.alt === stampInfo.dataset.jsId) {
        stampInfo.classList.remove('hidden');
      }
    }

    this._img.addEventListener('mouseenter', this.enterZoomMode);
    this._thumbnails.addEventListener('click', e => {
      if (e.target.tagName === 'IMG') {
        this._magnifiedImg.style.background = `url(${e.target.src}) no-repeat #fff`;
        this._magnifiedImg.previousElementSibling.src = e.target.src;

        for (let stampInfo of this._stampInfos) {
          if (e.target.alt === stampInfo.dataset.jsId) {
            console.log(e.target.alt)
            stampInfo.classList.remove('hidden');
          } else {
            stampInfo.classList.add('hidden');
          }
        }
      }
    });
  };

  enterZoomMode = () => {
    this._img.addEventListener('mousemove', e => {
      // dimensions de l'image
      let { width, height, top, left } = this._imgBox.getBoundingClientRect();

      // calcul de la position du curseur dans l'image
      let x = e.clientX - left;
      let y = e.clientY - top;

      this._magnifiedImg.style.background = `url(${this._img.src}) no-repeat #fff`;
      this._magnifiedImg.style.opacity = 1;

      let style = this._magnifiedImg.style;

      let xperc = (x / width) * 100;
      let yperc = (y / height) * 100;

      // Add some margin for right edge
      if (x > 0.01 * width) {
        xperc += 0.15 * xperc;
      }

      // Add some margin for bottom edge
      if (y >= 0.01 * height) {
        yperc += 0.15 * yperc;
      }

      // Set the background of the magnified image horizontal
      style.backgroundPositionX = xperc - 9 + '%';
      // Set the background of the magnified image vertical
      style.backgroundPositionY = yperc - 9 + '%';

      // Move the magnifying glass with the mouse movement.
      style.left = x - 110 + 'px';
      style.top = y - 60 + 'px';
    });

    this._img.addEventListener('mouseleave', () => {
      this._magnifiedImg.style.opacity = 0;
    });
  };
}
