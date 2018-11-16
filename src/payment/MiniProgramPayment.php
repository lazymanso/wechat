<?php

namespace lazymanso\wechat\payment;

use lazymanso\wechat\config\Command;
use lazymanso\wechat\Common;

/**
 * 微信小程序支付
 */
class MiniProgramPayment extends Common
{
	/**
	 * 支付小程序appid
	 * @var string
	 */
	protected $strAppId;

	/**
	 * 支付小程序secret
	 * @var string
	 */
	protected $strAppSecret;

	/**
	 * 小程序支付商户号
	 * @var string
	 */
	protected $strMchId;

	/**
	 * 小程序支付商户名称
	 * @var string
	 */
	protected $strMchName;

	/**
	 * 商户设置的密钥 微信商户平台(pay.weixin.qq.com)-->账户设置-->API安全-->密钥设置
	 * @var string
	 */
	protected $strKey;

	/**
	 * 默认的小程序支付配置
	 * @var array
	 */
	protected $aConfig = [
		'appid' => '',
		'secret' => '',
		'mchid' => '',
		'mchname' => '',
		'apikey' => '',
	];

	/**
	 * 构造
	 * @param array $aConfig [in]商户小程序支付配置，格式如下：
	 * <pre>
	 * array(
	 *  'appid' => '',
	 *  'secret' => '',
	 * 	'mchid' => '',
	 * 	'mchname' => '',
	 * 	'apikey' => '',
	 * )
	 * </pre>
	 */
	public function __construct(array $aConfig = [])
	{
		if (!empty($aConfig))
		{
			$this->aConfig = array_merge($this->aConfig, $aConfig);
		}
		$this->strAppId = $this->aConfig['appid'];
		$this->strAppSecret = $this->aConfig['secret'];
		$this->strMchId = $this->aConfig['mchid'];
		$this->strMchName = $this->aConfig['mchname'];
		$this->strKey = $this->aConfig['apikey'];
	}
	/**
	 * 设置商户支付配置
	 * @param array $aConfig [in]商户支付配置
	 * @return boolean
	 */
//	public function setConfig(array $aConfig)
//	{
//		if (!$this->checkFields($aConfig, ['appid', 'secret', 'mchid', 'mchname', 'apikey'], [], true))
//		{
//			return false;
//		}
//		$this->strAppId = $aConfig['appid'];
//		$this->strAppSecret = $aConfig['secret'];
//		$this->strMchId = $aConfig['mchid'];
//		$this->strMchName = $aConfig['mchname'];
//		$this->strKey = $aConfig['apikey'];
//		return true;
//	}

	/**
	 * 生成签名
	 * @param array $aParam [in]签名参数
	 * @return string
	 */
	protected function sign(array $aParam)
	{
		//签名步骤一：按字典序排序参数
		ksort($aParam);
		$buff = '';
		foreach ($aParam as $k => $v)
		{
			if ($k != 'sign' && $v != '' && !is_array($v))
			{
				$buff .= $k . '=' . $v . '&';
			}
		}
		//签名步骤二：在string后加入KEY
		$strPreSignString = $buff . 'key=' . $this->strKey;
		//签名步骤三：MD5加密
		$string = md5($strPreSignString);
		//签名步骤四：所有字符转为大写
		return strtoupper($string);
	}

	/**
	 * 微信小程序统一下单
	 * @param array $aInput [in]必填['out_trade_no','total_fee',openid]
	 * <pre>
	 * out_trade_no - string,必填,商户自定义的订单交易号
	 * total_fee - int,必填,订单金额,单位（分）
	 * openid - string,必填,发起支付的小程序用户的openid
	 * notify_url - string,必填,接收微信支付结果通知的回调地址
	 * </pre>
	 * @param array $aOutput [out]返回['prepay_id','trade_type']
	 * @return boolean
	 */
	public function unifiedOrder(array $aInput)
	{
		if (!$this->checkFields($aInput, ['out_trade_no', 'total_fee', 'openid', 'notify_url']))
		{
			return false;
		}
		if ($aInput['total_fee'] <= 0)
		{
			$this->setError('商品价格小于等于0，下单失败');
			return false;
		}
		$strBody = $this->strMchName . '-' . $aInput['out_trade_no'];
		$aParam = [
			'appid' => $this->strAppId,
			'mch_id' => $this->strMchId,
			'nonce_str' => $this->createNoncestr(),
			'body' => $strBody,
			'out_trade_no' => $aInput['out_trade_no'],
			'total_fee' => $aInput['total_fee'],
			'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],
			'notify_url' => $aInput['notify_url'], //接收微信支付结果的地址
			'openid' => $aInput['openid'],
			'trade_type' => 'JSAPI',
		];
		$aParam['sign'] = $this->sign($aParam);
		return $this->doCommand(Command::PAY_UNIFIED_ORDER, $aParam, 'xml');
	}

	/**
	 * 订单查询
	 * 该接口提供所有微信支付订单的查询，商户可以通过查询订单接口主动查询订单状态
	 * @param array $aInput [in]参数列表
	 * <pre>
	 * out_trade_no - string,必填,商户的订单编号
	 * </pre>
	 * @param array $aOutput [out]响应内容
	 * @link https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_2
	 * @return boolean|array 返回 false 时表示出错,返回空数组时表示订单不存在
	 */
	public function queryOrder(array $aInput)
	{
		if (!$this->checkFields($aInput, ['out_trade_no'], [], true))
		{
			return false;
		}
		// 请求参数
		$aParam = [
			'appid' => $this->strAppId,
			'mch_id' => $this->strMchId,
			'nonce_str' => $this->createNoncestr(),
			'out_trade_no' => $aInput['out_trade_no'],
		];
		// 签名
		$aParam['sign'] = $this->sign($aParam);
		//
		if (false === $aResponse = $this->doCommand(Command::PAY_QUERY_ORDER, $aParam, 'xml'))
		{
			// ORDERNOTEXIST - 订单不存在
			if ('ORDERNOTEXIST' === $this->getErrorCode())
			{
				return [];
			}
			return false;
		}
		return $aResponse;
	}

	/**
	 * 返回wx.requestPayment所需要的object参数
	 * @param array $aInput [in]输入['package'=>'prepay_id=$strPrepayId']
	 * @return false|array
	 */
	public function getRequestPaymentObject(array $aInput)
	{
		if (!$this->checkFields($aInput, ['package'], [], true))
		{
			return false;
		}
		$strNowtime = (string) time();
		$strNonce = $this->createNoncestr();
		//签名参数
		$aSignParam = [
			'appId' => $this->strAppId,
			'timeStamp' => $strNowtime,
			'nonceStr' => $strNonce,
			'package' => $aInput['package'],
			'signType' => 'MD5',
		];
		//输出数据
		return [
			'timeStamp' => $strNowtime,
			'nonceStr' => $strNonce,
			'package' => $aInput['package'],
			'signType' => 'MD5',
			'paySign' => $this->sign($aSignParam),
		];
	}

	/**
	 * 处理微信支付结果通知数据
	 * @param array $aInput [in]'xml', 'order_sn', 'total_fee'
	 * @return boolean|array 处理成功会返回通知信息（数组）
	 */
	public function paymentNotify(array $aInput)
	{
		if (!$this->checkFields($aInput, ['xml', 'order_sn', 'total_fee'], [], true))
		{
			return false;
		}
		$aParam = $this->xml2array($aInput['xml']);
		$outradeNo = $aParam['out_trade_no'];
		$sign = $aParam['sign'];
		$totalfee = $aParam['total_fee'];
		//
		if ($outradeNo != $aInput['order_sn'])
		{
			$this->setError('订单号不一致');
			return false;
		}
		// 对比微信推送的消息内容中支付金额和商户侧的订单金额是否相等
		if ($totalfee != $aInput['total_fee'])
		{
			$this->setError('订单金额与商户侧的订单金额不一致');
			return false;
		}
		// 校验签名
		if ($sign !== $this->sign($aParam))
		{
			$this->setError('校验支付结果签名失败，签名不一致');
			return false;
		}
		return $aParam;
	}
}
