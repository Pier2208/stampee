export default class Loader {
  constructor(el) {
    this._el = el;
    this._elBody = document.getElementsByTagName('body')[0];
    
    this.init();
  }

  init = () => {
    this._el.addEventListener('click', this.showLoader);
  };

  showLoader = () => {
    this._elBody.insertAdjacentHTML(
      'beforeend',
      ` <div class='overlay'>
            <div class='lds-hourglass'></div>
        </div>`
    );
  };
}
