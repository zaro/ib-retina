if (document.cookie.indexOf('devicePixelRatio') === -1) {
	document.cookie='devicePixelRatio=' + ((window.devicePixelRatio !== undefined) ? window.devicePixelRatio : 1) + '; path=/';
}