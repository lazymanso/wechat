<?php

namespace lazymanso\wechat\config;

/**
 * 微信系统订单交易码
 */
class OrderState
{
	/* ====================================================================== */
	// 微信支付结果
	/** 支付成功 */
	const TRADE_STATE_SUCCESS = 'SUCCESS'; //
	/** 转入退款 */
	const TRADE_STATE_REFUND = 'REFUND'; //
	/** 未支付 */
	const TRADE_STATE_NOTPAY = 'NOTPAY'; //
	/** 已关闭 */
	const TRADE_STATE_CLOSED = 'CLOSED'; //
	/** 已撤销 */
	const TRADE_STATE_REVOKED = 'REVOKED'; //
	/** 用户支付中 */
	const TRADE_STATE_USERPAYING = 'USERPAYING'; //
	/** 支付失败 */
	const TRADE_STATE_PAYERROR = 'PAYERROR'; //

	/* ====================================================================== */
	// 微信退款结果
	/** 退款成功 */
	const REFUND_STATE_SUCCESS = 'SUCCESS'; //
	/** 退款失败 */
	const REFUND_STATE_FAIL = 'FAIL'; //
	/** 退款处理中 */
	const REFUND_STATE_PROCESSING = 'PROCESSING'; //
	/**
	 * <pre>
	 * 转入代发，退款到银行发现用户的卡作废或者冻结了，导致原路退款银行卡失败，
	 * 资金回流到商户的现金帐号，
	 * 需要商户人工干预，通过线下或者财付通转账的方式进行退款
	 * </pre>
	 */
	const REFUND_STATE_CHANGE = 'CHANGE'; //
	/** 退款关闭 */
	const REFUND_STATE_REFUNDCLOSE = 'REFUNDCLOSE'; //

}
