<?php

/**
 * نمونه ربات ساده انتقال محتوا از تلگرام به ایتا.
 * نسخه 1.1
 *
 * کپی رایت - 2020 - پوریا ب
 *
 * این فایل یک ربات تلگرامی جهت انتقال فایل می باشد
 * این برنامه رایگان می باشد و شما میتوانید آن را طبق شرایط کپی رایت ویرایش و استفاده نمایید (GNU Affero General Public License version 3 طبق شرایط)
 * امیدواریم این برنامه مفید واقع شود
 *
 * @author    Pouria B <po.pooria@gmail.com>
 * @copyright 2020 - Pouria B <po.pooria@gmail.com>
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPLv3
 *
 * @link https://eitaa.com/Anonymous_Devz ارتباط با برنامه نویس در پیامرسان ایتا
 * @link https://t.me/EitaaSender_Bot     آیدی ربات نمونه انتقال محتوا در تلگرام
 *
 * راهنما
 * این سورس رو در یک هاست آپلود کنید و کنار این فایل یک پوشه با نام
 * Files
 * جهت ذخیره فایل های آپلودی ایجاد کنید
 * فقط توکن ربات تلگرام و ایتایار رو در جای مشخص شده
 * در 3 خط کد زیر وارد کنید
 */

    $bot_url    = "https://api.telegram.org/bot[توکن ربات تلگرام]";
    $bot_dl_url = "https://api.telegram.org/file/bot[توکن ربات تلگرام]";
    $et_bot_url = "https://eitaayar.ir/api/bot[توکن شما در سایت ایتایار]";
    $et_chat_id = '[شناسه عددی کانال در سایت ایتایار جهت انتقال محتوا]';

    $update = file_get_contents("php://input");
    $update_array = json_decode($update, true);

    if(isset($update_array["message"]))
    {
        $text                    = $update_array["message"]["text"];
        $chat_id                 = $update_array["message"]["chat"]["id"];
        $message_caption         = $update_array["message"]["caption"];
        $message_id              = $update_array["message"]["message_id"];

        $message_file_id         = $update_array["message"]["document"]["file_id"];
        $message_file_size       = $update_array["message"]["document"]["file_size"];
        $message_file_name       = $update_array["message"]["document"]["file_name"];

        $message_audio_id        = $update_array["message"]["audio"]["file_id"];
        $message_audio_size      = $update_array["message"]["audio"]["file_size"];
        $message_audio_title     = $update_array["message"]["audio"]["title"];

        $message_voice_id        = $update_array["message"]["voice"]["file_id"];
        $message_voice_size      = $update_array["message"]["voice"]["file_size"];

        $message_video_id        = $update_array["message"]["video"]["file_id"];
        $message_video_size      = $update_array["message"]["video"]["file_size"];

        $message_sticker_id      = $update_array["message"]["sticker"]["file_id"];
        $message_sticker_size    = $update_array["message"]["sticker"]["file_size"];

        $message_photo           = $update_array["message"]["photo"];

        if(isset($update_array["message"]["text"]))
        send_message();

        if(isset($update_array["message"]["document"]))
        save_file();

        if(isset($update_array["message"]["audio"]))
        save_audio();

        if(isset($update_array["message"]["voice"]))
        save_voice();

        if(isset($update_array["message"]["video"]))
        save_video();

        if(isset($update_array["message"]["sticker"]))
        save_sticker();

        if(isset($update_array["message"]["photo"]))
        save_photo();
    }

    //-------------------------------------

    // ارسال پیام
    function send_message()
    {
      // نمایش پیغام با شروع ربات
      if($GLOBALS['text'] == "/start")
      {
        // نمایش پیام هنگام شروع ربات
        $reply = "حالا هر محتوایی به ربات بفرستید به کانال ایتا منتقل میشه\n" .
                 "آیدی سازنده ربات در ایتا و تلگرام: @Anonymous_Devz";
        $url = $GLOBALS['bot_url'] . "/sendMessage";
        $post_params = [
          'chat_id' => $GLOBALS['chat_id'] ,
          'text'    => $reply
        ];
        send_reply($url, $post_params);
      }

      else if ($GLOBALS['text'])
      {
        // انتقال متن ارسالی به ایتا
        $t = time();
        $url         = $GLOBALS['et_bot_url'] . "/sendMessage";
        $post_params = [
          'chat_id' => $GLOBALS['et_chat_id'] ,
          'text'    => $GLOBALS['text']
        ];
        send_to_eitaa($url, $post_params);
        $time = time()-$t;
          
        // پاسخ به کاربر در تلگرام
        $url         = $GLOBALS['bot_url'] . "/sendMessage";
        $post_params = [
          'chat_id'             => $GLOBALS['chat_id'] ,
          'reply_to_message_id' => $GLOBALS['message_id'] ,
          'text'                => "✅متن منتقل شد.\nزمان انتقال: $time ثانیه"
        ];
        send_reply($url, $post_params);
      }
    }

    //-------------------------------------

    // ذخیره فایل ها
    function save_file() {
      if ($GLOBALS['message_file_size'] < 20971520) { //20 MB
        $update_array = $GLOBALS['update_array'];

        $url = $GLOBALS['bot_url'] . "/getFile";
        $post_params = [ 'file_id' => $GLOBALS['message_file_id']];
        $result = send_reply($url, $post_params);

        $result_array = json_decode($result, true);
        $file_path    = $result_array["result"]["file_path"];
        
        $t = time();
          
        $url = $GLOBALS['bot_dl_url'] . "/$file_path";
        $file_data = file_get_contents($url);

        $file_path = $GLOBALS['message_file_name'];
        $my_file   = fopen($file_path, 'w');
        fwrite($my_file, $file_data);
        fclose($my_file);

        // ارسال فایل به ایتا
        $url     = $GLOBALS['et_bot_url'] . "/sendFile";
        $post_params = [
          'chat_id' => $GLOBALS['et_chat_id'] ,
          'file'    => new CURLFILE(realpath($file_path)) ,
          'caption' => $GLOBALS['message_caption']
        ];
        send_to_eitaa($url, $post_params);
          
        $time = time()-$t;
          
        show_reply("✅فایل منتقل شد.\n⌛️زمان انتقال: $time ثانیه");

        unlink($file_path); // حذف فایل بعد از انتقال

        }

        // خطا
        else if($message_file_size > 20971520) {  // 20 MB
            show_reply("🚫حجم فایل نباید بیش از 20 مگابایت باشد!");
        }
    }

    //-------------------------------------

    // ذخیره موسیقی
    function save_audio() 
    {
      if ($GLOBALS['message_audio_size'] < 20971520) { // 20 MB
        $update_array = $GLOBALS['update_array'];

        $url = $GLOBALS['bot_url'] . "/getFile";
        $post_params = [ 'file_id' => $GLOBALS["message_audio_id"] ];
        $result = send_reply($url, $post_params);

        $result_array = json_decode($result, true);
        $file_path    = $result_array["result"]["file_path"];
        
        $t = time();
          
        $url = $GLOBALS['bot_dl_url'] . "/$file_path";
        $file_data = file_get_contents($url);

        $img_path = $GLOBALS['message_audio_title'];
        $my_file  = fopen($img_path, 'w');
        fwrite($my_file, $file_data);
        fclose($my_file);

        // ارسال موسیقی به ایتا
        $url         = $GLOBALS['et_bot_url'] . "/sendFile";
        $post_params = [
          'chat_id'   => $GLOBALS['et_chat_id'] ,
          'file'      => new CURLFILE(realpath($GLOBALS['message_audio_title'])) ,
          'caption'   => $GLOBALS['message_caption']
        ];
        send_to_eitaa($url, $post_params);
         
        $time = time()-$t;
        
        show_reply("✅موسیقی منتقل شد.\n⌛️زمان انتقال: $time ثانیه");

        unlink($GLOBALS['message_audio_title']); // حذف موسیقی بعد از انتقال
      }

      // خطا
      else if($GLOBALS['message_audio_size'] > 20971520) {  // 20 MB
          show_reply("🚫حجم موسیقی نباید بیش از 20 مگابایت باشد!");
      }
    }

    //-------------------------------------

    // ذخیره پیام صوتی
    function save_voice() 
    {
      if ($GLOBALS['message_voice_size'] < 20971520) { // 20 MB
        $update_array = $GLOBALS['update_array'];

        $url = $GLOBALS['bot_url'] . "/getFile";
        $post_params = [ 'file_id' => $GLOBALS["message_voice_id"] ];
        $result = send_reply($url, $post_params);

        $result_array = json_decode($result, true);
        $file_path    = $result_array["result"]["file_path"];
        
        $t = time();
          
        $url = $GLOBALS['bot_dl_url'] . "/$file_path";
        $file_data = file_get_contents($url);

        $voice_path = "Files/voice.ogg";
        $my_file  = fopen($voice_path, 'w');
        fwrite($my_file, $file_data);
        fclose($my_file);
        
        // ارسال پیام صوتی به ایتا
        $url         = $GLOBALS['et_bot_url'] . "/sendFile";
        $post_params = [
          'chat_id' => $GLOBALS['et_chat_id'] ,
          'file'    => new CURLFILE(realpath($voice_path)) ,
          'caption' => $GLOBALS['message_caption']
        ];
        send_to_eitaa($url, $post_params);
          
        $time = time()-$t;
          
        show_reply("✅پیام صوتی منتقل شد.\n⌛️زمان انتقال: $time ثانیه");

        unlink($voice_path); // حذف پیام صوتی بعد از انتقال
      }

      // خطا
      else if($GLOBALS['message_voice_size'] > 20971520) {  // 20 MB
          show_reply("🚫حجم پیام صوتی نباید بیش از 20 مگابایت باشد!");
      }
    }

    //-------------------------------------

    // ذخیره ویدیو
    function save_video() 
    {
      if ($GLOBALS['message_video_size'] < 20971520) { // 20 MB
        $update_array = $GLOBALS['update_array'];

        $url = $GLOBALS['bot_url'] . "/getFile";
        $post_params = [ 'file_id' => $GLOBALS["message_video_id"] ];
        $result = send_reply($url, $post_params);

        $result_array = json_decode($result, true);
        $file_path    = $result_array["result"]["file_path"];
        
        $t = time();
          
        $url = $GLOBALS['bot_dl_url'] . "/$file_path";
        $file_data = file_get_contents($url);

        $video_path = "Files/" . "video_" . rand(1, 9999999) . ".mp4";;
        $my_file  = fopen($video_path, 'w');
        fwrite($my_file, $file_data);
        fclose($my_file);
    
        // ارسال ویدیو به ایتا
        $url         = $GLOBALS['et_bot_url'] . "/sendFile";
        $post_params = [
          'chat_id' => $GLOBALS['et_chat_id'] ,
          'file'    => new CURLFILE(realpath($video_path)) ,
          'caption' => $GLOBALS['message_caption']
        ];
        send_to_eitaa($url, $post_params);
        
        $time = time()-$t;
          
        show_reply("✅ویدیو منتقل شد.\n⌛️زمان انتقال: $time ثانیه");
        
        unlink($video_path); // حذف ویدیو بعد از انتقال
      }

      // خطا
      else if($GLOBALS['message_video_size'] > 20971520) {  // 20 MB
          show_reply("🚫حجم ویدیو نباید بیش از 20 مگابایت باشد!");
      }
    }

    //-------------------------------------

    // ذخیره استیکر
    function save_sticker() 
    {
      $update_array = $GLOBALS['update_array'];

      $url = $GLOBALS['bot_url'] . "/getFile";
      $post_params = [ 'file_id' => $GLOBALS["message_sticker_id"] ];
      $result = send_reply($url, $post_params);

      $result_array = json_decode($result, true);
      $file_path    = $result_array["result"]["file_path"];
      
      $t = time();
       
      $url = $GLOBALS['bot_dl_url'] . "/$file_path";
      $file_data = file_get_contents($url);

      $sticker_path = "Files/" . "sticker_" . rand(1, 9999999) . ".webp";;
      $my_file  = fopen($sticker_path, 'w');
      fwrite($my_file, $file_data);
      fclose($my_file);
      
      // ارسال استیکر به ایتا
      $url         = $GLOBALS['et_bot_url'] . "/sendFile";
      $post_params = [
        'chat_id' => $GLOBALS['et_chat_id'] ,
        'file'    => new CURLFILE(realpath($sticker_path))
      ];
      send_to_eitaa($url, $post_params);
        
      $time = time()-$t;
        
      show_reply("✅استیکر منتقل شد.\n⌛️زمان انتقال: $time ثانیه");

      unlink($sticker_path); // حذف استیکر بعد از انتقال
    }


    //-------------------------------------

    // ذخیره تصویر
    function save_photo() 
    {
        $update_array = $GLOBALS['update_array'];
        
        $diff_size_count = sizeof($update_array["message"]["photo"]);
        
        for($i = $diff_size_count - 1 ; $i >= 0 ; $i--)
        {
            $file_size = $update_array["message"]["photo"][$i]["file_size"];
            if($file_size < 1000000)
            {  // 1 MB
                $file_id = $update_array["message"]["photo"][$i]["file_id"];
                break;
            }
        }

        $url = $GLOBALS['bot_url'] . "/getFile";
        $post_params = [ 'file_id' => $file_id ];
        $result = send_reply($url, $post_params);

        $result_array = json_decode($result, true);
        $file_path    = $result_array["result"]["file_path"];

        $url = $GLOBALS['bot_dl_url'] . "/$file_path";
        $file_data = file_get_contents($url);

        $img_path = "Files/" . "image_" . rand(1, 9999999) . ".jpg";;
        $my_file  = fopen($img_path, 'w');
        fwrite($my_file, $file_data);
        fclose($my_file);

        $t = time();
        
        // ارسال تصویر به ایتا
        $url         = $GLOBALS['et_bot_url'] . "/sendFile";
        $post_params = [
          'chat_id' => $GLOBALS['et_chat_id'] ,
          'file'    => new CURLFILE(realpath($img_path)) ,
          'caption' => $GLOBALS['message_caption']
        ];
        send_to_eitaa($url, $post_params);
        
        $time = time()-$t;
        
        show_reply("✅تصویر منتقل شد.\n⌛️زمان انتقال: $time ثانیه");

        unlink($img_path); // حذف تصویر بعد از انتقال
    }

    //-------------------------------------

    // ارسال اطلاعات به سرور تلگرام با روش کرل
    function send_reply($url, $post_params) 
    {
        $cu = curl_init();
        curl_setopt($cu, CURLOPT_URL, $url);
        curl_setopt($cu, CURLOPT_POSTFIELDS, $post_params);
        curl_setopt($cu, CURLOPT_RETURNTRANSFER, true);  // get result
        $result = curl_exec($cu);
        curl_close($cu);
        return $result;
    }

    // ارسال اطلاعات به سرور ایتا با روش کرل
    function send_to_eitaa($url , $post_params) 
    {
        $cu  = curl_init();
        curl_setopt($cu, CURLOPT_URL, $url);
        curl_setopt($cu, CURLOPT_POSTFIELDS, $post_params);
        curl_setopt($cu, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cu, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($cu);
        curl_close($cu);
        return $result;
        if (curl_error($cu)) {var_dump(curl_error($cu));}
        else {return json_decode($result);}
    }

    // ارسال پاسخ به کاربر در تلگرام
    function show_reply($reply) 
    {
      $reply;
      $url = $GLOBALS['bot_url'] . "/sendMessage";
      $post_params = [
        'chat_id' => $GLOBALS['chat_id'] ,
        'text'    => $reply ,
        'reply_to_message_id' => $GLOBALS['message_id']
      ];
      send_reply($url, $post_params);
    }

    //-------------------------------------
?>
