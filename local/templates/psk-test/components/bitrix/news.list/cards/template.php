<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$this->setFrameMode(true);
?>

<div class="cards">
  <? if (count($arResult['ITEMS'])) : ?>
    <? foreach ($arResult['ITEMS'] as $arItem) : ?>
     
      <div class="cards__item">
        <div class="card">
          <div class="row">
            <div class="col-lg-6">
              <div class="card__images">
                <div class="fotorama" data-nav="thumbs">
                  <? foreach ($arItem['PROPERTIES']['PLANS']['VALUE'] as $image) :
                    $resizeImage = CFile::ResizeImageGet(
                      $image, 
                      array('width' => 600, 'height' => 400),
                       BX_RESIZE_IMAGE_EXACT,
                      true
                    );

                  ?>
                    <img src="<?=$resizeImage['src']?>" alt="">
                  <? endforeach ?>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="card__content">
                <? if ($arItem['PROPERTIES']['TITLE']['VALUE']) : ?>
                  <div class="card__title">
                    <?= $arItem['PROPERTIES']['TITLE']['VALUE'] ?>
                  </div>
                <? endif; ?>
                <? if ($arItem['PROPERTIES']['READY_DATE']['VALUE']) : ?>
                  <div class="card__date">
                    срок сдачи: <?= $arItem['PROPERTIES']['READY_DATE']['VALUE'] ?>
                  </div>
                <? endif; ?>

                <?if ($arItem['TAGS_STRING']):?>
                <div class="card__tags">
                  <?=$arItem['TAGS_STRING']?>
                </div>
                <?endif?>
                <?if ($arItem['CONTRIBUTION_STRING']):?>
                <div class="card__contribuion">
                  <div class="card__contribuion-label">
                    Первоначальный
                    <br>взнос
                  </div>
                  <div class="card__contribuion-value">
                   <?=$arItem['CONTRIBUTION_STRING']?>₽
                  </div>
                </div>
                <?endif;?>
                <div class="card__price">
                  <div class="card__price-label">
                    Цена
                  </div>
                  <div class="card__price-value">
                    <?
                      $price = number_format($arItem['PROPERTIES']['PRICE']['VALUE'], 0, ' ', ' ')
                    ?>
                    от <?=$price?> руб.
                  </div>
                </div>

                <div class="card__button">
                  <button id="callbackModal" data-bs-toggle="modal" data-apartment-id="<?=$arItem['ID']?>" data-bs-target="#callback">Узнать цену</button>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    <? endforeach ?>
  <? endif; ?>
</div>