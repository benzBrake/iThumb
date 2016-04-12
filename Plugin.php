<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * TE题图插件：<a href="http://blog.iplayloli.com/typecho-plugin-iThumb.html">发布页面</a>|<a href="https://github.com/Char1sma/iThumb">使用帮助</a>
 * 感谢<a href="http://dt27.org/">DT27</a>
 *
 * @package iThumb
 * @author Ryan<github-charisma@32mb.cn>
 * @version 0.1.3
 * @link http://blog.iplayloli.com/
 */
 class iThumb_Plugin implements Typecho_Plugin_Interface {
	 /**
	 * 激活插件方法,如果激活失败,直接抛出异常
	 *
	 * @access public
	 * @return String
	 * @throws Typecho_Plugin_Exception
	 */
	 public static function activate()
	{
		Typecho_Plugin::factory('admin/write-post.php')->option = array('iThumb_Plugin', 'addOption');
		Typecho_Plugin::factory('admin/write-post.php')->bottom = array('iThumb_Plugin', 'addFooter');
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
	public static function config(Typecho_Widget_Helper_Form $form){}
	/**
	 * 个人用户的配置面板
	 *
	 * @access public
	 * @param Typecho_Widget_Helper_Form $form
	 * @return void
	 */
	public static function personalConfig(Typecho_Widget_Helper_Form $form){}
	/**
	 * 附加JS和CSS
	 *
	 * @access public
	 * @return void
	 */
	public static function addOption()
	{
		echo '<section class="typecho-post-option">
	<label for="token-input-tags" class="typecho-label">题图</label>
	<div id="thumb-preview-area" style="border:3px dashed #D9D9D6;background-color:#FFF;color:#999;min-height: 35px;" class="p">
	<a class="selectThumb">选择图片</a>
	<a class="deleteThumb hide">删除图片</a>
	</div>
</section>';
	}
	public static function addFooter()
	{
?>
<div id="thumbPanel" class="hide">
<div class='outerPanel'>
</div>
<div class="innerPanel">
<h1 class="nomp"><?php _e('题图'); ?></h1>
<button type="button" class="thumb-panel-close">X</button>
<div id="ListOnThumbPanel">
</div>
</div>
</div>
<style>
.hide {
	display:none;
}
.nomp {
	padding: 0px;
	margin: 0px;
}
#thumb-preview-area > a:hover {
	cursor:pointer;
}
.outerPanel {
	position: fixed;
	top: 0px;
	left: 0px;
	right: 0px;
	bottom: 0px;
	z-index: 300;
	background: #000 none repeat scroll 0% 0%;
	opacity: 0.7;
}
.innerPanel {
	background: #f3f3f3;
	z-index: 301;
	position: fixed;
	top: 30px;
	right: 30px;
	left: 30px;
	bottom: 30px;
	padding: 20px;
}
.thumb-panel-close {
	position: fixed;
	right: 50px;
	top: 50px;
	border: none;
}
#uploadlink {
    position: fixed;
    top: 70px;
    left: 8em;
}
#ListOnThumbPanel ul {}
#ListOnThumbPanel ul>li {
	display:inline-block;
	margin:5px;
	border:1px dashed;
}
#ListOnThumbPanel ul>li>*:hover {
	cursor:pointer;
}
</style>
	<script>
        $(document).ready(function () {
			timThumb = "<?php echo Helper::options()->pluginUrl.'/iThumb/timthumb.php'; ?>";
			$('.thumb-panel-close').click(function() {
				$('#thumbPanel').addClass('hide');
			});
			function previewThumb(url) {
				thumbimg = $('img',$('#thumb-preview-area'));
				if (thumbimg.length) {
					thumbimg.attr('src',timThumb + '?src=' + url +'&h=250&w=250&zc=1');
				} else {
					$('#thumb-preview-area').append('<img src="'+ timThumb + '?src=' + url +'&h=250&w=250&zc=1" />');
				}
			}
			function refleshThumbList() {
				thumbs = $('img',$('#ListOnThumbPanel'));
				if (thumbs.length) thumbs.parent().parent().remove();
				fileList = $('li',$('#file-list'));
				ListOnThumbPanel = $('#ListOnThumbPanel');
				ThumbListHtml = "<ul>";
				for(ttt=0;ttt<fileList.length;ttt++) {
					var link = fileList.eq(ttt).attr('data-url');
					var name = $('a',fileList.eq(ttt)).eq(0).text();
					var ext = link.substring(link.lastIndexOf(".")).toUpperCase();
					if (ext=='.JPG'||ext=='.JPEG'||ext=='.PNG'||ext=='.GIF'||ext=='.TIFF') 
						ThumbListHtml = ThumbListHtml +'<li data-url="'+ link +'"><img src="'+ timThumb + '?src=' + link +'&h=250&w=250&zc=1" /></li>';
				}
				ThumbListHtml = ThumbListHtml + "</ul>";
				ListOnThumbPanel.append(ThumbListHtml);
				$('img',$('#ListOnThumbPanel')).click(function() {
				$("[value='thumbUrl']").parent().parent().children('td:eq(2)').children('textarea').val($(this).parent().attr('data-url'));
				previewThumb($(this).parent().attr('data-url'));
				$('.deleteThumb').removeClass('hide');
				$('#thumbPanel').addClass('hide');
			});
			}
			$('.selectThumb').click(function() {
				refleshThumbList();
				$('#thumbPanel').removeClass('hide');
			})
            var btn = $('i', $('#custom-field-expand'));
            if (btn.hasClass('i-caret-right')) {
                btn.removeClass('i-caret-right').addClass('i-caret-down');
                $('#custom-field-expand').parent().removeClass('fold');
            }
            if (!$('[name="fieldNames[]"]').is("[value='thumbUrl']")) {
                if($('[name="fieldNames[]"]').last().val()!=""){
                    var html = '<tr><td><input type="text" name="fieldNames[]" placeholder="<?php _e('字段名称'); ?>" class="text-s w-100"></td>'
                            + '<td><select name="fieldTypes[]" id="">'
                            + '<option value="str"><?php _e('字符'); ?></option>'
                            + '<option value="int"><?php _e('整数'); ?></option>'
                            + '<option value="float"><?php _e('小数'); ?></option>'
                            + '</select></td>'
                            + '<td><textarea name="fieldValues[]" placeholder="<?php _e('字段值'); ?>" class="text-s w-100" rows="2"></textarea></td>'
                            + '<td><button type="button" class="btn btn-xs"><?php _e('删除'); ?></button></td></tr>',
                        el = $(html).hide().appendTo('#custom-field table tbody').fadeIn();
                    attachDeleteEvent(el);
                }
                $('[name="fieldTypes[]"]').last().find("option[value='str']").attr("selected",true).siblings().remove();
                $('[name="fieldNames[]"]').last().attr('value', 'thumbUrl').attr('readonly','readonly');
                $('[name="fieldValues[]"]').last().attr('placeholder','文章缩略图，推荐尺寸：700px*220px');
                var html = '<tr><td><input type="text" name="fieldNames[]" placeholder="<?php _e('字段名称'); ?>" class="text-s w-100"></td>'
                        + '<td><select name="fieldTypes[]" id="">'
                        + '<option value="str"><?php _e('字符'); ?></option>'
                        + '<option value="int"><?php _e('整数'); ?></option>'
                        + '<option value="float"><?php _e('小数'); ?></option>'
                        + '</select></td>'
                        + '<td><textarea name="fieldValues[]" placeholder="<?php _e('字段值'); ?>" class="text-s w-100" rows="2"></textarea></td>'
                        + '<td><button type="button" class="btn btn-xs"><?php _e('删除'); ?></button></td></tr>',
                    el = $(html).hide().appendTo('#custom-field table tbody').fadeIn();
                attachDeleteEvent(el);
            }else{
				var thumbarea = $("[value='thumbUrl']").parent().parent().children('td:eq(2)').children('textarea');
                thumbarea.attr('placeholder','文章缩略图，推荐尺寸：700px*220px');
				if (thumbarea.val()!='') previewThumb(thumbarea.val());
				$('.deleteThumb').removeClass('hide');
            }
			$('.deleteThumb').click(function() {
				$('.deleteThumb').addClass('hide');
				$('img',$('#thumb-preview-area')).remove();
				$("[value='thumbUrl']").parent().parent().children('td:eq(2)').children('textarea').val('');
			});
        });
	</script>
<?php
	}
 }
 ?>