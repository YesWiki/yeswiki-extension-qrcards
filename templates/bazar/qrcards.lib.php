<?php
function display($val, $default = '', $checkUrl = false, $valtext = 'default')
{
    if ($valtext != 'default' && empty($valtext)) {
        return '';
    } elseif (!empty($val)) {
        if ($checkUrl) {
            $headers = @get_headers($val);
  
            // Use condition to check the existence of URL
            if ($headers && strpos($headers[0], '200')) {
                return $val;
            } else {
                return '';
            }
        } else {
            return $val;
        }
    } else {
        return $default;
    }
}
function f($text)
{
    return strip_tags($GLOBALS['wiki']->format($text), '<br>');
}

function truncate($string, $length=550, $append="&hellip;")
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
    if ($fiche['listeListePriseEnMain'] == 'facile') {
        return '⚫️';
    } elseif ($fiche['listeListePriseEnMain'] == 'moyen') {
        return '⚫️ ⚫️';
    } elseif ($fiche['listeListePriseEnMain'] == 'complexe') {
        return '⚫️ ⚫️ ⚫️';
    } else {
        return '';
    }
}

function displayCard($fiche, $view='print')
{
    $thumbwidth = 300;
    $thumbheight = 300;
    $thumbresize = 'fit';
    $imgLogo = !empty($GLOBALS['wiki']->config['card_logo']) ? $GLOBALS['wiki']->config['card_logo'] : 'tools/qrcards/images/metacartes.png';
    $defaultMiniIcon = 'tools/qrcards/images/metacartes.png';
    if (isset($fiche['imagebf_image']) and is_file('files/'.$fiche['imagebf_image'])) {
      $image = '<img alt="image" src="'.redimensionner_image(
          'files/' . $fiche['imagebf_image'],
          'cache/image_' . $thumbwidth . 'x' . $thumbheight . '_' . $fiche['imagebf_image'],
          $thumbwidth,
          $thumbheight,
          $thumbresize
      ).'" />';
    } else {
        $image = '<img alt="logo qrcard" src="'.$imgLogo.'" />';
    }
    $picto1 = display($fiche['bf_picto_boite1'], $defaultMiniIcon, true, $fiche['bf_texte_boite1']);
    if (!empty($picto1)) {
        $picto1 = '<img src="'.$picto1.'" alt="mini-picto1" />';
    }
    $picto2 = display($fiche['bf_picto_boite2'], $defaultMiniIcon, true, $fiche['bf_texte_boite2']);
    if (!empty($picto2)) {
        $picto2 = '<img src="'.$picto2.'" alt="mini-picto2" />';
    }
    $picto3 = display($fiche['bf_picto_boite3'], $defaultMiniIcon, true, $fiche['bf_texte_boite3']);
    if (!empty($picto3)) {
        $picto3 = '<img src="'.$picto3.'" alt="mini-picto3" />';
    }
    $link = (!empty($fiche['bf_url'])) ? $fiche['bf_url'] : $GLOBALS['wiki']->href('', $fiche['id_fiche']);
    $types = baz_valeurs_liste('ListeTypeCarte');
    $type = '';
    if (!empty($fiche['listeListeTypeCarte']) && !empty($types['label'][$fiche['listeListeTypeCarte']])) {
        $type = $types['label'][$fiche['listeListeTypeCarte']];
    }
    $elements = [
      'fiche' => $fiche,
      'view' => $view,
      'difficulty' => display_difficulty($fiche),
      'type' => $type,
      'logoimage' => $imgLogo,
      'mainimage' => $image,
      'picto1' => $picto1,
      'picto2' => $picto2,
      'picto3' => $picto3,
      'textpicto1' => display($fiche['bf_texte_boite1']),
      'textpicto2' => display($fiche['bf_texte_boite2']),
      'textpicto3' => display($fiche['bf_texte_boite3']),
      'longtext' => f($fiche['bf_essentiel']),
      'qrcode' => $GLOBALS['wiki']->format('{{qrcode text="'.$link.'"}}'),
      'link' => $link,
      'shortlink' => str_replace(array('https://', 'http://'), '', $link)
    ];
    $output = $GLOBALS['wiki']->render("@qrcards/card-layouts/qrcard.twig", $elements);
    return $output;
}
