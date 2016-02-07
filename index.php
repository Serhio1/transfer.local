<?php

ini_set("display_errors", 1);
ini_set("track_errors", 1);
ini_set("html_errors", 1);
error_reporting(E_ALL);

$available_cms = array('n'     => 'Not selected',
                       'wp'    => 'Wordpress', 
                       'joom'  => 'Joomla',
                       'drp'   => 'Drupal');
    require_once 'form.php';
?>





<?php

if (isset($_POST['ftp_server'])) {
//echo posix_getpwuid(posix_getuid())['name'];
}
/**
 *  Working script
 */
if (isset($_POST['ftp_server']) && isset($_POST['ftp_login']) && isset($_POST['ftp_password']) && isset($_POST['local_basedir']) && isset($_POST['remote_basedir'])) {
$ftp_server = filter_input(INPUT_POST, 'ftp_server', FILTER_SANITIZE_SPECIAL_CHARS); //ftp.ff.shn-host.ru
$ftp_login = filter_input(INPUT_POST, 'ftp_login', FILTER_SANITIZE_SPECIAL_CHARS);
$ftp_password = filter_input(INPUT_POST, 'ftp_password', FILTER_SANITIZE_SPECIAL_CHARS);
$local_basedir = filter_input(INPUT_POST, 'local_basedir', FILTER_SANITIZE_SPECIAL_CHARS);
$remote_basedir = filter_input(INPUT_POST, 'remote_basedir', FILTER_SANITIZE_SPECIAL_CHARS);

mkdir($local_basedir, 0755, true);
$transfer_sh = fopen("transfer.sh", "w") or die("Unable to create transfer file");
fwrite($transfer_sh, "#!bin/bash\n");
if ($remote_basedir == '') {
    $remote_basedir = '/';
} else {
    $remote_basedir = '/' . trim($remote_basedir, '/') . '/';
}
$cut_dirs = substr_count($remote_basedir, '/')-1;
//exec("wget -r -nH -nv --no-parent -o $local_basedir/transfer_ftp.log -P $local_basedir/ --cut-dirs $cut_dirs ftp://$ftp_login:$ftp_password@$ftp_server:$remote_basedir > /dev/null &");

/**
 *  TODO: Add exceptions for log files, cache - http://stackoverflow.com/questions/8755229/how-to-download-all-files-but-not-html-from-a-website-using-wget
 */
fwrite($transfer_sh, "wget -r -nH -nv --no-parent -o $local_basedir/transfer_ftp.log -P $local_basedir/ --cut-dirs $cut_dirs ftp://$ftp_login:$ftp_password@$ftp_server:$remote_basedir\n");


/**
 *  Mysql db transfer
 */
if (isset($_POST['remote_db_host']) && isset($_POST['remote_db_login']) && isset($_POST['remote_db_password']) && isset($_POST['remote_db'])) {
/*$dbname = 'yiokixpg_wp562';
$dbuser = 'yiokixpg_wp562';
$dbpass = '9S@48nP]7t';
$dbhost = '144.76.218.198';*/
$dbname = filter_input(INPUT_POST, 'remote_db', FILTER_SANITIZE_SPECIAL_CHARS);
$dbuser = filter_input(INPUT_POST, 'remote_db_login', FILTER_SANITIZE_SPECIAL_CHARS);
$dbpass = filter_input(INPUT_POST, 'remote_db_password', FILTER_SANITIZE_SPECIAL_CHARS);
$dbhost = filter_input(INPUT_POST, 'remote_db_host', FILTER_SANITIZE_SPECIAL_CHARS);

/*$connect = mysql_connect($dbhost, $dbuser, $dbpass) or die("Connection to $dbhost failed.");
if ($connect) {
    echo 'Mysql connection: OK<br>';
}
mysql_select_db($dbname) or die("Connection is fine, but $dbname databse not exist");
*/

//exec('mysqldump --opt -h 144.76.218.198 -u yiokixpg_wp562 -p9S@48nP]7t yiokixpg_wp562 > test.sql &');
//exec("mysqldump --opt -h $dbhost -u $dbuser -p$dbpass $dbname > $local_basedir/$dbname.sql &");
$dsn = "mysql:host=$dbhost;dbname=$dbname;charset=utf8";
$opt = array(
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
);
try {
    $pdo = new PDO($dsn, $dbuser, $dbpass, $opt);
    fwrite($transfer_sh, "mysqldump --opt -h $dbhost -u $dbuser -p$dbpass $dbname > $local_basedir/$dbname.sql\n");
} catch (PDOException $e) {
    echo 'Нет соединения с удаленной базой данных';
}

/**
 *  Add new db to cpanel
 */
require("xmlapi.php"); // this can be downloaded from https://github.com/CpanelInc/xmlapi-php/blob/master/xmlapi.php
$cpaneluser = posix_getpwuid(posix_getuid())['name'];
$xmlapi = new xmlapi(gethostname()); 
$xmlapi->set_port( 2083 );   
$xmlapi->password_auth($cpaneluser, $_POST['cpanel_pass']);    
$xmlapi->set_debug(0);//output actions in the error log 1 for true and 0 false 


$databasename = filter_input(INPUT_POST, 'local_db', FILTER_SANITIZE_SPECIAL_CHARS);
$databaseuser = filter_input(INPUT_POST, 'local_db_user', FILTER_SANITIZE_SPECIAL_CHARS);
$databasepass = filter_input(INPUT_POST, 'local_db_pass', FILTER_SANITIZE_SPECIAL_CHARS);

//create database    
$createdb = $xmlapi->api1_query($cpaneluser, "Mysql", "adddb", array($databasename));   
//create user 
$usr = $xmlapi->api1_query($cpaneluser, "Mysql", "adduser", array($databaseuser, $databasepass));   
//add user 
$addusr = $xmlapi->api1_query($cpaneluser, 'Mysql', 'adduserdb', array('' . $databasename . '', '' . $databaseuser . '', 'all'));

echo 'New DB created!<br>';

//exec("mysql -u czcmdqjp_testwp -p64Un4mR_kZyd czcmdqjp_testwp < test.sql &");
//exec("mysql -u czcmdqjp_db -p64Un4mR_kZyd czcmdqjp_testwp < $local_basedir/$dbname.sql > /dev/null 2>/dev/null &");
fwrite($transfer_sh, "mysql -u $cpaneluser"."_"."$databaseuser -p$databasepass $cpaneluser"."_"."$databasename < $local_basedir/$dbname.sql\n");


echo 'DB imported<br>';


$cms_conf_callback = filter_input(INPUT_POST, 'cms', FILTER_SANITIZE_SPECIAL_CHARS) . '_conf';

if (function_exists($cms_conf_callback)) {
    fwrite($transfer_sh, $cms_conf_callback($cpaneluser, $local_basedir, $databasename, $databaseuser, $databasepass));
}



fwrite($transfer_sh, "rm -- $0");
fclose($transfer_sh);
//exec("sh transfer.sh > /dev/null &");
}

}

function wp_conf($cpaneluser, $local_basedir, $databasename, $databaseuser, $databasepass)
{
    $res = "sed -i \"s/define('DB_NAME'.*/define('DB_NAME', '$cpaneluser".'_'."$databasename');/g\" $local_basedir/wp-config.php\n";
    $res .= "sed -i \"s/define('DB_USER'.*/define('DB_USER', '$cpaneluser".'_'."$databaseuser');/g\" $local_basedir/wp-config.php\n";
    $res .= "sed -i \"s/define('DB_PASSWORD'.*/define('DB_PASSWORD', '$databasepass');/g\" $local_basedir/wp-config.php\n";
    $res .= "sed -i \"s/define('DB_HOST'.*/define('DB_HOST', 'localhost');/g\" $local_basedir/wp-config.php\n";

    return $res;
}

function joom_conf($cpaneluser, $local_basedir, $databasename, $databaseuser, $databasepass)
{
    $res = "sed -i \"s/public $db =.*/public $db = '$cpaneluser".'_'."$databasename';/g\" $local_basedir/configuration.php\n";
    $res .= "sed -i \"s/public $user =.*/public $user = '$cpaneluser".'_'."$databaseuser';/g\" $local_basedir/configuration.php\n";
    $res .= "sed -i \"s/public $password =.*/public $password = '$databasepass';/g\" $local_basedir/configuration.php\n";
    $res .= "sed -i \"s/public $host =.*/public $host = 'localhost';/g\" $local_basedir/configuration.php\n";
    $res .= "sed -i \"s/public $tmp_path =.*/public $tmp_path = '$local_basedir/tmp';/g\" $local_basedir/configuration.php\n";
    $res .= "sed -i \"s/public $log_path =.*/public $log_path = '$local_basedir/logs';/g\" $local_basedir/configuration.php\n";
    $res .= 'sed -i \"s/$_SERVER[\'DOCUMENT_ROOT\'] =.*/$_SERVER[\'DOCUMENT_ROOT\'] = '.$local_basedir.';/g\" $local_basedir/configuration.php'."\n";
     

    return $res;
}


/*
 *  PHP way - works only for little site
 *
if (isset($_POST['ftp_server'])) {
$ftp_server = filter_input(INPUT_POST, 'ftp_server', FILTER_SANITIZE_SPECIAL_CHARS);//"ftp.ff.shn-host.ru";
$conn_id = ftp_connect ($ftp_server)
    or die("Couldn't connect to $ftp_server");
   
$login_result = ftp_login($conn_id, filter_input(INPUT_POST, 'ftp_login', FILTER_SANITIZE_SPECIAL_CHARS), filter_input(INPUT_POST, 'ftp_password', FILTER_SANITIZE_SPECIAL_CHARS));
if ((!$conn_id) || (!$login_result))
    die("FTP Connection Failed");
    
$local_basedir = posix_getpwuid(posix_getuid())['dir'] . '/' . filter_input(INPUT_POST, 'local_basedir', FILTER_SANITIZE_SPECIAL_CHARS);

$remote_basedir = filter_input(INPUT_POST, 'remote_basedir', FILTER_SANITIZE_SPECIAL_CHARS);

$dir = filter_input(INPUT_POST, 'dir', FILTER_SANITIZE_SPECIAL_CHARS);

//chdir($local_dir);
//ftp_sync ($remote_dir);
ftp_sync ($conn_id, $local_basedir, $remote_basedir, $dir);

ftp_close($conn_id); 
}
// ftp_sync - Copy directory and file structure
function ftp_sync ($conn_id, $local_basedir, $remote_basedir, $dir) {

    if ($dir != ".") {
        if (ftp_chdir($conn_id, $remote_basedir .'/'. $dir) == false) {
            echo ("Change Dir Failed: $remote_basedir/$dir<BR>\r\n");
            return;
        }
        if (!(is_dir($local_basedir.'/'.$dir))) {
            mkdir($local_basedir.'/'.$dir);
        }
        chdir ($local_basedir.'/'.$dir);
    }

    $contents = ftp_nlist($conn_id, ".");
    foreach ($contents as $file) {
   
        if ($file == '.' || $file == '..')
            continue;
       
        if (@ftp_chdir($conn_id, $file)) {
            ftp_chdir ($conn_id, "..");
            ftp_sync ($conn_id, $local_basedir.'/'.$dir, $remote_basedir.'/'.$dir, $file);
        }
        else
            ftp_get($conn_id, $file, $file, FTP_BINARY);
    }
       
    ftp_chdir ($conn_id, "..");
    chdir ("..");

}*/

?>
