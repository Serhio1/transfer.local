<?php

/**
 *  Checks FTP connection
 */
$server = filter_input(INPUT_POST, 'ftp_server', FILTER_SANITIZE_SPECIAL_CHARS);
$username = filter_input(INPUT_POST, 'ftp_login', FILTER_SANITIZE_SPECIAL_CHARS);
$password = filter_input(INPUT_POST, 'ftp_password', FILTER_SANITIZE_SPECIAL_CHARS);
$basedir = filter_input(INPUT_POST, 'remote_basedir', FILTER_SANITIZE_SPECIAL_CHARS);

if (!empty($server) && !empty($username) && !empty($password)) {

    try {
        $con = ftp_connect($server);
        if (false === $con) {
            throw new Exception('Unable to connect');
        }

        $loggedIn = ftp_login($con,  $username,  $password);
        if (true === $loggedIn) {
            echo 'Авторизация: ОК<br>';
        } else {
            throw new Exception('Авторизация: FAIL');
        }

        echo '<script src="getfolder.js"></script>';
        $chdir = filter_input(INPUT_POST, 'chdir', FILTER_SANITIZE_SPECIAL_CHARS);
        if (isset($chdir) && $chdir || empty($basedir)) {
            ftp_chdir($con, $basedir);
            $folder_raw_list = ftp_rawlist($con, ".");
            $folder_list = ftp_nlist($con, ".");
            array_shift($folder_raw_list);
            array_shift($folder_list);
            echo '<ul>';
            foreach ($folder_list as $key => $folder_name) {
                if ($folder_raw_list[$key][0] == 'd') {
                    echo '<li onclick="chFolder(\''.$folder_name.'\')">' . $folder_name . '</li>';
                }
            }
            echo '</ul>';
        } else {
            if (@ftp_chdir($con, $basedir)) {
                echo 'Выбор директории: ОК <button onclick="chFolder(\'\')">Изменить директорию</button>';
            } else { 
                echo 'Выбор директории: FAIL. Существующие директории: <br> Для выбора директории перейдите в нее.';
                //echo '<iframe id="remoteftp" onload="processIframe(this)" name="remoteFtp" src="ftp://' . $username . ':' . $password . '@' . $server . '" frameborder="0" width="100%" height="400px" align="center">Ваш браузер не поддерживает плавающие фреймы!</iframe>';
                
                $folder_raw_list = ftp_rawlist($con, ".");
                $folder_list = ftp_nlist($con, ".");
                echo '<ul>';
                foreach ($folder_list as $key => $folder_name) {
                    if ($folder_raw_list[$key][0] == 'd') {
                        echo '<li onclick="chFolder(\''.$folder_name.'\')">' . $folder_name . '</li>';
                  }
                  
                }
                echo '</ul>';
                //echo implode(ftp_nlist($con, "."), '<br>');
            }
        }

        
        ftp_close($con);
    } catch (Exception $e) {
        die();
        //echo "Failure: " . $e->getMessage();
    }
} else {
    die ('Соединение по FTP не удалось. Проверьте правильность введенных параметров. Если есть блокировка подключения по IP, добавьте этот IP в исключения: ' . $_SERVER['REMOTE_ADDR']);
}
