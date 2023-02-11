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

function isNewerVersion(myVer, newVer) {
    if (myVer === newVer) {
        return false;
    }

    const myVerSplit = myVer.split('-');
    const myVerNum = myVerSplit[0];
    const myVerType = myVerSplit[1] || 'final';

    const newVerSplit = newVer.split('-');
    const newVerNum = newVerSplit[0];
    const newVerType = newVerSplit[1] || 'final';

    if (myVerNum === newVerNum) {
        if (myVerType === 'alpha' && (newVerType === 'beta' || newVerType === 'final')) {
            return true;
        } else if (myVerType === 'final' && newVerType === 'alpha') {
            return false;
        } else if (myVerType === 'beta' && newVerType === 'alpha') {
            return false;
        } else {
            return false;
        }
    } else {
        const myVerParts = myVerNum.split('.');
        const newVerParts = newVerNum.split('.');

        for (let i = 0; i < myVerParts.length; i++) {
            const myVerPart = Number(myVerParts[i]);
            const newVerPart = Number(newVerParts[i] || 0);

            if (myVerPart > newVerPart) {
                return false;
            } else if (myVerPart < newVerPart) {
                return true;
            }
        }

        return true;
    }
}