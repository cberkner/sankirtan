<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
ob_start();
set_time_limit(0);

require_once dirname(__FILE__) . '/includes/admininclude.php';
require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Updater.php';

$hasPermission = $oAccount->permission('DISPLAY_IMAGES_VIEW', false, false);
/** @global JTLSmarty $smarty */
$action = isset($_GET['action']) ? $_GET['action'] : null;
$type   = isset($_GET['type']) ? $_GET['type'] : null;

if (!$hasPermission) {
    $oAccount->redirectOnFailure();
    makeResponse(null, null, 401);
}

switch ($action) {

    case 'stats':
        $items = getItems(true);

        if ($type === null || in_array($type, $items)) {
            makeResponse(null, 'Invalid argument request', 500);
        }

        $item = $items[$type];
        makeResponse($item->stats);
        break;

    case 'cache':
        $index = isset($_GET['index']) ? (int)$_GET['index'] : null;

        if ($type === null || $index === null) {
            makeResponse(null, 'Invalid argument request', 500);
        }

        $started = time();
        $result  = (object)[
            'total'          => 0,
            'renderTime'     => 0,
            'nextIndex'      => 0,
            'renderedImages' => 0,
            'images'         => []
        ];

        if ($index === 0) {
            $_SESSION['image_count'] = count(MediaImage::getImages($type, true));
            $_SESSION['renderedImages'] = 0;
        }

        $total  = $_SESSION['image_count'];
        $images = MediaImage::getImages($type, true, $index, IMAGE_PRELOAD_LIMIT);
        while (count($images) === 0 && $index < $total) {
            $index += 10;
            $images = MediaImage::getImages($type, true, $index, IMAGE_PRELOAD_LIMIT);
        }
        foreach ($images as $image) {
            $seconds = time() - $started;
            if ($seconds >= 10) {
                break;
            }
            $result->images[] = MediaImage::cacheImage($image);
            ++$index;
            ++$_SESSION['renderedImages'];
        }
        $result->total          = $total;
        $result->renderTime     = time() - $started;
        $result->nextIndex      = $index;
        $result->renderedImages = $_SESSION['renderedImages'];
        if ($_SESSION['renderedImages'] >= $total) {
            unset($_SESSION['image_count']);
            unset($_SESSION['renderedImages']);
        }

        /*
        $urls = [];
        foreach ($chunked as $index => $image) {
            $params = http_build_query([
                'action' => 'cache_image',
                'type' => $type,
                'index' => $index
            ], '', '&');
            $urls[] = Shop::getAdminURL() . '/bilderverwaltung.php?' . $params;
        };

        $r = new MultiRequest();
        $r->process($urls, function($data, $curl) use(&$results) {
            $result = json_decode($data);
            if ($result->error === null) {
                $results[] = $result->data;
            } 
        });
        
        foreach ($chunked as $index => &$image) {
            $results[] = MediaImage::cacheImage($image);
        }
        */

        makeResponse($result);
        break;

    case 'cache_image':
        $index = isset($_GET['index']) ? (int)$_GET['index'] : null;

        if ($type === null || $index === null) {
            makeResponse(null, 'Invalid argument request', 500);
        }

        $images = MediaImage::getImages($type);

        if (!array_key_exists($index, $images)) {
            exit;
        }

        $image = $images[$index];
        $data  = MediaImage::cacheImage($image, false);

        makeResponse((object) [
            'index' => $index,
            'data'  => $data
        ]);

        break;

    case 'clear':
        if ($type !== null && preg_match('/[a-z]*/', $type)) {
            MediaImage::clearCache($type);
            unset($_SESSION['image_count']);
            unset($_SESSION['renderedImages']);
            if (isset($_GET['isAjax']) && $_GET['isAjax'] === 'true') {
                makeResponse((object)['success' => 'Cache wurde erfolgreich zur&uuml;ckgesetzt']);
            }
            $smarty->assign('success', 'Cache wurde erfolgreich zur&uuml;ckgesetzt');
        }

    default:
        $smarty->assign('items', getItems())
            ->assign('TYPE_PRODUCT', Image::TYPE_PRODUCT)
            ->assign('SIZE_XS', Image::SIZE_XS)
            ->assign('SIZE_SM', Image::SIZE_SM)
            ->assign('SIZE_MD', Image::SIZE_MD)
            ->assign('SIZE_LG', Image::SIZE_LG)
            ->display('bilderverwaltung.tpl');
        break;
}

/**
 * @param bool $filesize
 * @return array
 */
function getItems($filesize = false)
{
    $item = (object) [
        'name'  => 'Produkte',
        'type'  => Image::TYPE_PRODUCT,
        'stats' => MediaImage::getStats(Image::TYPE_PRODUCT, $filesize)
    ];

    return [Image::TYPE_PRODUCT => $item];
}

/**
 * @param array|object $data
 * @param null|string  $error
 * @param int          $errno
 */
function makeResponse($data, $error = null, $errno = 200)
{
    if (ob_get_level() > 0) {
        ob_end_clean();
    }

    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Content-type: application/json');

    if ($error !== null) {
        header(makeHTTPHeader(500), true, $error);
    }

    $result = (object) [
        'error' => $error,
        'data'  => utf8_convert_recursive($data)
    ];

    $json = json_encode($result);

    echo $json;
    exit;
}
