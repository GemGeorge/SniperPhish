var dt_log;
loadTableLog();

$("#modal_export_log_selector").select2({
    minimumResultsForSearch: -1
}); 

function loadTableLog() {
    try {
        dt_log.destroy();
    } catch (err) {}
    $('#table_log thead').empty();
    $('#table_log tbody > tr').remove();

    var arr_tb_heading=[{ data: 'id', title: "#" },
        { data: 'username', title: "Username" },
        { data: 'log', title: "Log" },
        { data: 'ip', title: "IP" },
        { data: 'date', title: "Date Time" }];

    dt_log = $('#table_log').DataTable({
        'processing': true,
        'serverSide': true,
        'ajax': {
            url:'manager/settings_manager',
            type: "POST",
            contentType: "application/json; charset=utf-8",
            data: function (d) {   //request parameters here
                    d.action_type = 'get_logs';
                    return JSON.stringify(d);
                },
            dataSrc: function ( resp ){
                for (var i=0; i<resp.data.length; i++){
                    resp.data[i]['id'] = i+1;
                }

                return resp.data
            }
        },
        'columns': arr_tb_heading,
        'pageLength': 20,
        'lengthMenu': [[20, 50, 100, 500, 1000, -1], [20, 50, 100, 500, 1000, "All"]],
        'order': [[ 0, "desc" ]],
        drawCallback:function(){
            $('[data-toggle="tooltip"]').tooltip({ trigger: "hover" });
            $("label>select").select2({minimumResultsForSearch: -1, });
        }
    });
}

function exportLogAction(e) {
    if(dt_log.rows().count() > 0){
        var file_format = $('#modal_export_log_selector').val();

        if(file_format == 'csv')
            content_type='text/csv';
        else
        if(file_format == 'pdf')
            content_type='application/pdf';
        else
        if(file_format == 'html')
            content_type='text/html';

        var xhr = new XMLHttpRequest();
        xhr.open('POST', 'manager/settings_manager', true);
        xhr.responseType = 'arraybuffer';
        
        enableDisableMe(e);
        xhr.send(JSON.stringify({ 
            action_type: "download_logs",
            file_format: file_format
        }));

        xhr.onload = function() {
            if (this.status == 200) {
                var link=document.createElement('a');
                link.href = window.URL.createObjectURL(new Blob([this.response],{ type: content_type}));
                link.download=this.getResponseHeader('content-disposition').split('filename=')[1].split(';')[0];
                link.click();
                $('#ModalExport').modal('toggle');
           }
           enableDisableMe(e);
        };
    }
    else
        toastr.error('', 'Table is empty!');
}

function clearLogAction() {
    $.post({
        url: 'manager/settings_manager',
        contentType: 'application/json; charset=utf-8',
        data: JSON.stringify({ 
            action_type: 'clear_log',
         })
    }).done(function (response) {
        if(response.result == 'success'){
            $('#modal_prompts').modal('toggle');
            toastr.success('', 'Log cleared successfully!');
            loadTableLog();
        }
        else
            toastr.error('', response.error);
    }); 
}