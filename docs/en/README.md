# qrcards extension

Creates printable cards with an attached QR code, to extend the experience online.

# Documentation

The full documentation lives here: https://metacartes.net/qrcartes/

# Changelog

## QR cards V0.8.3

- documentation site added to the menu
- card printing bugs fixed
- title size now adapts to the title length
- pictogram text slightly larger
- bookmarklet added to the documentation

## QR cards V0.8.2

- fixed errors when printing cards in colour
- facets hidden when printing

## QR cards V0.8.1

- form updated, help text fixed
- default cards: tags added and text fixed
- custom launcher icon added
- legacy/list bug fixed: lists are now correct whatever the wiki version, duplication only shows on 4.5
- import bug fixed: entries now import correctly when the lists are empty

## QR cards V0.8.0

- made compatible with the upcoming YesWiki version (4.5)
- duplication: clone existing cards inside the same wiki, or from a remote wiki you can write to
- card sets: build distinct card sets from the same database. A card can belong to several sets by drag and drop. This partly solves the bug that prevented having several card sets on one wiki.
- keyword categorisation: attach one or more keywords to a card and sort on them without having to build lists first
- card type sorting: beyond recipes and ingredients, ten types taken from the information seeds model are now offered. This helps card sets work together.
- linked cards: cards can now point at each other.
- geolocation: place QR card data on a map through YesWiki's mapping feature.
- dating: place events in time and feed cards into YesWiki's calendar feature.
- card input form reworked:
    - wording and help texts rewritten to be easier to follow
    - laid out in tabs to cut down the information load
    - formatting allowed in the essential field, so cards can show formatted content
- default pages updated: new documentation page with a legend
- launcher pages updated

### Migrating to QR cards 0.8

Two options:

- wait for YesWiki 4.5 to be released before updating
- advanced users: switch to the testing channel

We chose not to force a migration that could break data, so the move is manual.

**Important: download your data as CSV before any update.**

## Earlier changes

- August 2024, V0.7.2: fixed a display bug when the database is duplicated from the main one; fixed a display bug in the line count of the essential field
- August 2024, V0.7.1: added a cut-off on the number of lines shown in the essential field
- May 2024, V0.7: stabilised version added to the YesWiki extensions and available on the prod branch; fixed a Firefox printing bug; database structure changed; changes to the associated pages integrated (form, sample cards, menus)
