# Extension Qrcard

Permet de créer des cartes imprimables avec un qrcode associé pour prolonger l'expérience en ligne.

## Un peu d'histoire

Cette extension est issue d'une collaboration entre Lilian Ricaud (lilious) et Florian Schmitt (mrflos), afin de faire émerger un outil numérique de création de cartes de type metacartes, afin de faciliter la production de prototypes de cartes, accompagner des projets de créations de carte sur d'autres thématiques, et réfléchir à des systèmes innovants de partage, d'animation, et de curations.

## Installation

Dans la page `GererMisesAJour` de votre YesWiki, recherchez l'extension **qrcards** et installez-la.

## Utilisation

### Créer des cartes

Dans la page `AdminQrCards` (menu en haut à droite Roue crantée > QrCards), aller dans l'onglet "Ajouter une carte" et remplir les champs.

### Choisir ses cartes favorites

Attention, il faut être identifié.e sur le wiki pour pouvoir mettre des cartes en favoris, veuillez donc tout d'abord Créeer votre compte et vous identifier.
Ensuite, dans la page `AdminQrCards` (menu en haut à droite Roue crantée > QrCards), cliquer sur l'étoile en dessous de la carte de votre choix pour en faire votre carte favorite.
Vous retrouverez toutes vos cartes favorites dans l'onglet "Mes cartes favorites"

### Imprimer une sélection de cartes

Dans la page `AdminQrCards` (menu en haut à droite Roue crantée > QrCards), à partir de l'onglet "Cartes" ou "Mes cartes favorites", choisir la vue "Vue impression".  
Retirer les cartes que vous ne souhaitez pas imprimer en cliquant sur le lien "Ne pas imprimer cette carte".
Utilisez la fonction Fichier > Imprimer du navigateur (ou le raccourci clavier Ctrl+P sur PC / Cmd+P sur Mac) pour imprimer.   
Attention : le navigateur Firefox ne gère pas bien l'impression en mode portrait et risque de couper les cartes en deux entre deux pages... Veuillez passer en mode paysage dans les options d'impressions, ou utilisez Chromium.

## Personnaliser les cartes

### Changer ou ajouter des types de carte

Dans bazar, onglet liste, modifier la liste "type de carte".

### Changer le logo

Dans `wakka.config.php` : changer `card_logo` par l'url de votre choix

### Changer le style des cartes

Dans la `PageCss`, copier/coller le css suivant et ajustez-le à vos besoins

```css
:root {
  /* largeur de la carte */
  --card-width: 75mm;

  /* hauteur de la carte */
  --card-height: 125mm;

  /* bordure de la carte */
  --card-border: 1px solid rgba(0, 0, 0, 0.3);

  /* marges pour les bords en couleur et entre les zones de la carte */
  --card-spacing: 5mm;

  /* Dans l'ordre, hauteurs de: type de carte / titre / image / texte résumé / pied de page (pictos, qrcode)*/
  --card-recto-zone-height: 5mm 20mm 50mm 20mm 20mm;

  /* Dans l'ordre, hauteurs de: type de carte / titre / texte long / pied de page (pictos, qrcode)*/
  --card-verso-zone-height: 5mm 20mm 70mm 20mm;

  /* typo type de la carte (en haut à droite) */
  --type-font-family: tahoma, sans-serif;
  --type-font-size: 10px;
  --type-color: #222;
  --type-text-transform: uppercase;

  /* typo titre de la carte */
  --title-font-family: garamond, serif;
  --title-font-size: 20px;
  --title-color: #222;
  --title-text-transform: uppercase;

  /* typo texte recto de la carte */
  --text-font-family: tahoma, sans-serif;
  --text-font-size: 16px;
  --text-color: #222;

  /* typo texte verso de la carte */
  --longtext-font-family: tahoma, sans-serif;
  --longtext-font-size: 16px;
  --longtext-color: #222;

  /* typo lien pied de page de la carte */
  --link-font-family: tahoma, sans-serif;
  --link-font-size: 10px;
  --link-color: #222;

  /* typo texte pictos pied de page de la carte */
  --picto-font-family: tahoma, sans-serif;
  --picto-font-size: 8px;
  --picto-color: #222;
}
```