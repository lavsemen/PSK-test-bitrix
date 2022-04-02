<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
    die();
?>
<? /** @const SITE_TEMPLATE_PATH */ ?>
<?

use Bitrix\Main\Application;
use Bitrix\Main\Page\Asset;

?>
<!DOCTYPE html>
<html lang="en">

    <head>

       <? Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/static/css/main.css"); ?>
       <? Asset::getInstance()->addCss(SITE_TEMPLATE_PATH . "/static/css/custom.css"); ?>

       <? Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/static/js/main.js'); ?>

       
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><? $APPLICATION->ShowTitle(); ?></title>
        <link rel="stylesheet" href="static/css/main.css">

        <? $APPLICATION->ShowCSS() ?>
        <? $APPLICATION->ShowHeadStrings() ?>
        <? $APPLICATION->ShowHeadScripts() ?>
    </head>
    <div id="panel">
        <? $APPLICATION->ShowPanel(); ?>
    </div>
    <body class="page">