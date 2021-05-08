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
 *          1. 如果开启了CDN加速，首页头图如果指定的是相对地址的URL(比如/usr/upload/.../abc.jpg)，那么会自动切换成CDN加速域名(变成http://CDN-URL/usr/upload/.../abc.jpg)。<br>
 *          2. 可以为代码块添加行号显示，可以为代码块添加一个复制按钮。<br><br>
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

        //后台新建文章编辑器
        Typecho_Plugin::factory('admin/write-post.php')->richEditor = array('HandsomeHelper_Plugin', 'Editor');
        Typecho_Plugin::factory('admin/write-page.php')->richEditor = array('HandsomeHelper_Plugin', 'Editor');
        Typecho_Plugin::factory('admin/write-post.php')->bottom = array('HandsomeHelper_Plugin', 'EditorEnd');
        

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
    public static function config(Typecho_Widget_Helper_Form $form) {
        $cdn = new Typecho_Widget_Helper_Form_Element_Checkbox('cdn',
            array(
                'EnableCDN4RelativePath' => '为相对路径的资源添加CDN域名(当前handsome主题的CDN设置：「' . Helper::options()->cdn_add . '」)'
            ),
            array(''),
            _t('CDN增强设置'), _t('如果启用了handsome插件的「前台引入vditor.js接管前台解析」功能，本设置将失效。'));
        $codeBlock = new Typecho_Widget_Helper_Form_Element_Checkbox('codeBlock',
            array(
                'EnableCodeShowLine' => '显示行号',
                'EnableCodeAllowCopy' => '添加复制按钮'
            ),
            array('EnableCodeShowLine', 'EnableCodeAllowCopy'),
            _t('代码块增强设置'), _t('如果启用了handsome插件的「前台引入vditor.js接管前台解析」功能，可能会有冲突，请关闭本设置。'));
        
        $form->addInput($cdn);
        $form->addInput($codeBlock);
    }

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
        $myOptions = $options->plugin('HandsomeHelper');

        // CDN增强设置 --------------------------------------------------------- S
        $optCDN = $myOptions->cdn;
        $enableCDN4RelativePath = false;
        foreach($optCDN as $item){
            if ($item === "EnableCDN4RelativePath") $enableCDN4RelativePath = true;
        }

        // 为相对路径的资源添加CDN域名
        $cdn_add = $options->cdn_add;
        if ($enableCDN4RelativePath && $cdn_add != ""){
            // 获取用户设置的CDN加速域名(在handsome后台设置)
            $cdnUrl = trim(explode("|",$cdn_add)[0]);
            
            $fileExtends='\.jpg|\.png|\.gif|\.svg|\.c|\.py|\.zip|\.pdf|\.mp4|\.mp3';

            // 替换所有以siteUrl开头的资源文件$options->siteUrl
            // TODO siteUrl尾部如果不带/则强制加上
            $siteUrlForRegx = str_replace("/","\\/", $options->siteUrl);
            $regxs='/(href="|src="|url\(|window.open\(")(' . $siteUrlForRegx . ')([\/\\w\-_]*)(' . $fileExtends . ')(["|\)])/i';
            $html = preg_replace( $regxs, '$1' . $cdnUrl . '/$3$4$5', $html );
            
            // 替换所有使用相对路径的地方
            // TODO 如果handsome设置了图片的压缩格式，需要添加在末尾
            $regxs='/(href="|src="|url\(|window.open\(")([^h][\/\\w\-_]*)(' . $fileExtends . ')/i';
            $html = preg_replace( $regxs, '$1' . $cdnUrl . '$2$3', $html );
        }

        echo $html;
        // CDN增强设置 --------------------------------------------------------- E

        // 代码块增强设置 -------------------------------------------------------- S
        $optCodeBlock = $myOptions->codeBlock;
        $enableCodeShowLine = false;
        $enableCodeAllowCopy = false;
        foreach($optCodeBlock as $item){
            if ($item === "EnableCodeShowLine") $enableCodeShowLine = true;
            if ($item === "EnableCodeAllowCopy") $enableCodeAllowCopy = true;
        }
        
        // 为代码高亮加行号
        if($enableCodeShowLine){
            ?>
                <script>
                    $(document).ready(function() {
                        $('pre code').each(function(){
                        var lines = $(this).text().split('\n').length - 1;
                        var $numbering = $('<ul/>').addClass('pre-numbering hljs');
                        $(this)
                            .addClass('has-numbering')
                            .parent()
                            .append($numbering);
                        for(i=1;i<=lines;i++){
                            $numbering.append($('<li/>').text(i));
                        }
                        });
                    });
                </script>
                <style>
                    pre {
                        position: relative;
                        margin-bottom: 24px;
                        border-radius: 3px;
                        border: 1px solid #C3CCD0;
                        background: #FFF;
                        overflow: hidden;
                        }
                    code {
                        display: block;
                        padding: 12px 24px;
                        overflow-y: auto;
                        font-weight: 300;
                        font-family: Menlo, monospace;
                        font-size: 0.8em;
                        }
                    code.has-numbering {
                        padding-left: 60px !important;
                    }
                    .pre-numbering {
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 50px;
                        /* padding: 12px 2px 12px 0; */
                        border-right: 1px solid #C3CCD0;
                        /* border-right: 1px solid #6ce26c; */
                        /* border-radius: 3px 0 0 3px; */
                        background-color: rgba(0, 0, 0, 0);
                        text-align: right;
                        margin: 0px !important;
                    }
                    .pre-numbering li {
                        list-style-type:none;
                        text-align:right;
                    }
                </style>
            <?php
        }

        // 为代码高亮添加一个复制按钮
        if($enableCodeAllowCopy){
            ?>
                <!-- https://github.com/zenorocha/clipboard.js -->
                <script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.8/dist/clipboard.min.js"></script>
                <script>
                    $(document).ready(function() {
                        var pres = $("pre");
                        if (pres.length) {
                            pres.each(function() {
                                var t = $(this).children("code").text();
                                var btn = $('<span class="copy">复制代码</span>').attr("data-clipboard-text",t);
                                $(this).prepend(btn);
                                var c = new ClipboardJS(btn[0]);
                                c.on("success", function() {
                                    btn.addClass("copyed").text("复制成功");
                                });
                                c.on("error", function() {
                                    btn.text("复制失败");
                                });
                                btn.mouseleave(function() {
                                    btn.text("复制代码").removeClass("copyed");
                                });
                            });
                        }
                    });
                </script>
                <style>
                    /*添加按钮*/
                    pre  {
                        position: relative;
                    }
                    pre > span {
                        position: absolute;
                        top: 0;
                        right: 0;
                        width: 20%;
                        border-radius: 2px;
                        text-align: center;
                        padding: 0 10px;
                        font-size: 14px;
                        background: rgba(100, 100, 100, 0.6);
                        color: #fff;
                        cursor: pointer;
                        display:none;
                        z-index: 999;
                    }
                    pre:hover > span {
                        display:block;
                    }
                    pre > .copyed {
                        background: #67c23a;
                    }
                </style>
            <?php
        }
        // 代码块增强设置 -------------------------------------------------------- E
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

    /**
     * 插入编辑器
     */
    public static function Editor($post)
    {
    }

    public static function EditorEnd($post)
    {
        ?>
        <script>
        $(document).ready(function() {
            var btnRow = $("#wmd-code-button");
            btnRow.after('<li class="wmd-button" id="wmd-dplayer-button" style="" title="测试"><img width="20px" src="/usr/plugins/HandsomeHelper/assets/icons/codeblock.png"></li>');
            //debugger
            //alert($("#wmd-album-button"));
        });
        </script>
        <?php
    }

    
}
