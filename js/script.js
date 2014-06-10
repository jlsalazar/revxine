function showoptions(inp, divname) {
	var slavediv;
	var i;
	for (i = 0; i < 4; i++) {
		slavediv = document.getElementById(divname + i);
		slavediv.style.display = 'none';
	}
	slavediv = document.getElementById(divname + inp);
	slavediv.style.display = 'block';
}

function showselect(inp, divname) {
	var slavediv;
	var i;
	for (i = 0; i < inp.length; i++) {
		slavediv = document.getElementById(divname + i);
		slavediv.style.display = 'none';
	}
	slavediv = document.getElementById(divname + inp.selectedIndex);
	slavediv.style.display = 'block';
}

function changeSelect(element, field) {
	alleleText = document.getElementsByName(field)[0];
	for (i = 0; i < element.options.length; i++) {
		if (element.options[i].selected) {
			alleleValue = element.options[i].value;
			if (alleleText.value == '') {
				alleleText.value = alleleValue;
			} else {
				alleleText.value += ',' + alleleValue;
			}
		}
	}
	cleanList(field);
}

function cleanList(field) {
	alleleText = document.getElementsByName(field)[0];
	alleleConfirmed = new Array();
	alleleTextFinal = '';
	alleleList = alleleText.value.split(',');
	for (i in alleleList) {
		if (alleleList[i].indexOf(' ') == 0) {
			alleleList[i] = alleleList[i].slice(1);
		}
		if (!alleleConfirmed[alleleList[i]]) {
			alleleConfirmed[alleleList[i]] = 1;
		}
	}
	for (i in alleleConfirmed) {
		if (allelesValid[i]) {
			alleleTextFinal += i + ',';
		} else {
			alert('No such allele: ' + i);
		}
	}
	alleleTextFinal = alleleTextFinal.slice(0, -1);
	alleleText.value = alleleTextFinal;
}
