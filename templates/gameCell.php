<?php
/**
 * @var Cell $cell
 * @var View $this
 */
?>
<div class="cell">
	<div class="cell-content <?php echo $this->getHtmlColor($cell->getColor())?> future-<?php echo $this->getHtmlColor($cell->getFutureColor())?>"></div>
</div>