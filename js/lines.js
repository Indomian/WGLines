function LinesEngine(htmlObject) {
	var CELL=-1;
	var BLANK=-2;
	var table=$(htmlObject);
	var map=[];
	var size;
	var selectedCell=null;

	var pathX=[],pathY=[];

	var rows=table.find('tr');
	size=rows.length;
	for(var y=0;y<rows.length;y++) {
		var cells=rows.find('div.cell-content');
		for(var x=0;x<cells.length;x++) {
			if(map[x]==undefined) {
				map[x]=[];
			}
			var cell=cells.eq(x);
			if(!cell.hasClass('empty-cell')) {
				map[x][y]=CELL;
			} else {
				map[x][y]=BLANK;
			}
		}
	}

	function findPath(fx,fy,tx,ty) {
		var dx = [1, 0, -1, 0];   // смещения, соответствующие соседям ячейки
		var dy = [0, 1, 0, -1];   // справа, снизу, слева и сверху
		var d, x, y, k;
		var stop;
		pathX=[];
		pathY=[];

		// распространение волны
		d = 0;
		map[fx][fy] = 0;            // стартовая ячейка помечена 0
		do {
			stop = true;               // предполагаем, что все свободные клетки уже помечены
			for ( y = 0; y < size; ++y )
				for ( x = 0; x < size; ++x )
					if ( map[x][y] == d ) {                         // ячейка (x, y) помечена числом d
						for ( k = 0; k < 4; ++k ) {                   // проходим по всем непомеченным соседям
							if ( map[x + dx[k]][y + dy[k]] == BLANK ) {
								stop = false;                            // найдены непомеченные клетки
								map[x + dx[k]][y + dy[k]] = d + 1;      // распространяем волну
							}
						}
					}
			d++;
		} while ( !stop && map[tx][ty] == BLANK );

		if (map[tx][ty] == BLANK) return false;  // путь не найден

		// восстановление пути
		var len = map[tx][ty];            // длина кратчайшего пути из (ax, ay) в (bx, by)
		x = tx;
		y = ty;
		d = len;
		while ( d > 0 ) {
			pathX[d] = x;
			pathY[d] = y;                   // записываем ячейку (x, y) в путь
			d--;
			for (k = 0; k < 4; ++k)
				if (map[x + dx[k]][y + dy[k]] == d) {
					x = x + dx[k];
					y = y + dy[k];           // переходим в ячейку, которая на 1 ближе к старту
					break;
				}
		}
		pathX[0] = fx;
		pathY[0] = fy;                    // теперь px[0..len] и py[0..len] - координаты ячеек пути
		return true;
	}

	table.find('td').click(function(e){
		var cell=$(this).find('div.cell-content');
		var td=$(this);
		if(cell.hasClass('empty-cell') && selectedCell!=null) {
			if(findPath(td.attr('data-x'),td.attr('data-y'),selectedCell.attr('data-x'),selectedCell.attr('data-y'))) {
				for(var i=0;i<pathX.length;i++) {
					table.find('td[data-x='+pathX[i]+'][data-y='+pathY[i]+']').addClass('pathItem');
				}
			} else {
				alert('No path');
			}
		} else if(!cell.hasClass('empty-cell')) {
			if(selectedCell!=null) {
				selectedCell.removeClass('selected');
			}
			if(selectedCell==cell) {
				selectedCell=null;
			} else {
				selectedCell=td;
				selectedCell.addClass('selected');
			}
		}
	});
}

$(document).ready(function(){
	var table=$('#gameTable');
	function resizeGameArea() {
		var size=Math.min($(window).height(),$(window).width());
		var rows=table.find('tr').length;
		var cellSize=size/rows;
		table.find('td>div').css({'height':cellSize,'width':cellSize});
	}

	$(window).resize(function(){
		resizeGameArea();
	});
	resizeGameArea();
	new LinesEngine(table);
});