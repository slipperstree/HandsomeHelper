<?php

class HandsomeHelper_Action extends Typecho_Widget implements Widget_Interface_Do
{
	private $db;
	private $options;
	private $prefix;

	/**
	 * 生成 index.html
	 */
	function makeIndexHtml()
	{
		// 备份之前的Content然后暂时清空缓冲区（否则如果请求从admin过来时缓冲区里后台信息会混在里面）
		$contentBef = ob_get_contents();
		$pathInfoBef = Typecho_Router::getPathInfo();
		ob_clean();

		// 重新开启缓冲区
		ob_start();

		Typecho_Router::setPathInfo("/");
		require( __TYPECHO_ROOT_DIR__ . '/index.php' );
		$content = ob_get_contents();
		$content .= "\n<!-- Create time: " . date( 'Y-m-d H:i:s' ) . " -->";
		/* 最后添加自动更新用js */
		$content .= "\n<script language=javascript src='/action/handsome-helper?do=createStaticIndex'></script>";
		$res = file_put_contents( __TYPECHO_ROOT_DIR__ . '/index.html', $content );
		ob_clean();

		// 恢复之前的Content
		ob_start();
		echo $contentBef;
		Typecho_Router::setPathInfo($pathInfoBef);

		// 返回
		if ( $res !== false )
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * 首页静态化
	 */
	public function createStaticIndex()
	{
		ini_set( 'date.timezone', 'PRC' );

		/* 缓存过期时间 单位：秒 */
		$expire = 600;
		// TODO 从设置里面取得 expire

		if ($this->request->get('force') != ""){
			// 强制刷新，带参数： http://你的域名/action/handsome-helper?do=createStaticIndex?force=y
			$this->request->get('force') == "y" && $this->makeIndexHtml();
		} else {
			// 自动刷新（每当有人访问主页时检查index.html文件的时间戳）
			$file_time = @filemtime( 'index.html' );
			time() - $file_time > $expire && $this->makeIndexHtml();
		}
	}

	public function action()
	{
		$user = Typecho_Widget::widget('Widget_User');
		$this->db = Typecho_Db::get();
		$this->prefix = $this->db->getPrefix();
		$this->options = Typecho_Widget::widget('Widget_Options');
		$this->on($this->request->is('do=createStaticIndex'))->createStaticIndex();
	}
}

?>
