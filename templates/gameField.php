<?php
/**
 * File renders gamefield
 * @var View $this
 * @var Field $field
 */
?>
<table id="gameTable">
	<?php for($i=0;$i<$field->getSize();$i++):?>
		<tr>
			<?php for($j=0;$j<$field->getSize();$j++):?>
				<td><?php $this->render('gameCell',array('cell'=>$field->getCell($i,$j)));?></td>
			<?php endfor?>
		</tr>
	<?php endfor?>
</table>