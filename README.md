## iThumb
Typecho文章缩略图(Post Thumbnail)插件
~~基于[SlantedExtend](https://github.com/DT27/SlantedExtend "SlantedExtend")的`thumbUrl`自定义字段增强增加了图片选择框。~~
由于和主题自定义字段冲突，导致本插件使用JS设置题图自定义字段不生效，目前版本改成数据库查询方式修改自定义字段。
图片选择是从当前文章附件列表选取的，目前不支持从上传的所有文件中选取。
### Version
> 0.1.4 修改自定义字段名称，修改自定义字段提交方式，新增默认题图。

### Usage
插件目录必须为iThumb
前台通过自定义字段获取缩略图链接
```php
<?php $this->thumb(); ?>
```
缩略图获取顺序：设置的题图->文章图片->插件配置里的默认图片->随机图片
### Thanks
[DT27](https://dt27.org/ "DT27")
[TimThumb](https://www.binarymoon.co.uk/projects/timthumb/ "TimThumb")
### Plugin License
>  Copyright © 2016-2020 Ryan
>  [Mozilla Public License Version 2.0](https://www.mozilla.org/en-US/MPL/2.0/ "Mozilla Public License Version 2.0")

### Preview
![ithumb-setThumb.gif](https://i.loli.net/2020/04/13/L1yx5CqVJnKfbMX.gif)

![ithumb-deleteThumb.gif](https://i.loli.net/2020/04/13/CAXnOSqwz2lUuQB.gif)

![ithumb-editpost.gif](https://i.loli.net/2020/04/13/dnwlSpuybAjWGB3.gif)