export default class Dropdown {
    constructor(el) {
      this._el = el;
      this._elFilters = this._el.querySelector('.filters__box');
      this._toggleIcon = this._el.querySelector('.navbar__icon > svg');
      this._toggleIconText = this._el.querySelector('.navbar__icon > figcaption');
  
      this.init();
    }
  
    init() {
      this._toggleIcon.addEventListener('click', this.toggleDropdown);
    }
  
    toggleDropdown = () => {
      this._elFilters.classList.toggle('open-filters');
      this._toggleIcon.classList.toggle('rotate');
      this._toggleIconText.textContent === 'Ouvrir les filtres'
        ? (this._toggleIconText.textContent = 'Fermer les filtres')
        : (this._toggleIconText.textContent = 'Ouvrir les filtres');
    };
  }
  