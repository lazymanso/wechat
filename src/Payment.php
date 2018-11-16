<?php

namespace lazymanso\wechat;

class Payment extends Common
{
	/**
	 * 支付产品名称
	 * @var string
	 */
	protected $strProductName = '';

	/**
	 * 商户支付配置
	 * @var array
	 */
	protected $aConfig = [];

	/**
	 * 构造
	 * @param array $aArgs [in]参数列表
	 * <pre>
	 * product - string,必填,支付产品名称,取值有以下几种
	 * 'qrcode':付款码支付
	 * 'jsapi':JSAPI支付
	 * 'miniapp':小程序支付
	 * 'native':Native支付
	 * 'app':APP支付
	 * 'h5':H5支付
	 * =========================================================================
	 * config - array,必填,商户支付配置,格式如下:
	 * array('appid' => '','secret' => '','mchid' => '','mchname' => '','apikey' => '')
	 * </pre>
	 * @throws \Exception
	 */
	public function __construct(array $aArgs)
	{
		// product取值
		if (!isset($aArgs['product']) || empty($aArgs['product']))
		{
			throw new \Exception('product name required');
		}
		// 商户支付配置
		if (!isset($aArgs['config']) || empty($aArgs['config']))
		{
			throw new \Exception('payment config required');
		}
		$this->strProductName = strtolower($aArgs['product']);
		$this->aConfig = $aArgs['config'];
	}

	/**
	 * 代理转发
	 * @param string $strAction [in]方法名
	 * @param array $aArgs [in]方法参数列表
	 * @throws \Exception
	 */
	public function __call($strAction, array $aArgs)
	{
		switch ($this->strProductName)
		{
			case 'qrcode':
				$strClassName = 'Qrcode';
				break;
			case 'jsapi':
				$strClassName = 'JSAPI';
				break;
			case 'miniapp':
				$strClassName = 'MiniProgram';
				break;
			case 'native':
				$strClassName = 'Native';
				break;
			case 'h5':
				$strClassName = 'H5';
				break;
			default :
				throw new \Exception('payment product not found');
		}
		$strClass = '\\lazymanso\\wechat\\payment\\' . $strClassName . '\\Payment';
		if (!class_exists($strClass))
		{
			throw new \Exception($strClass . ' not found');
		}
		if (!method_exists($strClass, $strAction))
		{
			throw new \Exception($strClass . '::' . $strAction . ' not found');
		}
		return call_user_func_array([new $strClass($this->aConfig), $strAction], $aArgs);
	}
}
