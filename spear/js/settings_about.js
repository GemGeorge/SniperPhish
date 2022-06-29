function checkUpdates(e){
    enableDisableMe(e);
    $.post({
        url: "https://sniperphish.com/update_info",
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: "check_updates"
        })
    }).done(function (data) {
        if(!data.error){
            if(isNewerVersion(curr_version.toLowerCase(),data.latest_version.toLowerCase()))
                $("#lb_new_version_status").html("New version available. Click <a href ='" + data.download_link + "'>here</a> to download. " + data.msg );
            else
                $("#lb_new_version_status").text("You are using the latest version.");
        }
        enableDisableMe(e);
    }); 
}

function isNewerVersion (oldVer, newVer) {
    var f_new = false;
    if(oldVer == newVer)
        f_new = false;
    else{
        var oldVer_num=oldVer.split('-')[0];
        var oldVer_type=oldVer.split('-')[1];
        var newVer_num=newVer.split('-')[0];
        var newVer_type=newVer.split('-')[1];

        if(oldVer_num == newVer_num){
            if(oldVer_type == 'alpha' && (newVer_type == 'beta' || newVer_type == null))
                f_new = true;
            else
                f_new = false;

            if(oldVer_type == null && (newVer_type == 'alpha' || newVer_type == 'beta'))
                f_new = false;
            else
                f_new = true;

            if(oldVer_type == 'beta' && (newVer_type == 'alpha' || newVer_type == 'beta'))
                f_new = false;
            else
                f_new = true;
        }

        const oldParts = oldVer_num.split('.');
        const newParts = newVer_num.split('.');
        for (var i = 0; i < newParts.length; i++) {
            const a = ~~newParts[i] // parse int
            const b = ~~oldParts[i] // parse int
            if (a > b) {
                f_new = true;
                break;
            }
            if (a < b){
                f_new = false;
                break;
            } 
        }
    }
    return f_new;
}