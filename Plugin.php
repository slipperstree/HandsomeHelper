<?php
/**
 * Handsome主题的辅助插件 <a href="https://github.com/slipperstree/HandsomeHelper">GitHubPage</a><br>
 * 创建日期：20210427 更新日期：<br>
 * 1 - 如果开启了CDN加速，首页头图如果指定的是相对域名或者是当前站点的域名，那么会自动切换成CDN加速域名。
 * 
 * @package Handsome Helper
 * @author mango
 * @version 0.1.0
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
}
