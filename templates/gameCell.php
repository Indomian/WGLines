<?php
/**
 * @var Cell $cell
 * @var View $this
 */
?>
<div style="padding:13px;width:50px;height:50px;background-color:<?php echo $this->getHtmlColor($cell->getColor())?>">
	<div style="width:24px;height:24px;background-color:<?php echo $this->getHtmlColor($cell->getFutureColor())?>"></div>
</div>