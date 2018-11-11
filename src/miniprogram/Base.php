<?php

namespace lazymanso\wechat\miniprogram;

use lazymanso\wechat\Common;

/**
 * 微信小程序基础类
 */
class Base extends Common
{
	/**
	 * curl服务类
	 * @var \Common\Service\Tool\CurlService
	 */
	protected $oCurlService = null;

	/**
	 * 返回数据
	 * @var array
	 */
	protected $response = [];

	/**
	 * API密钥
	 * @var string
	 */
	protected $_strKey;

	/**
	 * 构造函数扩展
	 * @access protected
	 * @return void
	 */
	protected function _initialize()
	{
		parent::_initialize();
		$this->oCurlService = fn_GetService(\ServiceConf::NAME_CURL);
	}

	/**
	 * 向微信发送请求
	 * @param int $nCommand [in]api接口代码
	 * @param mixed $param [in]参数
	 * @param string $strDataType [in opt]参数格式化类型(xml,json) 默认不处理
	 * @param string $strCurlMethod [in opt]请求类型 默认post opt['get','post','file']
	 * @return boolean
	 */
	protected function _doCommand($nCommand, $param, $strDataType = '', $strCurlMethod = 'post')
	{
		$inputParam = $param;
		if (!$url = Command::get($nCommand, $param))
		{
			$this->setError(Command::getError());
			return false;
		}

		//处理参数
		switch ($strDataType)
		{
			case 'xml':
				$data = $this->_array2xml($param);
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
		$this->oCurlService->setUuid($this->_strUuid);

		switch ($strCurlMethod)
		{
			case 'get':
				$cmdResult = $this->oCurlService->get($url, $result);
				break;
			case 'post':
				$cmdResult = $this->oCurlService->post($url, $data, $result, $bUseCert);
				break;
			case 'file':
				$cmdResult = $this->oCurlService->file($url, $data, $result);
				break;
			default :
				$this->setError('错误的发送请求方式，' . $strCurlMethod);
				return false;
		}
		if (!$cmdResult)
		{
			$this->logError('curl 错误：' . $this->getError(), $this->_strUuid);
			return false;
		}

		$aOutput = [];
		if (!empty($this->response))
		{
			$this->response = [];
		}
		if (!$this->_checkCurlServiceResult($result, $aOutput))
		{
			$strQuery = is_array($inputParam) ? print_r($inputParam, true) : $inputParam;
			$this->logError('执行命令失败：' . $this->getError() . '，请求数据：' . $strQuery . '，格式化数据：' . $data, $this->_strUuid);
			return false;
		}
		$this->response = $aOutput;

		return true;
	}

	/**
	 * 生成签名
	 * @param array $aParam [in]签名参数
	 * @return string
	 */
	protected function _getSign(array $aParam)
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
		$strPreSignString = $buff . 'key=' . $this->_strKey;
		//签名步骤三：MD5加密
		$string = md5($strPreSignString);
		//签名步骤四：所有字符转为大写
		return strtoupper($string);
	}

	/**
	 * 产生随机字符串，不长于32位
	 * @param int $length
	 * @return string
	 */
	protected function _createNoncestr($length = 32)
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
	 * 微信小程序登录信息的code 换取 session_key
	 * @param array $aInput [in]参数['uuid', 'js_code']
	 * @param array $aOutput [out]返回数据[openid,session_key,'unionid']
	 * @return boolean
	 */
	public function getSession(array $aInput, array &$aOutput = [])
	{
		if (!$this->_checkFields($aInput, ['uuid', 'js_code']) || !$this->_checkUuid($aInput))
		{
			return false;
		}

		$aCommonParam = [
			//'appid' => $this->_aSiteBaseInfo['app_id'],
			'js_code' => $aInput['js_code'],
			'grant_type' => 'authorization_code',
			'secret' => $this->_aSiteBaseInfo['app_secret'],
		];
		$nCmdCode = Command::BASE_JSCODE_TO_SESSION;
		$strAppid = $this->_aSiteBaseInfo['app_id'];

		//查询小程序本地是否存在授权信息
		$_oOpenAuthorizeService = fn_GetService(\ServiceConf::NAME_OPEN_AUTHORIZE);
		$aCheckAuthOutput = [];
		if (false === $nAuthed = $_oOpenAuthorizeService->isAuth(['site_id' => $this->_nSiteId], $aCheckAuthOutput))
		{
			return false;
		}
		//如果授权了则走第三方换取session
		if (1 === $nAuthed)
		{
			$aCommonParam['component_appid'] = \ComponentConf::COMPONENT_APPID;
			$aToken = [];
			if (!$_oOpenAuthorizeService->getComponentAccessToken($aToken))
			{
				return false;
			}
			$aCommonParam['component_access_token'] = $aToken['component_access_token'];
			$nCmdCode = Command::COMPONENT_JSCODE_TO_SESSION;
			$strAppid = $aCheckAuthOutput['authorizer_appid'];
		}

		$aCommonParam['appid'] = $strAppid;

		//openid,session_key
		if (!$this->_doCommand($nCmdCode, $aCommonParam, '', 'get'))
		{
			return false;
		}

		$aOutput = $this->response;
		//解密数据
		if (!empty($aInput['encrypted_data']) && !empty($aInput['iv']) && $nCmdCode === Command::COMPONENT_JSCODE_TO_SESSION)
		{
			if ($aDecryptedData = $this->decryptData($strAppid, $this->response['session_key'], $aInput['encrypted_data'], $aInput['iv']))
			{
				$aOutput['unionid'] = isset($aDecryptedData['unionId']) ? $aDecryptedData['unionId'] : '';
			}
			$aOutput['unionid'] = isset($aDecryptedData['unionId']) ? $aDecryptedData['unionId'] : '';
		}
		return true;
	}

	/**
	 * 获取diy_session表中的微信小程序用户会话信息
	 * @param string $str3rdSessionKey
	 * @param array $aField
	 * @return boolean
	 */
	protected function _getLocalSessionInfo($str3rdSessionKey, array $aField = [])
	{
		$oSessionModel = D('Session');
		if (false === $aSessionList = $oSessionModel->getByField('local_session_key', $str3rdSessionKey, $aField))
		{
			$this->setError('内部错误,请稍后再试');
			return false;
		}
		if (empty($aSessionList))
		{
			$this->setError('会话信息不存在,请重新登录后再试');
			return false;
		}
		return $aSessionList[0];
	}

	/**
	 * 检测传入的token信息是否可用
	 * @param array $aTokenInfo [in]参数['expiry_time'=>'过期日期时间Y-d-m H:i:s']
	 * @param int $nBeforeHandTime [in]过期时间提早多少秒检测，默认0s
	 * @return int|boolean false；表示有错误，1：表示可用，0：表示不可用
	 */
	protected function isTokenAvailable($aTokenInfo, $nBeforeHandTime = 0)
	{
		if (empty($aTokenInfo))
		{
			return 0;
		}
		if (!$this->_checkFields($aTokenInfo, ['expiry_time']))
		{
			return false;
		}
		return date('Y-m-d H:i:s', time() + $nBeforeHandTime) < $aTokenInfo['expiry_time'] ? 1 : 0;
	}

	/**
	 * 刷新access_token
	 * @param array $aInput [in]参数 ['app_id', 'app_secret']
	 * @param array $aOutput [out]输出['access_token', 'expiry_time']
	 * @return boolean
	 */
	public function refreshAccessToken(array $aInput, array &$aOutput = [])
	{
		if (!$this->_checkFields($aInput, ['app_id', 'app_secret'], [], true))
		{
			return false;
		}
		$appid = $aInput['app_id'];
		$appSecret = $aInput['app_secret'];
		$aParam = [
			'grant_type' => 'client_credential',
			'appid' => $appid,
			'secret' => $appSecret,
		];

		if (!$this->_doCommand(Command::BASE_ACCESS_TOKEN, $aParam, '', 'get'))
		{
			return false;
		}
		$aOutput = $this->response;
		if (!isset($aOutput['access_token']))
		{
			$this->setError('刷新微信小程序access_token失败：' . $aOutput['errcode'] . ',' . $aOutput['errmsg']);
			return false;
		}
		//更新token表
		$strExpiryDate = '';
		if (!$this->_updateAccessToken($appid, $aOutput, $strExpiryDate))
		{
			return false;
		}
		$aOutput['expiry_time'] = $strExpiryDate;
		$aOutput['app_id'] = $appid;
		return true;
	}

	/**
	 * 刷新token后保存操作
	 * @param string $strAppid [in]小程序appid
	 * @param array $aRefreshData [in]刷新token接口返回数据
	 * @param string $strExpiryDate [out]新的过期时间
	 * @return boolean
	 */
	protected function _updateAccessToken($strAppid, array $aRefreshData, &$strExpiryDate)
	{
		//更新token表
		$oTokenModel = D('SiteAccessToken');
		$aSaveData = array(
			'access_token' => $aRefreshData['access_token'],
			'expiry_time' => date('Y-m-d H:i:s', time() + $aRefreshData['expires_in']),
		);
		if ($oTokenModel->getCount(['app_id' => $strAppid]))
		{
			$bSaveRes = $oTokenModel->updateByParentId($aSaveData, $strAppid);
		}
		else
		{
			$aSaveData['app_id'] = $strAppid;
			$bSaveRes = $oTokenModel->insertSingle($aSaveData);
		}
		if (false === $bSaveRes)
		{
			$this->setError('保存微信小程序access_token失败,access_token:' . $aRefreshData['access_token'] . ',' . $oTokenModel->getError());
			return false;
		}
		$strExpiryDate = $aSaveData['expiry_time'];
		return true;
	}

	/**
	 * 检查curl服务返回结果
	 * @param mixed $result [in]curl返回数据
	 * @param array $aOutput [out]
	 * @return boolean
	 */
	protected function _checkCurlServiceResult($result = '', &$aOutput = [])
	{
		if (0 === strpos($result, '<xml>'))
		{
			$aResult = $this->_xml2array($result);
			if (in_array($aResult['err_code'], ['ORDERNOTEXIST', 'REFUNDNOTEXIST']))
			{
				return true;
			}
			elseif ('SYSTEMERROR' === $aResult['err_code'])
			{
				$this->setError('系统异常，请稍后再试', '', false);
				return false;
			}
			elseif ('FAIL' === $aResult['return_code'])
			{
				$this->setError($aResult['return_code'] . '，' . $aResult['return_msg']);
				return false;
			}
			elseif ('FAIL' === $aResult['result_code'])
			{
				$this->setError($aResult['err_code'] . '，' . $aResult['err_code_des']);
				return false;
			}
			$aOutput = $aResult;
		}
		elseif ($aResult = json_decode($result, true))
		{
			if (isset($aResult['errcode']) && !empty($aResult['errcode']))
			{
				//减少不必要的日志记录
				if (in_array($aResult['errcode'], [\ErrorConf::ERROR_WEIXIN_OPEN_NOT_EXISTS,
					\ErrorConf::ERROR_WEIXIN_OPEN_ACCOUNT_HASBOUND_OPEN,
					\ErrorConf::ERROR_WEIXIN_COMPONENT_NOT_AUTHORIZED,
					\ErrorConf::ERROR_WEIXIN_INVALID_CARDID,
					\ErrorConf::ERROR_WEIXIN_TEMPLATE_MESSAGE_ID,
					\ErrorConf::ERROR_WEIXIN_CONTENT_SECURITY_RISKY,
				]))
				{
					$this->setError($aResult['errcode'], '', false);
				}
				else
				{
					$this->setError($aResult['errcode']);
				}
				return false;
			}
			$aOutput = $aResult;
		}
		else
		{
			$aOutput = $result;
		}
		return true;
	}

	/**
	 * xml转换成数组
	 * @param string $xml
	 * @return array
	 */
	protected function _xml2array($xml)
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
	protected function _array2xml(array $aParam)
	{
		$xml = '<xml>';
		foreach ($aParam as $key => $val)
		{
			if (is_array($val))
			{
				$xml .= '<' . $key . '>' . $this->_array2xml($val) . '</' . $key . '>';
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
	 * 通过appid获取token
	 * @param array $aInput [in]参数 ['app_id', 'app_secret']
	 * @param array $aOutput [out]输出['access_token', 'expiry_time']
	 * @return boolean
	 */
	public function getAccessTokenByAppid(array $aInput, array &$aOutput = [])
	{
		if (!$this->_checkFields($aInput, ['app_id', 'app_secret'], [], true))
		{
			return false;
		}
		$strAppid = $aInput['app_id'];
		$oSiteAccessTokenModel = D('SiteAccessToken');
		$aMap = [
			'app_id' => $strAppid,
			'state' => \WxappConf::MP_ACCESS_TOKEN_NORMAL,
		];
		if (false === $aAccessTokenList = $oSiteAccessTokenModel->selectByLocator($aMap, ['access_token', 'expiry_time']))
		{
			$this->setError($oSiteAccessTokenModel);
			return false;
		}
		if (empty($aAccessTokenList) || $aAccessTokenList[0]['expiry_time'] < date('Y-m-d H:i:s', time()))
		{
			$aRefreshResult = [];
			if (!$this->refreshAccessToken($aInput, $aRefreshResult))
			{
				return false;
			}
			$aOutput = $aRefreshResult;
		}
		else
		{
			$aOutput = $aAccessTokenList[0];
		}
		return true;
	}

	/**
	 * 获取微信通讯access token，过期则刷新
	 * @param array $aInput [in]参数['uuid']
	 * @param array $aOutput [out]输出['access_token', 'expiry_time']
	 * @return boolean
	 */
	public function getAccessToken(array $aInput, array &$aOutput = [])
	{
		if (!$this->_checkAppid($aInput))
		{
			return false;
		}
		$oSiteAccessTokenModel = D('SiteAccessToken');
		$aMap = [
			'app_id' => $this->_aSiteBaseInfo['app_id'],
			'state' => \WxappConf::MP_ACCESS_TOKEN_NORMAL,
		];
		if (false === $aAccessTokenList = $oSiteAccessTokenModel->selectByLocator($aMap, ['access_token', 'expiry_time', 'app_id']))
		{
			$this->setError($oSiteAccessTokenModel);
			return false;
		}

		$nAvailable = $this->isTokenAvailable($aAccessTokenList[0], 600);
		if (false === $nAvailable)
		{
			return false;
		}
		if (0 === $nAvailable)
		{
			$aRefreshResult = [];
			if (!$this->refreshAccessToken($this->_aSiteBaseInfo, $aRefreshResult))
			{
				return false;
			}
			$aOutput = $aRefreshResult;
		}
		else
		{
			$aOutput = $aAccessTokenList[0];
		}
		return true;
	}

	/**
	 * 检验数据的真实性，并且获取解密后的明文.
	 * @param $sessionKey string 用户在小程序登录后获取的会话密钥
	 * @param $encryptedData string 加密的用户数据
	 * @param $iv string 与用户数据一同返回的初始向量
	 * @return boolean|array
	 */
	protected function decryptData($appid, $sessionKey, $encryptedData, $iv)
	{
		if (strlen($sessionKey) != 24)
		{
			$this->setError('encodingAesKey 非法！');
			return false;
		}
		$aesKey = base64_decode($sessionKey);

		if (strlen($iv) != 24)
		{
			$this->setError('初始向量非法！');
			return false;
		}
		$aesIV = base64_decode($iv);

		$aesCipher = base64_decode($encryptedData);

		$result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

		$aData = json_decode($result, true);
		if (empty($aData) || $aData['watermark']['appid'] != $appid)
		{
			$aInData = [
				'appid' => $appid,
				'session_key' => $sessionKey,
				'encrypt_data' => $encryptedData,
				'iv' => $iv,
				'decrypt_data' => $aData,
			];
			$this->setError('解密后得到的数据非法！解密数据：' . print_r($aInData, true));
			return false;
		}
		return $aData;
	}

	/**
	 * 微信小程序应用信息
	 * @param int $nSiteId 微信小程序应用ID
	 * @return array 应用信息
	 */
	protected function _getSiteInfo($nSiteId)
	{
		$oModel = D('Site');
		$aLocator = [
			's.id' => $nSiteId
		];
		$aField = [
			's.*',
			'at.access_token', 'at.expiry_time', 'at.state',
		];
		$aJoin = [
			['site_access_token', 'at.app_id=s.app_id', 'at']
		];

		if (false === $aData = $oModel->getList($aLocator, [], $aField, $aJoin, 's', 1))
		{
			$this->setError($oModel);
			return false;
		}
		/**
		 * 新增或刷新token,开发阶段使用,正式环境需要用独立的一个进程来专门刷新token或新建token
		 */
		$aOutput = [];
		if (empty($aData[0]['access_token']) || $aData[0]['expiry_time'] < date('Y-m-d H:i:s', time()))
		{
			if (!$this->refreshAccessToken($aData[0], $aOutput))
			{
				return false;
			}
			$aData[0]['state'] = 1;
			$aData[0]['access_token'] = $aOutput['access_token'];
			$aData[0]['expiry_time'] = $aOutput['expiry_time'];
		}
		//
		if (\WxappConf::MP_ACCESS_TOKEN_NORMAL != $aData[0]['state'] ||
		$aData[0]['expiry_time'] < date('Y-m-d H:i:s', time()))
		{
			$this->setError('微信小程序API TOKEN不可用,稍后再试');
			return false;
		}
		return $aData[0];
	}

	/**
	 * 校验一张图片是否含有违法违规内容
	 * @param array $aInput [in]输入参数
	 * <pre>
	 * uuid 当前应用uuid
	 * media 需要检测的图片本地路径
	 * 要检测的图片文件，格式支持PNG、JPEG、JPG、GIF，图片尺寸不超过 750px * 1334px
	 * </pre>
	 * @return integer|boolean 返回1：表示内容有违规问题，返回0：表示正常，返回false表示检测出错
	 */
	public function imageSecurityCheck(array $aInput)
	{
		if (!$this->_checkFields($aInput, ['uuid', 'media'], [], true))
		{
			return false;
		}

		$aTokenInfo = [];
		if (!$this->getAccessToken($aInput, $aTokenInfo))
		{
			return false;
		}

		$aParam = [
			'access_token' => $aTokenInfo['access_token'],
			'media' => $aInput['media'],
		];

		if (!$this->_doCommand(Command::IMAGE_SECURITY_CHECK, $aParam, '', 'file'))
		{
			//不合规返回1并删除
			if (\ErrorConf::ERROR_WEIXIN_CONTENT_SECURITY_RISKY == $this->getErrorNo())
			{
				$this->setPreDeleteFilePath($aParam['media']);
				return 1;
			}
			return false;
		}

		return 0;
	}

	/**
	 * 检查一段文本是否含有违法违规内容
	 * @param array $aInput [in]输入参数
	 * <pre>
	 * uuid 当前应用uuid
	 * content 要检测的文本内容，长度不超过 500KB
	 * </pre>
	 * @return integer|boolean 返回1：表示内容有违规问题，返回0：表示正常，返回false表示检测出错
	 */
	public function messageSecurityCheck(array $aInput)
	{
		if (!$this->_checkFields($aInput, ['uuid', 'content'], [], true))
		{
			return false;
		}

		if (strlen($aInput['content']) > 512000)
		{
			$this->setError('文本内容过长！');
			return false;
		}

		$aTokenInfo = [];
		if (!$this->getAccessToken($aInput, $aTokenInfo))
		{
			return false;
		}

		$aParam = [
			'access_token' => $aTokenInfo['access_token'],
			'content' => $aInput['content'],
		];

		if (!$this->_doCommand(Command::MESSAGE_SECURITY_CHECK, $aParam, 'json', 'post'))
		{
			if (\ErrorConf::ERROR_WEIXIN_CONTENT_SECURITY_RISKY == $this->getErrorNo())
			{
				return 1;
			}
			return false;
		}

		return 0;
	}
}
