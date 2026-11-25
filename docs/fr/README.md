# Extension QR cartes

Crée des cartes imprimables avec un QR code associé pour prolonger l'expérience en ligne. Chaque carte est une fiche : le recto et le verso sont imprimés, le QR code renvoie vers la page de la fiche sur le wiki.

Documentation complète : https://metacartes.net/qrcartes/

## Ce que l'extension installe

`./yeswicli migrate` (ou l'installation depuis `/admin/updates`) crée :

- le formulaire **QrCards** (1400) et le formulaire **Sets de cartes** (1401) ;
- les listes `ListeComplexite`, `ListeDegreDeMaturite` et `ListeTypeCarte` ;
- deux cartes d'exemple, `bienvenue` et `demarrer` ;
- les pages `VoirQrCards`, `AjouterQrCards`, `FavorisQrCards` et `DocumentationQrCartes`, lisibles par tous et modifiables par les admins ;
- le menu `MenuQrCards`, affiché en onglets en haut de ces pages ;
- une entrée « Qr Cartes » dans le menu d'accès rapide, avec ses sous-entrées (elles s'affichent si le menu d'accès rapide montre les menus déroulants, dans `/admin/layout`).

Rien n'est créé si une page, une liste ou un formulaire du même nom existe déjà.

## Afficher des cartes

La liste des cartes est le modèle `qrcards` de `{{entrylist}}` :

```
{{entrylist id="1400" template="qrcards" dynamic="false" groups="bf_tags" titles="Mots clés"}}
```

Trois boutons « Vue impression » préparent l'impression, selon le format voulu :

| Bouton | Adresse | Ce qui sort de l'imprimante |
|---|---|---|
| standard | `&print=standard` | des cartes de 75 × 125 mm, recto et verso côte à côte, deux cartes par feuille A4 |
| A5 | `&print=a5` | une carte par feuille A4 paysage, le recto sur une moitié et le verso sur l'autre |
| A4 | `&print=a4` | le recto sur une feuille A4 et le verso sur la suivante, pour une impression recto verso |

Les formats A5 et A4 agrandissent la carte entière, polices comprises, et utilisent l'image d'origine au lieu de la miniature. La carte garde ses proportions, il reste donc une marge blanche autour. Les anciennes adresses en `&print=1` donnent le format standard. « Ne pas imprimer » retire une carte de la page avant d'imprimer.

Une carte seule s'affiche avec l'action `{{qrcard}}` :

```
{{qrcard tag="bienvenue"}}
{{qrcard tag="bienvenue" view="print"}}
{{qrcard tag="bienvenue" view="print" format="a5"}}
```

| Paramètre | Obligatoire | Rôle |
|---|---|---|
| `tag` | oui | la fiche de la carte |
| `view` | non | `web` (la carte se retourne au survol) ou `print` (recto et verso côte à côte) |
| `format` | non | avec `view="print"` : `standard` (par défaut), `a5` ou `a4`, comme les boutons ci-dessus |

La fiche d'une carte montre la carte à côté des contenus de la page associée. Une fiche **Sets de cartes** affiche les cartes qu'elle contient, dans l'ordre choisi.

Un autre formulaire peut aussi montrer sa fiche sous forme de carte : ajoutez-lui le champ **QR carte** dans le constructeur de formulaires, à l'endroit où la carte doit apparaître. La carte reprend les champs du formulaire QrCards que ce formulaire possède (`bf_titre`, `bf_accroche`, `imagebf_image`, `bf_chapeau`…).

## Ajouter une carte depuis le navigateur

La page `DocumentationQrCartes` propose un bouton « +qrcard » à glisser dans la barre de signets : depuis n'importe quel site, il ouvre le formulaire avec le titre de la page, son adresse dans « Sources » et le texte sélectionné au verso. Le formulaire QrCards a aussi son propre signet, dans la liste des formulaires.

## Personnaliser

La page `ConfigMetacarte`, si elle existe, contient du YAML :

```yaml
template: hexa
card_set_image: https://example.com/logo-du-set.svg
card_colors:
  - field: listeListeTypeCarte
    key: "①"
    color: "#f6d365"
```

- `template` : la mise en page des cartes, `qrcard` par défaut ou `hexa`. Une mise en page à vous se place dans `custom/templates/qrcards/card-layouts/<nom>.twig`.
- `card_set_image` : le logo imprimé en bas des cartes.
- `card_colors` : une couleur de fond selon la valeur d'un champ, à la place de la couleur choisie dans la fiche.

Le paramètre `card_logo` de la configuration du wiki remplace l'image affichée quand une carte n'a pas de visuel.

Les dimensions et les polices des cartes sont des variables CSS, à redéfinir dans `custom/styles/custom.css` :

| Variable | Rôle |
|---|---|
| `--card-width`, `--card-height` | taille de la carte (75 × 125 mm) |
| `--card-border` | bordure de la carte |
| `--card-spacing` | marges des bords en couleur et entre les zones |
| `--card-recto-zone-height` | hauteurs du type, du titre, de l'image, du résumé et du pied de page au recto |
| `--card-verso-zone-height` | hauteurs du type, du titre, du texte long et du pied de page au verso |
| `--type-*` | police du type de carte (en haut à droite) |
| `--title-*` | police du titre, avec `--title-xl-font-size` et `--title-xxl-font-size` pour les titres longs |
| `--text-*` | police de l'accroche au recto |
| `--longtext-*` | police du texte au verso |
| `--link-*` | police du lien en pied de page |
| `--print-a5-scale`, `--print-a4-scale` | agrandissement de la carte en impression A5 (1,67) et A4 (2,36) ; à réduire si vous agrandissez la carte |
| `--picto-*` | police des textes sous les icônes |

# Changelog

## QR cards 5.0 (YesWiki Ectoplasme)

- portage pour YesWiki Ectoplasme : formulaires, listes, menus et pages créés par les migrations au format Ectoplasme
- les cartes sont dessinées en Twig, sans jQuery ni Bootstrap
- action `{{qrcard}}` pour afficher une carte n’importe où
- le menu « Qr Cartes » est une entrée du menu d’accès rapide, les onglets des pages sont le menu `MenuQrCards`
- le signet « +qrcard » est aussi activé sur le formulaire des cartes
- la date de fin d’une carte est un champ de date de fin, et non plus un second champ de date de début

## QR cards V0.8.3 - Liste des changements

- ajout du site de la documentation dans le menu
- corrections bugs impression des cartes
- taille des titres adaptatif en fonction de la longueur
- taille des textes pictos un poil plus grande
- ajout du bookmarklet dans la doc

## QR cards V0.8.2 - Liste des changements

- correction des erreurs à l'impression des cartes en couleurs
- on cache les facettes pour l'impression

## QR cards V0.8.1 - Liste des changements

- mise à jour du formulaire avec correction aide
- cartes par défaut ajout de tags et correction du texte
- ajout icone personnalisée lanceur
- correction bug legacy/list : qu'importe la version wiki les listes sont bonnes. duplication apparaît seulement sur la 4.5.
- correction bug import : corrige le problème d'import de fiches lorsque les listes sont vides

## QR cards V0.8.0 - Liste des changements

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