export default class FilePreview {
  constructor(el) {
    this._el = el;
    this._elFileInput = this._el.querySelector('.file-select');
    this._elImgPreview = this._el.querySelector('.fileSelect__preview');
    this._previewImage = '';

    this.init();
  }

  init() {
    this._elFileInput.addEventListener('change', () => {
      // clean up DOM from previous thumbnails
      this.resetThumb();
      // get image from file input
      const file = this._elFileInput.files[0];
      // create a local version of the image and store it in the browser: https://flaviocopes.com/url/
      this._previewImage = URL.createObjectURL(file);
      // insert thumb into DOM
      this.insertThumb(this._previewImage);
    });
  }

  /**
   * Add image thumbnail to DOM
   */
  insertThumb = img => {
    this._elImgPreview.insertAdjacentHTML('beforeend', `<img src="${img}" alt="Preview" />`);
  };

  /**
   * Destroy image from browser memory and remove it from DOM
   */
  resetThumb = () => {
    URL.revokeObjectURL(this._previewImage);
    this._elImgPreview.innerHTML = '';
    this._previewImage = '';
  };
}
