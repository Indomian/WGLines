<?php
/**
 * File renders gamefield
 * @var View $this
 * @var Field $field
 */
?>
<table id="gameTable">
	<?php for($y=0;$y<$field->getSize();$y++):?>
		<tr>
			<?php for($x=0;$x<$field->getSize();$x++):?>
				<td data-x="<?php echo $x?>" data-y="<?php echo $y?>"><?php $this->render('gameCell',array('cell'=>$field->getCell($x,$y)));?></td>
			<?php endfor?>
		</tr>
	<?php endfor?>
</table>