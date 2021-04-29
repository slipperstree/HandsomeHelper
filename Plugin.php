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
 *          1. 如果开启了CDN加速，首页头图如果指定的是相对地址的URL(比如/usr/upload/.../abc.jpg)，那么会自动切换成CDN加速域名(变成http://CDN-URL/usr/upload/.../abc.jpg)。<br><br>
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
 * @version 1.0.0
 * @link https://github.com/slipperstree/HandsomeHelper
 */

class HandsomeHelper_Plugin implements Typecho_Plugin_Interface {

    public static $themeOptions = NULL;

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        //主题渲染后将URL替换掉（包含首页，归档页，正文页等）
        Typecho_Plugin::factory('Widget_Archive')->afterRender = array('HandsomeHelper_Plugin', 'afterRenderArchive');

        //后台管理header
        Typecho_Plugin::factory('admin/menu.php')->navBar = array('HandsomeHelper_Plugin', 'navBarAdminHeaderMenu');
        //前台header
        Typecho_Plugin::factory('Widget_Archive')->header = array('HandsomeHelper_Plugin', 'headerArchive');

        //正文页(渲染后显示前做替换)
        //Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('HandsomeHelper_Plugin', 'contentEx');

        //首页(主题渲染前将内容改掉即可)
        //Typecho_Plugin::factory('Widget_Archive')->beforeRender = array('HandsomeHelper_Plugin', 'beforeRenderArchive');

        self::$themeOptions = Helper::options();
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

    // Debug打开时，后台管理闪烁提示
    public static function navBarAdminHeaderMenu()
    {
        if (defined('__TYPECHO_DEBUG__') && __TYPECHO_DEBUG__) {
            echo<<<EOF
                <!-- 开发测试用环境醒目 -->
                <style>
                    @keyframes fade {
                        from {
                            opacity: 1.0;
                        }
                        50% {
                            opacity: 0.4;
                        }
                        to {
                            opacity: 1.0;
                        }
                    }

                    @-webkit-keyframes fade {
                        from {
                            opacity: 1.0;
                        }
                        50% {
                            opacity: 0.4;
                        }
                        to {
                            opacity: 1.0;
                        }
                    }
                    .headerBox {
                        display: block;
                        position:fixed;
                        background-color: black;
                        right:5px;
                        top:5vh;
                    }
                    .headerBoxText {
                        color: yellow;
                        padding: 5px 30px;
                        padding-top: 3px;
                        font-size: x-large;
                        animation: fade 600ms infinite;
                        -webkit-animation: fade 600ms infinite;
                        white-space: nowrap;
                        font-family: "Source Sans Pro","Hiragino Sans GB","Microsoft Yahei",SimSun,Helvetica,Arial,Sans-serif,monospace;
                    }
                    @media screen and (max-width: 1024px) {
                        .headerBox {
                            top:12vh;
                        }
                        .headerBoxText {
                            font-size: 20px;
                        }
                        .dash {
                            display: none;
                        }
                    }
                </style>
                <div class="headerBox">
                    <div class="headerBoxText"><span class="dash">---------------------- </span>开发用测试环境<span class="dash"> ----------------------</span></div>
                </div>
                <!-- 开发测试用环境醒目 -->
EOF;
        }
    }

    // Debug打开时，前台页面闪烁提示
    public static function headerArchive($header, $archive)
    {
        if (defined('__TYPECHO_DEBUG__') && __TYPECHO_DEBUG__) {
            echo<<<EOF
                <!-- 开发测试用环境醒目 -->
                <style>
                    @keyframes fade {
                        from {
                            opacity: 1.0;
                        }
                        50% {
                            opacity: 0.4;
                        }
                        to {
                            opacity: 1.0;
                        }
                    }

                    @-webkit-keyframes fade {
                        from {
                            opacity: 1.0;
                        }
                        50% {
                            opacity: 0.4;
                        }
                        to {
                            opacity: 1.0;
                        }
                    }
                    .headerBox {
                        display: block;
                        position:fixed;
                        color: yellow;
                        z-index:99999;
                        padding: 10px;
                        animation: fade 600ms infinite;
                        -webkit-animation: fade 600ms infinite;
                        white-space: nowrap;

                        font-size: xx-large;
                        top:-6px;
                        right:220px;
                    }
                    @media screen and (max-width: 1024px) {
                        .headerBox {
                            font-size: 27px;
                            top:-5px;
                            right: unset;
                            left: 22%;
                        }
                        .dash {
                            display: none;
                        }
                        .navbar-brand img {
                            display: none !important;
                        }
                    }
                </style>
                <div class="headerBox"><span class="dash">-------------------- </span>开发用测试环境<span class="dash"> --------------------</span></div>
                <!-- 开发测试用环境醒目 -->
EOF;
        }
    }
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    public static function afterRenderArchive($archive)
    {
        // 首先取得渲染后得到的缓冲区所有内容并阻止缓冲区输出到页面
        $html = ob_get_contents();
        ob_end_clean();

        $options = Helper::options();
        if ($options->cdn_add == ""){
            echo $html;
            return;
        } else{
            // 获取用户设置的CDN加速域名(在handsome后台设置)
            $cdnUrl = trim(explode("|",Helper::options()->cdn_add)[0]);
            $html = preg_replace( '/(href="|src="|url\()([^h][\/\\w\-_]*)(\.jpg|\.png|\.gif|\.svg|\.c|\.py|\.zip|\.pdf|\.mp4|\.mp3)(["|\)])/i', '$1' . $cdnUrl . '$2$3$4"', $html );

            echo $html;
        }
    }

    // 本意是在页面渲染之前修改掉设置项里的头图URL供后面的主题渲染，但实际测试行不通，即使修改掉了也会重新赋值，目前没有解决
    // 本钩子函数暂时保留(未使用)
    public static function beforeRenderArchive($archive)
    {
        $options = Helper::options();
        if ($options->cdn_add == ""){
            return;
        }
        
        // 获取用户设置的CDN加速域名(在handsome后台设置)
        $cdnUrl = trim(explode("|",Helper::options()->cdn_add)[0]);

        // 当前ArchiveWidgte指向的是列表的最后一项，需要在这里做循环处理，用next方法可依次从上到下取得列表文章的archive对象
        // 注意next一旦调用指针就会后移一个，为了不影响后面的主题渲染最后需要将指针还原（指向最后一个即可？？）
        while ($archive->next()) {
            $thmS = trim($archive->fields->thumbSmall);   // 小头图地址
            $thmB = trim($archive->fields->thumb);        // 大头图地址

            // 如果头图地址以h(ttp(s)://)开头，表示使用了绝对URL，不做处理
            // 否则在前面加上CDN加速域名
            if ($thmS != "" && $thmS[0]!='h') {
                $archive->fields->thumbSmall = $cdnUrl . $thmS;
            }
            if ($thmB != "" && $thmB[0]!='h') {
                $archive->fields->thumb = $cdnUrl . $thmB;
                $archive->fields->thumb = $cdnUrl . $thmB;
                $archive->fields->thumb = $cdnUrl . $thmB;
            }
        }
        
        // url(/usr/uploads/2021/04/4208328511.png) 替换成 url([cdn_add]/usr/uploads/2021/04/4208328511.png)
        //echo "111" . $html;
        //var_export($archive->stack[0]);
        //var_export(preg_replace( '/(url\()([^h][\/\\w\-_]*)(\.jpg|\.png|\.gif)\)/i', '$1' . $cdnUrl . '$2$3"', $html ));
    }

    /**
     * 内容页替换图片相对URL为CDN域名的URL（跟afterRenderArchive功能重复，内容保留但没有调用）
     * 
     * @access public
     * @return string
     */
    public static function contentEx( $html, $widget ) {
        
        $options = Helper::options();
        if ($options->cdn_add == ""){
            return $html;
        }

        $cdnUrl = trim(explode("|",Helper::options()->cdn_add)[0]);
        // src="/usr/uploads/2021/04/4208328511.png" 替换成 src="[cdn_add]/usr/uploads/2021/04/4208328511.png"
        $html = preg_replace( '/(href="|src=")([^h][\/\\w\-_]*)(\.jpg|\.png|\.gif)"/i', '$1' . $cdnUrl . '$2$3"', $html );
        return $html;
        
	}

    /**
     * 获取指定文章的自定义field设置内容（未使用）
     * 
     * @access public
     * @param $cid 文章cid
     * @param $key 自定义字段名
     * @return void
     */
    public static function getCustomOption($cid, $key){
        $db = Typecho_Db::get();
        $rows = $db->fetchAll($db->select('table.fields.str_value')->from('table.fields')
            ->where('table.fields.cid = ?', $cid)
            ->where('table.fields.name = ?', $key)
        );
        // 如果有多个值则存入数组
        foreach ($rows as $row) {
            $img = $row['str_value'];
            if (!empty($img)) {
                $values[] = $img;
            }
        }
        return $values;
    }
}
