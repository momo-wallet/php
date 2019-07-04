<?php


namespace MService\Payment\Shared\Constants;

class RequestType
{
    /*
     * ======================= USING FOR MERCHANT/PARTNER PUBLIC =================
     */
    const UN_SUPPORT = "UN_SUPPORT";
    const CAPTURE_MOMO_WALLET = "captureMoMoWallet";
    const TRANSACTION_STATUS = "transactionStatus";
    const REFUND_MOMO_WALLET = "refundMoMoWallet";
    const QUERY_REFUND = "refundStatus"; //
    const REFUND_ATM = "refundMoMoATM"; //
    const WALLET_BALANCE = "walletBalance";
    const PAY_WITH_ATM = "payWithMoMoATM";
    const TOPUP_MOBILE = "topUpMoMo";
    const BUY_CARD_PHONE = "buyCardMoMo";
    const SUBSCRIBE = "subscribeMoMo";
    const PAY_WITH_SUBSCRIBE = "payWithSubscribeMoMo";
    const AUTHORIZE_MOMO_WALLET = "subscriptionToken";
    const TRANS_TYPE_MOMO_WALLET = "momo_wallet";

    const FINISH_WITH_MOMO_ATM = "finishProcessMoMoATM"; //
    const PAY_WITH_QR = "finishProcessMoMoATM"; //

    const CONFIRM_APP_TRANSACTION = "capture";
    const CANCEL_APP_TRANSACTION = "revertAuthorize";
    const VERSION = 2.0;
    const APP_PAY_TYPE = 3;

    /*
     * ========================= USING INTERNAL ==============================
     */
    const QUERY_STATUS_PAY_WITH_APP = "queryStatusPayWithApp";
    const QUERY_STATUS_AUTHORIZE_WITH_APP = "queryStatusAuthorizeWithApp";
    const PAY_WITH_APP = "payWithApp";

}