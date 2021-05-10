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
 *          本插件主要是为了自己方便而写，非handsome官方插件，请自行承担使用风险。<br>
 *          作者：<a href="http://blog.mangolovecarrot.net">芒果爱吃胡萝卜</a><br><br>
 *          handsome主题增强功能<br>
 *          1. CDN相关。如果开启了CDN加速，首页头图如果指定的是相对地址的URL(比如/usr/upload/.../abc.jpg)，那么会自动切换成CDN加速域名(变成http://CDN-URL/usr/upload/.../abc.jpg)。<br>
 *          2. 代码块增强。可以为代码块添加行号显示，添加复制功能等。<br>
 *          3. 图片显示增强。文章图片强制居左等。<br>
 *          以下功能非handsome主题也可以使用
 *          静态首页功能（强烈推荐），定时读取当前首页内容并在网站根目录生成index.html，显著提高首页访问速度。比redis等全站缓存灵活可靠不会出现奇怪的问题。<br><br>
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
 * @version 1.1.0
 * @link https://github.com/slipperstree/HandsomeHelper
 */

class HandsomeHelper_Plugin implements Typecho_Plugin_Interface {

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

        //生成静态首页(禁用插件时需要删除action！)
        Helper::addAction('handsome-helper', 'HandsomeHelper_Action');

        //注册文章、页面保存时的 hook
        // Typecho_Plugin::factory('Widget_Contents_Post_Edit')->finishPublish = array('HandsomeHelper_Plugin', 'XXX');
        // Typecho_Plugin::factory('Widget_Contents_Post_Edit')->finishDelete = array('HandsomeHelper_Plugin', 'XXX');
        // Typecho_Plugin::factory('Widget_Contents_Page_Edit')->finishPublish = array('HandsomeHelper_Plugin', 'XXX');
        // Typecho_Plugin::factory('Widget_Contents_Page_Edit')->finishDelete = array('HandsomeHelper_Plugin', 'XXX');

        Typecho_Plugin::factory('admin/footer.php')->end = array('HandsomeHelper_Plugin', 'delayAjaxCmd');
        

        //正文页(渲染后显示前做替换)
        //Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('HandsomeHelper_Plugin', 'contentEx');

        //首页(主题渲染前将内容改掉即可)
        //Typecho_Plugin::factory('Widget_Archive')->beforeRender = array('HandsomeHelper_Plugin', 'beforeRenderArchive');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){
        Helper::removeAction('handsome-helper');
    }
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form) {

        // 点击手动刷新静态首页按钮时会回调过来并指定action
        if (isset($_GET['action']) && $_GET['action'] == 'refreshStaticIndex') {
            self::refreshStaticIndex();
        }

        if (Helper::options()->cdn_add != "") {
            $handsomeCDN = '<font color="blue">' . trim(explode("|",Helper::options()->cdn_add)[0]) . '</font>';
            $handsomeImagePostSuffix = '<font color="green">' . Helper::options()->imagePostSuffix . '</font>';
            $cdn = new Typecho_Widget_Helper_Form_Element_Checkbox('cdn',
            array(
                'EnableCDN4RelativePath' => '为相对路径的资源添加CDN域名和图片后缀<url><li>　　　当前handsome主题设置的CDN「' . $handsomeCDN . '」</li><li>　　　当前handsome主题设置的图片后缀「' . $handsomeImagePostSuffix . '」</li><li>　　　例</li><li>　　　启用前「src="/usr/upload/abc.jpg"」</li><li>　　　启用后「src="' . $handsomeCDN . '/usr/upload/abc.jpg?' . $handsomeImagePostSuffix . '"」</li>'
            ),
            array(''),
            _t('CDN增强'), _t('如果启用了handsome插件的「前台引入vditor.js接管前台解析」功能，本设置将失效。'));
            
        } else {
            $cdn = new Typecho_Widget_Helper_Form_Element_Checkbox('cdn',
            array(
                'EnableCDN4RelativePath' => '为相对路径的资源添加CDN域名和图片后缀<url><li>　　　<font color=red>当前handsome主题没有设置CDN(本地图片云存储(镜像)加速)，本设置无效。</font></li><li>　　　启用效果例</li><li>　　　启用前「src="/usr/upload/abc.jpg"」</li><li>　　　启用后「src="http://CDN域名/usr/upload/abc.jpg?图片压缩后缀"」</li>'
            ),
            array(''),
            _t('CDN增强'), _t('如果启用了handsome插件的「前台引入vditor.js接管前台解析」功能，本设置将失效。'));
        }
        
        $codeBlock = new Typecho_Widget_Helper_Form_Element_Checkbox('codeBlock',
            array(
                'EnableCodeShowLine' => '显示行号',
                'EnableCodeAllowCopy' => '添加复制按钮'
            ),
            array('EnableCodeShowLine', 'EnableCodeAllowCopy'),
            _t('代码块增强'), _t('如果启用了handsome插件的「前台引入vditor.js接管前台解析」功能，可能会有冲突，请关闭本设置。'));

        $image = new Typecho_Widget_Helper_Form_Element_Checkbox('image',
            array(
                'IsImageLeft' => '文章图片居左显示（handsome主题默认是居中显示，但PC端图片和文字有割裂感，特别是小图片的时候比较突兀）'
            ),
            array(''),
            _t('图片增强'), _t(''));

        $static = new Typecho_Widget_Helper_Form_Element_Checkbox('static',
            array(
                'EnableStaticIndex' => '启用静态首页'
            ),
            array(''),
            _t('启用静态首页（建议开启）'), _t('读取当前首页内容并在网站根目录生成index.html，显著提高首页访问速度。（记得提高站点默认文件index.html的优先级）<br>'));

        $form->addInput(new Group_Title('btnTitle', NULL, NULL, _t('速度增强设置'), NULL));
        $form->addInput($cdn);
        $form->addInput($static);

        //已启用静态首页功能，显示手动刷新按钮
        if (self::isUseStaticIndex()) {
            $file_time = @filemtime( '../index.html' );
            $btnRefreshStaticIndex = new Typecho_Widget_Helper_Form_Element_Submit();
            $btnRefreshStaticIndex->value(_t('手动更新静态首页'));
            $btnRefreshStaticIndex->description(_t('<font color=red>默认10分钟自动更新一次</font>，如果想立刻更新请点击该按钮。（最后更新时间：' . date( 'Y-m-d H:i:s', $file_time) . '）'));
            $btnRefreshStaticIndex->input->setAttribute('class', 'btn btn-s btn-warn btn-operate');
            $btnRefreshStaticIndex->input->setAttribute('formaction', Typecho_Common::url('/options-plugin.php?config=HandsomeHelper&action=refreshStaticIndex', Helper::options()->adminUrl));
            $form->addItem($btnRefreshStaticIndex);
        }
        
        $form->addInput(new Group_Title('btnTitle', NULL, NULL, _t('文章显示增强设置'), NULL));
        $form->addInput($codeBlock);
        $form->addInput($image);
        
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

        // 为相对路径的资源添加CDN域名和自定义后缀（多用于压缩）
        $cdn_add = $options->cdn_add;
        $imagePostSuffix = $options->imagePostSuffix;
        if ($enableCDN4RelativePath && $cdn_add != ""){
            // 获取用户设置的CDN加速域名(在handsome后台设置)
            $cdnUrl = trim(explode("|",$cdn_add)[0]);
            
            $fileExtends='\.jpg|\.png|\.gif|\.svg|\.c|\.py|\.zip|\.pdf|\.mp4|\.mp3';

            // 替换所有以siteUrl开头的资源文件$options->siteUrl
            // TODO: siteUrl尾部如果不带/则强制加上
            // 正则说明： url\(\'?  匹配 url(' 或者 url(  都可以（?表示可以匹配任意次但尽可能少的匹配，如果是两个问号表示匹配0到1次也是尽可能少的匹配）
            $siteUrlForRegx = str_replace("/","\\/", $options->siteUrl);
            $regxs='/(href="|src="|url\(\'?|window.open\(")(' . $siteUrlForRegx . ')([\/\\w\-_]*)(' . $fileExtends . ')(["|\)])/i';
            $html = preg_replace( $regxs, '$1' . $cdnUrl . '/$3$4$5', $html );
            
            // 替换所有使用相对路径的地方
            $regxs='/(href="|src="|url\(\'?|window.open\(")([^h][\/\\w\-_]*)(' . $fileExtends . ')/i';
            $html = preg_replace( $regxs, '$1' . $cdnUrl . '$2$3', $html );

            // 如果定义了图片后缀再加上后缀（多用于压缩）
            if ($imagePostSuffix != "") {
                $imageExtends='\.jpg|\.png|\.gif|\.svg';
                $regxs='/(href="|src="|url\(\'?|window.open\(")(' . str_replace("/","\\/", $cdnUrl) . ')([\/\\w\-_]*)(' . str_replace("/","\\/", $imageExtends) . ')/i';
                $html = preg_replace( $regxs, '$1$2/$3$4?' . $imagePostSuffix, $html );
            }
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
            // 获取typecho版本
            // 形如 generator = Typecho 1.1/17.10.30
            $typechoVersion = trim(explode("/",$options->generator)[0]);
            $fixLine = "";
            // 1.0版本的换行数会比1.1版本的换行数多一行，需要减掉
            if ($typechoVersion === "Typecho 1.0") {
                $fixLine = " - 1";
            } elseif ($typechoVersion === "Typecho 1.1") {
                $fixLine = "";
            }
            ?>
                <script>
                    $(document).ready(function() {
                        $('pre code').each(function(){
                        var lines = $(this).text().split('\n').length <?php echo $fixLine; ?> ;
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

        // 图片增强设置 ---------------------------------------------------------- S
        $optImage = $myOptions->image;
        $isImageLeft = false;
        foreach($optImage as $item){
            if ($item === "IsImageLeft") $isImageLeft = true;
        }

        // 图片强制居左显示
        if ($isImageLeft) {
            ?>
            <style>
                #post-content img {
                    margin: 10px 0px;
                }

                figcaption.post-img-figcaption {
                    text-align: left;
                }
            </style>
            <?php
        }
        // 图片增强设置 ---------------------------------------------------------- E
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

    // 手动刷新首页静态页面
    public static function refreshStaticIndex($contents = null, $edit = null)
    {
        //首先判断是否启用了该功能
        $enableStaticIndex = self::isUseStaticIndex();

        if ($edit != null) {
            //如果是增删改文章hook进来的话不能直接调用，情况比较复杂，特别是当对多个文件操作（删除）时，
            //调用插件的位置不是在所有操作都完成以后而是循环调用，所以不能取得正确结果
            //所以采用在客户端埋下js代码，页面完成刷新后让浏览器请求一次刷新API，不在这里做，放在管理页面footer共通部分去了
            if ($enableStaticIndex) {
                //调用action NG
                //Typecho_Widget::widget('HandsomeHelper_Action')->makeIndexHtml();
            }

        } else {
            //如果是插件设置界面点手动刷新进来 OK
            if ($enableStaticIndex) {
                if (Typecho_Widget::widget('HandsomeHelper_Action')->makeIndexHtml()) {
                    Typecho_Widget::widget('Widget_Notice')->set(_t("静态首页index.html更新成功，去首页试试效果吧"), 'success');
                } else {
                    Typecho_Widget::widget('Widget_Notice')->set(_t("网站根目录没有写入权限，请给网站根目录777权限"),
                    'error');
                }
            } else {
                Typecho_Widget::widget('Widget_Notice')->set(_t("没有启用静态首页功能。"), 'error');
            }
        }
    }

    // 管理后台某些操作时强制刷新缓存
    // TODO: 一般来说，增删改文章或页面时才需要，但性能上没什么大问题所以暂时先这样了，而且每次刷新管理页面都做缓存的话比较保险，因为偶尔ajax会失败
    public static function delayAjaxCmd()
    {
        if (self::isUseStaticIndex()) {
            ?>
            <script>
                $(document).ready(function() {
                    $.get('/action/handsome-helper?do=createStaticIndex&force=y');
                });
            </script>
            <?php
        } else {
            // TODO: 如果没有开启首页静态功能，尝试删除index.html文件（正确的位置应该不在这里而是在设置页面的保存按钮按下时和插件禁用的时候）
        }
    }

    public static function isUseStaticIndex(){
        //检查有无启用静态首页功能
        $enableStaticIndex = false;
        //在config函数中试图使用自身的插件设置有个小问题
        //在启用插件和设置插件的时候都会调用config函数
        //但启用的时候，DB中尚不存在任何设置所以是找不到的，这里简单的try了一下
        //启用以后数据库中就用插件的设置了，下次设置的时候就不会出错了
        try {
            $myPlugin = Helper::options()->plugin('HandsomeHelper');
            if ($myPlugin != null) {
                $optStatic = Helper::options()->plugin('HandsomeHelper')->static;
                if($optStatic != null) {
                    foreach($optStatic as $item){
                        if ($item === "EnableStaticIndex") $enableStaticIndex = true;
                    }
                }
            }
        } catch (\Throwable $th) {
            //只会在启用插件时发生异常，无视即可，什么也不做
            //throw $th;
        }

        return $enableStaticIndex;
    }
}

class Group_Title extends Typecho_Widget_Helper_Form_Element
{
    public function label($value)
    {
        /** 创建标题元素 */
        if (empty($this->label)) {
            $this->label = new Typecho_Widget_Helper_Layout('label', array('class' => 'typecho-label', 'style' => 'font-size: 2em;border-bottom: 1px #ddd solid;padding-top:2em;'));
            $this->container($this->label);
        }

        $this->label->html($value);
        return $this;
    }

    public function input($name = NULL, array $options = NULL)
    {
        $input = new Typecho_Widget_Helper_Layout('p', array());
        $this->container($input);
        $this->inputs[] = $input;
        return $input;
    }

    protected function _value($value)
    {
    }
}