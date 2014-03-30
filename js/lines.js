function Map(size,debugHtml) {
	var data=[];

	this.get=function(x,y) {
		var pos=size*y+x;
		if(pos>=data.length) {
			return undefined;
		}
		return data[pos];
	};

	this.set=function(x,y,value) {
		var pos=size*y+x;
		data[pos]=value;
		if(debugHtml!=undefined) {
			debugHtml.find('tr:eq('+y+')>td:eq('+x+')>div>div').html(value);
		}
	}
}

function LinesEngine(htmlObject) {
	var CELL=-1;
	var BLANK=-2;
	var table=$(htmlObject);
	var size;
	var map;
	var selectedCell=null;
	var animatedPoint;
	var prevPathPoint;
	var callbacks={
		'clearMoves':function() {
			$('#steps').empty();
		}
	};

	var path=[];

	function initMap() {
		var rows=table.find('tr');
		size=rows.length;
		map=new Map(size);
		for(var y=0;y<rows.length;y++) {
			var cells=rows.eq(y).find('div.cell-content');
			for(var x=0;x<cells.length;x++) {
				var cell=cells.eq(x);
				if(!cell.hasClass('empty-cell')) {
					map.set(x,y,CELL);
				} else {
					map.set(x,y,BLANK);
				}
			}
		}
	}

	function findPath(fx,fy,tx,ty) {
		fx=parseInt(fx);
		fy=parseInt(fy);
		tx=parseInt(tx);
		ty=parseInt(ty);
		table.find('div>div').html('');
		initMap();
		var dx = [1, 0, -1, 0];   // смещения, соответствующие соседям ячейки
		var dy = [0, 1, 0, -1];   // справа, снизу, слева и сверху
		var d, x, y, k, nx, ny;
		var stop;
		path=[];
		// распространение волны
		d = 0;
		map.set(fx,fy, 0);            // стартовая ячейка помечена 0
		do {
			stop = true;               // предполагаем, что все свободные клетки уже помечены
			for ( y = 0; y < size; y++ )
				for ( x = 0; x < size; x++ )
					if ( map.get(x,y) == d ) {                         // ячейка (x, y) помечена числом d
						for ( k = 0; k < 4; k++ ) {                   // проходим по всем непомеченным соседям
							nx=x+dx[k];
							ny=y+dy[k];
							if(nx>-1 && nx<size && ny>-1 && ny<size) {
								if ( map.get(nx,ny) == BLANK ) {
									stop = false;                            // найдены непомеченные клетки
									map.set(nx,ny,d + 1);      // распространяем волну
								}
							}
						}
					}
			d++;
		} while ( !stop && map.get(tx,ty) == BLANK );

		if (map.get(tx,ty) == BLANK) return false;  // путь не найден

		// восстановление пути
		var len = map.get(tx,ty);            // длина кратчайшего пути из (ax, ay) в (bx, by)
		x = tx;
		y = ty;
		d = len;
		while ( d > 0 ) {
			path[d]={'x':x,'y':y};
			d--;
			for (k = 0; k < 4; ++k) {
				nx=x+dx[k];
				ny=y+dy[k];
				if(nx>-1 && nx<size && ny>-1 && ny<size) {
					if (map.get(nx,ny) == d) {
						x = nx;
						y = ny;           // переходим в ячейку, которая на 1 ближе к старту
						break;
					}
				}
			}
		}
		path[0]={'x':x,'y':y};
		return true;
	}

	function noPath() {
		alert('No path');
	}

	function moveCellVisual(from,to,callback) {
		var fromCell=table.find('tr:eq('+from.y+')>td:eq('+from.x+')>div.cell');
		var toCell=table.find('tr:eq('+to.y+')>td:eq('+to.x+')>div.cell');
		var destination=toCell.offset();
		var source=fromCell.offset();
		animatedPoint.animate({'left':destination.left+4,'top':destination.top+4},200,'swing',function(){
			callback();
		});
		return true;
	}

	function stepPath() {
		if(path.length==0) {
			animatedPoint.remove();
			return;
		}
		var next=path.shift();
		if(next.hasOwnProperty('callback')) {
			animatedPoint.remove();
			next.callback();
		}
		if(next.hasOwnProperty('x') && !moveCellVisual(prevPathPoint,next,stepPath)) {
			animatedPoint.remove();
			return;
		}
		prevPathPoint=next;
	}

	function updateMap(map) {
		for(var x=0;x<map.length;x++) {
			for(var y=0;y<map.length;y++) {
				table.find('tr:eq('+y+')>td:eq('+x+')>div>div').attr('class',map[x][y].class);
			}
		}
	}

	function sendRequestAction(action,data,callback) {
		var request={
			'action':action
		};
		if(data!=undefined) {
			request.data=data;
		}
		selectedCell=null;
		$.getJSON('/index.php',request,function(result){
			if(result.hasOwnProperty('error')) {
				alert(result.error);
			} else {
				if(result.hasOwnProperty('callbacks')) {
					for(var i=0;result.callbacks.length;i++) {
						if(callbacks.hasOwnProperty(result.callbacks[i])) {
							callbacks[result.callbacks[i]]();
						}
					}
				}
				if(result.hasOwnProperty('map')) {
					if(path.length>0) {
						path.push({
							'callback':function() {
								updateMap(result.map);
							}
						});
					} else {
						updateMap(result.map);
					}
				}
				if(result.hasOwnProperty('score')) {
					$('#score').html(result.score);
				}
				if(result.hasOwnProperty('move')) {
					$('#steps').append('<li class="list-group-item">'+result.move+'</li>');
				}
				if(callback!=undefined) {
					callback();
				}
			}
		});
	}

	function moveCell(selectedCell,destinationCell) {
		var from={x:0,y:0},to={x:0,y:0};
		from.x=selectedCell.attr('data-x');
		from.y=selectedCell.attr('data-y');
		to.x=destinationCell.attr('data-x');
		to.y=destinationCell.attr('data-y');
		table.find('.pathItem').removeClass('pathItem');
		if(findPath(from.x,from.y,to.x,to.y)) {
			selectedCell.removeClass('selected');
			/**
			 * Made animation point
			 */
			var fromPoint=selectedCell.find('div.cell-content');
			animatedPoint=fromPoint.clone();
			animatedPoint.css({
				'position':'absolute',
				'width':fromPoint.outerWidth(),
				'height':fromPoint.outerHeight(),
				'left':fromPoint.offset().left+4,
				'top':fromPoint.offset().top+4
			});
			$('body').append(animatedPoint);
			fromPoint.attr('class','cell-content empty-cell future-empty-cell');
			prevPathPoint=path.shift();
			sendRequestAction('step',{'from':from,'to':to},stepPath);
		} else {
			noPath();
		}
	}

	$('#newGame').click(function(e){
		e.preventDefault();
		sendRequestAction('restart');
	});

	table.find('td').click(function(e){
		var cell=$(this).find('div.cell-content');
		var td=$(this);
		if(cell.hasClass('empty-cell') && selectedCell!=null) {
			moveCell(selectedCell,td);
		} else if(!cell.hasClass('empty-cell')) {
			if(selectedCell!=null) {
				selectedCell.removeClass('selected');
				if(selectedCell.attr('data-x')==td.attr('data-x') &&
					selectedCell.attr('data-y')==td.attr('data-y')) {
					selectedCell=null;
				} else {
					selectedCell=td;
					selectedCell.addClass('selected');
				}
			} else {
				selectedCell=td;
				selectedCell.addClass('selected');
			}
		}
	});
}

$(document).ready(function(){
	var table=$('#gameTable');
	/*function resizeGameArea() {
		var size=Math.min($(window).height(),$(window).width());
		var rows=table.find('tr').length;
		var cellSize=size/rows;
		table.find('td>div').css({'height':cellSize,'width':cellSize});
	}

	$(window).resize(function(){
		resizeGameArea();
	});
	resizeGameArea();*/
	new LinesEngine(table);
});