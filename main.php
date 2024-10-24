<?php

$token = $argv[1];

$channel = $argv[2];

$lines = $argv[3];

$columns = $argv[4];

$lastmsg;

readline_callback_handler_install('', function() { });

$typed_msg = '';

function send_message($message, $token, $channel) {
    passthru('curl -s -X POST -H "Authorization: '.$token.'" -H "Content-Type: application/json" -d \'{"content": "'.$message.'"}\' https://discord.com/api/v10/channels/'.$channel.'/messages');
}

while (true) {
    $r = array(STDIN);
    $w = null;
    $e = null;
    $n = stream_select($r, $w, $e, 0, 0);
    if ($n && in_array(STDIN, $r)) {
        $pressed = stream_get_contents(STDIN, 1);
    } else {
        $pressed = null;
    }

    if (isset($pressed)) {
        if ($pressed == "\n" || $pressed == "\r") {
            send_message($typed_msg, $token, $channel);
            $typed_msg = '';
        } else if ($pressed == "\177") {
            $typed_msg = substr($typed_msg, 0, strlen($typed_msg) - 1);
        } else {
            $typed_msg .= $pressed;
        }
    }

    echo "\033[".$lines.";0H\033[0K\033[38;2;255;145;255m => ".$typed_msg."\033[0m";
    
    $json = array();

    exec('curl -s -H "Authorization: '.$token.'" https://discord.com/api/v10/channels/'.$channel.'/messages', $json);

    $json_str = implode($json);

    $messages = array_reverse(json_decode($json_str));

    if ($messages[0]->id == $lastmsg) {
        continue;
    }

    echo "\033[2J";
    
    $lastmsg = $messages[0]->id;
    
    foreach (array_values($messages) as $msg) {
        echo "\033[38;5;75m".$msg->author->username."\033[0m\n";
        echo ' - '.$msg->content."\n";
        if (count($msg->attachments) > 0) {
            $images = array();
            foreach (array_values($msg->attachments) as $att) {
                if (str_starts_with($att->content_type, 'image/')) {
                    unset($images);
                    exec('python image.py "'.$att->url.'" "'.floor($columns * 0.5).'" "y"', $images);
                    foreach (array_values($images) as $line) {
                        echo $line."\n";
                    }
                    echo "\n";
                }
            
            }
        }
    }
}
