<?php
/**
 * This file is only intended to deliver HTML,
 * read from a Markdown-file,
 * via the jquery-function .load().
 *
 * Parameters are:
 * ('jtl_token': '', 'path': '')
 *
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

date_default_timezone_set('Europe/Berlin');

// try to guess our root-path, by the knowing "we are somewhere in 'admin'"
$szRootPath = preg_split('/admin/', __FILE__)[0];
// "close the ring" to the main-application (for the correct _SESSION)
require_once $szRootPath . '/admin/includes/admininclude.php';

if (validateToken()) {

    $szMdStyle = '
        <style>
            div.markdown {
                padding: 0px 10px;
            }
            div.markdown ul li {
                list-style: outside none disc;
            }
            div.markdown ol li {
                list-style: outside none decimal;
            }
            div.markdown p {
                text-align: justify;
            }
            div.markdown blockquote {
                font-size: inherit;
            }
            div.markdown pre {
                overflow-wrap: break-word;
                white-space: pre-line;
                word-break: unset;
            }
            div.markdown table {
                border: 1px solid #ddd;
            }
            div.markdown thead tr th,
            div.markdown tbody tr th,
            div.markdown tfoot tr th,
            div.markdown thead tr td,
            div.markdown tbody tr td,
            div.markdown tfoot tr td {
                border: 1px solid #ddd;
                padding: 8px;
            }
            div.markdown thead tr th,
            div.markdown thead tr td {
                border-bottom-width: 2px;
            }
        </style>
    ';
    if (file_exists($_POST['path'])) {
        // slurp in the files content
        $szFileContent = file_get_contents(utf8_decode($_POST['path']));
        // check, if we got a Markdown-parser
        if (class_exists('Parsedown')) {
            $oParseDown       = new Parsedown();
            $szLicenseContent = $oParseDown->text($szFileContent);
        } else {
            // if we don't have a parser, we deliver plain 'pre-formatted' text
            $szLicenseContent = '<pre>' . $szFileContent . '</pre>';
        }
        // spit out, what we have
        $szLicenseContent = mb_convert_encoding($szLicenseContent, 'HTML-ENTITIES');
        echo $szMdStyle . "\n<div class='markdown'>\n" . $szLicenseContent . "\n</div>\n";
    }
}

