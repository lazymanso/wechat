<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace lazymanso\wechat;

use lazymanso\wechat\config\Command;

/**
 * 模板消息
 */
class Template extends Common
{

	/**
	 * 构造
	 * @param array $aConfig [in]参数列表
	 * <pre>
	 * access_token - string,接口调用凭证
	 * </pre>
	 */
	public function __construct(array $aConfig = [])
	{
		$this->strToken = $aConfig['access_token'];
	}

	/**
	 * 发送一个模板消息
	 * @link https://developers.weixin.qq.com/miniprogram/dev/api/open-api/template-message/sendTemplateMessage.html
	 * @param array $aInput [in]参数列表
	 * <pre>
	 * template_id - string,必填,模板消息ID
	 * form_id - string,必填,form id
	 * data - array,必填,消息内容
	 * touser - string,必填,小程序用户openid
	 * page - string,选填,点击消息后的跳转页面，仅限本小程序内的页面
	 * </pre>
	 * @return false|array
	 */
	public function send(array $aInput)
	{
		if (!$this->checkFields($aInput, ['template_id', 'form_id', 'data', 'touser'], [], true))
		{
			return false;
		}
		return $this->doCommand(Command::TPL_SEND_MESSAGE, $aInput, 'json');
	}

	/**
	 * 获取帐号下已存在的模板列表
	 * @link https://developers.weixin.qq.com/miniprogram/dev/api/open-api/template-message/getTemplateList.html
	 * @param array $aInput [in]参数列表
	 * <pre>
	 * offset - int,必填,从offset开始
	 * count - int,必填,拉取count条记录
	 * </pre>
	 * @return false|array
	 */
	public function getTemplateList(array $aInput)
	{
		if (!$this->checkFields($aInput, ['offset', 'count']))
		{
			return false;
		}
		return $this->doCommand(Command::TPL_LIST_PRIVATE, $aInput, 'json');
	}
}
