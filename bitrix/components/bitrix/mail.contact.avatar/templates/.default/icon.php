<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
?>
<span class="mail-contact-avatar mail-contact-avatar-icon"
	  style="width: <?=$arResult['avatarSize'] ?>px;
		  min-width: <?=$arResult['avatarSize'] ?>px;
		  height: <?=$arResult['avatarSize'] ?>px;
		  min-height: <?=$arResult['avatarSize'] ?>px;
		  font-size: <?=$arResult['initialsFontSize'] ?>px;
		  line-height: <?=$arResult['avatarSize'] ?>px;
		  background-color: <?=$arResult['COLOR'] ?>"><?=$arResult['INITIALS'] ?></span>
