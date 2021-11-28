export default class Card {
  constructor(el) {
    this._el = el;
    this._elFavoriteToggleBtns = this._el.querySelectorAll('[data-js-favorite]');

    this.init();
  }

  init = () => {
    this._elFavoriteToggleBtns.forEach(elFavoriteToggleBtn => {
      elFavoriteToggleBtn.addEventListener('click', e => {
        // changer la couleur du coeur au clic
        e.target.parentNode.classList.toggle('favorite');

        // si on est sur la page des favoris, un clic sur le coeur signifie supprimer la carte d'enchère
        if (document.title === 'Mes favoris | Stampee') {
          const cardToRemove = e.target.closest('.card__header').parentNode;
          cardToRemove.parentNode.removeChild(cardToRemove);

          // s'il n' y pas plus de cartes d'enchère, on affiche un message
          if (!document.querySelector('.grid__cards').children.length) {
            document.querySelector(
              '.dashboard'
            ).innerHTML = `<p class="dashboard__message">Vous n'avez aucun favori dans votre album.</p>`;
          }
        }
        // on récupère l'id de l'enchère
        let auctionId = e.target.closest('.card').dataset.jsCard;

        // que l'on passe en argument à fetch
        this.callFetch({ auction: auctionId });
      });
    });
  };

  callFetch = async data => {
    try {
      return fetch(`/stampee/public/favorite/toggle?auction=${encodeURIComponent(data.auction)}`, {
        method: 'GET'
      });
    } catch (err) {
      throw new Error(`La requête fetch a échouée: ${err.message}`);
    }
  };
}
