<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Socialnetwork\UserToGroupTable;

if (!function_exists('__GCE_GetGroup'))
{
	function __GCEGetGroup(&$group_id, &$arGroupProperties = array(), &$arGroupTmp = array(), $tab = false)
	{
		global $USER;

		if (!CModule::IncludeModule("socialnetwork"))
		{
			$group_id = 0;
			return;
		}

		$arGroup = CSocNetGroup::getById($group_id);
		if ($arGroup &&
			(
				(
					$tab === 'edit'
					&& (
						(int)$arGroup['OWNER_ID'] === (int)$USER->getId()
						|| CSocNetUser::IsCurrentUserModuleAdmin()
					)
				)
				|| (
					$tab === 'invite'
					&& (
						CSocNetUser::IsCurrentUserModuleAdmin()
						|| CSocNetGroup::CanUserInitiate($USER->GetID(), $group_id)
					)
				)
			)
		)
		{
			$arGroupTmp["NAME"] = $arGroup["NAME"];
			$arGroupTmp["DESCRIPTION"] = $arGroup["DESCRIPTION"];
			$arGroupTmp["IMAGE_ID_DEL"] = "N";
			$arGroupTmp["SUBJECT_ID"] = $arGroup["SUBJECT_ID"];
			$arGroupTmp["VISIBLE"] = $arGroup["VISIBLE"];
			$arGroupTmp["OPENED"] = $arGroup["OPENED"];
			$arGroupTmp["CLOSED"] = $arGroup["CLOSED"];
			$arGroupTmp["PROJECT"] = ($arGroup["PROJECT"] === 'Y' ? 'Y' : 'N');
			$arGroupTmp["PROJECT_DATE_START"] = ($arGroupTmp["PROJECT"] === 'Y' ? $arGroup["PROJECT_DATE_START"] : false);
			$arGroupTmp["PROJECT_DATE_FINISH"] = ($arGroupTmp["PROJECT"] === 'Y' ? $arGroup["PROJECT_DATE_FINISH"] : false);
			$arGroupTmp["KEYWORDS"] = $arGroup["KEYWORDS"];
			$arGroupTmp["OWNER_ID"] = $arGroup["OWNER_ID"];
			$arGroupTmp["INITIATE_PERMS"] = $arGroup["INITIATE_PERMS"];
			$arGroupTmp["SPAM_PERMS"] = $arGroup["SPAM_PERMS"];
			$arGroupTmp["IMAGE_ID"] = $arGroup["IMAGE_ID"];
			$arGroupTmp["IMAGE_ID_FILE"] = CFile::GetFileArray($arGroup["IMAGE_ID"]);
			$arGroupTmp["IMAGE_ID_IMG"] = '<img src="'.($arGroupTmp["IMAGE_ID_FILE"] != false ? $arGroupTmp["IMAGE_ID_FILE"]["SRC"] : "/bitrix/images/1.gif").'" height="60" class="sonet-group-create-popup-image" id="sonet_group_create_popup_image" border="0">';
			$arGroupTmp["MODERATOR_IDS"] = [];
			$arGroupTmp["LANDING"] = ($arGroup["LANDING"] === 'Y' ? 'Y' : 'N');
			$arGroupTmp["SCRUM_OWNER_ID"] = ($arGroup["SCRUM_OWNER_ID"] ?: null);
			$arGroupTmp["SCRUM_MASTER_ID"] = ($arGroup["SCRUM_MASTER_ID"] ?: null);
			$arGroupTmp["SCRUM_SPRINT_DURATION"] = ($arGroup["SCRUM_SPRINT_DURATION"] ?: null);
			$arGroupTmp["SCRUM_TASK_RESPONSIBLE"] = ($arGroup["SCRUM_TASK_RESPONSIBLE"] ?: null);
			$arGroupTmp["AVATAR_TYPE"] = (string)($arGroup["AVATAR_TYPE"] ?? '');

			foreach ($arGroupProperties as $field => $arUserField)
			{
				if (array_key_exists($field, $arGroup))
				{
					$arGroupProperties[$field]["VALUE"] = $arGroup["~".$field];
					$arGroupProperties[$field]["ENTITY_VALUE_ID"] = $arGroup["ID"];
				}
			}

			$arGroupTmp["IS_EXTRANET_GROUP"] = (
				CModule::IncludeModule("extranet")
				&& CExtranet::IsExtranetSocNetGroup($group_id)
					? "Y"
					: "N"
			);

			$res = UserToGroupTable::getList(array(
				'filter' => array(
					'ROLE' => UserToGroupTable::ROLE_MODERATOR,
					'GROUP_ID' => $group_id,
				),
				'select' => array('USER_ID')

			));
			while ($relation = $res->fetch())
			{
				$arGroupTmp["MODERATOR_IDS"][] = (int)$relation['USER_ID'];
			}
		}
		else
		{
			$arGroupTmp["VISIBLE"] = "Y";
			$arGroupTmp["IS_EXTRANET_GROUP"] = "N";
			$group_id = 0;
		}
	}
}

if (!function_exists('__GCE_GetFeatures'))
{
	function __GCE_GetFeatures($group_id, &$arFeatures)
	{
		if (!CModule::IncludeModule("socialnetwork"))
		{
			return;
		}

		$arFeaturesTmp = array();

		if ((int)$group_id > 0)
		{
			$dbResultTmp = CSocNetFeatures::getList(
				array(),
				array("ENTITY_ID" => $group_id, "ENTITY_TYPE" => SONET_ENTITY_GROUP)
			);
			while ($arResultTmp = $dbResultTmp->GetNext())
			{
				$arFeaturesTmp[$arResultTmp["FEATURE"]] = $arResultTmp;
			}
		}

		$arSocNetFeaturesSettings = CSocNetAllowed::getAllowedFeatures();

		$sampleKeysList = array(
			'tasks' => 1,
			'calendar' => 2,
			'files' => 3,
			'chat' => 4,
			'forum' => 5,
			'microblog' => 6,
			'blog' => 7,
			'photo' => 8,
			'group_lists' => 9,
			'wiki' => 10,
			'content_search' => 11,
			'marketplace' => 12,
		);

		uksort($arSocNetFeaturesSettings, function($a, $b) use ($sampleKeysList) {
			$valA = ($sampleKeysList[$a] ?? 100);
			$valB = ($sampleKeysList[$b] ?? 100);
			if ($valA > $valB)
			{
				return 1;
			}

			if ($valA < $valB)
			{
				return -1;
			}

			return 0;
		});

		foreach ($arSocNetFeaturesSettings as $feature => $arFeature)
		{
			if (
				!is_array($arFeature["allowed"]) 
				|| !in_array(SONET_ENTITY_GROUP, $arFeature["allowed"])
			)
			{
				continue;
			}

			if ((int)$group_id === 0)
			{
				$arFeaturesTmp[$feature]["ACTIVE"] = (
					$feature === "chat"
						? \CUserOptions::getOption("socialnetwork", "default_chat_create_default", "Y")
						: COption::getOptionString("socialnetwork", "default_".$feature."_create_default", "Y", SITE_ID)
				);
			}

			$arFeatures[$feature] = array(
				"FeatureName" => (!empty($arFeaturesTmp) ? $arFeaturesTmp[$feature]["FEATURE_NAME"] : false),
				"Active" => (
					!empty($arFeaturesTmp)
					&& array_key_exists($feature, $arFeaturesTmp)
						? ($arFeaturesTmp[$feature]["ACTIVE"] === "Y") // saved
						: true
				)
			);
		}
	}
}
