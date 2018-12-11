function selectMenu(url, id)
{
    document.getElementById('ifr-main').src = url;
    for (var i = 0; i < menus.length; i++) {
    	document.getElementById(menus[i]).className = 'menu-inactive';
    }
    document.getElementById(id).className = 'menu-active';
}

function modal(show)
{
    window.parent.parent.parent.document.getElementById('modal-container').style.display = show ? 'block' : 'none';
}

window.onclick = function(event)
{
    if (event.target == document.getElementById('modal-container'))
        modal(false);
};

function open_iframe_modal(url)
{
	window.parent.parent.parent.document.getElementById('ifr-modal').src = url;
}
