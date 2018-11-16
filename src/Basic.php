<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace lazymanso\wechat;

use lazymanso\wechat\config\Command;

/**
 * 一些基础的微信接口操作
 */
class Basic extends Common
{
	/**
	 * 应用配置
	 * @var array
	 */
	protected $aConfig = [
		'appid' => '',
		'secret' => '',
	];

	/**
	 * 构造
	 * @param array $aConfig [in]应用配置，格式如下：
	 * <pre>
	 * array(
	 *  'appid' => '',
	 *  'secret' => '',
	 * )
	 * </pre>
	 */
	public function __construct(array $aConfig = [])
	{
		if (!empty($aConfig))
		{
			$this->aConfig = array_merge($this->aConfig, $aConfig);
		}
		$this->strAppid = $this->aConfig['appid'];
		$this->strSecret = $this->aConfig['secret'];
	}

	/**
	 * 获取微信接口调用凭证
	 * @link https://developers.weixin.qq.com/miniprogram/dev/api/open-api/access-token/getAccessToken.html
	 * @return false|array 返回false表示请求失败或出错
	 * <pre>
	 * 成功时返回token信息
	 * access_token - string,获取到的凭证
	 * expires_in - int,凭证有效时间，单位：秒。目前是7200秒之内的值
	 * </pre>
	 */
	public function token()
	{
		$aParam = [
			'appid' => $this->strAppid,
			'secret' => $this->strSecret,
		];
		return $this->doCommand(Command::BASE_ACCESS_TOKEN, $aParam);
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
