import FilePreview from './classes/FilePreview.js';
import Lightbox from './classes/Lightbox.js';
import Loader from './classes/Loader.js';
import Dropdown from './classes/Dropdown.js';
import Card from './classes/Card.js';

(() => {
  if (document.querySelector('.fileSelect')) {
    new FilePreview(document.querySelector('.fileSelect'));
  }

  const elLightBoxes = document.querySelectorAll('.lightbox');
  for (let elLightBox of elLightBoxes) new Lightbox(elLightBox);

  const elAsyncBtns = document.querySelectorAll('[data-loader]');
  for (let elAsyncBtn of elAsyncBtns) new Loader(elAsyncBtn);

  const elFilters = document.querySelectorAll('.filters');
  for (let elFilter of elFilters) {
    new Dropdown(elFilter);
  }

  // cards
  const elCards = document.querySelectorAll('.card');
  for (let elCard of elCards) new Card(elCard);
})();
