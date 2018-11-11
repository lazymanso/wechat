<?php

namespace lazymanso\wechat\miniprogram;

use lazymanso\wechat\Common;
use lazymanso\wechat\util\Curl;

/**
 * 微信小程序基础类
 */
class Base extends Common
{
	/**
	 * 小程序appid
	 * @var string
	 */
	protected $strAppId;

	/**
	 * 小程序secret
	 * @var string
	 */
	protected $strAppSecret;

	/**
	 * 简单的 php curl 工具类
	 * @var \lazymanso\wechat\util\Curl
	 */
	protected $oCurlUtil;

	/**
	 * 初始化
	 */
	public function __construct()
	{

	}

	/**
	 * 向微信发送请求
	 * @param int $nCommand [in]api接口代码
	 * @param mixed $param [in]参数
	 * @param string $strDataType [in opt]参数格式化类型(xml,json) 默认不处理
	 * @param string $strCurlMethod [in opt]请求类型 默认post opt['get','post','file']
	 * @return mxied
	 */
	protected function doCommand($nCommand, $param, $strDataType = '')
	{
		if (!$aRequest = Command::get($nCommand, $param))
		{
			$this->setError(Command::getError());
			return false;
		}
		//处理参数
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
		//发送请求
		$result = '';
		switch ($nCommand)
		{
			case Command::PAY_REFUND_ORDER:
				$bUseCert = true;
				break;
			default :
				$bUseCert = false;
		}
		$this->oCurlUtil = new Curl;
		switch ($aRequest['method'])
		{
			case 'get':
				$cmdResult = $this->oCurlUtil->get($aRequest['url'], $result);
				break;
			case 'post':
				$cmdResult = $this->oCurlUtil->post($aRequest['url'], $data, $result, $bUseCert);
				break;
			case 'file':
				$cmdResult = $this->oCurlUtil->file($aRequest['url'], $data, $result);
				break;
		}
		if (!$cmdResult)
		{
			return false;
		}
		return $this->checkResult($result);
	}

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
	 * @return boolean|array|string
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
				$this->setError($aResult['err_code'] . ',' . $aResult['err_code_des']);
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
				$this->setError($aResult['errcode']);
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

	/**
	 * 检验数据的真实性，并且获取解密后的明文
	 * @param array $aInput [in]参数列表
	 * <pre>
	 * appid - string,必填,小程序appid
	 * session_key - string,必填,用户在小程序登录后获取的会话密钥
	 * encrypt_data - string,必填,加密的用户数据
	 * iv - string,必填,与用户数据一同返回的初始向量
	 * </pre>
	 * @param array $aOutput [out]明文信息
	 * @return boolean
	 */
	protected function decryptData(array $aInput, array &$aOutput = [])
	{
		if (!$this->checkFields($aInput, ['appid', 'session_key', 'encrypt_data', 'iv'], [], true))
		{
			return false;
		}
		if (strlen($aInput['session_key']) != 24)
		{
			$this->setError('encodingAesKey 非法！');
			return false;
		}
		$aesKey = base64_decode($aInput['session_key']);
		if (strlen($aInput['iv']) != 24)
		{
			$this->setError('初始向量非法！');
			return false;
		}
		$aesIV = base64_decode($aInput['iv']);
		$aesCipher = base64_decode($aInput['encrypt_data']);
		$result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
		$aData = json_decode($result, true);
		if (empty($aData) || ($aData['watermark']['appid'] != $aInput['appid']))
		{
			$this->setError('解密后得到的数据非法！解密数据：' . print_r($aInput, true));
			return false;
		}
		$aOutput = $aData;
		return true;
	}
}
