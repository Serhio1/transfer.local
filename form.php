
<form method="post" action="">
    <label>CPanel password: <input type="text" name="cpanel_pass" /></label><br>
    <label>Directory for your site: <input type="text" name="local_basedir" value="<?php echo posix_getpwuid(posix_getuid())['dir'] . '/yoursitename.com'; ?>" /> / </label><br>
    <p>FTP connection</p>
    <label>Remote FTP host: <input type="text" name="ftp_server" /></label><br>
    <label>Remote FTP login: <input type="text" name="ftp_login" /></label><br>
    <label>Remote FTP password: <input type="text" name="ftp_password" /></label><br>
    <label>Remote hosting site directory: <input type="text" name="remote_basedir" /> / </label><br>
    <button id="check_ftp_connection">Check FTP connection</button>
    <div id="ftp_connection_result"></div>

    <p>MySQL connection</p>
    <label>Remote MySQL host: <input type="text" name="remote_db_host" /></label><br>
    <label>Remote MySQL login: <input type="text" name="remote_db_login" /></label><br>
    <label>Remote MySQL password: <input type="text" name="remote_db_password" /></label><br>
    <label>Remote MySQL database: <input type="text" name="remote_db" /></label><br>
    <button id="check_db_connection">Check MySQL connection</button>
    <div id="db_connection_result"></div>
    
    <p>Create new db</p>
    <label>New DB name: <?php echo posix_getpwuid(posix_getuid())['name'], '_'; ?> <input type="text" name="local_db" /></label><br>
    <label>New DB user: <?php echo posix_getpwuid(posix_getuid())['name'], '_'; ?> <input type="text" name="local_db_user" /></label><br>
    <label>New DB password: <input type="text" name="local_db_pass" /></label><br>
    
    <label for="cms-select">Your CMS is: </label>
    <select id="cms-select" name="cms">
        <?php 
        foreach ($available_cms as $cms_key => $cms_name) {
            echo "<option value=\"$cms_key\">$cms_name</option>";
        } 
        ?>
    </select><br>
    <input type="submit" value="Transfer">
</form>

<script src="jquery-2.2.0.min.js"></script>
<script>
$('#check_db_connection').click(function(event) {
  resultContainer = $('#db_connection_result');
  resultContainer.html('Идет проверка...');
  event.preventDefault();
  dbHost = $('[name="remote_db_host"]').val();
  dbLogin = $('[name="remote_db_login"]').val();
  dbPassword = $('[name="remote_db_password"]').val();
  dbName = $('[name="remote_db"]').val();
  $.ajax({
      url: 'test_mysql_conn.php',
      type: 'POST',
      data: {
        remote_db_host: dbHost,
        remote_db_login: dbLogin,
        remote_db_password: dbPassword,
        remote_db: dbName
      },
      success: function (data) {
        resultContainer.html(data);
      }
  });  
});

$('#check_ftp_connection').click(function(event) {
  resultContainer = $('#ftp_connection_result');
  resultContainer.html('Идет проверка...');
  event.preventDefault();
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
        remote_basedir: ftpBaseDir
      },
      success: function (data) {
        resultContainer.html(data);
      }
  });  
});
</script>


