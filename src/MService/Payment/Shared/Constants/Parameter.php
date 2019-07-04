<?php


namespace MService\Payment\Shared\Constants;

class Parameter
{
    public const PARTNER_CODE = "partnerCode";
    public const ACCESS_KEY = "accessKey";
    public const REQUEST_ID = "requestId";
    public const AMOUNT = "amount";

    public const ORDER_ID = "orderId";
    public const ORDER_INFO = "orderInfo";

    public const RETURN_URL = "returnUrl";
    public const NOTIFY_URL = "notifyUrl";

    public const REQUEST_TYPE = "requestType";
    public const EXTRA_DATA = "extraData";
    public const BANK_CODE = "bankCode";
    public const TRANS_ID = "transId";
    public const PAY_TRANS_ID = "transid";
    public const MESSAGE = "message";
    public const LOCAL_MESSAGE = "localMessage";
    public const DESCRIPTION = "description";
    public const PAY_URL = "payUrl";
    public const DEEP_LINK = "deeplink";
    public const QR_CODE = "qrCode";
    public const ERROR_CODE = "errorCode";
    public const STATUS = "status";
    public const PAY_TYPE = "payType";
    public const TRANS_TYPE = "transType";
    public const ORDER_TYPE = "orderType";
    public const MOMO_TRANS_ID = "momoTransId";
    public const PAYMENT_CODE = "paymentCode";

    public const DATE = "responseTime";
    public const VERSION = "version";
    public const HASH = "hash";
    public const APP_PAY_TYPE = "appPayType";
    public const APP_DATA = "appData";
    public const SIGNATURE = "signature";

    public const CUSTOMER_NUMBER = "customerNumber";
    public const PARTNER_REF_ID = "partnerRefId";
    public const PARTNER_TRANS_ID = "partnerTransId";
    public const USERNAME = "userName";
    public const PARTNER_NAME = "partnerName";
    public const STORE_ID = "storeId";
    public const STORE_NAME = "storeName";

    //URI for different processes in MOMO payment system:
    public const PAY_GATE_URI = "/gw_payment/transactionProcessor";
    public const PAY_APP_URI = "/pay/app";
    public const PAY_POS_URI = "/pay/pos";
    public const PAY_CONFIRMATION_URI = "/pay/confirm";
    public const PAY_STATUS_URI = "/pay/query-status";
    public const PAY_REFUND_URI = "/pay/refund";
    public const PAY_QR_CODE_URI = "/pay/notify";

}