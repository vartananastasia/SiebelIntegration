<?php
/**
 * Created by PhpStorm.
 * User: d.ivanov
 * Date: 14.11.2018
 * Time: 16:10
 */

namespace Taber\Siebel\Application;

use Taber\Siebel\Methods\CreateOrder;
use Taber\Siebel\Methods\UpdateOrderStatus;

class OrderApp
{
    public function __construct()
    {

    }

    static public function updateOrderStatus(int $orderId, string $oderBitrixStatus) {
        //Выборка статусов заказа. todo Посмотреть, можно ли добавить сопоставление статусов в базе
        /*$rsStatus = \CSaleStatus::GetList(array("SORT" => "ASC"), array("LID" => LANGUAGE_ID), false, false, array("ID", "NAME"));
        while ( $status = $rsStatus->fetch() ) {

        }*/
        //Возвращаем заказ, чтобы узнать его номер
        $obOrder = \Girlfriend\Models\Eshop\Order::getOrderById($orderId);
        $orderNumber = $obOrder->getField("ACCOUNT_NUMBER");

        $statusMatching = [
            CreateOrder::BITRIX_STATUS_WAITING_PAYMENT  => CreateOrder::CHEQUE_STATUS_WAITING_PAYMENT,
            CreateOrder::BITRIX_STATUS_WAITING_ACCEPT   => CreateOrder::CHEQUE_STATUS_WAITING_ACCEPT,
            CreateOrder::BITRIX_STATUS_ACCEPTED         => CreateOrder::CHEQUE_STATUS_ACCEPTED,
            CreateOrder::BITRIX_STATUS_SHIPPED          => CreateOrder::CHEQUE_STATUS_SHIPPED,
            CreateOrder::BITRIX_STATUS_FINISHED         => CreateOrder::CHEQUE_STATUS_FINISHED,
            CreateOrder::BITRIX_STATUS_CANCELED         => CreateOrder::CHEQUE_STATUS_CANCELED,
            CreateOrder::BITRIX_STATUS_DELIVERY_CANCELED => CreateOrder::CHEQUE_STATUS_CANCELED
        ];

        //Определяем статус в зибеле по массиву сопоставления
        if($statusMatching[$oderBitrixStatus]) {
            $oderSiebelStatus = $statusMatching[$oderBitrixStatus];
        } else {
            $oderSiebelStatus = CreateOrder::CHEQUE_STATUS_CANCELED;
        }

        $arSiebelParams = [
            "ChequeId" => CreateOrder::CHEQUE_ID_PREFIX . $orderNumber,
            "ChequeStatus" => $oderSiebelStatus
        ];

        try {
            $soapApi = UpdateOrderStatus::createMethod($arSiebelParams);
        } catch (\Taber\Siebel\SiebelException\SiebelErrorResponceException $e) {
            //
        } catch (\Taber\Siebel\SiebelException\SiebelException $e) {
            //непредвиденные ошибки. нельзя прерывать скрипт, так как он выполняется в хендлере
        }
    }
}