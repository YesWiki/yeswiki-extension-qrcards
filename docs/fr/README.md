# Extension Qrcard

Permet de créer des cartes imprimables avec un qrcode associé pour prolonger l'expérience en ligne.



## Installation

Dans la page `GererMisesAJour` de votre YesWiki, recherchez l'extension **qrcards** et installez-la.

## Utilisation

Pour simplifier l'usage, l'extension ajoute dans la barre du haut un "lanceur", un menu avec des raccourcis vers les fonctions principales.

### Créer des cartes

Dans le lanceur (menu en haut à droite),  aller dans l'onglet "Ajouter une carte" (page `AjouterQrCards`) et remplir les champs.

### Voir les cartes

Dans le lanceur (menu en haut à droite),  aller dans la page "Voir les cartes" (page `VoirQrCards`) pour visualiser l'ensemble des cartes. Le passage de la souris permet de visualiser le verso des cartes.

### Imprimer une sélection de cartes

Dans la page `VoirQrCards`, à partir de l'onglet "Cartes" (ou "Mes cartes favorites"), choisir la vue "Vue impression".  

Retirer les cartes que vous ne souhaitez pas imprimer en cliquant sur le lien "Ne pas imprimer cette carte".

Utilisez la fonction Fichier > Imprimer du navigateur (ou le raccourci clavier Ctrl+P sur PC / Cmd+P sur Mac) pour imprimer.   
 

### Choisir ses cartes favorites

Attention, il faut être identifié.e sur le wiki pour pouvoir mettre des cartes en favoris, veuillez donc tout d'abord Créeer votre compte et vous identifier.

Ensuite, dans la page `VoirQrCards` (menu en haut à droite Roue crantée > QrCards), cliquer sur l'étoile en dessous de la carte de votre choix pour en faire votre carte favorite.

Vous retrouverez toutes vos cartes favorites dans l'onglet "Cartes favorites"

### Exporter des cartes

Il est possible d'exporter les cartes en vue de les sauvegarder ou de les reinstaller sur un autre site. Pour cela, il faut télécharger le fichier CSV correspondant au formulaire QR cards

http://www.monsuperwikiqrcards.com/?BazaR&vue=exporter&id=1400

### Importer des cartes

Lorsque l'on souhaite créer de nombreuses cartes rapidemment, il est possible d'importer des contenus à partir d'un tableur. Pour cela, il faut télécharger le fichier CSV correspondant au formulaire QR cards. 

http://www.monsuperwikiqrcards.com/?BazaR&vue=exporter&id=1400

Ouvrir le fichier .CSV en choisissant la virgule comme séparateur, puis dans le tableau remplir les différents champs.

Enfin il faut réimporter le tableau dans la base de données `QrCards`

http://www.monsuperwikiqrcards.com/?BazaR&vue=importer


## Personnaliser les cartes (usage simple)

### Changer ou ajouter des types de carte

Dans le mode visualisation des cartes (`VoirQrCards`), en dessous de chaque carte, il est possible de visualiser (icone oeil) ou éditer (icone crayon) une carte. 

Modifier le formulaire pour personnaliser la carte.

## Personnaliser les cartes (usage avancé)

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



## Un peu d'histoire

Cette extension est issue d'une collaboration entre Lilian Ricaud (lilious) et Florian Schmitt (mrflos), afin de faire émerger un outil numérique de création de cartes de type metacartes, afin de faciliter la production de prototypes de cartes, accompagner des projets de créations de carte sur d'autres thématiques, et réfléchir à des systèmes innovants de partage, d'animation, et de curations.

Depuis 2023, le projet a bénéficié d'un financement de l'ADEME dans le cadre de son Appel à commun. 

Dans ce cadre Mélanie (Mélanie_lac) et Lilian,via Ozon et Métacartes, portent juridiquement le projet auprès de l'ADEME et organisent des temps de travail en physique, les résidences Mets ta carte, des ateliers pluridisciplinaires pour faire avancer le projet. Florian continue le developpement avec l'aide de Lilian comme product owner.


## Changelog

- aout 2024 : sortie V0.7.2 :
 - correction d'un bug d'affichage lorsque la base de données est dupliqué de la base principale.
 - correction d'un bug d'affichage dans le nombre de lignes champs essentiel
- aout 2024 : sortie V0.7.1 : Ajout d'un cut off sur le nombre de lignes affichées dans le champs essentiel.
- mai 2024 : sortie V0.7   
 - version stabilisée ajoutée aux extensions yeswiki et accessible via la branche prod
 - correction bug impresison firefox
 - changement de la structure de la base de données
 - intégration des modification des pages associées (formulaire, cartes exemples, menus...)
