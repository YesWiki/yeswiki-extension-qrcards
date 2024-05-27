<?php

use YesWiki\Core\Controller\AuthController;
use YesWiki\Security\Controller\SecurityController;
use YesWiki\Core\Service\FavoritesManager;

function display($img)
{
    if (!empty($img) && file_exists('files/' . $img)) {
        return 'files/' . $img;
    }
    return false;
}

function f($text)
{
    return strip_tags($GLOBALS['wiki']->format($text), '<br>');
}

function truncate($string, $length = 550, $append = "&hellip;")
{
    $string = trim($string);

    if (strlen($string) > $length) {
        $string = wordwrap($string, $length);
        $string = explode("\n", $string, 2);
        $string = $string[0] . $append;
    }

    return $string;
}

function display_difficulty($fiche)
{
    if (empty($fiche['listeListeComplexite'])) {
        return '';
    }
    if ($fiche['listeListeComplexite'] == 'facile') {
        return '⚫️';
    } elseif ($fiche['listeListeComplexite'] == 'moyen') {
        return '⚫️ ⚫️';
    } elseif ($fiche['listeListeComplexite'] == 'complexe') {
        return '⚫️ ⚫️ ⚫️';
    } else {
        return '';
    }
}

function displayCard($fiche, $view = 'print')
{
    $thumbwidth = 300;
    $thumbheight = 300;
    $thumbresize = 'fit';
    $imgLogo = !empty($GLOBALS['wiki']->config['card_logo']) ? $GLOBALS['wiki']->config['card_logo'] : 'tools/qrcards/images/metacartes.png';
    if (isset($fiche['imagebf_image']) and is_file('files/' . $fiche['imagebf_image'])) {
        $image = '<img loading="lazy" alt="image" src="' . redimensionner_image(
            'files/' . $fiche['imagebf_image'],
            'cache/image_' . $thumbwidth . 'x' . $thumbheight . '_' . $fiche['imagebf_image'],
            $thumbwidth,
            $thumbheight,
            $thumbresize
        ) . '" />';
    } else {
        $image = '<img loading="lazy" alt="logo qrcard" src="' . $imgLogo . '" />';
    }
    $picto1 = !empty($fiche['imagebf_picto_boite1']) ? display($fiche['imagebf_picto_boite1']) : false;
    if ($picto1) {
        $picto1 = '<img loading="lazy" src="' . $picto1 . '" alt="mini-picto1" />';
    }
    $picto2 = !empty($fiche['imagebf_picto_boite2']) ? display($fiche['imagebf_picto_boite2']) : false;
    if ($picto2) {
        $picto2 = '<img loading="lazy" src="' . $picto2 . '" alt="mini-picto2" />';
    }
    $picto3 = !empty($fiche['imagebf_picto_boite3']) ? display($fiche['imagebf_picto_boite3']) : false;
    if ($picto3) {
        $picto3 = '<img loading="lazy" src="' . $picto3 . '" alt="mini-picto3" />';
    }
    $link = (!empty($fiche['bf_url'])) ? $fiche['bf_url'] : $GLOBALS['wiki']->href('', $fiche['id_fiche']);
    $types = baz_valeurs_liste('ListeTypeCarte');
    $type = '';
    if (!empty($fiche['listeListeTypeCarte']) && !empty($types['label'][$fiche['listeListeTypeCarte']])) {
        $type = $types['label'][$fiche['listeListeTypeCarte']];
    }
    $user = $GLOBALS['wiki']->services->get(AuthController::class)->getLoggedUser();
    $favoritesManager = $GLOBALS['wiki']->services->get(FavoritesManager::class);
    if (!empty($user) && $favoritesManager->areFavoritesActivated()) {
        $fav['currentuser'] = $user['name'];
        $fav['isUserFavorite'] = $favoritesManager->isUserFavorite($user['name'], $fiche['id_fiche']);
    } else {
        $fav = ['currentuser' => null, 'isUserFavorite' => null];
    }
    $linkedit = $linkdelete = null;
    if ($GLOBALS['wiki']->HasAccess("write")) {
        // on ajoute le lien d'édition si l'action est autorisée
        if ($GLOBALS['wiki']->HasAccess("write", $fiche['id_fiche']) && !$GLOBALS['wiki']->services->get(SecurityController::class)->isWikiHibernated()) {
            $linkedit = $GLOBALS['wiki']->href("edit", $fiche['id_fiche'], 'incomingurl=' . $GLOBALS['wiki']->href());
        }

        // if current user is owner or admin
        if ($GLOBALS['wiki']->UserIsOwner($fiche['id_fiche']) || $GLOBALS['wiki']->UserIsAdmin()) {
            if (!$GLOBALS['wiki']->services->get(SecurityController::class)->isWikiHibernated()) {
                $linkdelete = $GLOBALS['wiki']->href("deletepage", $fiche['id_fiche'], 'incomingurl=' . $GLOBALS['wiki']->href());
            }
        }
    }
    $elements = [
        'currentPage' => $GLOBALS['wiki']->getPageTag(),
        'fiche' => $fiche,
        'view' => $view,
        'difficulty' => display_difficulty($fiche),
        'type' => $type,
        'logoimage' => $imgLogo,
        'mainimage' => $image,
        'picto1' => $picto1,
        'picto2' => $picto2,
        'picto3' => $picto3,
        'textpicto1' => $fiche['bf_texte_boite1'] ?? '',
        'textpicto2' => $fiche['bf_texte_boite2'] ?? '',
        'textpicto3' => $fiche['bf_texte_boite3'] ?? '',
        'longtext' => f($fiche['bf_chapeau']),
        'qrcode' => $GLOBALS['wiki']->format('{{qrcode text="' . $link . '"}}'),
        'link' => $link,
        'shortlink' => str_replace(array('https://', 'http://'), '', $link),
        'currentuser' => $fav['currentuser'],
        'isUserFavorite' => $fav['isUserFavorite'],
        'linkedit' => $linkedit,
        'linkdelete' => $linkdelete
    ];
    $output = $GLOBALS['wiki']->render("@qrcards/card-layouts/qrcard.twig", $elements);
    return $output;
}
