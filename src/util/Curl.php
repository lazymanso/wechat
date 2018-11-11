<?php

namespace lazymanso\wechat\util;

use lazymanso\wechat\Common;

/**
 * php curl
 */
class Curl extends Common
{
	/**
	 * 超时时间
	 * @var int
	 */
	protected $_nTimeout = 60;

	/**
	 * 证书key文件名
	 */
	const KEYNAME = 'apiclient_key.pem';

	/**
	 * 证书cert文件名
	 */
	const CERTNAME = 'apiclient_cert.pem';

	/**
	 * 证书密钥文件路径
	 * @var string
	 */
	private $_strCertPath = '';

	/**
	 * 证书文件路径
	 * @var string
	 */
	private $_strKeyPath = '';

	/**
	 * curl 句柄
	 * @var resource
	 */
	private static $_oCurl = null;

	/**
	 * 是否使用证书
	 * @var boolean
	 */
	private $_bLoadCert = false;

	/**
	 * 构造函数扩展
	 * @access protected
	 * @return void
	 */
	public function __construct()
	{
		$this->_initCurl();
	}

	/**
	 * 初始化 curl
	 */
	private function _initCurl()
	{
		if (is_null(self::$_oCurl))
		{
			self::$_oCurl = curl_init();
			curl_setopt(self::$_oCurl, CURLOPT_HEADER, 0);
			curl_setopt(self::$_oCurl, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt(self::$_oCurl, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt(self::$_oCurl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt(self::$_oCurl, CURLOPT_TIMEOUT, $this->_nTimeout);
			curl_setopt(self::$_oCurl, CURLOPT_ENCODING, 'gzip');
		}
	}

	/**
	 * 执行curl
	 * @param mixed $result [out]执行返回结果
	 * @return boolean
	 */
	private function _execCurl(&$result = '')
	{
		$result = curl_exec(self::$_oCurl);
		$aHttpStatusInfo = curl_getinfo(self::$_oCurl); //返回状态码
		$msg = null;
		if ($result === false)
		{
			$msg = curl_error(self::$_oCurl);
		}
		if (200 != $aHttpStatusInfo['http_code'] || !is_null($msg))
		{
			if ($this->_bLoadCert)
			{
				unlink($this->_strCertPath);
				unlink($this->_strKeyPath);
			}
			$this->setError($msg);
			return false;
		}
		return true;
	}

	/**
	 * Curl Get方式请求
	 * @param string $url 请求地址
	 * @param mixed $result 返回结果
	 * @return boolean
	 */
	public function get($url, &$result = '')
	{
		curl_setopt(self::$_oCurl, CURLOPT_URL, $url);
		curl_setopt(self::$_oCurl, CURLOPT_POST, 0);
		return $this->_execCurl($result);
	}

	/**
	 * Curl Post方式请求
	 * @param string $url 请求链接
	 * @param mix $param post参数
	 * @param mixed $result 返回结果
	 * @param boolean $useCert 是否使用证书
	 * @return boolean
	 */
	public function post($url, $param, &$result = '', $useCert = false)
	{
		curl_setopt(self::$_oCurl, CURLOPT_URL, $url);
		curl_setopt(self::$_oCurl, CURLOPT_POST, 1);
		curl_setopt(self::$_oCurl, CURLOPT_POSTFIELDS, $param);
		//证书
		$this->_bLoadCert = $useCert;
		if ($useCert)
		{
			if (!$this->_getPemByUuid())
			{
				return false;
			}
			curl_setopt(self::$_oCurl, CURLOPT_SSLCERTTYPE, 'PEM');
			curl_setopt(self::$_oCurl, CURLOPT_SSLCERT, $this->_strCertPath);
			curl_setopt(self::$_oCurl, CURLOPT_SSLKEYTYPE, 'PEM');
			curl_setopt(self::$_oCurl, CURLOPT_SSLKEY, $this->_strKeyPath);
		}
		return $this->_execCurl($result);
	}

	/**
	 * Curl 上传文件请求
	 * @param string $url [in]请求链接
	 * @param array $param [in]post参数
	 * @param mixed $result [out]返回结果
	 * @return boolean
	 */
	public function file($url, $param, &$result = '')
	{
		curl_setopt(self::$_oCurl, CURLOPT_URL, $url);
		curl_setopt(self::$_oCurl, CURLOPT_SAFE_UPLOAD, true);
		curl_setopt(self::$_oCurl, CURLOPT_POST, 1);

		$bHasBuffer = false;
		$bHasFile = false;
		foreach ($param as $key => $value)
		{
			if (is_file($value))
			{
				$strFileKey = $key;
				$strTempPath = $value;
				$bHasFile = true;
				break;
			}
			elseif (is_string($value) && fn_IsBinary($value) && fn_IsFileBuffer($value))
			{
				$strFileKey = $key;
				$strBufferValue = $value;
				$bHasBuffer = true;
				break;
			}
		}

		if (!$bHasFile && !$bHasBuffer)
		{
			$this->setError('请输入上传文件的二进制流或文件路径！');
			return false;
		}

		$oFinfo = new \finfo(FILEINFO_MIME_TYPE);
		//如果有本地文件传进来
		if ($bHasFile)
		{
			$strFileType = $oFinfo->file($strTempPath);
		}
		else
		{
			$strFileType = $oFinfo->buffer($strBufferValue);
			switch ($strFileType)
			{
				case 'image/jpeg':
					$strFileExt = '.jpg';
					break;
				case 'image/png':
					$strFileExt = '.png';
					break;
			}
			$strTempPath = UPLOAD_PATH . $this->_strUuid . '/' . genUniqueSn() . $strFileExt;
			if (false === file_put_contents($strTempPath, $strBufferValue))
			{
				$this->setError('创建临时文件失败！');
				return false;
			}
			$this->setPreDeleteFilePath($strTempPath);
		}

		$param[$strFileKey] = new \CURLFile(realpath($strTempPath), $strFileType);
		curl_setopt(self::$_oCurl, CURLOPT_POSTFIELDS, $param);
		return $this->_execCurl($result);
	}

	/**
	 * 设置超时时间
	 * @param int $nTime [in]时间，秒
	 */
	public function setTimeout($nTime)
	{
		$this->_nTimeout = $nTime;
	}

	/**
	 * 析构
	 * @return void
	 */
	public function __destruct()
	{
		//关闭句柄
		curl_close(self::$_oCurl);
	}
}
