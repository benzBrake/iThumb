<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * iThumb：Typecho 题图插件 | <a href="https://doufu.ru/archives/typecho-plugin-ithumb.html">发布页面</a>
 * @package iThumb
 * @author Ryan<github-benzbrake@woai.ru>
 * @version 0.1.4
 * @link https://doufu.ru
 */
class iThumb_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return String
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        if (array_key_exists('ArticleImg', Typecho_Plugin::export()['activated'])) {
            throw new Typecho_Plugin_Exception('启用失败，与 ArticleImg 插件不兼容');
        }

        // 文章编辑
        Typecho_Plugin::factory('admin/write-post.php')->option = array(__CLASS__, 'addOption');
        Typecho_Plugin::factory('admin/write-post.php')->bottom = array(__CLASS__, 'addFooter');
        Typecho_Plugin::factory('Widget_Contents_Post_Edit')->finishPublish = array(__CLASS__, "applyThumb");

        // 页面编辑
        Typecho_Plugin::factory('admin/write-page.php')->option = array(__CLASS__, 'addOption');
        Typecho_Plugin::factory('admin/write-page.php')->bottom = array(__CLASS__, 'addFooter');
        Typecho_Plugin::factory('Widget_Contents_Page_Edit')->finishPublish = array(__CLASS__, "applyThumb");

        // 前台
        Typecho_Plugin::factory('Widget_Abstract_Contents')->filter = array(__CLASS__, 'render');
    }
/**
 * 禁用插件方法,如果禁用失败,直接抛出异常
 *
 * @static
 * @access public
 * @return void
 * @throws Typecho_Plugin_Exception
 */
    public static function deactivate()
    {}
    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $defaultUrl = new Typecho_Widget_Helper_Form_Element_Text('default_image', null, _t(''), _t('默认文章图片URL'), _t('在这里输入默认的图片URL，示例<code>https://ooo.0o0.ooo/2017/02/13/58a165406ce28.png</code>'));
        $form->addInput($defaultUrl);
    }
    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {}
    /**
     * 把题图写入数据库
     *
     * @return void
     * @date 2020-04-12
     */
    public static function applyThumb($contents, $post)
    {
        $thumbUrl = $post->request->get('ithumb-url', '');
        self::setField('thumb', 'str', $thumbUrl, $post->cid);
    }
    /**
     * 获取所有字段
     *
     * @access public
     * @param int $cid
     * @return Typecho_Config
     */
    private static function getFields($cid)
    {
        $db = Typecho_Db::get();
        $fields = array();
        $rows = $db->fetchAll($db->select()->from('table.fields')
                ->where('cid = ?', $cid));

        foreach ($rows as $row) {
            $fields[$row['name']] = $row[$row['type'] . '_value'];
        }
        return new Typecho_Config($fields);
    }
    /**
     * 设置单个字段
     *
     * @param string $name
     * @param string $type
     * @param string $value
     * @param integer $cid
     * @access private
     * @return integer
     */
    private function setField($name, $type, $value, $cid)
    {
        $db = Typecho_Db::get();
        if (empty($name) || !in_array($type, array('str', 'int', 'float'))) {
            return false;
        }

        $exist = $db->fetchRow($db->select('cid')->from('table.fields')
                ->where('cid = ? AND name = ?', $cid, $name));

        if (empty($exist)) {
            return $db->query($db->insert('table.fields')
                    ->rows(array(
                        'cid' => $cid,
                        'name' => $name,
                        'type' => $type,
                        'str_value' => 'str' == $type ? $value : null,
                        'int_value' => 'int' == $type ? intval($value) : 0,
                        'float_value' => 'float' == $type ? floatval($value) : 0,
                    )));
        } else {
            return $db->query($db->update('table.fields')
                    ->rows(array(
                        'type' => $type,
                        'str_value' => 'str' == $type ? $value : null,
                        'int_value' => 'int' == $type ? intval($value) : 0,
                        'float_value' => 'float' == $type ? floatval($value) : 0,
                    ))
                    ->where('cid = ? AND name = ?', $cid, $name));
        }
    }
    /**
     * 附加JS和CSS
     *
     * @access public
     * @return void
     */
    public static function addOption($post)
    {
        $fields = self::getFields($post->cid);
        $thumb = "";
        if (isset($fields->thumb)) {
            $thumb = $fields->thumb;
        }
        ?>
<section class="typecho-post-option">
	<label for="ithumb-url" class="typecho-label"><?php _e('题图');?></label>
	<p><input id="ithumb-url" name="ithumb-url" type="text" value="<?php echo $thumb; ?>" class="hide w-100 text" /></p>
	<div id="thumb-preview-area">
		<?php if ($thumb !== ""): ?>
		<div class="thumb-preview-area-header">
			<a class="selectThumb"><?php _e('选择图片');?></a>
			<a class="deleteThumb"><?php _e('删除图片');?></a>
		</div>
		<div>
			<img src="<?php echo $thumb; ?>" />
		</div>
		<?php else: ?>
		<div class="thumb-preview-area-header">
			<a class="selectThumb"><?php _e('选择图片');?></a>
			<a class="deleteThumb hide"><?php _e('删除图片');?></a>
		</div>
		<?php endif;?>
	</div>
</section>
<?}
    public static function addFooter()
    {
        ?>
<div id="thumb-panel" class="hide">
	<div class='outer-panel'>
		<div class="inner-panel">
			<header class="thumb-panel-header clearfix">
				<h1 class="thumb-panel-title"><?php _e('题图');?></h1>
				<div class="right">
					<a id="upload-link" class="thumb-panel-btn">上传</a>
					<button type="button" class="thumb-panel-btn thumb-panel-close">X</button>
				</div>
			</header>
			<section id="thumb-list-panel">
			</section>
		</div>
	</div>
</div>
<style>
.hide {
	display:none;
}
#thumb-preview-area {
	border: 1px solid #D9D9D6;
    max-height: 240px;
    overflow: auto;
    background-color: #FFF;
    border-radius: 2px;
	margin: 1em 0;
}
#thumb-preview-area > div {
	padding: 6px 12px;
}
#thumb-preview-area .thumb-preview-area-header {
	margin-bottom: .5em;
	border-bottom: 1px solid #D9D9D6;
	display: flex;
}
#thumb-preview-area .thumb-preview-area-header .deleteThumb {
	margin-left: auto;
}
#thumb-preview-area img {
	width: 100%;
}
#thumb-preview-area .thumb-preview-area-header a:hover {
	cursor:pointer;
}
#thumb-panel .outer-panel {
	position: fixed;
	top: 0px;
	left: 0px;
	right: 0px;
	bottom: 0px;
	z-index: 300;
	background-color: rgba(0,0,0, .7);
}
#thumb-panel .inner-panel {
	background: #f3f3f3;
	position: fixed;
	top: 30px;
	right: 30px;
	left: 30px;
	bottom: 30px;
	padding: 20px;
}
#thumb-panel .thumb-panel-header {
	display: flex;
	align-items: center;
}
#thumb-panel .thumb-panel-title {
    margin: 0;
}
#thumb-panel .thumb-panel-header .right {
	margin-left: auto;
}
#thumb-panel .thumb-panel-btn {
	border: none;
	padding: 5px 10px;
	background-color: #E9E9E6;
	color: #666;
	border: 2px solid #ccc;
    -moz-border-radius: 2px;
    -webkit-border-radius: 2px;
    border-radius: 2px;
	outline: none;
	transition: background-color .2s;
	text-decoration: none;
}
#thumb-panel #upload-link {
	display: none;
}
#thumb-panel .thumb-panel-btn:hover {
    background-color: #467B96;
    cursor: pointer;
    color: #FFF;
}
#thumb-panel .thumb-panel-close:hover {
	background-color: coral;
}
#uploadlink {
	display: none;
}
#thumb-list-panel {
	border:3px dashed #D9D9D6;
	background-color:#FFF;
	color:#999;
	min-height: 35px;
}
#thumb-list-panel ul {
    margin: 0;
    padding: 1em;
    width: 100%;
    box-sizing: border-box;
	display: flex;
	flex-flow: wrap;
}
#thumb-list-panel ul > li {
	display: block;
	width: 25%;
}
#thumb-list-panel ul > li .img {
    background-size: cover;
    height: 240px;
    border: 1px dashed;
    margin: .5em;
	display: flex;
	flex-direction: column-reverse;
	align-items: center;
}
#thumb-list-panel ul > li .img:hover {
	cursor: pointer;
}
#thumb-list-panel ul > li cite {
    font-style: normal;
    padding: .5em;
    display: block;
    background: rgba(0, 0, 0, .45);
    width: 100%;
    text-align: center;
    color: #fff;
}
</style>
	<script>
        $(document).ready(function () {
			$(":input[name='fields[thumb]']", $("#custom-field")).parents('tr').hide();
			$(":input[value='thumb']", $("#custom-field")).parents('tr').hide();
			$('.thumb-panel-close').click(function() {
				$('#thumb-panel').addClass('hide');
			});
			// 在题图框预览图片
			function previewThumb(url) {
				var thumbimg = $('img',$('#thumb-preview-area'));
				if (thumbimg.length) {
					thumbimg.attr('src', url);
				} else {
					$('#thumb-preview-area').append('<div><img src="'+ url + '"/></div>');
				}
				$('.deleteThumb').removeClass('hide');
			}
			// 刷新图片选择框中的图片
			function refleshAttachThumbs() {
				var panel = $('#thumb-list-panel');
				var thumbs = $('.img', panel);
				if (thumbs.length) thumbs.parents('ul').remove();
				var fileList = $('li', $('#file-list'));
				var html = "<ul>";
				for(times=0; times < fileList.length; times++) {
					var link = fileList.eq(times).data('url');
					var isImage = fileList.eq(times).data('image');
					var cid = fileList.eq(times).data('cid');
					var name = $('a',fileList.eq(times)).eq(0).text();
					if (isImage == 1)
						html +='<li data-cid="' + cid +'" data-url="'+ link +'"><div class="img" style="background-image:url(' + link + ')"><cite>' + name + '</cite></div></li>';
				}
				html += "</ul>";
				panel.append(html);
				// 设置题图
				$('.img', panel).click(function() {
					$("#ithumb-url").val($(this).parents('li').data('url'));
					previewThumb($(this).parents('li').data('url'));
					$('.deleteThumb').removeClass('hide');
					$('#thumb-panel').addClass('hide');
				});
			}
			// 显示图片选择框
			$('.selectThumb').click(function() {
				refleshAttachThumbs();
				$('#thumb-panel').removeClass('hide');
			})
			// 展开自定义字段
            var btn = $('i', $('#custom-field-expand'));
            if (btn.hasClass('i-caret-right')) {
                btn.removeClass('i-caret-right').addClass('i-caret-down');
                $('#custom-field-expand').parent().removeClass('fold');
            }
			// 删除题图
			$('.deleteThumb').click(function() {
				$('.deleteThumb').addClass('hide');
				$('img',$('#thumb-preview-area')).parent().remove();
				$("#ithumb-url").val('');
			});
        });
	</script>
<?php
}
    /**
     * 获取题图
     *
     * @param int $cid
     * @return String
     * @date 2020-04-12
     */
    public static function render($value, $archive)
    {
        if (!(isset($value['thumb']))) {
            $value['thumb'] = "";
        }
        if (empty($value['thumb'])) {
            preg_match_all("/<[img|IMG].*?src=[\'|\"](.*?)[\'|\"].*?[\/]?>/", $archive->content, $matches);
            if (isset($matches[1])) {
                foreach ($matches[1] as $v) {
                    if (strpos($thumb, __TYPECHO_PLUGIN_DIR__ . "/TePass") !== false) {
                        $value['thumb'] = $v;
                        break;
                    }
                }
            }
        }
        if (empty($value['thumb'])) {
            $value['thumb'] = Helper::options()->plugin('iThumb')->default_image;
        }
        if (empty($value['thumb'])) {
            $value['thumb'] = Helper::options()->pluginUrl . '/iThumb/images/' . mt_rand(0, 9) . '.jpg';
        }
        return $value;
    }
}