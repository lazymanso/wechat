<?php

namespace lazymanso\wechat;

//use lazymanso\wechat\config\ErrorCode;

class Common
{
	/**
	 * 错误信息
	 * @static
	 * @access private
	 * @var string
	 */
	private static $_strError = '';

	/**
	 * 错误号
	 * @static
	 * @access private
	 * @var string
	 */
	private static $_strErrorNo = '';

	/**
	 * 错误码
	 * @static
	 * @access private
	 * @var string
	 */
	private static $_strErrorCode = '';

	/**
	 * 检测要求的字段是否存在
	 * @param array $aData [in]数据
	 * @param array $aField [in]必须字段名,如果设置了严格模式,值为空时(0,'',false,NULL)返回错误
	 * @param array $aOptField [in opt]可选字段名,值可以为空,但$aData中必须有其中的至少一个字段
	 * @param bool $bStrict [in opt]严格检查
	 * @return boolean
	 */
	protected function checkFields(array $aData, array $aField, array $aOptField = [], $bStrict = false)
	{
		$key = $this->_arrayKeyExist($aField, $aData, $bStrict);
		if ($key !== true)
		{
			$this->setError('必须的参数缺少，参数名：' . $key);
			return false;
		}
		// 可选字段必须具其一
		if (!empty($aOptField))
		{
			$aResult = array_intersect($aOptField, array_keys($aData));
			if (empty($aResult))
			{
				$this->setError('可选参数缺少，需要参数列表：' . join(',', $aOptField));
				return false;
			}
		}
		return true;
	}

	/**
	 * 检查要求的键名是否在数据数组中存在
	 * @access private
	 * @param array $aKeys [in]要检测的键名
	 * @param array $aData [in]待检测数据
	 * @param boolean $bStrict [in opt]严格检查
	 * @return boolean|string 全部存在返回true，否则返回缺少的键名
	 */
	private function _arrayKeyExist(array $aKeys, array $aData, $bStrict = false)
	{
		foreach ($aKeys as $key)
		{
			if ((!array_key_exists($key, $aData)) || ($bStrict && empty($aData[$key])))
			{
				return $key;
			}
		}
		return true;
	}

	/**
	 * 获取错误信息
	 * @access public
	 * @return mixed 错误信息
	 */
	public function getError()
	{
		return self::$_strError;
	}

	/**
	 * 获取错误号
	 * @access public
	 * @return int 错误号
	 */
	public function getErrorNo()
	{
		return is_numeric(self::$_strErrorNo) ? self::$_strErrorNo : 0;
	}

	/**
	 * 获取错误信息
	 * @access public
	 * @return mixed 错误信息
	 */
	public function getErrorCode()
	{
		return self::$_strErrorCode;
	}

	/**
	 * 设置错误信息
	 * @access protected
	 * @param string $mxError [in]错误信息
	 * @return void
	 */
	protected function setError($mxError)
	{
		self::$_strError = $mxError;
	}

	/**
	 * 获取错误号
	 * @access protected
	 * @param int $nNumber [in]错误号
	 * @return void
	 */
	protected function setErrorNo($nNumber)
	{
		self::$_strErrorNo = $nNumber;
	}

	/**
	 * 获取错误状态码
	 * @access protected
	 * @param string $strCode [in]错误号
	 * @return void
	 */
	protected function setErrorCode($strCode)
	{
		self::$_strErrorCode = $strCode;
	}
}
