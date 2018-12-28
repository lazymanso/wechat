<?php

namespace lazymanso\wechat;

use lazymanso\common\Base;
use lazymanso\common\Curl;
use lazymanso\wechat\config\Command;

class Common extends Base
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

	public function getAppid()
	{
		return $this->strAppId;
	}
}
