<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main;
use Bitrix\Sale;

/**
 * Class ShipmentRules
 * @package Bitrix\Sale\Internals
 */
class ShipmentRules
{
	/**
	 * ShipmentRules constructor.
	 */
	protected function __construct() {}

	/**
	 * @param Sale\Order $order
	 * @param PoolQuantity $pool
	 *
	 * @return array
	 */
	public static function createOrderRuleMap(Sale\Order $order, PoolQuantity $pool)
	{
		$resultList = array();

		$productPool = ItemsPool::getPoolByCode($order->getInternalId());
		if (empty($productPool))
		{
			return $resultList;
		}

		foreach ($productPool as $itemList)
		{
			foreach ($itemList as $item)
			{
				if ($item instanceof Sale\ShipmentItem)
				{
					$itemRule = static::createReserveRuleForShipmentItem($item, $pool);
					if (!empty($itemRule) && is_array($itemRule))
					{
						$resultList[] = $itemRule;
					}

					$itemRule = static::createShipRule($item, $pool);
					if (!empty($itemRule) && is_array($itemRule))
					{
						$resultList[] = $itemRule;
					}
				}
				elseif ($item instanceof Sale\ReserveQuantity)
				{
					$itemRule = static::createReserveRule($item, $pool);
					if (!empty($itemRule) && is_array($itemRule))
					{
						$resultList[] = $itemRule;
					}
				}
			}
		}

		return $resultList;
	}

	/**
	 * @param Sale\ShipmentItem $shipmentItem
	 * @param PoolQuantity $pool
	 *
	 * @return array|bool
	 * @throws Main\ObjectNotFoundException
	 */
	private static function createShipRule(Sale\ShipmentItem $shipmentItem, PoolQuantity $pool)
	{
		$basketItem = $shipmentItem->getBasketItem();
		if ($basketItem->isBundleParent())
		{
			return false;
		}

		$poolQuantitiesList = $pool->getQuantitiesWithStore(PoolQuantity::POOL_QUANTITY_TYPE);
		if (empty($poolQuantitiesList))
		{
			return false;
		}

		$productId = $basketItem->getProductId();

		$rule = array(
			'SHIPMENT_ITEM' => $shipmentItem,
			'BASKET_ITEM' => $basketItem,
			'PRODUCT_ID' => $productId,
			'PROVIDER_NAME' => $basketItem->getProvider(),
		);

		$storeData = Sale\Internals\Catalog\Provider::createMapShipmentItemStoreData($shipmentItem);
		if (!empty($storeData))
		{
			$reservedQuantity = 0;
			$needReserveByStore = [];
			foreach ($storeData as $item)
			{
				$reservedQuantity += $item['RESERVED_QUANTITY'];

				$needReserveByStore[$item['STORE_ID']] = $item['RESERVED_QUANTITY'] > 0;
			}

			$rule['STORE_DATA'] = [
				$shipmentItem->getInternalIndex() => $storeData
			];

			$rule['NEED_RESERVE'] = $reservedQuantity > 0;

			$rule['NEED_RESERVE_BY_STORE'] = [
				$shipmentItem->getInternalIndex() => $needReserveByStore
			];
		}
		else
		{
			$rule['NEED_RESERVE'] = $basketItem->getReservedQuantity() > 0;
			$rule['NEED_RESERVE_BY_STORE'] = [
				$shipmentItem->getInternalIndex() => [
					Sale\Configuration::getDefaultStoreId() => $rule['NEED_RESERVE']
				]
			];

			$reservedQuantity = $basketItem->getReserveQuantityCollection()->getQuantityByStoreId(
				Sale\Configuration::getDefaultStoreId()
			);
			if ($shipmentItem->getQuantity() < $reservedQuantity)
			{
				$reservedQuantity = $shipmentItem->getQuantity();
			}

			$rule['RESERVED_QUANTITY'] = $reservedQuantity;
			$rule['RESERVED_QUANTITY_BY_STORE'] = [
				$shipmentItem->getInternalIndex() => [
					Sale\Configuration::getDefaultStoreId() => $reservedQuantity
				]
			];
		}

		$coefficient = 1;
		if (array_key_exists($productId, $poolQuantitiesList))
		{
			$shipment = $shipmentItem->getCollection()->getShipment();

			if ($shipment->needShip() === Sale\Internals\Catalog\Provider::SALE_TRANSFER_PROVIDER_SHIPMENT_NEED_SHIP)
			{
				$coefficient = -1;
			}

			$quantityByStore = [];
			foreach ($storeData as $item)
			{
				$storeId = $item['STORE_ID'];
				$quantity = $coefficient * $item['QUANTITY'];

				if (!isset($quantityByStore[$storeId]))
				{
					$quantityByStore[$storeId] = 0;
				}

				$quantityByStore[$storeId] += $quantity;

				$poolQuantity = $poolQuantitiesList[$productId][$storeId] - $quantity;

				$pool->setByStore(PoolQuantity::POOL_QUANTITY_TYPE, $productId, $storeId, $poolQuantity);
			}

			if ($quantityByStore)
			{
				$rule['ACTION'][PoolQuantity::POOL_QUANTITY_TYPE] = [
					'QUANTITY_BY_STORE' => $quantityByStore,
					'QUANTITY' => array_sum($quantityByStore)
				];
			}
			else
			{
				$storeId = Sale\Configuration::getDefaultStoreId();

				$quantity = $coefficient * $shipmentItem->getQuantity();

				$rule['ACTION'][PoolQuantity::POOL_QUANTITY_TYPE] = [
					'QUANTITY' => $quantity,
					'QUANTITY_BY_STORE' => [
						$storeId => $quantity
					]
				];
			}
		}

		return $rule;
	}

	/**
	 * @param Sale\ShipmentItem $shipmentItem
	 * @param PoolQuantity $pool
	 *
	 * @return array|bool
	 * @throws Main\ObjectNotFoundException
	 */
	private static function createReserveRuleForShipmentItem(Sale\ShipmentItem $shipmentItem, PoolQuantity $pool)
	{
		$basketItem = $shipmentItem->getBasketItem();

		$shipment = $shipmentItem->getCollection()->getShipment();

		$poolReservationList = $pool->getQuantitiesWithStore(PoolQuantity::POOL_RESERVE_TYPE);

		if (empty($poolReservationList))
		{
			return false;
		}

		$productId = $basketItem->getProductId();

		$rule = [
			'SHIPMENT_ITEM' => $shipmentItem,
			'BASKET_ITEM' => $basketItem,
			'PRODUCT_ID' => $productId,
			'PROVIDER_NAME' => $basketItem->getProvider(),
			'STORE' => []
		];

		if ($shipment->needShip() === Sale\Internals\Catalog\Provider::SALE_TRANSFER_PROVIDER_SHIPMENT_NEED_NOT_SHIP)
		{
			return false;
		}

		if (!isset($poolReservationList[$productId]))
		{
			return $rule;
		}

		$needReserveQuantity = $shipmentItem->getNeedReserveQuantity();
		if ($needReserveQuantity == 0)
		{
			$needQuantityByStore = $poolReservationList[$productId];
			$rule['ACTION'][PoolQuantity::POOL_RESERVE_TYPE] = [
				'QUANTITY_BY_STORE' => $needQuantityByStore,
				'QUANTITY' => array_sum($needQuantityByStore),
			];

			$pool->delete(PoolQuantity::POOL_RESERVE_TYPE, $productId);

			return $rule;
		}

		$needQuantityByStore = [];

		$reserveCollection = $basketItem->getReserveQuantityCollection();

		/** @var Sale\ShipmentItemStore $itemStore */
		foreach ($shipmentItem->getShipmentItemStoreCollection() as $itemStore)
		{
			if (
				isset($poolReservationList[$productId][$itemStore->getStoreId()])
				&& $poolReservationList[$productId][$itemStore->getStoreId()] == 0
			)
			{
				continue;
			}

			/** @var Sale\ReserveQuantity $reserve */
			foreach ($reserveCollection as $reserve)
			{
				if (
					$reserve->getStoreId() !== $itemStore->getStoreId()
					|| !$reserve->getFields()->isChanged('QUANTITY')
				)
				{
					continue;
				}

				if (!isset($needQuantityByStore[$reserve->getStoreId()]))
				{
					$needQuantityByStore[$reserve->getStoreId()] = 0;
				}

				$needQuantity = $reserve->getQuantity() - $reserve->getFields()->getOriginalValues()['QUANTITY'];
				if (abs($needQuantity) > abs($needReserveQuantity))
				{
					$needQuantityByStore[$reserve->getStoreId()] += $needReserveQuantity;
					$needReserveQuantity = 0;
					$poolReservationList[$productId][$reserve->getStoreId()] -= $needReserveQuantity;
				}
				else
				{
					$needQuantityByStore[$reserve->getStoreId()] += $needQuantity;
					$needReserveQuantity -= $needQuantity;
					$poolReservationList[$productId][$reserve->getStoreId()] -= $needQuantity;
				}
			}
		}

		if ($needReserveQuantity != 0)
		{
			$storeId = Sale\Configuration::getDefaultStoreId();
			if (!isset($needQuantityByStore[$storeId]))
			{
				$needQuantityByStore[$storeId] = 0;
			}

			foreach ($reserveCollection as $reserve)
			{
				if (
					!$reserve->getStoreId() === $storeId
					|| !$reserve->getFields()->isChanged('QUANTITY')
				)
				{
					continue;
				}

				$needQuantity = $reserve->getQuantity() - $reserve->getFields()->getOriginalValues()['QUANTITY'];
				if (abs($needQuantity) > abs($needReserveQuantity))
				{
					$needQuantityByStore[$reserve->getStoreId()] += $needReserveQuantity;
					$needReserveQuantity = 0;
					$poolReservationList[$productId][$reserve->getStoreId()] -= $needReserveQuantity;
				}
				else
				{
					$needQuantityByStore[$reserve->getStoreId()] += $needQuantity;
					$needReserveQuantity -= $needQuantity;
					$poolReservationList[$productId][$reserve->getStoreId()] -= $needQuantity;
				}
			}

			if ($needReserveQuantity != 0)
			{
				foreach ($poolReservationList[$productId] as $storeId => $quantity)
				{
					if ($needReserveQuantity == 0)
					{
						break;
					}

					if ($quantity != 0)
					{
						if (!isset($needQuantityByStore[$storeId]))
						{
							$needQuantityByStore[$storeId] = 0;
						}

						if (abs($needReserveQuantity) > abs($quantity))
						{
							$needQuantityByStore[$storeId] += $quantity;
							$needReserveQuantity -= $quantity;
						}
						else
						{
							$needQuantityByStore[$storeId] += $needReserveQuantity;
							$needReserveQuantity = 0;
						}

					}
				}
			}
		}

		$rule['ACTION'][PoolQuantity::POOL_RESERVE_TYPE] = [
			'QUANTITY_BY_STORE' => $needQuantityByStore,
			'QUANTITY' => array_sum($needQuantityByStore),
		];

		return $rule;
	}

	/**
	 * @param Sale\ReserveQuantity $reserve
	 * @param PoolQuantity $pool
	 *
	 * @return array|bool
	 */
	private static function createReserveRule(Sale\ReserveQuantity $reserve, PoolQuantity $pool)
	{
		$basketItem = $reserve->getCollection()->getBasketItem();

		$poolReservationList = $pool->getQuantitiesWithStore(PoolQuantity::POOL_RESERVE_TYPE);
		if (
			empty($poolReservationList)
			|| !isset($poolReservationList[$basketItem->getProductId()])
		)
		{
			return false;
		}

		$rule = [
			'PRODUCT_ID' => $basketItem->getProductId(),
			'BASKET_ITEM' => $basketItem,
			'PROVIDER_NAME' => $basketItem->getProvider(),
			'RESERVE_ITEM' => $reserve,
			'STORE' => []
		];

		$quantityByStore = [];
		if ($reserve->getQuantity() > 0)
		{
			$quantity = $reserve->getFields()->getOriginalValues()['QUANTITY'] ?? 0;
			$quantityByStore = [
				$reserve->getStoreId() => $reserve->getQuantity() - $quantity
			];
		}
		elseif ($reserve->getQuantity() == 0)
		{
			$quantity = $reserve->getFields()->getOriginalValues()['QUANTITY'];
			$quantityByStore = [$reserve->getStoreId() => -$quantity];
		}

		if ($quantityByStore)
		{
			$rule['ACTION'][PoolQuantity::POOL_RESERVE_TYPE] = [
				'QUANTITY_BY_STORE' => $quantityByStore,
				'QUANTITY' => array_sum($quantityByStore),
			];
		}

		$pool->setByStore(PoolQuantity::POOL_RESERVE_TYPE, $basketItem->getProductId(), $reserve->getStoreId(), 0);

		return $rule;
	}


	/**
	 * @param array $rules
	 * @param array $context
	 *
	 * @return Sale\Result
	 * @throws Main\ObjectNotFoundException
	 */
	public static function saveRules(array $rules, array $context)
	{
		$result = new Sale\Result();
		$shipProductsList = array();

		foreach ($rules as $ruleData)
		{
			if (empty($ruleData['ACTION']))
			{
				continue;
			}

			foreach ($ruleData['ACTION'] as $action => $quantityItem)
			{
				if (is_array($quantityItem))
				{
					$quantity = $quantityItem['QUANTITY'];
					$quantityByStore = $quantityItem['QUANTITY_BY_STORE'] ?? 0;
				}
				else
				{
					$quantity = $quantityItem;
					$quantityByStore = [];
				}

				if ($quantity == 0)
				{
					continue;
				}

				$fields = $ruleData;
				$fields['QUANTITY'] = $quantity;
				$fields['QUANTITY_BY_STORE'] = $quantityByStore;
				unset($fields['ACTION']);

				if ($action == PoolQuantity::POOL_QUANTITY_TYPE)
				{
					$shipProductsList[] = $fields;
				}
				elseif ($action == PoolQuantity::POOL_RESERVE_TYPE)
				{
					$reserveProductsList[] = $fields;
				}
			}
		}

		if (!empty($reserveProductsList))
		{
			$creator = Sale\Internals\ProviderCreator::create($context);
			foreach ($reserveProductsList as $reserveProductData)
			{
				$creator->addProductData($reserveProductData);
			}

			$r = $creator->reserve();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}
		
		if (!empty($shipProductsList))
		{
			$creator = Sale\Internals\ProviderCreator::create($context);
			foreach ($shipProductsList as $shipProductData)
			{
				$creator->addProductData($shipProductData);
			}

			$r = $creator->ship();
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}

			if ($r->hasWarnings())
			{
				$result->addWarnings($r->getWarnings());
			}

			$r = $creator->setItemsResultAfterShip($r);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
			}
		}


		return $result;
	}

}