<?php

function show($file_content = "", $tags = array(null => null))
{
    if (file_exists(Config::$path['template_abs'] . "/" . $file_content . ".html")) {
        $file_content = file_get_contents(Config::$path['template_abs'] . "/" . $file_content . ".html");
    } else if (file_exists(Config::$path['template_default_abs'] . $file_content . ".html")) {
        $file_content = file_get_contents(Config::$path['template_default_abs'] . $file_content . ".html");
    } else if (file_exists($file_content)) {
        $file_content = file_get_contents($file_content);
    }
    foreach ($tags as $name => $value) {
        if (!is_array($value) && !is_object($value)) {
            $file_content = str_replace('{' . $name . '}', $value, $file_content);
        }
    }
    return $file_content;
}

function get_replace_array()
{
    return array(' ', '/', '.', '+');
}

function randomstring($length = 6)
{
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
    srand((double)microtime() * 1000000);
    $i = 0;
    $tmp = "";
    while ($i < $length) {
        $num = rand() % strlen($chars);
        $tmp .= substr($chars, $num, 1);
        $i++;
    }
    return $tmp;
}

function custom_verify($pw, $pw2)
{
    global $config;
    return password_verify($pw . $config['salt'], $pw2);
}

function customHasher($pw)
{
    global $config;
    return password_hash($pw . $config['salt'], PASSWORD_BCRYPT, array('cost' => 12));
}

function get_gravatar($email, $s = 80, $img = false)
{
    $d = 'wavatar';
    $r = 'g';
    $url = 'https://www.gravatar.com/avatar/';
    $url .= md5(strtolower(trim($email)));
    $url .= "?s=$s&amp;d=$d&amp;r=$r";
    if ($img) {
        $url = '<img src="' . $url . '" />';
    }
    return $url;
}

function msg($msg, $kind = 'stock')
{
    global $meta;
    switch ($kind) {
        default:
            $file = show("msg/msg_stock");
    }

    $meta['title'] = $msg;
    if (!isset($_SESSION['last_site'])) {
        $_SESSION['last_site'] = Config::$path['pages'];
    }
    $msg = show($file, array("msg" => $msg,
        "link" => $_SESSION['last_site']));
    Auth::backSideFix();
    return $msg;
}

function permTo($permission)
{
    if (isset($_SESSION['group_main_id'])) {
        $perm = Db::query("SELECT " . $permission . " From groups WHERE id = :id LIMIT 1", array('id' => $_SESSION['group_main_id']), PDO::FETCH_OBJ);
        if (isset($perm->$permission)) {
            return $perm->$permission;
        }
    }
    return 0;
}

function check_email_address($str_email_address)
{
    if ('' != $str_email_address && !((preg_match("/[_\.0-9a-z-]+@([0-9a-z-]+\.)+[a-z]{2,6}/i", $str_email_address)))) {
        return false;
    } else {
        return true;
    }
}

function updateRSS()
{
    global $path;

    $xml = new DOMDocument('1.0', 'UTF-8');
    $xml->formatOutput = true;

    $roo = $xml->createElement('rss');
    $roo->setAttribute('version', '2.0');
    $xml->appendChild($roo);
    $cha = $xml->createElement('channel');
    $roo->appendChild($cha);
    $new = $xml->createElement('title', 'CMS - News');
    $cha->appendChild($new);
    $new = $xml->createElement('description', 'D4ho.de - CMS');
    $cha->appendChild($new);
    $bld = $xml->createElement('image');
    $cha->appendChild($bld);
    $bld->appendChild($xml->createElement('url', "http://dummyimage.com/120x61"));

    $qry = Db::npquery('SELECT * FROM news WHERE grp = 2 AND public_show = 1 ORDER BY date DESC');
    foreach ($qry as $rss_feed) ;
    {
        $new = $xml->createElement('item');
        $cha->appendChild($new);
        $rss['title'] = $rss_feed['title'];
        $image = '&lt;img style="border: 0px none; margin: 0px; padding: 0px;" align="right" alt="" width="60" height="60" src="' . $rss_feed['main_image'] . '" &gt;';
        $rss['description'] = $image . $rss_feed['description'];
        $rss['language'] = Config::$settings->lang;
        $rss['link'] = "http://cms.d4ho.de/pages/news.php?id=" . $rss_feed['id'];
        $rss['pubDate'] = date("D, j M Y H:i:s ", $rss_feed['date']);
        $hea = $xml->createElement('image');
        $new->appendChild($hea);
        $img = $xml->createElement('url', $rss_feed['main_image']);
        $hea->appendChild($img);

        foreach ($rss as $tag => $value) {
            $hea = $xml->createElement($tag, utf8_encode($value));
            $new->appendChild($hea);
        }
    }

    if ($xml->save($path['rss'] . 'public-news.xml')) {
        return true;
    }
    return false;
}




function get_module_name($id)
{
    $mod = Db::npquery("SELECT module FROM modules WHERE id = $id", PDO::FETCH_OBJ);
    return $mod[0]->module;
}

function sendmail($content, $subject, $receiver)
{
    $mail = new PHPMailer();
    $mail->isSendmail();

    $mail->SetFrom('admin@' . $_SERVER['HTTP_HOST'], 'mailFrom->' . $_SERVER['HTTP_HOST']);

    $mail->CharSet = "utf-8";
    $mail->Subject = $subject;

    $mail->msgHTML(
        show(
            'mail/layout',
            array(
                'title' => $subject,
                'content' => $content,
                'date' => date('l jS \am F Y H:i:s', time())
            )
        )
    );
    $mail->addAddress($receiver);
    if ($mail->send()) {
        return true;
    }
    return false;
}

function goBack()
{
    header('Location: ' . $_SESSION['last_site']);
    exit();
}

function goToWithMsg($url, $msg, $type = 'info')
{
    new Notification($msg, $type);
    if ($url == 'back') {
        goBack();
    }  else if ($url == 'home') {
        goToSite(Config::$settings->home);
    } else {
        header('Location: ' . $url);
        exit();
    }
}

function goToSite($url)
{
    if ($url == 'back') {
        goBack();
    } else if ($url == 'home') {
        goToSite(Config::$settings->home);
    } else {
        header('Location: ' . $url);
        exit();
    }
}

function con_to_lang($str)
{
    return '{s_' . $str . '}';
}

function get_public_properties($object)
{
    $result = get_object_vars($object);
    if ($result === NULL or $result === FALSE) {
        throw new UnexpectedValueException("Given $object parameter is not an object.");
    }
    return $result;
}


function sendMessage($sender, $receiver, $content, $title, $email = "")
{
    $rec_user = new UserModel($receiver);
    sendmail($content, $title, $rec_user->email);
    $in = array(
        'sender_id' => $sender,
        'receiver_id' => $receiver,
        'email' => $email,
        'date' => time(),
        'content' => $content,
        'title' => $title
    );
    return Db::insert('messages', $in);
}
