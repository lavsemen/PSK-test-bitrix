<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

  foreach ($arResult['ITEMS'] as $key => &$arItem) {
    
    if ($tags = $arItem['PROPERTIES']['TAGS']['VALUE']) {
      $arItem['TAGS_STRING'] = implode(", ",$tags);
    }
    
    $contribution = $arItem['PROPERTIES']['CONTRIBUTION']['VALUE']? (int)$arItem['PROPERTIES']['CONTRIBUTION']['VALUE']: 15;
    $price = (int)$arItem['PROPERTIES']['PRICE']['VALUE'];

    $arItem['CONTRIBUTION_STRING'] = getCountribution($price, $contribution);
  }

function getCountribution($prcie, $contribution = 15) {
  $result = number_format(($prcie / 100) * $contribution, 0, ' ', ' ');

  return 'от ' . $result;
}
