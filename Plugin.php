<?php
/**
 * <strong style="color:#3f51b5;">Handsome Helper 非官方辅助插件</strong>
 * <div class="mdui-theme-primary-indigo mdui-theme-accent-pink">
 *      <button class="mdui-btn mdui-color-theme-400 mdui-icon-left mdui-ripple"
 *          mdui-dialog="{target: '#dialog'}" >
 *          <i class="mdui-icon mdui-icon-left material-icons">chat</i>
 *          about
 *      </button>
 *      <a href="https://github.com/slipperstree/HandsomeHelper">
 *          <button class="mdui-btn mdui-color-theme-400 mdui-icon-left mdui-ripple">
 *              <i class="mdui-icon mdui-icon-left material-icons">link</i>
 *              github
 *          </button>
 *      </a>
 * </div>
 * <div class="mdui-dialog" id="dialog">
 *      <div class="mdui-dialog-title">关于 Handsome Helper</div>
 *      <div class="mdui-dialog-content">
 *          本插件非handsome官方插件，请自行承担使用风险。<br>
 *          作者：<a href="http://blog.mangolovecarrot.net">芒果爱吃胡萝卜</a><br><br>
 *          主要功能<br>
 *          1. 如果开启了CDN加速，头图部分或者是正文部分如果使用的是相对路径，那么会自动切换成CDN加速域名。<br><br>
 *          有任何问题或建议欢迎去主页留言或点击GITHUB按钮在Github上发起issue。<br>
 *      <div class="mdui-dialog-actions">
 *          <button class="mdui-btn mdui-ripple" mdui-dialog-close>我知道了</button>
 *      </div>
 * </div>
 * <link rel="stylesheet"
 *       href="https://cdn.jsdelivr.net/npm/mdui@1.0.1/dist/css/mdui.min.css"
 *       integrity="sha384-cLRrMq39HOZdvE0j6yBojO4+1PrHfB7a9l5qLcmRm/fiWXYY+CndJPmyu5FV/9Tw"
 *       crossorigin="anonymous" />
 * <script src="//cdn.jsdelivr.net/npm/mdui@1.0.1/dist/js/mdui.min.js"></script>
 * @package Handsome Helper
 * @author mango
 * @version 0.1.0
 * @link https://github.com/slipperstree/HandsomeHelper
 */

class HandsomeHelper_Plugin implements Typecho_Plugin_Interface {

    /**
	 * 索引ID
	 */
	public static $id = 1;
	
	public static $pattern = '/(&lt;|<)!--\s*series-index\s*--(&gt;|>)/i';

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('SeriesIndex_Plugin', 'contentEx');
        //Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = array('SeriesIndex_Plugin', 'excerptEx');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form) {}
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    /**
     * 列表页忽略目录生成标记
     * 
     * @access public
     * @return string
     */
    public static function excerptEx( $html, $widget, $lastResult){
        return preg_replace(self::$pattern,'',$html);
    }

    /**
     * 内容页构造索引目录
     * 
     * @access public
     * @return string
     */
    public static function contentEx( $html, $widget, $lastResult ) {
        $html = empty( $lastResult ) ? $html : $lastResult;

		// 文章标题：$widget->title  形如：开源代码分析学习 - 有趣的聊天机器人 - 01
		// 文章链接：$widget->permalink  形如：http://blog.mangolovecarrot.net/2020/07/01/chatbot01/
		// 文章slug：$widget->slug  形如：chatbot01

		// 先取slug末尾的数字（不论几位都可以）
		$slug = $widget->slug;
		$slugLastDigitCnt = 0;
		while ($slugLastDigitCnt < strlen($slug) && is_numeric(substr($slug, strlen($slug)-($slugLastDigitCnt+1)))) {
			$slugLastDigitCnt++;
		}

		if ($slugLastDigitCnt > 0 && $slugLastDigitCnt < strlen($slug)) {
			// slug末尾有数字，并且不全为数字的话就假定除了数字的部分的前缀是系列的slug前缀
			// 将slug前缀作为检索条件在contents表中查询同一个系列的文章（标题? 内容?里面第一个# 或 ## 或 ###的文字）
			$slugPrefix = substr($slug, 0, strlen($slug)-$slugLastDigitCnt);

			$db = Typecho_Db::get();

			$seriesContents = $db->fetchAll($db
            ->select()->from('table.contents')
			->where('table.contents.slug like ?', $slugPrefix . '%')
			->where('table.contents.type = ?', 'post')
			->order('table.contents.slug', Typecho_Db::SORT_ASC));

			// 如果只有一篇，表示除了当前这篇文章以外没有相同slug前缀的文章，不生成系列文章
			if (count($seriesContents) <= 1) {
				return $html;
			}
			
			$seriesIndexHtml = "<div style='padding-left:10px; padding-top:10px; padding-bottom:10px; border:0px solid blue; background:#EDEFED; border-left:3px solid #D2D7D2;'>";
			//$seriesIndexHtml = "<div style='border:0px solid blue;background:#EDEFED;'>";
			$seriesIndexHtml .= "<h3>系列文章</h3>";
			$seriesIndexHtml .= "<ul>";
			foreach ($seriesContents as $seriesContent) {
				// TODO 取到以后拼接出url，并用上面取出的文字作为链接文字
				$url = self::getURL($seriesContent, $widget);
				//$url = "#";

				if ($seriesContent['slug'] == $slug) {
					// 当前文章不加link
					$seriesIndexHtml .= "<li><b>" . $seriesContent['title'] . "</b>【当前文章】</li>";
				} else {
					$seriesIndexHtml .= "<li><a href='" . $url . "'  title='" . $seriesContent['title'] . "'>" . $seriesContent['title'] . "</a></li>";
				}
			}
			$seriesIndexHtml .= "</ul>";
			$seriesIndexHtml .= "</div>";

			// 在最后添加系列目录（也可允许替换 <!-- series-index --> )

			// 文章末尾添加模式
			return $html . $seriesIndexHtml;

			// 替换 <!-- series-index --> 模式
			//return preg_replace( self::$pattern, '<div class="index-menu">' . $seriesIndexHtml . '</div>', $html );
		} else {

			// slug不符合规则，不做任何处理
			return $html . "";
		}
	}

	public static function getURL($dbContentRow, $widget) {
		$value = array();
		//return "#";
		$value['date'] = new Typecho_Date($dbContentRow['created']);

        /** 生成日期 */
        $value['year'] = $value['date']->year;
        $value['month'] = $value['date']->month;
		$value['day'] = $value['date']->day;
		$value['slug'] = urlencode($dbContentRow['slug']);
		$value['category'] = urlencode($dbContentRow['category']);
		
		$pathinfo = Typecho_Router::url("post", $value);
		return Typecho_Common::url($pathinfo, $widget->options->index);
	}
	
	// 获取系列文章列表
	protected function getSeriesList($column, $offset, $type, $status = NULL, $authorId = 0, $pageSize = 20)
    {
        $select = $this->db->select(array('COUNT(table.contents.cid)' => 'num'))->from('table.contents')
        ->where("table.contents.{$column} > {$offset}")
        ->where("table.contents.type = ?", $type);

        if (!empty($status)) {
            $select->where("table.contents.status = ?", $status);
        }

        if ($authorId > 0) {
            $select->where('table.contents.authorId = ?', $authorId);
        }

        $count = $this->db->fetchObject($select)->num + 1;
        return ceil($count / $pageSize);
    }
}
