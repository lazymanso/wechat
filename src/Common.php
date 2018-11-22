<?php

namespace lazymanso\wechat;

use lazymanso\wechat\util\Curl;
use lazymanso\wechat\config\Command;

class Common
{
	/**
	 * 应用appid
	 * @var string
	 */
	protected $strAppid = '';

	/**
	 * 应用secret
	 * @var string
	 */
	protected $strSecret = '';

	/**
	 * 接口调用凭证
	 * @var string
	 */
	protected $strToken = '';

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

	/**
	 * 向微信发送请求
	 * @param int $nCommand [in]api接口代码
	 * @param mixed $param [in]请求参数
	 * @param string $strDataType [in opt]参数格式化类型(xml,json) 默认不处理
	 * @return mxied 经过处理的微信接口响应内容，返回 false 时表示出错
	 */
	protected function doCommand($nCommand, $param, $strDataType = '')
	{
		if (!$aRequest = Command::get($nCommand))
		{
			$this->setError('获取微信接口请求设置信息失败');
			return false;
		}
		// 检测$param中是否存在命令所需的get参数
		$aQuery = [];
		foreach ($aRequest['query'] as $key)
		{
			if (in_array($key, ['access_token', 'component_access_token']))
			{
				$aQuery[$key] = $this->strToken;
			}
			elseif (!isset($param[$key]) || empty($param[$key]))
			{
				$this->setError('缺少query参数：' . $key);
				return false;
			}
			else
			{
				$aQuery[$key] = $param[$key];
			}
			unset($param[$key]);
		}
		if (!empty($aQuery))
		{
			$strQueryString = http_build_query($aQuery);
			$aRequest['url'] .= (false === strpos($aRequest['url'], '?') ? '?' . $strQueryString : '&' . $strQueryString);
		}
		// 处理格式化请求内容
		switch ($strDataType)
		{
			case 'xml':
				$data = $this->array2xml($param);
				break;
			case 'json':
				$data = empty($param) ? '{}' : json_encode($param, JSON_UNESCAPED_UNICODE);
				break;
			default :
				$data = $param;
		}
		// 发送请求
		$this->oCurlUtil = new Curl;
		$result = '';
		switch ($aRequest['method'])
		{
			case 'get':
				$cmdResult = $this->oCurlUtil->get($aRequest['url'], $result);
				break;
			case 'post':
				$cmdResult = $this->oCurlUtil->post($aRequest['url'], $data, $result);
				break;
			case 'file':
				$cmdResult = $this->oCurlUtil->file($aRequest['url'], $data, $result);
				break;
		}
		$strTraceParam = is_array($data) ? print_r($data, true) : $data;
		if (!$cmdResult)
		{
			$this->setError('请求微信接口失败 ' . $aRequest['method'] . ' ' . $aRequest['url'] . ' ' . $strTraceParam);
			return false;
		}
		$response = $this->checkResult($result);
		if (false === $response)
		{
			$data = is_array($data) ? print_r($data, true) : $data;
			$this->setError($this->getError() . '，接口信息：' . print_r($aRequest, true) . '，接口参数：' . $strTraceParam);
			return false;
		}
		return $response;
	}

	/**
	 * 产生随机字符串，不长于32位
	 * @param int $length [in]长度
	 * @return string
	 */
	protected function createNoncestr($length = 32)
	{
		$chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
		$str = '';
		for ($i = 0; $i < $length; $i++)
		{
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}

	/**
	 * 检查并处理curl返回结果
	 * @param mixed $result [in]curl返回数据
	 * @return mixed 返回 false ,表示有错误
	 */
	protected function checkResult($result = '')
	{
		if (0 === strpos($result, '<xml>'))
		{
			$aResult = $this->xml2array($result);
			//return_code 和 result_code 都为 SUCCESS 时，请求才算成功
			if ('FAIL' === $aResult['return_code'])
			{
				$this->setError($aResult['return_code'] . ',' . $aResult['return_msg']);
				return false;
			}
			elseif ('FAIL' === $aResult['result_code'])
			{
				$this->setError($aResult['err_code_des']);
				$this->setErrorCode($aResult['err_code']);
				return false;
			}
			else
			{
				return $aResult;
			}
		}
		elseif ($aResult = json_decode($result, true))
		{
			if (isset($aResult['errcode']) && !empty($aResult['errcode']))
			{
				$this->setErrorNo($aResult['errcode']);
				$this->setError($aResult['errmsg']);
				return false;
			}
			return $aResult;
		}
		else
		{
			return $result;
		}
	}

	/**
	 * xml转换成数组
	 * @param string $xml
	 * @return array
	 */
	protected function xml2array($xml)
	{
		if (empty($xml))
		{
			return [];
		}
		libxml_disable_entity_loader(true);
		$xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		$val = json_decode(json_encode($xmlstring), true);
		array_walk_recursive($val, function(&$value) {
			$value = trim($value);
		});
		return $val;
	}

	/**
	 * 数组转换成xml
	 * @param array $aParam [in]
	 * @return string
	 */
	protected function array2xml(array $aParam)
	{
		$xml = '<xml>';
		foreach ($aParam as $key => $val)
		{
			if (is_array($val))
			{
				$xml .= '<' . $key . '>' . $this->array2xml($val) . '</' . $key . '>';
			}
			elseif (is_numeric($val) && is_int($val))
			{
				$xml .= '<' . $key . '>' . $val . '</' . $key . '>';
			}
			else
			{
				$xml .= '<' . $key . '><![CDATA[' . $val . ']]></' . $key . '>';
			}
		}
		$xml .= '</xml>';
		return $xml;
	}

	public function getAppid()
	{
		return $this->strAppId;
	}
}
