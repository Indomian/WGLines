$(document).ready(function(){
	var table=$('#gameTable');
	function resizeGameArea() {
		var height=$(window).height();
		var rows=table.find('tr').length;
		var cellHeight=height/rows-26;
		table.find('td>div').css({'height':cellHeight,'width':cellHeight});
	}

	$(window).resize(function(){
		resizeGameArea();
	});
	resizeGameArea();
});