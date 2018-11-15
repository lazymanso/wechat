<?php

namespace lazymanso\wechat\miniprogram;

use lazymanso\wechat\config\Command;

/**
 * 微信小程序模板消息
 */
class Template extends Base
{

	/**
	 * 发送一个模板消息
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
		$aInput['access_token'] = $this->strToken;
		if (false === $aResponse = $this->doCommand(Command::TPL_SEND_MESSAGE, $aInput, 'json'))
		{
			return false;
		}
		return $aResponse;
	}
}
