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

	/* ====================================================================== */
	// 微信退款渠道refund_channel
	/** 原路退款 */
	const REFUND_CHANNEL_ORIGINAL = 'ORIGINAL'; //
	/** 退回到余额 */
	const REFUND_CHANNEL_BALANCE = 'BALANCE'; //
	/** 原账户异常退到其他余额账户 */
	const REFUND_CHANNEL_OTHER_BALANCE = 'OTHER_BALANCE'; //
	/** 原银行卡异常退到其他银行卡 */
	const REFUND_CHANNEL_OTHER_BANKCARD = 'OTHER_BANKCARD'; //

	/**
	 * 微信系统订单交易(支付)返回码说明
	 * @var array
	 */
	private static $_aTradeStateMap = [
		self::TRADE_STATE_SUCCESS => '已支付',
		self::TRADE_STATE_REFUND => '转入退款',
		self::TRADE_STATE_NOTPAY => '未支付',
		self::TRADE_STATE_CLOSED => '已关闭',
		self::TRADE_STATE_REVOKED => '已撤销',
		self::TRADE_STATE_USERPAYING => '用户支付中',
		self::TRADE_STATE_PAYERROR => '支付失败',
	];

	/**
	 * 微信系统订单退款返回码说明
	 * @var array
	 */
	private static $_aRefundStateMap = [
		self::REFUND_STATE_SUCCESS => '退款成功',
		self::REFUND_STATE_FAIL => '退款失败',
		self::REFUND_STATE_PROCESSING => '退款处理中',
		self::REFUND_STATE_CHANGE => '退款异常',
		self::REFUND_STATE_REFUNDCLOSE => '退款关闭',
	];

	/**
	 * 退款渠道返回码说明
	 * @var array
	 */
	private static $_aRefundChannelMap = [
		self::REFUND_CHANNEL_ORIGINAL => '原路退款',
		self::REFUND_CHANNEL_BALANCE => '退回到余额',
		self::REFUND_CHANNEL_OTHER_BALANCE => '原账户异常退到其他余额账户',
		self::REFUND_CHANNEL_OTHER_BANKCARD => '原银行卡异常退到其他银行卡',
	];

	/**
	 * 获取微信系统订单交易微信状态码说明
	 * @param string $strCode [in]状态码
	 * @return string|array
	 */
	public static function getTradeState($strCode = '')
	{
		if (empty($strCode))
		{
			return self::$_aTradeStateMap;
		}
		else
		{
			return isset(self::$_aTradeStateMap[$strCode]) ? self::$_aTradeStateMap[$strCode] : '未知状态';
		}
	}

	/**
	 * 获取微信系统订单退款状态码说明
	 * @param string $strCode [in]状态码
	 * @return string|array
	 */
	public static function getRefundState($strCode = '')
	{
		if (empty($strCode))
		{
			return self::$_aRefundStateMap;
		}
		else
		{
			return isset(self::$_aRefundStateMap[$strCode]) ? self::$_aRefundStateMap[$strCode] : '未知状态';
		}
	}

	/**
	 * 获取退款渠道说明
	 * @param string $strCode [in]状态码
	 * @return string|array
	 */
	public static function getRefundChannel($strCode = '')
	{
		if (empty($strCode))
		{
			return self::$_aRefundChannelMap;
		}
		else
		{
			return isset(self::$_aRefundChannelMap[$strCode]) ? self::$_aRefundChannelMap[$strCode] : '未知渠道';
		}
	}
}
