<?php

namespace lazymanso\wechat\miniprogram;

/**
 * 微信小程序接口命令到api url配置类
 */
class Command
{
	const BASEAPIURI = 'https://api.weixin.qq.com/';
	const MINIPROGRAM_PAYAPIURI = 'https://api.mch.weixin.qq.com/';
	/* ====================================================================== */
	//基础类API列表
	/** 开发者服务器使用登录凭证 code 获取 session_key 和 openid */
	const BASE_JSCODE_TO_SESSION = 1001000; //
	/** 获取微信通讯token */
	const BASE_ACCESS_TOKEN = 1001001; //
	/** 上传图标至微信服务器 */
	const BASE_MEDIA_UPLOADIMG = 1001002; //
	/** 获取api_ticket */
	const BASE_GET_TICKET = 1001003; //

	/* ====================================================================== */
	//微信支付API列表
	/** 统一下单 */
	const PAY_UNIFIED_ORDER = 1002000; //
	/** 查询订单 */
	const PAY_QUERY_ORDER = 1002001; //
	/** 关闭订单 */
	const PAY_CLOSE_ORDER = 1002002; //
	/** 申请退款 */
	const PAY_REFUND_ORDER = 1002003; //
	/** 查询退款 */
	const PAY_QUERY_REFUND_ORDER = 1002004; //
	/** 下载对账单 */
	const PAY_DOWNLOAD_BILL = 1002005; //
	/** 拉取订单评价数据 */
	const PAY_QUERUY_ORDER_COMMENT = 1002006; //

	/* ====================================================================== */
	//小程序模板消息接口
	/** 获取小程序模板库标题列表 */
	const TPL_LIB_LIST = 1003000; //
	/** 获取模板库某个模板标题下关键词库 */
	const TPL_LIB_KEYWORD_LIST = 1003001; //
	/** 组合模板并添加至帐号下的个人模板库 */
	const TPL_ADD_PRIVATE = 1003002; //
	/** 获取帐号下已存在的模板列表 */
	const TPL_LIST_PRIVATE = 1003003; //
	/** 删除帐号下的某个模板 */
	const TPL_DEL_PRIVATE = 1003004; //
	/** 发送模版消息 */
	const TPL_SEND_MESSAGE = 1003005; //

	/* ====================================================================== */
	//卡劵接口
	/** 创建卡劵 */
	const CARD_CREATE = 1004000; //
	/** 卡劵生成二维码 */
	const CARD_CREATE_QRCODE = 1004001; //
	/** 删除卡卷 */
	const CARD_COUPON_DELETE = 1004002; //
	/** 查看卡券详情 */
	const CARD_GET_DETAIL = 1004003; //
	/** Code解码接口 */
	const CARD_CODE_DECRYPT = 1004004; //
	/** 查询Code接口 */
	const CARD_CODE_DETAIL = 1004005; //
	/** 核销Code接口 */
	const CARD_CODE_CONSUME = 1004006; //
	/** 更改卡券信息接口 */
	const CARD_UPDATE = 1004007; //
	/** 修改库存接口 */
	const CARD_MODIFY_STOCK = 1004008; //
	/** 删除卡券接口 */
	const CARD_DELETE = 1004009; //
	/** 设置卡券失效接口 */
	const CARD_CODE_UNAVAILABLE = 1004010; //
	/** 支付即会员规则添加 */
	const CARD_PAYGIFT_ADD = 1004011; //
	/** 支付即会员规则删除 */
	const CARD_PAYGIFT_DELETE = 1004012; //
	/** 查询会员信息,包括激活资料、积分信息以及余额等信息 */
	const CARD_QUERY_USERINFO = 1004013; //
	/** 更新会员信息,包括激活资料、积分信息以及余额等信息 */
	const CARD_UPDATE_USERINFO = 1004014; //

	/* ====================================================================== */
	//小程序获取二维码
	/** 获取小程序码（永久有效，有数量限制） */
	const QRCODE_GET_WXACODE = 1005000; //
	/** 获取小程序码（适用于需要的码数量极多，或仅临时使用的业务场） */
	const QRCODE_GET_WXACODE_UNLIMIT = 1005001; //
	/** 获取小程序二维码（适用于需要的码数量较少的业务场景） */
	const QRCODE_GET_WXAQRCODE = 1005002; //

	/* ====================================================================== */
	//内容安全
	/** 校验一张图片是否含有违法违规内容 */
	const IMAGE_SECURITY_CHECK = 1006000; //
	/** 检查一段文本是否含有违法违规内容 */
	const MESSAGE_SECURITY_CHECK = 1006001; //

	/* ====================================================================== */
	//微信开放平台-第三方平台接口
	/** 获取第三方平台component_access_token */
	const COMPONENT_ACCESS_TOKEN = 500; //
	/** 获取预授权码pre_auth_code */
	const COMPONENT_PRE_AUTH_CODE = 501; //
	/** 使用授权码换取公众号或小程序的接口调用凭据和授权信息 */
	const COMPONENT_API_QUERY_AUTH = 502; //
	/** 获取（刷新）授权公众号或小程序的接口调用凭据（令牌） */
	const COMPONENT_API_AUTHORIZER_TOKEN = 503; //
	/** 获取授权方的帐号基本信息 */
	const COMPONENT_API_GET_AUTHORIZER_INFO = 504; //
	/** 获取授权方的选项设置信息 */
	const COMPONENT_API_GET_AUTHORIZER_OPTION = 505; //
	/** 设置授权方的选项信息 */
	const COMPONENT_API_SET_AUTHORIZER_OPTION = 506; //
	/** 开发者服务器使用登录凭证 code 获取 session_key 和 openid */
	const COMPONENT_JSCODE_TO_SESSION = 507; //

	/** 开放平台 *///
	/** 创建开放平台帐号并绑定公众号/小程序 */
	const OPEN_CREATE_ACCOUNT = 600; //
	/** 获取公众号/小程序所绑定的开放平台帐号 */
	const OPEN_GET_BIND_ACCOUNT = 601; //
	/** 将公众号/小程序绑定到开放平台帐号下 */
	const OPEN_BIND_ACCOUNT = 602; //
	/** 将公众号/小程序从开放平台帐号下解绑 */
	const OPEN_UNBIND_ACCOUNT = 603; //

	/** 代码管理 *///
	/** 为授权的小程序帐号上传小程序代码 */
	const CODE_CONTROL_COMMIT = 700; //
	/** 获取体验小程序的体验二维码 */
	const CODE_CONTROL_GET_QRCODE = 701; //
	/** 获取授权小程序帐号的可选类目 */
	const CODE_CONTROL_GET_CATEGORY = 702; //
	/** 获取小程序的第三方提交代码的页面配置 */
	const CODE_CONTROL_GET_PAGE = 703; //
	/** 将第三方提交的代码包提交审核 */
	const CODE_CONTROL_SUBMIT_AUDIT = 704; //
	/** 查询某个指定版本的审核状态 */
	const CODE_CONTROL_GET_AUDIT_STATUS = 705; //
	/** 查询最新一次提交的审核状态 */
	const CODE_CONTROL_GET_LATEST_AUDIT_STATUS = 706; //
	/** 发布已通过审核的小程序 */
	const CODE_CONTROL_RELEASE = 707; //
	/** 获取草稿箱内的所有临时代码草稿 */
	const CODE_CONTROL_GET_DRAFT_LIST = 708; //
	/** 获取代码模版库中的所有小程序代码模版 */
	const CODE_CONTROL_GET_TEMPLATE_LIST = 709; //
	/** 将草稿箱的草稿选为小程序代码模版 */
	const CODE_CONTROL_ADD_TEMPLATE = 710; //
	/** 删除指定小程序代码模版 */
	const CODE_CONTROL_DELETE_TEMPLATE = 711; //
	/** 小程序审核撤回 */
	const CODE_CONTROL_UNDO_AUDIT = 712; //
	/** 修改小程序线上代码的可见状态 */
	const CODE_CONTROL_CHANGE_VISIT_STATUS = 713; //
	/** 小程序版本回退 */
	const CODE_CONTROL_REVERT_RELEASE = 714; //

	/** 设置 *///
	/** 设置小程序服务器域名 */
	const SETTING_MODIFY_DOMAIN = 800; //
	/** 设置小程序业务域名 */
	const SETTING_SET_WEBVIEW_DOMAIN = 801; //
	/** 绑定微信用户为小程序体验者 */
	const SETTING_BIND_TESTER = 802; //
	/** 解绑体验者 */
	const SETTING_UNBIND_TESTER = 803; //

	/**
	 * 数据对象字段的类型显示映射
	 * @static
	 * @access private
	 * @var array
	 */
	private static $_aMap = [
		/** 基础类 */
		self::BASE_JSCODE_TO_SESSION => [self::BASEAPIURI . 'sns/jscode2session', ['appid', 'secret', 'js_code', 'grant_type']],
		self::BASE_ACCESS_TOKEN => [self::BASEAPIURI . 'cgi-bin/token', ['appid', 'secret', 'grant_type']],
		self::BASE_MEDIA_UPLOADIMG => [self::BASEAPIURI . 'cgi-bin/media/uploadimg', ['access_token']],
		self::BASE_GET_TICKET => [self::BASEAPIURI . 'cgi-bin/ticket/getticket', ['access_token', 'type']],
		/** 小程序支付接口 */
		self::PAY_UNIFIED_ORDER => ['post', self::MINIPROGRAM_PAYAPIURI . 'pay/unifiedorder'],
		self::PAY_QUERY_ORDER => ['post', self::MINIPROGRAM_PAYAPIURI . 'pay/orderquery'],
		self::PAY_CLOSE_ORDER => ['post', self::MINIPROGRAM_PAYAPIURI . 'pay/closeorder'],
		self::PAY_REFUND_ORDER => ['post', self::MINIPROGRAM_PAYAPIURI . 'secapi/pay/refund'],
		self::PAY_QUERY_REFUND_ORDER => ['post', self::MINIPROGRAM_PAYAPIURI . 'pay/refundquery'],
		self::PAY_DOWNLOAD_BILL => ['post', self::MINIPROGRAM_PAYAPIURI . 'pay/downloadbill'],
		self::PAY_QUERUY_ORDER_COMMENT => ['post', self::MINIPROGRAM_PAYAPIURI . 'billcommentsp/batchquerycomment'],
		/** 小程序模版消息 */
		self::TPL_LIB_LIST => [self::BASEAPIURI . 'cgi-bin/wxopen/template/library/list', ['access_token']],
		self::TPL_LIB_KEYWORD_LIST => [self::BASEAPIURI . 'cgi-bin/wxopen/template/library/get', ['access_token']],
		self::TPL_ADD_PRIVATE => [self::BASEAPIURI . 'cgi-bin/wxopen/template/add', ['access_token']],
		self::TPL_LIST_PRIVATE => [self::BASEAPIURI . 'cgi-bin/wxopen/template/list', ['access_token']],
		self::TPL_DEL_PRIVATE => [self::BASEAPIURI . 'cgi-bin/wxopen/template/del', ['access_token']],
		self::TPL_SEND_MESSAGE => [self::BASEAPIURI . 'cgi-bin/message/wxopen/template/send', ['access_token']],
		/** 卡劵接口 */
		self::CARD_CREATE => [self::BASEAPIURI . 'card/create', ['access_token']],
		self::CARD_CREATE_QRCODE => [self::BASEAPIURI . 'card/qrcode/create', ['access_token']],
		self::CARD_COUPON_DELETE => [self::BASEAPIURI . 'card/delete', ['access_token']],
		self::CARD_GET_DETAIL => [self::BASEAPIURI . 'card/get', ['access_token']],
		self::CARD_CODE_DECRYPT => [self::BASEAPIURI . 'card/code/decrypt', ['access_token']],
		self::CARD_CODE_DETAIL => [self::BASEAPIURI . 'card/code/get', ['access_token']],
		self::CARD_CODE_CONSUME => [self::BASEAPIURI . 'card/code/consume', ['access_token']],
		self::CARD_UPDATE => [self::BASEAPIURI . 'card/update', ['access_token']],
		self::CARD_MODIFY_STOCK => [self::BASEAPIURI . 'card/modifystock', ['access_token']],
		self::CARD_DELETE => [self::BASEAPIURI . 'card/delete', ['access_token']],
		self::CARD_CODE_UNAVAILABLE => [self::BASEAPIURI . 'card/code/unavailable', ['access_token']],
		self::CARD_PAYGIFT_ADD => [self::BASEAPIURI . 'card/paygiftcard/add', ['access_token']],
		self::CARD_PAYGIFT_DELETE => [self::BASEAPIURI . 'card/paygiftcard/delete', ['access_token']],
		self::CARD_QUERY_USERINFO => [self::BASEAPIURI . 'card/membercard/userinfo/get', ['access_token']],
		self::CARD_UPDATE_USERINFO => [self::BASEAPIURI . 'card/membercard/updateuser', ['access_token']],
		/** 小程序获取二维码 */
		self::QRCODE_GET_WXACODE => [self::BASEAPIURI . 'wxa/getwxacode', ['access_token']],
		self::QRCODE_GET_WXACODE_UNLIMIT => [self::BASEAPIURI . 'wxa/getwxacodeunlimit', ['access_token']],
		self::QRCODE_GET_WXAQRCODE => [self::BASEAPIURI . 'cgi-bin/wxaapp/createwxaqrcode', ['access_token']],
		/** 内容安全接口 */
		self::IMAGE_SECURITY_CHECK => [self::BASEAPIURI . 'wxa/img_sec_check', ['access_token']],
		self::MESSAGE_SECURITY_CHECK => [self::BASEAPIURI . 'wxa/msg_sec_check', ['access_token']],
		/** 微信第三方平台接口 */
		self::COMPONENT_ACCESS_TOKEN => [self::BASEAPIURI . 'cgi-bin/component/api_component_token'],
		self::COMPONENT_PRE_AUTH_CODE => [self::BASEAPIURI . 'cgi-bin/component/api_create_preauthcode', ['component_access_token']],
		self::COMPONENT_API_QUERY_AUTH => [self::BASEAPIURI . 'cgi-bin/component/api_query_auth', ['component_access_token']],
		self::COMPONENT_API_AUTHORIZER_TOKEN => [self::BASEAPIURI . 'cgi-bin/component/api_authorizer_token', ['component_access_token']],
		self::COMPONENT_API_GET_AUTHORIZER_INFO => [self::BASEAPIURI . 'cgi-bin/component/api_get_authorizer_info', ['component_access_token']],
		self::COMPONENT_API_GET_AUTHORIZER_OPTION => [self::BASEAPIURI . 'cgi-bin/component/api_get_authorizer_option', ['component_access_token']],
		self::COMPONENT_API_SET_AUTHORIZER_OPTION => [self::BASEAPIURI . 'cgi-bin/component/api_set_authorizer_option', ['component_access_token']],
		self::COMPONENT_JSCODE_TO_SESSION => [self::BASEAPIURI . 'sns/component/jscode2session', ['appid', 'js_code', 'grant_type', 'component_appid', 'component_access_token']],
		/** 微信开放平台绑定/解绑接口 */
		self::OPEN_CREATE_ACCOUNT => [self::BASEAPIURI . 'cgi-bin/open/create', ['access_token']],
		self::OPEN_GET_BIND_ACCOUNT => [self::BASEAPIURI . 'cgi-bin/open/get', ['access_token']],
		self::OPEN_BIND_ACCOUNT => [self::BASEAPIURI . 'cgi-bin/open/bind', ['access_token']],
		self::OPEN_UNBIND_ACCOUNT => [self::BASEAPIURI . 'cgi-bin/open/unbind', ['access_token']],
		/** 代码管理 */
		self::CODE_CONTROL_COMMIT => [self::BASEAPIURI . 'wxa/commit', ['access_token']],
		self::CODE_CONTROL_GET_QRCODE => [self::BASEAPIURI . 'wxa/get_qrcode', ['access_token']],
		self::CODE_CONTROL_GET_CATEGORY => [self::BASEAPIURI . 'wxa/get_category', ['access_token']],
		self::CODE_CONTROL_GET_PAGE => [self::BASEAPIURI . 'wxa/get_page', ['access_token']],
		self::CODE_CONTROL_SUBMIT_AUDIT => [self::BASEAPIURI . 'wxa/submit_audit', ['access_token']],
		self::CODE_CONTROL_GET_AUDIT_STATUS => [self::BASEAPIURI . 'wxa/get_auditstatus', ['access_token']],
		self::CODE_CONTROL_GET_LATEST_AUDIT_STATUS => [self::BASEAPIURI . 'wxa/get_latest_auditstatus', ['access_token']],
		self::CODE_CONTROL_RELEASE => [self::BASEAPIURI . 'wxa/release', ['access_token']],
		self::CODE_CONTROL_GET_DRAFT_LIST => [self::BASEAPIURI . 'wxa/gettemplatedraftlist', ['access_token']],
		self::CODE_CONTROL_GET_TEMPLATE_LIST => [self::BASEAPIURI . 'wxa/gettemplatelist', ['access_token']],
		self::CODE_CONTROL_ADD_TEMPLATE => [self::BASEAPIURI . 'wxa/addtotemplate', ['access_token']],
		self::CODE_CONTROL_DELETE_TEMPLATE => [self::BASEAPIURI . 'wxa/deletetemplate', ['access_token']],
		self::CODE_CONTROL_UNDO_AUDIT => [self::BASEAPIURI . 'wxa/undocodeaudit', ['access_token']],
		self::CODE_CONTROL_CHANGE_VISIT_STATUS => [self::BASEAPIURI . 'wxa/change_visitstatus', ['access_token']],
		self::CODE_CONTROL_REVERT_RELEASE => [self::BASEAPIURI . 'wxa/revertcoderelease', ['access_token']],
		//第三方平台修改小程序
		self::SETTING_MODIFY_DOMAIN => [self::BASEAPIURI . 'wxa/modify_domain', ['access_token']],
		self::SETTING_SET_WEBVIEW_DOMAIN => [self::BASEAPIURI . 'wxa/setwebviewdomain', ['access_token']],
		self::SETTING_BIND_TESTER => [self::BASEAPIURI . 'wxa/bind_tester', ['access_token']],
		self::SETTING_UNBIND_TESTER => [self::BASEAPIURI . 'wxa/unbind_tester', ['access_token']],
	];

	/**
	 * 获取接口地址
	 * @access public
	 * @param int $nCode [in]代码
	 * @param mixed $param [out]额外参数
	 * @return mixed
	 */
	public static function get($nCode, &$param = '')
	{
		if (empty($nCode) || !isset(self::$_aMap[$nCode]))
		{
			return false;
		}
		$aConfig = self::$_aMap[$nCode];
		$method = $aConfig[0];
		$url = $aConfig[1];
		$aKey = $aConfig[2];
		return [
			'method' => $method,
			'url' => $url,
		];
		//检测$param中是否存在命令所需的get参数
		$aUrlParam = [];
		foreach ($aKey as $key)
		{
			if (!isset($param[$key]) || empty($param[$key]))
			{
				self::setError('获取指令地址失败，缺少参数：' . $key);
				return false;
			}
			$aUrlParam[$key] = $param[$key];
			unset($param[$key]);
		}
		return $url . '?' . http_build_query($aUrlParam);
	}
}
