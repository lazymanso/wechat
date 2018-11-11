<?php

namespace lazymanso\wechat;

class MiniProgram extends Common
{
	/**
	 * 小程序功能模块名称，如支付、模板消息
	 * @var string
	 */
	protected $strModule = '';

	/**
	 * 各模块类实例化的缓存
	 * @var array
	 */
	private static $_aModuleInstance = [];

	/**
	 * 构造
	 * @param string $strModule [in]小程序功能模块名称，如支付、模板消息
	 * @throws \Exception
	 */
	public function __construct($strModule)
	{
		if (empty($strModule))
		{
			throw new \Exception('module name required');
		}
		$this->strModule = $strModule;
	}

	/**
	 * 代理转发
	 * @param string $strAction [in]方法名
	 * @param array $aParam [opt]方法参数列表
	 * @throws \Exception
	 */
	public function __call($strAction, array $aParam = [])
	{
		$strClass = '\\lazymanso\\wechat\\miniprogram\\' . $this->strModule;
		if (!class_exists($strClass))
		{
			throw new \Exception($strClass . ' not found');
		}
		if (!method_exists($strClass, $strAction))
		{
			throw new \Exception($strClass . '::' . $strAction . ' not found');
		}
		return call_user_func_array([self::getInstance($strClass), $strAction], $aParam);
	}

	/**
	 * 获取一个模块的实例
	 * @param string $strClass [in]模块的类名称
	 * @return \lazymanso\wechat\miniprogram\*
	 */
	public static function getInstance($strClass)
	{
		if (isset(self::$_aModuleInstance[$strClass]))
		{
			return self::$_aModuleInstance[$strClass];
		}
		$oInstance = new $strClass;
		self::$_aModuleInstance[$strClass] = $oInstance;
		return $oInstance;
	}
}
