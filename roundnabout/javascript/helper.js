<?php header('Content-Type: application/javascript');?>String.prototype.replaceBlock = function(search,list) {
	var pos = 0;
	var replaced = '';
	var search_start = '{'+search+'/}';
	var search_end = '{/'+search+'}';
	var remove_block = false;
	
	if (!isArray(list)) {
		if (list==null) {
			remove_block = true;
		}
		list = [list];
	}

	while (pos!=-1) {
		if ((pos_start = this.indexOf(search_start,pos))>-1) {
			replaced += this.substr(pos,pos_start-pos);
			if ((pos_end = this.indexOf(search_end,pos_start))>-1) {
				if (!remove_block) {
					var sub = this.substr(pos_start+search_start.length,pos_end-pos_start-search_start.length);
					for (var index in list) {
						replaced += sub.replace('{'+search+'}',list[index]);
					}
				}
				pos = pos_end+search_end.length;
			} else {
				replaced += this.substr(pos_start,this.length-pos_start);
				pos = -1;
			}
		} else {
			replaced += this.substr(pos,this.length-pos);
			pos = -1;
		}
	}
	return replaced;
}

String.prototype.replaceTag = function(search,list) {
	var pos = 0;
	var replaced = '';
	var search_tag = '{'+search+'}';

	if (!isArray(list)) {
		list = [list];
	}

	while (pos!=-1) {
		if ((pos_next = this.indexOf(search_tag,pos))>-1) {
			replaced += this.substr(pos,pos_next-pos);
			replaced += list.join(', ');
			pos = pos_next+search_tag.length;
		} else {
			replaced += this.substr(pos,this.length-pos);
			pos = -1;
		}
	}
	return replaced;
}

var isArray = (function () {
	// Use compiler's own isArray when available
	if (Array.isArray) {
		return Array.isArray;
	}

	// Retain references to variables for performance
	// optimization
	var objectToStringFn = Object.prototype.toString, arrayToStringResult = objectToStringFn.call([]);

	return function (subject) {
		return objectToStringFn.call(subject) === arrayToStringResult;
	};
}());

function Divvy(array_var, number_of_sub_arrays) {
	var mod = array_var.length%number_of_sub_arrays;
	if (mod == 0) {
		var in_each = Math.floor(array_var.length/number_of_sub_arrays);
	} else {
		var deficit = array_var.length+(number_of_sub_arrays-mod);
		var in_each = Math.floor(deficit/number_of_sub_arrays);
	}
	var result = [];
	var not_finished = true;
	var index = 0;
	while (not_finished) {
		var sub_result = [];
		for (var i=0; i<in_each; i++) {
			if (index<array_var.length) {
				sub_result.push(array_var[index]);
			} else {
				not_finished = false;
			}
			index++;
		}
		if (sub_result.length>0) {
			result.push(sub_result);
		}
	}
	
	return result;
}

function Whole(number) {
	return Math.floor(number+0.5);
}

function Pad(n, width, z) {
	z = z || '0';
	n = n + '';
	return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
}

function Tag(tag_name,content,attributes) {
	var html_attributes = [''];
	for (attribute in attributes) {
		html_attributes.push(attribute+'="'+attributes[attribute]+'"');
	}
	return '<'+tag_name+html_attributes.join(' ')+'>'+content+'</'+tag_name+'>';
}
