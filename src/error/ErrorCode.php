<?php

namespace lazymanso\wechat;

/**
 * 微信接口错误代码与错误信息配置类
 */
class ErrorCode
{
	/** access token 错误 */
	const INVALID_ACCESS_TOKEN = 40001; //
	/** 不合法或已过期的 code */
	const INVALID_CODE = 40029; //
	/** template id 不正确或已删除 */
	const TEMPLATE_MESSAGE_ID = 40037; //
	/** 无效的 code */
	const INVALID_SERIAL_CODE = 40056; //
	/** invalid card id */
	const INVALID_CARDID = 40073; //
	/** invalid dateinfo */
	const INVALID_DATEINFO = 40100; //
	/** invalid appsecret */
	const INVALID_APPSECRET = 40125; //
	/** form id 不正确，或者过期 */
	const FORM_ID = 41028; //
	/** form id 已被使用 */
	const FORM_ID_INVALID = 41029; //
	/** 模板消息设置的page不正确 */
	const INVALID_TPLMSG_PAGE = 41030; //
	/** access token 过期 */
	const EXPIRED_ACCESS_TOKEN = 42001; //
	/** 请使用GET方式请求微信接口 */
	const REQUIRE_GET_METHOD = 43001; //
	/** 请使用POST方式请求微信接口 */
	const REQUIRE_POST_METHOD = 43002; //
	/** 请求微信接口的数据格式错误 */
	const REQUEST_DATA_FORMAT = 47001; //
	/** 功能未授权，请确认小程序已获取该接口 */
	const UNAUTHORIZED = 48001; //
	/** 第三方未被授权 */
	const COMPONENT_NOT_AUTHORIZED = 61003; //
	/** IP未注册白名单 */
	const IP_NOTIN_WHITELIST = 61004; //
	/** 标签格式错误 */
	const AUDIT_CODE_VERSION_TAG_FORMAT = 85006; //
	/** 页面路径错误 */
	const AUDIT_CODE_VERSION_PAGE_PATH = 85007; //
	/** 类目填写错误 */
	const AUDIT_CODE_VERSION_CATEGORY = 85008; //
	/** 已经有正在审核的版本 */
	const AUDIT_CODE_VERSION_ALREADY_EXIST = 85009; //
	/** item_list有项目为空 */
	const AUDIT_CODE_VERSION_ITEMLIST_INCOMPLETE = 85010; //
	/** 标题填写错误 */
	const AUDIT_CODE_VERSION_TITLE = 85011; //
	/** 没有审核版本 */
	const AUDIT_CODE_VERSION_NOT_EXIST = 85019; //
	/** 审核状态未满足发布 */
	const AUDIT_CODE_VERSION_CANNOT_RELEASE = 85020; //
	/** 小程序类目信息失效（类目中含有官方下架的类目，请重新选择类目） */
	const AUDIT_CODE_VERSION_CATEGORY_INVALID = 85077; //
	/** 近7天提交审核的小程序数量过多，请耐心等待审核完毕后再次提交 */
	const AUDIT_CODE_VERSION_TOO_MANY = 85085; //
	/** 提交代码审核之前需提前上传代码 */
	const AUDIT_CODE_VERSION_NOT_UPLOAD_CODE = 85086; //
	/** 小程序还未设置昵称、头像、简介。请先设置完后再重新提交。 */
	const AUDIT_CODE_VERSION_BASEINFO_INCOMPLETE = 86002; //
	/** 现网已经在灰度发布，不能进行版本回退 */
	const GRAY_RELEASE_FORBID_REVERT = 87011; //
	/** 内容含有违法违规内容 */
	const CONTENT_SECURITY_RISKY = 87014; //
	/** 该公众号/小程序已经绑定了开放平台帐号 */
	const OPEN_ACCOUNT_HASBOUND_OPEN = 89000; //
	/** 授权方与开放平台帐号主体不相同 */
	const OPEN_NOT_SAME_CONTRACTOR = 89001; //
	/** 该公众号/小程序未绑定微信开放平台帐号 */
	const OPEN_NOT_EXISTS = 89002; //
	/** 该开放平台帐号并非通过api创建，不允许操作 */
	const OPEN_NOT_CREATE_BY_API = 89003; //
	/** 该开放平台帐号所绑定的公众号/小程序已达上限(100个) */
	const OPEN_REACHED_THE_LIMIT = 89004; //
	/** 个人小程序不支持设置业务域名 */
	const PERSON_CANNTO_SET_WEBVIEW_DOMAIN = 89231; //
	/** 签名验证错误 */
	const VALIDATE_SIGNATURE = -40001; //
	/** xml解析失败 */
	const PARSE_XML = -40002; //
	/** sha加密生成签名失败 */
	const COMPUTE_SIGNATURE = -40003; //
	/** encodingAesKey 非法IllegalAesKey */
	const ILLEGAL_AESKEY = -40004; //
	/** appid 校验错误 ValidateAppidError */
	const VALIDATE_APPID = -40005; //
	/** aes 加密失败 EncryptAESError */
	const ENCRYPT_AES = -40006; //
	/** aes 解密失败 DecryptAESError */
	const DECRYPT_AES = -40007; //
	/** 解密后得到的buffer非法 IllegalBuffer */
	const ILLEGAL_BUFFER = -40008; //
	/** base64加密失败 EncodeBase64Error */
	const ENCODE_BASE64 = -40009; //
	/** base64解密失败 DecodeBase64Error */
	const DECODE_BASE64 = -40010; //
	/** 生成xml失败 GenReturnXmlError */
	const GEN_RETURNXML = -40011; //

	/* 微信API接口错误号 *///
	/**
	 * 错误信息映射
	 * @static
	 * @access private
	 * @var array
	 */
	private static $_aErrorMsgMap = array(
		self::INVALID_ACCESS_TOKEN => '无效的 access token',
		self::INVALID_CODE => '不合法或已过期的 code',
		self::INVALID_SERIAL_CODE => '无效的 code 码',
		self::INVALID_CARDID => 'invalid card id',
		self::INVALID_DATEINFO => '时间信息设置错误',
		self::INVALID_APPSECRET => 'invalid appsecret',
		self::EXPIRED_ACCESS_TOKEN => 'access token 过期',
		self::REQUIRE_GET_METHOD => '请使用 GET 方式请求微信接口',
		self::REQUIRE_POST_METHOD => '请使用 POST 方式请求微信接口',
		self::REQUEST_DATA_FORMAT => '请求微信接口的数据格式错误',
		self::FORM_ID => 'form id 错误，或者过期',
		self::FORM_ID_INVALID => 'form id 已被使用',
		self::TEMPLATE_MESSAGE_ID => 'template id 错误或已删除',
		self::INVALID_TPLMSG_PAGE => 'page 路径错误或小程序未发布',
		self::UNAUTHORIZED => '功能未授权，请确认小程序已获取该接口',
		self::COMPONENT_NOT_AUTHORIZED => '第三方未被授权',
		self::IP_NOTIN_WHITELIST => 'IP未注册白名单',
		self::AUDIT_CODE_VERSION_TAG_FORMAT => '标签格式错误',
		self::AUDIT_CODE_VERSION_PAGE_PATH => '页面路径错误',
		self::AUDIT_CODE_VERSION_CATEGORY => '类目填写错误',
		self::AUDIT_CODE_VERSION_ALREADY_EXIST => '有已经正在审核的版本',
		self::AUDIT_CODE_VERSION_ITEMLIST_INCOMPLETE => '提交审核项目未填写完整',
		self::AUDIT_CODE_VERSION_TITLE => '标题填写错误',
		self::AUDIT_CODE_VERSION_NOT_EXIST => '小程序代码没有审核版本',
		self::AUDIT_CODE_VERSION_CANNOT_RELEASE => '小程序代码审核状态未满足发布',
		self::AUDIT_CODE_VERSION_CATEGORY_INVALID => '小程序类目失效，请重新选择',
		self::AUDIT_CODE_VERSION_TOO_MANY => '近7天提交审核的小程序数量过多，请耐心等待审核完毕后再次提交',
		self::AUDIT_CODE_VERSION_NOT_UPLOAD_CODE => '未检测到代码，请重新上传代码后再试',
		self::AUDIT_CODE_VERSION_BASEINFO_INCOMPLETE => '小程序还未设置昵称、头像、简介，请设置后再重新提交',
		self::GRAY_RELEASE_FORBID_REVERT => '现网已经在灰度发布，不能进行版本回退',
		self::CONTENT_SECURITY_RISKY => '内容含有违法违规内容',
		self::OPEN_ACCOUNT_HASBOUND_OPEN => '该公众号/小程序已经绑定了开放平台帐号',
		self::OPEN_NOT_SAME_CONTRACTOR => '授权方与开放平台帐号主体不相同',
		self::OPEN_NOT_EXISTS => '该公众号/小程序未绑定微信开放平台帐号',
		self::OPEN_NOT_CREATE_BY_API => '该开放平台帐号并非通过api创建，不允许操作',
		self::OPEN_REACHED_THE_LIMIT => '该开放平台帐号所绑定的公众号/小程序已达上限(100个)',
		self::PERSON_CANNTO_SET_WEBVIEW_DOMAIN => '个人小程序不支持设置业务域名',
		//微信加解密
		self::VALIDATE_SIGNATURE => '签名验证错误',
		self::PARSE_XML => 'xml 解析失败',
		self::COMPUTE_SIGNATURE => 'sha 加密生成签名失败',
		self::ILLEGAL_AESKEY => 'encodingAesKey 非法',
		self::VALIDATE_APPID => 'appid 校验错误',
		self::ENCRYPT_AES => 'aes 加密失败',
		self::DECRYPT_AES => 'aes 解密失败',
		self::ILLEGAL_BUFFER => '解密后得到的 buffer 非法',
		self::ENCODE_BASE64 => 'base64 加密失败',
		self::DECODE_BASE64 => 'base64 解密失败',
		self::GEN_RETURNXML => '生成 xml 失败',
	);

	/**
	 * 获取错误信息
	 * @static
	 * @access public
	 * @param int $nErrorCode [in]错误码
	 * @return string 错误信息
	 */
	public static function getError($nErrorCode)
	{
		return isset(self::$_aErrorMsgMap[$nErrorCode]) ? self::$_aErrorMsgMap[$nErrorCode] : '未知错误：' . $nErrorCode;
	}
}
