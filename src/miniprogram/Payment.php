<?php

namespace lazymanso\wechat\miniprogram;

/**
 * 微信小程序支付
 */
class Payment extends Base
{
	/**
	 * 支付小程序appid
	 * @var string
	 */
	protected $strAppId;

	/**
	 * 支付小程序secret
	 * @var string
	 */
	protected $strAppSecret;

	/**
	 * 小程序支付商户号
	 * @var string
	 */
	protected $strMchId;

	/**
	 * 小程序支付商户名称
	 * @var string
	 */
	protected $strMchName;

	/**
	 * 商户设置的密钥 微信商户平台(pay.weixin.qq.com)-->账户设置-->API安全-->密钥设置
	 * @var string
	 */
	protected $strKey;

	/**
	 * 设置商户支付配置
	 * @param array $aConfig [in]商户支付配置
	 * @return boolean
	 */
	public function setConfig(array $aConfig)
	{
		if (!$this->checkFields($aConfig, ['appid', 'secret', 'mchid', 'mchname', 'apikey'], [], true))
		{
			return false;
		}
		$this->strAppId = $aConfig['appid'];
		$this->strAppSecret = $aConfig['secret'];
		$this->strMchId = $aConfig['mchid'];
		$this->strMchName = $aConfig['mchname'];
		$this->strKey = $aConfig['apikey'];
		return true;
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
	 * 微信小程序统一下单
	 * @param array $aInput [in]必填['out_trade_no','total_fee',openid]
	 * <pre>
	 * out_trade_no - string,必填,商户自定义的订单交易号
	 * total_fee - int,必填,订单金额,单位（分）
	 * openid - string,必填,发起支付的小程序用户的openid
	 * notify_url - string,必填,接收微信支付结果通知的回调地址
	 * </pre>
	 * @param array $aOutput [out]返回['prepay_id','trade_type']
	 * @return boolean
	 */
	public function unifiedOrder(array $aInput)
	{
		if (!$this->checkFields($aInput, ['out_trade_no', 'total_fee', 'openid', 'notify_url']))
		{
			return false;
		}
		if ($aInput['total_fee'] <= 0)
		{
			$this->setError('商品价格小于等于0，下单失败');
			return false;
		}
		$strBody = $this->strMchName . '-' . $aInput['out_trade_no'];
		$aParam = [
			'appid' => $this->strAppId,
			'mch_id' => $this->strMchId,
			'nonce_str' => $this->createNoncestr(),
			'body' => $strBody,
			'out_trade_no' => $aInput['out_trade_no'],
			'total_fee' => $aInput['total_fee'],
			'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],
			'notify_url' => $aInput['notify_url'], //接收微信支付结果的地址
			'openid' => $aInput['openid'],
			'trade_type' => 'JSAPI',
		];
		$aParam['sign'] = $this->sign($aParam);
		if (false === $aResponse = $this->doCommand(Command::PAY_UNIFIED_ORDER, $aParam, 'xml'))
		{
			return false;
		}
		return $aResponse;
	}

	/**
	 * 订单查询
	 * 该接口提供所有微信支付订单的查询，商户可以通过查询订单接口主动查询订单状态
	 * @param array $aInput [in]参数列表
	 * <pre>
	 * out_trade_no - string,必填,商户的订单编号
	 * </pre>
	 * @param array $aOutput [out]响应内容
	 * @link https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_2
	 * @return boolean|array 返回 false 时表示出错,返回空数组时表示订单不存在
	 */
	public function queryOrder(array $aInput)
	{
		if (!$this->checkFields($aInput, ['out_trade_no'], [], true))
		{
			return false;
		}
		// 请求参数
		$aParam = [
			'appid' => $this->strAppId,
			'mch_id' => $this->strMchId,
			'nonce_str' => $this->createNoncestr(),
			'out_trade_no' => $aInput['out_trade_no'],
		];
		// 签名
		$aParam['sign'] = $this->sign($aParam);
		//
		if (false === $aResponse = $this->doCommand(Command::PAY_QUERY_ORDER, $aParam, 'xml'))
		{
			// ORDERNOTEXIST - 订单不存在
			if ('ORDERNOTEXIST' === $this->getError())
			{
				return [];
			}
			return false;
		}
		return $aResponse;
	}

	/**
	 * 返回wx.requestPayment所需要的object参数
	 * @param array $aInput [in]输入['package'=>'prepay_id=$strPrepayId']
	 * @return false|array
	 */
	public function getRequestPaymentObject(array $aInput)
	{
		if (!$this->checkFields($aInput, ['package'], [], true))
		{
			return false;
		}
		$strNowtime = (string) time();
		$strNonce = $this->createNoncestr();
		//签名参数
		$aSignParam = [
			'appId' => $this->strAppId,
			'timeStamp' => $strNowtime,
			'nonceStr' => $strNonce,
			'package' => $aInput['package'],
			'signType' => 'MD5',
		];
		//输出数据
		return [
			'timeStamp' => $strNowtime,
			'nonceStr' => $strNonce,
			'package' => $aInput['package'],
			'signType' => 'MD5',
			'paySign' => $this->sign($aSignParam),
		];
	}

	/**
	 * 订单关闭
	 * @param array $aInput [in]输入
	 * <pre>
	 * 必填项：['uuid','out_trade_no']
	 * </pre>
	 * @param array $aOutput [out]输出
	 * @return boolean
	 */
	public function closeOrder(array $aInput, array &$aOutput = [])
	{
		if (!$this->_checkFields($aInput, ['uuid', 'out_trade_no'], [], true))
		{
			return false;
		}
		if (!$this->_checkUuid($aInput))
		{
			return false;
		}
		$strOutTradeNo = $aInput['out_trade_no'];
		//查询订单状态
		$aOrderStateResult = [];
		if (!$this->queryOrder($aInput, $aOrderStateResult))
		{
			return false;
		}
		//微信订单不存在或已关闭或已退款,直接返回成功
		if (empty($aOrderStateResult) ||
		in_array($aOrderStateResult['trade_state'], [\WxappConf::WXORDER_TRADE_STATE_CLOSED, \WxappConf::WXORDER_TRADE_STATE_REFUND]))
		{
			return true;
		}
		//已支付不允许执行关单,需先调用退款操作
		if (in_array($aOrderStateResult['trade_state'], [\WxappConf::WXORDER_TRADE_STATE_SUCCESS]))
		{
			$this->setError('订单不允许关闭，原因：' . \WxappConf::getWXOrderTradeState($aOrderStateResult['trade_state']));
			return false;
		}
		//获取商户支付配置
		if (!$this->_getPaymentContent($this->_nSiteId))
		{
			return false;
		}
		$aParam = array(
			'appid' => $this->_strAppId,
			'mch_id' => $this->_strMchId,
			'nonce_str' => $this->_createNoncestr(),
			'out_trade_no' => $strOutTradeNo,
		);
		//签名
		$aParam['sign'] = $this->_getSign($aParam);
		//
		if (!$this->_doCommand(Command::PAY_CLOSE_ORDER, $aParam, 'xml'))
		{
			return false;
		}
		$aOutput = $this->response;
		return true;
	}

	/**
	 * 申请退款
	 * @param array $aInput [in]输入
	 * <pre>
	 * 必填项['uuid', 'order_id', 'refund_fee']
	 * </pre>
	 * @param array $aOutput [out]输出
	 * @return boolean
	 */
	public function refundOrder(array $aInput, array &$aOutput = [])
	{
		if (!$this->_checkFields($aInput, ['uuid', 'order_id', 'refund_fee']) || !$this->_checkUuid($aInput))
		{
			return false;
		}
		//要先查下微信系统的退款状态
		$aQueryRefundInput = [
			'uuid' => $this->_strUuid,
			'order_id' => $aInput['order_id'],
		];
		$aStatus = [];
		if (!$this->queryRefundOrder($aQueryRefundInput, $aStatus))
		{
			return false;
		}
		switch ($aStatus['refund_status'])
		{
			case \WxappConf::WXORDER_REFUND_STATE_PROCESSING:
			case \WxappConf::WXORDER_REFUND_STATE_SUCCESS:
			case \WxappConf::WXORDER_REFUND_STATE_CHANGE:
				$this->setError(\WxappConf::getWXOrderRefundState($aStatus['refund_status']));
				return false;
			case \WxappConf::WXORDER_REFUND_STATE_FAIL:
			case \WxappConf::WXORDER_REFUND_STATE_REFUNDCLOSE:
				$aOrderInfo = [
					'out_trade_no' => $aStatus['out_trade_no'],
					'out_refund_no' => $aStatus['out_refund_no'],
					'total_fee' => $aStatus['total_fee'],
				];
				break;
			default :
				$aOrderInfo = [];
				if (!$this->_oOrderService->makeRefundSn($aInput, $aOrderInfo))
				{
					return false;
				}
		}
		if (!$this->_getPaymentContent($this->_nSiteId))
		{
			return false;
		}
		$aParam = array(
			'appid' => $this->_strAppId,
			'mch_id' => $this->_strMchId,
			'nonce_str' => $this->_createNoncestr(),
			'out_trade_no' => $aOrderInfo['out_trade_no'],
			'out_refund_no' => $aOrderInfo['out_refund_no'],
			'total_fee' => $aOrderInfo['total_fee'],
			'refund_fee' => $aInput['refund_fee'],
			'op_user_id' => $this->_strMchId,
		);
		$aParam['sign'] = $this->_getSign($aParam);
		//
		if (!$this->_doCommand(Command::PAY_REFUND_ORDER, $aParam, 'xml'))
		{
			return false;
		}
		/**
		  if (!$this->_afterRefundOrder())
		  {
		  $this->logError('记录微信退款申请返回内容失败,订单ID:' . $aInput['order_id'], $aInput['order_id']);
		  }
		 *
		 */
		return true;
	}

	/**
	 * 向微信申请退款成功后的操作
	 * @return boolean
	 */
	private function _afterRefundOrder()
	{
		$aReturnData = $this->response;
		if (empty($aReturnData['refund_channel']))
		{
			$aReturnData['refund_channel'] = '';
		}
		elseif (is_array($aReturnData['refund_channel']))
		{
			$aReturnData['refund_channel'] = implode(',', $aReturnData['refund_channel']);
		}
		$aReturnData['site_id'] = $this->_nSiteId;
		$oOrderRefundModel = D('OrderRefund');
		if (false === $nOrderRefundId = $oOrderRefundModel->insertSingle($aReturnData))
		{
			$this->setError($oOrderRefundModel);
			return false;
		}
		//附表数据
		$oOrderRefundDataModel = D('OrderRefundData');
		$aRefundData = [];
		$aRefundCouponParam = [];
		$aDbField = $oOrderRefundDataModel->getDbFields();
		foreach ($aReturnData as $key => $value)
		{
			if (in_array($key, $aDbField))
			{
				$aRefundData[$key] = $value;
			}
			elseif (0 === strpos($key, 'coupon_refund_'))
			{
				$id = substr($key, strrpos($key) + 1);
				$subkey = substr($key, 0, strrpos($key));
				$aRefundCouponParam[$id][$subkey] = $value;
			}
		}
		$aRefundData['coupon_refund_param'] = serialize($aRefundCouponParam);
		if (false === $oOrderRefundDataModel->insertSingle($aRefundData))
		{
			$this->setError($oOrderRefundDataModel);
			return false;
		}
		return true;
	}

	/**
	 * 微信退款结果通知处理
	 * @param array $aInput [in]
	 * @param array $aOutput [out]
	 * @return boolean
	 */
	public function refundNotify(array $aInput, array &$aOutput = [])
	{
		if (!$this->_checkFields($aInput, ['xml']))
		{
			return false;
		}
		$strXml = trim($aInput['xml']);
		$this->logInfo($strXml);
		//return true;
		$aParam = $this->_xml2array($strXml);
		if ('SUCCESS' !== $aParam['return_code'])
		{
			$this->setError('通信失败');
			return false;
		}
		$strAppid = $aParam['appid'];
		$strMchid = $aParam['mch_id'];

		$oPaymentContent = D('PaymentContent');
		$aLocator = [
			'content_id' => $strAppid,
			'content_num' => $strMchid,
			'content_status' => 1,
		];
		$aField = ['content_key'];
		if (false === $aPaymentContentList = $oPaymentContent->getList($aLocator, [], $aField, [], '', 1))
		{
			$this->setError('获取支付配置信息错误：' . $oPaymentContent->getError());
			return false;
		}
		$strApikey = $aPaymentContentList[0]['content_key'];

		//解密结果信息
		$strDecrypt = base64_decode($aParam['req_info'], true);
		$strReqInfoXml = openssl_decrypt($strDecrypt, 'aes-256-ecb', md5($strApikey), OPENSSL_RAW_DATA);
		$aReqInfo = $this->_xml2array($strReqInfoXml);

		//写入交易记录表
		$aResult = array_merge($aReqInfo, $aParam);
		if (!$this->_recordRefundNotifyResult($aResult))
		{
			$this->logInfo($strXml, $aResult['out_trade_no']);
			return false;
		}
		return true;
	}

	/**
	 * 新增退款记录，更新订单退款状态
	 * @param array $aParam
	 * @return boolean
	 */
	private function _recordRefundNotifyResult(array $aParam)
	{
		$strOrderSn = $aParam['out_trade_no'];

		//订单服务类
		$aOrderField = ['id', 'site_id', 'order_status'];
		$aOrderInfo = [];
		if (!$this->_oOrderService->getOrderBySn($strOrderSn, $aOrderField, $aOrderInfo))
		{
			$this->setError('处理微信退款回调错误：' . $this->_oOrderService->getError(), $strOrderSn);
			//返回true，告诉微信不要再发回调请求了
			return true;
		}

		//检测是否多商家订单
		$aMallOrderInfo = D('MallOrder')->getByField('order_id', $aOrderInfo['id'], ['master_site_id']);
		if (!empty($aMallOrderInfo))
		{
			$aOrderInfo = array_merge($aOrderInfo, $aMallOrderInfo[0]);
		}

		//
		$nPaymentSiteId = isset($aOrderInfo['master_site_id']) ? $aOrderInfo['master_site_id'] : $aOrderInfo['site_id'];
		$aParam['site_id'] = $nPaymentSiteId;
		$nOrderId = $aOrderInfo['id'];

		switch ($aParam['refund_status'])
		{
			case \WxappConf::WXORDER_REFUND_STATE_SUCCESS:
				$nUpdateState = \WxappConf::ORDER_REFUND_STATE_DONE;
				break;
			case \WxappConf::WXORDER_REFUND_STATE_CHANGE:
				$nUpdateState = \WxappConf::ORDER_REFUND_STATE_CHANGE;
				break;
			case \WxappConf::WXORDER_REFUND_STATE_REFUNDCLOSE:
				$nUpdateState = \WxappConf::ORDER_REFUND_STATE_CLOSED;
				break;
			case \WxappConf::WXORDER_REFUND_STATE_FAIL:
				$nUpdateState = \WxappConf::ORDER_REFUND_STATE_FAIL;
				break;
		}

		//事务处理订单支付结果状态
		$this->_startTrans();
		try
		{
			//记录微信通知
			$oModel = D('PaymentRefundNotify');
			$oModel->insertSingle($aParam);

			//更新订单refund_status
			$aUpdateOrder = [
				'refund_status' => $nUpdateState,
			];
			if ($aOrderInfo['order_status'] != \WxappConf::ORDER_STATE_CANC)
			{
				$aUpdateOrder['order_status'] = \WxappConf::ORDER_STATE_CANC;
				$aRestoreInput = [
					'order_id' => $nOrderId,
				];
				//如果已完成的则不再恢复库存
				if (\WxappConf::ORDER_STATE_FSHD != $aOrderInfo['order_status'])
				{
					if (!$this->_oOrderService->restoreStock($aRestoreInput))
					{
						E($this->_oOrderService->getError());
					}
				}
			}

			if (!$this->_oOrderService->updateOrderById($nOrderId, $aUpdateOrder))
			{
				E($this->_oOrderService->getError());
			}

			$bHasError = false;
		}
		catch (\Exception $phpex)
		{
			$this->setError($phpex->getMessage());
			$bHasError = true;
		}

		if (!$this->_endTrans($bHasError))
		{
			return false;
		}

		return !$bHasError;
	}

	/**
	 * 查询退款
	 * @param array $aInput [in]输入
	 * <pre>
	 * array(
	 * 'uuid', 必填
	 * 'order_id', 必填 订单ID
	 * )
	 * </pre>
	 * @param array $aOutput [out]输出
	 * @return boolean
	 */
	public function queryRefundOrder(array $aInput, array &$aOutput = [])
	{
		if (!$this->_checkFields($aInput, ['uuid', 'order_id']))
		{
			return false;
		}
		if (!$this->_checkUuid($aInput))
		{
			return false;
		}
		$nOrderId = intval($aInput['order_id']);
		$aOrderField = ['order_sn'];
		$aOrderInfo = [];
		if (!$this->_oOrderService->getOrderById($nOrderId, $aOrderField, $aOrderInfo))
		{
			return false;
		}
		if (!$this->_getPaymentContent($this->_nSiteId))
		{
			return false;
		}
		$aParam = array(
			'appid' => $this->_strAppId,
			'mch_id' => $this->_strMchId,
			'nonce_str' => $this->_createNoncestr(),
			'out_trade_no' => $aOrderInfo['order_sn'],
		);
		$aParam['sign'] = $this->_getSign($aParam);
		//
		if (!$this->_doCommand(Command::PAY_QUERY_REFUND_ORDER, $aParam, 'xml'))
		{
			return false;
		}
		if ('REFUNDNOTEXIST' === $this->response['err_code'])
		{
			$aOutput = [];
			return true;
		}
		if (!$this->_afterQueryRefundOrder())
		{
			$this->setError('更新订单退款信息失败，订单ID：' . $nOrderId . '，错误信息：' . $this->getError(), $this->_strUuid);
			return false;
		}
		$aOutput = $this->response;
		return true;
	}

	/**
	 * 接口查询订单后的处理
	 */
	private function _afterQueryRefundOrder()
	{
		$aReturnData = $this->response;
		$aReturnData['site_id'] = $this->_nSiteId;
		//订单退款记录更新
		$oOrderRefundModel = D('OrderRefund');
		//附表数据
		$oOrderRefundDataModel = D('OrderRefundData');
		$aRefundDbField = $oOrderRefundModel->getDbFields();
		$aRefundDataDbField = $oOrderRefundDataModel->getDbFields();
		$aRefundList = [];
		$aRefundCommon = [];
		$aRefundDataList = [];
		foreach ($aReturnData as $key => $value)
		{
			if (in_array($key, $aRefundDbField))
			{
				$aRefundCommon[$key] = $value;
			}
			$nRightPos = strrpos($key, '_');
			$nLastId = substr($key, $nRightPos + 1);
			if (!is_numeric($nLastId))
			{
				continue;
			}
			$subkey = substr($key, 0, $nRightPos);
			if (in_array($subkey, $aRefundDbField))
			{
				$aRefundList[$nLastId][$subkey] = $value;
			}
			if (in_array($subkey, $aRefundDataDbField))
			{
				$aRefundDataList[$nLastId][$subkey] = $value;
			}
			elseif (0 === strpos($key, 'coupon_refund_'))
			{
				$nSubRightPos = strrpos($subkey, '_');
				$nFirstId = substr($subkey, $nSubRightPos + 1);
				$strSsubkey = substr($subkey, 0, $nSubRightPos);
				$aRefundDataList[$nFirstId]['coupon_refund_param'][$nLastId][$strSsubkey] = $value;
			}
		}
		//批量更新退款状态
		try
		{
			foreach ($aRefundList as $aRefundItem)
			{
				$aSaveData = array_merge($aRefundItem, $aRefundCommon);
				$aRefundMap = [
					'site_id' => $this->_nSiteId,
					'out_refund_no' => $aRefundItem['out_refund_no'],
				];
				if ($oOrderRefundModel->getCount($aRefundMap))
				{
					$oOrderRefundModel->updateByLocator($aSaveData, $aRefundMap);
				}
				else
				{
					$oOrderRefundModel->insertSingle($aSaveData);
				}
			}
			foreach ($aRefundDataList as $key => &$aRefundDataItem)
			{
				if (!empty($aRefundDataItem['coupon_refund_param']))
				{
					$aRefundDataItem['coupon_refund_param'] = serialize($aRefundDataItem['coupon_refund_param']);
					$aRefundDataItem['site_id'] = $this->_nSiteId;
					$aRefundDataMap = [
						'out_refund_no' => $aRefundDataItem['out_refund_no'],
					];
					if ($oOrderRefundDataModel->getCount($aRefundDataMap))
					{
						$oOrderRefundDataModel->updateByLocator($aRefundDataItem, $aRefundDataMap);
					}
					else
					{
						$oOrderRefundDataModel->insertSingle($aRefundDataItem);
					}
				}
			}
		}
		catch (SystemExc $ex)
		{
			$this->setError('更新订单退款信息失败,' . $ex->getMessage());
			return false;
		}
		catch (\Exception $phpex)
		{
			$this->setError('更新订单退款信息失败,' . $phpex->getMessage());
			return false;
		}
		return true;
	}

	/**
	 * 下载对账单
	 * @param array $aInput [in]输入
	 * <pre>
	 * array(
	 * 'uuid', 必填
	 * 'out_trade_no', 必填
	 * )
	 * </pre>
	 * @param array $aOutput [out]输出
	 * @return boolean
	 */
	public function downloadBill(array $aInput, array &$aOutput = [])
	{
		return false;
	}

	/**
	 * 拉取订单评价数据
	 * @param array $aInput [in]输入
	 * <pre>
	 * array(
	 * 'uuid', 必填
	 * 'out_trade_no', 必填
	 * )
	 * </pre>
	 * @param array $aOutput [out]输出
	 * @return boolean
	 */
	public function queryOrderComment(array $aInput, array &$aOutput = [])
	{
		return false;
	}

	/**
	 * 返回wx.requestPayment所需要的object参数
	 * @param array $aInput [in]输入['uuid', 'login_session', 'order_id','mall_id']
	 * @param array $aOutput [out]返回数据['timeStamp','nonceStr','package','signType','paySign']
	 * @return boolean
	 */
	public function getMallRequestPaymentObject(array $aInput, array &$aOutput = [])
	{
		if (!$this->_checkFields($aInput, ['uuid', 'login_session', 'order_id', 'mall_id']))
		{
			return false;
		}

		$aMallInfo = [];
		if (!$this->checkMall($aInput, $aMallInfo))
		{
			return false;
		}

		$nOrderId = intval($aInput['order_id']);
		//获取订单信息
		$aOrderInfo = [];
		$aOrderField = ['order_sn', 'order_amount', 'shipping_price', 'site_id'];
		if (!$this->_oOrderService->getOrderById($nOrderId, $aOrderField, $aOrderInfo))
		{
			return false;
		}

		if ($aMallInfo['site_id'] != $aOrderInfo['site_id'])
		{
			$this->setError(\ErrorConf::ERROR_AUTH_SITEDATA_OWNER);
			return false;
		}

		$aCommonInput = array_merge($aInput, [
			'out_trade_no' => $aOrderInfo['order_sn'],
			'total_fee' => ($aOrderInfo['order_amount'] + $aOrderInfo['shipping_price']) * 100,
		]);

		//查询订单状态
		$aOrderStateResult = [];
		if (!$this->queryOrder($aCommonInput, $aOrderStateResult))
		{
			return false;
		}
		if (!empty($aOrderStateResult) && !in_array($aOrderStateResult['trade_state'], [\WxappConf::WXORDER_TRADE_STATE_NOTPAY,
			\WxappConf::WXORDER_TRADE_STATE_PAYERROR]))
		{
			$this->setError('订单状态不允许支付,原因:' . \WxappConf::getWXOrderTradeState($aOrderStateResult['trade_state']));
			return false;
		}

		//下单
		$aUnifiedOrderResult = [];
		if (!$this->unifiedOrder($aCommonInput, $aUnifiedOrderResult))
		{
			return false;
		}

		//保存formId
		$oTplmsgService = fn_GetService(\ServiceConf::NAME_TEMPLATE_MESSAGE);
		$aSaveFormIdInput = [
			'uuid' => $this->_strUuid,
			'keyword' => 'order_' . $nOrderId,
			'form_id' => $aUnifiedOrderResult['prepay_id'],
			'form_id_type' => \WxappConf::FORM_ID_TYPE_PREPAY,
		];
		$oTplmsgService->saveFormId($aSaveFormIdInput);

		$strPrepayId = 'prepay_id=' . $aUnifiedOrderResult['prepay_id'];

		//获取商户支付配置
		if (!$this->_getPaymentContent($this->_nSiteId))
		{
			return false;
		}
		$strNowtime = (string) time();
		$strNonce = $this->_createNoncestr();
		//签名参数
		$aSignParam = [
			'appId' => $this->_strAppId,
			'timeStamp' => $strNowtime,
			'nonceStr' => $strNonce,
			'package' => $strPrepayId,
			'signType' => 'MD5',
		];
		//输出数据
		$aOutput = [
			'timeStamp' => $strNowtime,
			'nonceStr' => $strNonce,
			'package' => $strPrepayId,
			'signType' => 'MD5',
			'paySign' => $this->_getSign($aSignParam),
		];
		return true;
	}

	/**
	 * 微信支付结果通知处理
	 * @param array $aInput [in]微信通知数据
	 * @param array $aOutput [out]
	 * @return boolean
	 */
	public function payNotify(array $aInput, array &$aOutput = [])
	{
		if (!$this->_checkFields($aInput, ['xml']))
		{
			return false;
		}
		$strXml = trim($aInput['xml']);
		$aParam = $this->_xml2array($strXml);
		$out_trade_no = $aParam['out_trade_no'];
		$strWxSign = $aParam['sign'];
		$total_fee = $aParam['total_fee'];

		//获取订单信息
		$aOrderField = ['id', 'order_sn', 'site_id', 'order_amount', 'user_id', 'shipping_price'];
		$aOrderInfo = [];
		if (!$this->_oOrderService->getOrderBySn($out_trade_no, $aOrderField, $aOrderInfo))
		{
			return false;
		}

		$fOrderTotalFee = number_format(($aOrderInfo['order_amount'] + $aOrderInfo['shipping_price']) * 100, 0, '', '');
		if ($total_fee != $fOrderTotalFee)
		{
			$this->setError('订单金额与商户侧的订单金额不一致，xml数据：' . $strXml, $aOrderInfo['order_sn']);
			return false;
		}

		//检测是否多商家订单
		$aMallOrderInfo = D('MallOrder')->getByField('order_id', $aOrderInfo['id'], ['master_site_id']);
		if (!empty($aMallOrderInfo))
		{
			$aOrderInfo = array_merge($aOrderInfo, $aMallOrderInfo[0]);
		}

		//验证签名
		$nPaymentSiteId = isset($aOrderInfo['master_site_id']) ? $aOrderInfo['master_site_id'] : $aOrderInfo['site_id'];
		if (!$this->_getPaymentContent($nPaymentSiteId))
		{
			return false;
		}

		$strMakeSign = $this->_getSign($aParam);
		if ($strWxSign !== $strMakeSign)
		{
			$this->setError('校验签名失败！');
			$this->logInfo('校验支付结果签名失败，xml数据：' . $strXml);
			return false;
		}

		//写入交易记录表
		if (!$this->_recordPayNotifyResult($aOrderInfo, $aParam))
		{
			return false;
		}

		//推送模版消息
		$this->_sendPayNotifyTplmsg($aOrderInfo, $aParam);

		//核销卡券
		if (!$this->_consumeCoupon($aOrderInfo))
		{
			return false;
		}

		if ('SUCCESS' === $aParam['result_code'])
		{
			$aOutput = [
				'site_id' => $aOrderInfo['site_id'],
				'user_id' => $aOrderInfo['user_id'],
				'order_id' => $aOrderInfo['id'],
				'order_amount' => $aOrderInfo['order_amount'] + $aOrderInfo['shipping_price']
			];
		}

		return true;
	}

	/**
	 * 核销订单使用的优惠券
	 * @param array $aOrderInfo [in]订单信息
	 * @return boolean
	 */
	private function _consumeCoupon(array $aOrderInfo)
	{
		$nSiteId = $aOrderInfo['site_id'];
		$nOrderId = $aOrderInfo['id'];
		//获取待核销的优惠券
		$oOrderCouponModel = D('OrderCoupon');
		$aLocator = [
			'site_id' => $nSiteId,
			'order_id' => $nOrderId,
		//'state' => 0,
		];
		if (false === $aPendingConsumeList = $oOrderCouponModel->getList($aLocator, [], ['code']))
		{
			$this->setError($oOrderCouponModel);
			return false;
		}
		$this->oCouponService = fn_GetService(\ServiceConf::NAME_COUPON);
		foreach ($aPendingConsumeList as $aItem)
		{
			//$nOrderCouponId = $aItem['id'];
			$nCode = $aItem['code'];
			//$strCardId = $aItem['card_id'];
			$aConsumeInput = [
				'site_id' => $nSiteId,
				'code' => $nCode,
			];
			$aConsumeResult = [];
			if (!$this->oCouponService->consumeCode($aConsumeInput, $aConsumeResult))
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * 发送支付结果模版消息
	 * @param array $aOrderInfo [in]订单信息
	 * @param array $aParam [in]支付结果内容
	 * @return boolean
	 */
	private function _sendPayNotifyTplmsg(array $aOrderInfo, array $aParam)
	{
		$nSiteId = $aOrderInfo['site_id'];
		$nOrderId = $aOrderInfo['id'];
		$nUserId = $aOrderInfo['user_id'];

		if (false === $aSiteInfo = $this->_getSiteInfoBySiteId($nSiteId, ['uuid']))
		{
			return true;
		}
		$oTplmsgService = fn_GetService(\ServiceConf::NAME_TEMPLATE_MESSAGE);
		$aSendMessage = [
			'uuid' => $aSiteInfo['uuid'],
			'order_id' => $nOrderId,
			'user_id' => $nUserId,
		];
		if ('SUCCESS' === $aParam['result_code'])
		{
			$aSendMessage['scene_id'] = \WxappConf::TPLMSG_ORDER_PAID_SUCCESS_NOTICE;
		}
		else
		{
			$aSendMessage['scene_id'] = \WxappConf::TPLMSG_ORDER_PAID_FAIL_NOTICE;
			$aSendMessage['reason'] = $aParam['err_code_des'] ? $aParam['err_code_des'] : '未知原因';
		}
		if (!$oTplmsgService->send($aSendMessage))
		{
			$this->setError($oTplmsgService, $aSiteInfo['uuid']);
			return false;
		}
		return true;
	}

	/**
	 * 根据site_id获取站点基本信息
	 * @param int $nSiteId
	 * @param array $aField
	 * @return boolean
	 */
	private function _getSiteInfoBySiteId($nSiteId, array $aField = [])
	{
		if (false === $aSiteInfo = D('Site')->getById($nSiteId, $aField))
		{
			return false;
		}
		if (empty($aSiteInfo))
		{
			return [];
		}
		return $aSiteInfo;
	}

	/**
	 * 新增交易记录，更新订单支付状态
	 * @param array $aOrderInfo
	 * @param array $aParam
	 * @return boolean
	 */
	private function _recordPayNotifyResult(array $aOrderInfo, array $aParam)
	{
		$nOrderId = $aOrderInfo['id'];
		$nPaymentSiteId = isset($aOrderInfo['master_site_id']) ? $aOrderInfo['master_site_id'] : $aOrderInfo['site_id'];

		$aParam['site_id'] = $nPaymentSiteId;

		$aLocator = [
			'transaction_id' => $aParam['transaction_id'],
		];
		$oPayNotifyModel = D('PaymentPayNotify');
		if (false === $aRecordList = $oPayNotifyModel->getList($aLocator, [], ['id', 'state'], [], '', 1))
		{
			$this->setError($oPayNotifyModel);
			return false;
		}

		if (isset($aRecordList[0]))
		{
			$nRecordId = $aRecordList[0]['id'];
			$nState = $aRecordList[0]['state'];
		}

		if ($nState)
		{
			return true;
		}

		//事务处理订单支付结果状态
		$this->_startTrans();
		try
		{
			if (empty($nRecordId))
			{
				$nRecordId = $oPayNotifyModel->insertSingle($aParam);
			}
			if (!$nState)
			{
				//更新订单pay_status
				$aUpdateOrder = [
					'pay_status' => 'SUCCESS' === $aParam['result_code'] ? \WxappConf::ORDER_PAY_STATE_PAID : \WxappConf::ORDER_PAY_STATE_FAIL,
					'pay_time' => strtotime($aParam['time_end']),
				];
				$this->_oOrderService->updateOrderById($nOrderId, $aUpdateOrder);
				//更新支付结果为已处理
				$oPayNotifyModel->updateById(['state' => 1], $nRecordId);
			}

			$bHasError = false;
		}
		catch (\Exception $phpex)
		{
			$this->setError($phpex->getMessage());
			$bHasError = true;
		}
		if (!$this->_endTrans($bHasError))
		{
			return false;
		}
		return !$bHasError;
	}

	/**
	 * 获取商户支付配置
	 * @param int $nSiteId [in]站点id
	 * @staticvar array $aPayAccountMap
	 * @return boolean
	 */
	private function _getPaymentContent($nSiteId)
	{
		static $aPayAccountMap = [];
		if (isset($aPayAccountMap[$nSiteId]))
		{
			$aPayAccount = $aPayAccountMap[$nSiteId];
		}
		else
		{
			$oPaymentContent = D('PaymentContent');
			$aLocator = [
				'site_id' => $nSiteId,
				'content_status' => 1,
			];
			$aField = [
				'content_id',
				'content_num',
				'content_openid',
				'content_key',
				'content_name',
			];
			if (false === $aPaymentContentList = $oPaymentContent->getList($aLocator, [], $aField, [], '', 1))
			{
				$this->setError('获取支付配置信息错误。' . $oPaymentContent->getError());
				return false;
			}
			if (empty($aPaymentContentList))
			{
				$this->setError('商家支付配置未完成，请稍候再试或联系商家处理');
				return false;
			}
			$aPayAccount = $aPaymentContentList[0];
			$aPayAccountMap[$nSiteId] = $aPayAccount;
		}
		$this->_strMchName = $aPayAccount['content_name'];
		$this->_strAppId = $aPayAccount['content_id'];
		$this->_strMchId = $aPayAccount['content_num'];
		$this->_strAppSecret = $aPayAccount['content_openid'];
		$this->_strKey = $aPayAccount['content_key'];
		return true;
	}
}
