# QR cards extension

Creates printable cards with a QR code that leads back to the card's page on the wiki. Each card is an entry: its front and back are printed, and the QR code opens the entry.

Full documentation (in French): https://metacartes.net/qrcartes/

## What the extension installs

`./yeswicli migrate` (or installing from `/admin/updates`) creates:

- the **QrCards** form (1400) and the **Sets de cartes** card set form (1401);
- the `ListeComplexite`, `ListeDegreDeMaturite` and `ListeTypeCarte` value lists;
- two sample cards, `bienvenue` and `demarrer`;
- the `VoirQrCards`, `AjouterQrCards`, `FavorisQrCards` and `DocumentationQrCartes` pages, readable by anyone and writable by admins;
- the `MenuQrCards` menu, drawn as tabs at the top of those pages;
- a "Qr Cartes" entry in the quick access menu, with its sub-entries (they show when the quick access menu draws dropdowns, in `/admin/layout`).

Nothing is created when a page, list or form of the same name is already there.

## Showing cards

The card list is the `qrcards` template of `{{entrylist}}`:

```
{{entrylist id="1400" template="qrcards" dynamic="false" groups="bf_tags" titles="Mots clés"}}
```

The "Vue impression" button (or `&print=1` in the address) lays the cards out front and back side by side, two per A4 page. "Ne pas imprimer" takes a card off the page before printing.

One card on its own is the `{{qrcard}}` action:

```
{{qrcard tag="bienvenue"}}
{{qrcard tag="bienvenue" view="print"}}
```

| Parameter | Required | Purpose |
|---|---|---|
| `tag` | yes | the card's entry |
| `view` | no | `web` (the card flips on hover) or `print` (front and back side by side) |

A card's entry page shows the card beside the rest of the entry. A card set entry shows the cards it holds, in the order chosen.

Any other form can show its entries as a card too: add the **QR card** field to it in the form builder, where the card should appear. The card uses whichever QrCards form fields that form has (`bf_titre`, `bf_accroche`, `imagebf_image`, `bf_chapeau`…).

## Adding a card from the browser

`DocumentationQrCartes` offers a "+qrcard" button to drag to the bookmarks bar. On any site it opens the card form with the page's title, its address under "Sources" and the selected text on the back. The QrCards form also has its own bookmarklet, in the list of forms.

## Customising

The `ConfigMetacarte` page, when there is one, holds YAML:

```yaml
template: hexa
card_set_image: https://example.com/set-logo.svg
card_colors:
  - field: listeListeTypeCarte
    key: "①"
    color: "#f6d365"
```

- `template`: the card layout, `qrcard` by default, or `hexa`. A layout of your own goes in `custom/templates/qrcards/card-layouts/<name>.twig`.
- `card_set_image`: the logo printed at the bottom of the cards.
- `card_colors`: a background colour picked by the value of a field, instead of the colour set in the entry.

The wiki's `card_logo` setting replaces the picture shown when a card has none.

Card sizes and fonts are CSS custom properties, which `custom/styles/custom.css` can redefine:

| Property | Purpose |
|---|---|
| `--card-width`, `--card-height` | card size (75 × 125 mm) |
| `--card-border` | card border |
| `--card-spacing` | width of the coloured edges and gap between zones |
| `--card-recto-zone-height` | heights of the type, title, picture, summary and footer on the front |
| `--card-verso-zone-height` | heights of the type, title, long text and footer on the back |
| `--type-*` | font of the card type (top right) |
| `--title-*` | title font, with `--title-xl-font-size` and `--title-xxl-font-size` for long titles |
| `--text-*` | font of the summary on the front |
| `--longtext-*` | font of the text on the back |
| `--link-*` | font of the footer link |
| `--picto-*` | font of the captions under the icons |
