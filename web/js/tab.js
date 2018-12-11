function selectTab(url,id)
{
    document.getElementById('content').src = url;
    for (var i = 0; i < tabs.length; i++) {
        document.getElementById(tabs[i]).className = 'tab-inactive';
    }
    document.getElementById(id).className = 'tab-active';
}
