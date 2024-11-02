# Extension Qrcard

Permet de créer des cartes imprimables avec un qrcode associé pour prolonger l'expérience en ligne.

# Documentation

voir la doc complète ici : https://metacartes.net/qrcartes/

# Changelog

## QR cards V0.8.1 - Liste des changements

- mise à jour de compatibilité avec la future version de YesWiki (4.5)
- ajout fonctionnalité duplication : permet de cloner des cartes existantes à l'intérieur du même wiki ou d'un wiki distant auquel on accès en écriture.
- ajout fonctionnalité sets de cartes : permet de créer des sets de cartes distincts à partir de la même base de données. Des cartes peuvent être associées à un ou plusieurs sets par simple glisser/déposer. Cette fonctionnalité résous partiellement le bug qui empêchait d'avoir plusieurs sets de cartes sur un même wiki.
- ajout catégorisation par mots clés : cette fonctionnalité permet maintenant d'associer un ou plusieurs mots clés à une carte pour pouvoir avoir ensuite bénéficier de fonctions de tri sans nécessité de créer des listes au préalable. 
- mise à jour d'une option de classement par type de cartes : en plus des recettes et ingrédients les types de cartes proposent maintenant 10 types tirés du modèle des graines d'informations. Cette fonction favorise interopérabilité entre des sets de cartes.
- ajout d'une option cartes liées pour pouvoir lier des cartes entre elles.
- ajout d'une option de géolocalisation des cartes : permet de positionner les données des qr cartes sur une carte géographique grâce à la fonction cartographie de YesWiki.
- ajout d'une option de datation des cartes : permet de situer des évènements et d'associer les cartes à la fonction calendrier de YesWiki.
- mise à jour ergonomie du formulaire de saisie de cartes : 
    - mise à jour des formulations et des textes d'aide pour simplifier la compréhension et l'utilisation
    - ajout mise en page sous forme d'onglets pour limiter la surcharge informationnelle.
    - ajout de possibilité de formatage dans le champs essentiel pour afficher contenus formatés sur les cartes.
- Mise à jour des pages par défaut : nouvelle page documentation avec légende
- Mise à jour du lanceur : mise à jour des pages du lanceur.

Consignes pour la migration/mise à jour de QR cartes 0.8

deux options : 
    - attendre la sortie de la version 4.5 de yeswiki avant de faire la mise à jour.
    - usagers avancés : vous pouvez passer en version testing

Nous avons choisi de ne pas faire de migration forcée qui pourraient casser des données. Il vous faut passer manuellement. 
Important: Télécharger vos données en CSV avant toute mise à jour.


## Changements passés 

- aout 2024 : sortie V0.7.2 :
 - correction d'un bug d'affichage lorsque la base de données est dupliqué de la base principale.
 - correction d'un bug d'affichage dans le nombre de lignes champs essentiel
- aout 2024 : sortie V0.7.1 : Ajout d'un cut off sur le nombre de lignes affichées dans le champs essentiel.
- mai 2024 : sortie V0.7   
 - version stabilisée ajoutée aux extensions yeswiki et accessible via la branche prod
 - correction bug impresison firefox
 - changement de la structure de la base de données
 - intégration des modification des pages associées (formulaire, cartes exemples, menus...)
