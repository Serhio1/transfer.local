function getFolder(event) {
    /*event.preventDefault();
    //var iframeHtml = window.frames.remoteFtp.contentWindow.location.href;
    //var iframeHtml = window.frames.remoteFtp;
    //console.log(iframeHtml.window.frames);
    var iframeHtml = window.frames.remoteFtp.contentWindow.document;
    console.log(iframeHtml);*/
    
}

function processIframe(object) {
    console.log(object.contentWindow.document);
}

function chFolder(folder) {
    $('[name="remote_basedir"]').val($('[name="remote_basedir"]').val().replace(/^\/|\/$/g, ''));
    if (folder == '..') {
        var path = $('[name="remote_basedir"]').val();
        $('[name="remote_basedir"]').val(path.substring(0, path.lastIndexOf("/")));
    } else {
        $('[name="remote_basedir"]').val($('[name="remote_basedir"]').val()+'/'+folder);
    }
    
    resultContainer = $('#ftp_connection_result');
    resultContainer.html('Идет проверка...');
    //event.preventDefault();
    ftpHost = $('[name="ftp_server"]').val();
    ftpLogin = $('[name="ftp_login"]').val();
    ftpPassword = $('[name="ftp_password"]').val();
    ftpBaseDir = $('[name="remote_basedir"]').val();
    $.ajax({
        url: 'test_ftp_conn.php',
        type: 'POST',
        data: {
          ftp_server: ftpHost,
          ftp_login: ftpLogin,
          ftp_password: ftpPassword,
          remote_basedir: ftpBaseDir,
          chdir: true
        },
        success: function (data) {
          resultContainer.html(data);
        }
    });
}
