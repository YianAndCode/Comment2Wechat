<?php
/**
 * 微信推送评论通知
 * 
 * @package Comment2Wechat
 * @author Y!an
 * @version 1.0.0
 * @link https://yian.me
 */
class Comment2Wechat_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
    
        Typecho_Plugin::factory('Widget_Feedback')->comment = array('Comment2Wechat_Plugin', 'sc_send');
        Typecho_Plugin::factory('Widget_Feedback')->trackback = array('Comment2Wechat_Plugin', 'sc_send');
        Typecho_Plugin::factory('Widget_XmlRpc')->pingback = array('Comment2Wechat_Plugin', 'sc_send');
        
        return _t('请配置此插件的 SCKEY, 以使您的微信推送生效');
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
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $key = new Typecho_Widget_Helper_Form_Element_Text('sckey', NULL, NULL, _t('SCKEY'), _t('SCKEY 需要在 <a href="http://sc.ftqq.com/">Server酱</a> 注册<br />
        同时，注册后需要在 <a href="http://sc.ftqq.com/">Server酱</a> 绑定你的微信号才能收到推送'));
        $form->addInput($key->addRule('required', _t('您必须填写一个正确的 SCKEY')));
    }
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    /**
     * 微信推送
     * 
     * @access public
     * @param array $comment 评论结构
     * @param Typecho_Widget $post 被评论的文章
     * @return void
     */
    public static function sc_send($comment, $post)
    {
        $options = Typecho_Widget::widget('Widget_Options');

        $sckey = $options->plugin('Comment2Wechat')->sckey;

        $text = "有人在您的博客发表了评论";
        $desp = "**".$comment['author']."** 在你的博客中说到：\n\n > ".$comment['text'];

        $postdata = http_build_query(
            array(
                'text' => $text,
                'desp' => $desp
                )
            );

        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata
                )
            );
        $context  = stream_context_create($opts);
        $result = file_get_contents('http://sc.ftqq.com/'.$sckey.'.send', false, $context);
        return  $comment;
    }
}
