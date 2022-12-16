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
        return '⚫️⚫️';
    } elseif ($fiche['listeListePriseEnMain'] == 'complexe') {
        return '⚫️⚫️⚫️';
    } else {
        return '';
    }
}

function displayCard($fiche, $view='print')
{
    $GLOBALS['wiki']->addCSSFile('tools/qrcards/styles/qrcards.css');
    $thumbwidth = 300;
    $thumbheight = 300;
    $thumbresize = 'fit';
    $imgLogo = 'tools/qrcards/images/metacartes.png';
    $defaultMiniIcon = 'tools/qrcards/images/metacartes.png';
    $classprefix = ($view == 'print') ? 'print-' : '';
    $output = '<div class="qrcard '.$classprefix.'front" style="background-color: '.$fiche['bf_card_color'].';">
            <div class="qrcard-header">
              <div class="complexity">'.display_difficulty($fiche).'</div>
              <h1 class="qrcard-title">'.$fiche['bf_titre'].'</h1>
            </div>
            <div class="qrcard-main-content">';

    if (isset($fiche['imagebf_image']) and is_file('files/'.$fiche['imagebf_image'])) {
        $output .= '<div class="qrcard-img">
                <img alt="image" src="'.redimensionner_image(
            'files/' . $fiche['imagebf_image'],
            'cache/image_' . $thumbwidth . 'x' . $thumbheight . '_' . $fiche['imagebf_image'],
            $thumbwidth,
            $thumbheight,
            $thumbresize
        ).'" />
              </div>';
    } else {
        $output .= '<div class="qrcard-img">
                <img alt="logo metacartes" src="'.$imgLogo.'" />
              </div>';
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
    $output .= '<div class="qrcard-text">
                <span>'.$fiche['bf_accroche'].'</span>
              </div>
            </div>
            <div class="qrcard-footer">
              <img class="logo" alt="logo"
                src="'.$imgLogo.'" />
              <div class="boite1">
                <div class="mini-picto">'.$picto1.'</div>
                <div class="mini-text">'.display($fiche['bf_texte_boite1']).'</div>
              </div>
              <div class="boite2">
                <div class="mini-picto">'.$picto2.'</div>
                <div class="mini-text">'.display($fiche['bf_texte_boite2']).'</div>
              </div>
              <div class="boite3">
                <div class="mini-picto">'.$picto3.'</div>
                <div class="mini-text">'.display($fiche['bf_texte_boite3']).'</div>
              </div>
            </div>
          </div>
          <div class="qrcard '.$classprefix.'back" style="background-color: '.$fiche['bf_card_color'].';">
            <div class="qrcard-header">
              <div class="complexity">'.display_difficulty($fiche).'</div>
              <h1 class="qrcard-title">'.$fiche['bf_titre'].'</h1>
            </div>
            <div class="qrcard-main-content">
              <div class="qrcard-longtext">
                <p class="verso1">'.f($fiche['bf_essentiel']).'</p>
              </div>
            </div>
            <div class="qrcard-footer">
              <img class="logo" alt="logo"
                src="'.$imgLogo.'" />
              <div class="link">
                <a class="card-link"
                  href="'.$GLOBALS['wiki']->href('', $fiche['id_fiche']).'">'.str_replace(array('https://', 'http://'), '', $GLOBALS['wiki']->href('', $fiche['id_fiche'])).'</a>
                <br /><img class="logo-cc" src="https://mirrors.creativecommons.org/presskit/buttons/88x31/png/by-sa.png"
                  alt="licence CC BY SA" />
              </div>
              <div class="qrcode">
                '.$GLOBALS['wiki']->format('{{qrcode text="'.$GLOBALS['wiki']->href('', $fiche['id_fiche']).'"}}').'
              </div>
            </div>
        </div>
        <div class="clearfix"></div>';
    if ($view != 'print') {
        $output =  '<div class="bazar-entry flip-container" ontouchstart="this.classList.toggle(\'hover\');" '.$fiche['html_data'].'><div><div class="flipper">'.$output.'</div></div></div>';
    } else {
        $output = '<div class="single-qrcard-container">'.$output.'</div>';
    }
    return $output;
}
