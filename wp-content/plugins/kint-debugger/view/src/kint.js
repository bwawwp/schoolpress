if ( typeof kint === 'undefined' ) {
	var kint = {

		selectText:function (element) {
			var selection = window.getSelection(),
					range = document.createRange();

			range.selectNodeContents(element);
			selection.removeAllRanges();
			selection.addRange(range);
		},

		addClass:function (ele) {
			kint.removeClass(ele).className += " kint-minus";
		},

		removeClass:function (ele) {
			ele.className = ele.className.replace(/(\s|^)kint-minus(\s|$)/, ' ');
			return ele
		},

		next:function (element, nodeName) {
			if ( !nodeName ) nodeName = 'dd';

			do {
				element = element.nextElementSibling;
			} while ( element.nodeName.toLowerCase() != nodeName );

			return element;
		},

		toggleChildren:function (element) {
			var parent = kint.next(element),
					nodes = parent.getElementsByClassName('kint-parent'),
					i = nodes.length,
					visible = parent.style.display == 'block';

			while ( i-- ) {
				kint.toggle(nodes[i], visible)
			}
			kint.toggle(element, visible)

		},

		toggle:function (element, hide) {
			var parent = kint.next(element),
					plus = element.getElementsByClassName('_kint-collapse')[0];


			if ( typeof hide == 'undefined' ) {
				hide = parent.style.display == 'block'
			}

			if ( hide ) {
				parent.style.display = 'none';
				kint.removeClass(plus);
			} else {
				parent.style.display = 'block';
				kint.addClass(plus);
			}
		},

		toggleAll:function (element) {
			var elements = document.getElementsByClassName('kint-parent'),
					i = elements.length,
					visible = kint.next(element.parentNode).style.display == 'block';


			while ( i-- ) {
				kint.toggle(elements[i], visible)
			}

		},

		toggleTrace:function (el, className) {
			var nel = el.parentNode.parentNode.getElementsByClassName(className)[0];
			nel.style.display = nel.style.display == 'block' ? 'none' : 'block';
		}

	};


	window.addEventListener("load", function () {
		var parents = document.getElementsByClassName('kint-parent'),
				i = parents.length, j,
				grandparents = document.getElementsByClassName('kint');


		while ( i-- ) {
			parents[i].addEventListener("mousedown", function () {
				kint.toggle(this)
			}, false);
		}

		// add separate events to click and doubleclick
		parents = document.getElementsByClassName('_kint-collapse');
		i = parents.length;
		while ( i-- ) {
			parents[i].addEventListener(
					"mousedown",
					function (e) {
						var that = this;

						setTimeout(function () {
							var timer = parseInt(that.kintTimer, 10);
							if ( timer > 0 ) {
								that.kintTimer--;
							} else {
								kint.toggleChildren(that.parentNode); // let's hope this is <dt>
							}
						}, 300);
						e.stopPropagation();
					},
					false

			);
			parents[i].addEventListener(
					"dblclick",
					function (e) {
						this.kintTimer = 2;
						kint.toggleAll(this);
						e.stopPropagation();
					},
					false
			);
		}

		i = grandparents.length;
		while ( i-- ) {
			parents = grandparents[i].getElementsByTagName('var');
			j = parents.length;

			while ( j-- ) {
				parents[j].addEventListener("mouseup", function () {
					kint.selectText(this);
				}, false);
			}

			parents = grandparents[i].getElementsByTagName('dfn');
			j = parents.length;

			while ( j-- ) {
				parents[j].addEventListener("mouseup", function () {
					kint.selectText(this);
				}, false);
			}
		}

		parents = document.getElementsByClassName('kint-args-parent');
		i = parents.length;
		while ( i-- ) {
			parents[i].addEventListener("click", function (e) {
				kint.toggleTrace(this, 'kint-args');
				e.preventDefault();
			}, false);
		}

		parents = document.getElementsByClassName('kint-source-parent');
		i = parents.length;
		while ( i-- ) {
			parents[i].addEventListener("click", function (e) {
				kint.toggleTrace(this, 'kint-source');
				e.preventDefault();
			}, false);
		}

		parents = document.getElementsByClassName('kint-object-parent');
		i = parents.length;
		while ( i-- ) {
			parents[i].addEventListener("click", function (e) {
				kint.toggleTrace(this, 'kint-object');
				e.preventDefault();
			}, false);
		}


		// add ajax call to contact editor but prevent link default action
		parents = document.getElementsByClassName('kint-ide-link');
		j = parents.length;
		while ( j-- ) {
			parents[j].addEventListener("click", function (e) {
				e.preventDefault();
				var ajax = new XMLHttpRequest();
				ajax.open('GET', this.href);
				ajax.send(null);
				return false;
			}, false);
		}


	}, false);
}