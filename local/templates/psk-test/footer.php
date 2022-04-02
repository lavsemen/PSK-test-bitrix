<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
  <? $APPLICATION->IncludeComponent(
    "bitrix:main.include",
    "",
    array(
      "AREA_FILE_SHOW" => "file",
      "AREA_FILE_SUFFIX" => "inc",
      "EDIT_TEMPLATE" => "",
      "PATH" => SITE_TEMPLATE_PATH . "/include/modals.php",
    )
  ); ?>
<script src="static/js/main.js"></script>
</body>

</html>