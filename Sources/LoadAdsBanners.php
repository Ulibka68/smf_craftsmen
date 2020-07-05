<?php
// Заполнение баннеров идет в LoadAds строка 80

// код для вставки наверх в ad -тестирование без коментариев
function list_ad_img_100_top() {
    global $ad_images,$modSettings;

    echo '<div class="ad_images_100">';
    $cnt=1;
    $keys_in_show = [];
    $total_weight=0;
    foreach ($ad_images as $key => $img) {
        if ($img['width'] !== '100') continue;
        $keys_in_show[] = array( 'key' => $key , 'weight' => $img['weight']);
        $total_weight += $img['weight'];
    }

    foreach ($keys_in_show as &$key) {
        $key['probability'] = mt_rand(0,$total_weight) * $key['weight'];
    }

    usort ($keys_in_show,function ($a,$b) {
        if ($a['probability'] == $b['probability']) {
            return 0;
        }
        return ($a['probability'] < $b['probability']) ? 1 : -1;
    });

    $cntMax = (int) $modSettings['ad_images_100_count_page'];
    for ($i = 0; $i < $cntMax; $i++) {
        $img = $ad_images[$keys_in_show[$i]['key']];
        echo PHP_EOL . '<a href="',$img['url'],'" target="_blank" rel="nofollow">' . PHP_EOL . '<img src="',$img['name'],'"></a>';
    }
    echo '</div>';

}

// текст баннера Yandex
function YandexScriptAD($place = 'desktop') {

    if ($place === 'desktop') {
        echo '<!-- Yandex.RTB R-A-590689-1 -->
            <div id="yandex_rtb_R-A-590689-1"></div>
            <script type="text/javascript">
                        (function(w, d, n, s, t) {
                            w[n] = w[n] || [];
                            w[n].push(function() {
                                Ya.Context.AdvManager.render({
                            blockId: "R-A-590689-1",
                            renderTo: "yandex_rtb_R-A-590689-1",
                            async: true
                        });
                    });
                            t = d.getElementsByTagName("script")[0];
                            s = d.createElement("script");
                            s.type = "text/javascript";
                            s.src = "//an.yandex.ru/system/context.js";
                            s.async = true;
                            t.parentNode.insertBefore(s, t);
                        })(this, this.document, "yandexContextAsyncCallbacks");
            </script>
            ';
    } else {
        echo '
        <!-- Yandex.RTB R-A-590689-2 -->
        <div id="yandex_rtb_R-A-590689-2"></div>
        <script type="text/javascript">
            (function(w, d, n, s, t) {
                w[n] = w[n] || [];
                w[n].push(function() {
                    Ya.Context.AdvManager.render({
                        blockId: "R-A-590689-2",
                        renderTo: "yandex_rtb_R-A-590689-2",
                        async: true
                    });
                });
                t = d.getElementsByTagName("script")[0];
                s = d.createElement("script");
                s.type = "text/javascript";
                s.src = "//an.yandex.ru/system/context.js";
                s.async = true;
                t.parentNode.insertBefore(s, t);
            })(this, this.document, "yandexContextAsyncCallbacks");
        </script>
        ';
    }

}




function show_mobile_ads() {
    global $ad_images,$modSettings,$context;

    if ( check_no_ads () ) return;


//    на мобильных рекламу справа не показывать
    if (! $context['is_mobile'] ) return;

    echo '<div class="ad_images_240_mobile">';
    $cnt=1;
    $keys_in_show = [];
    $total_weight=0;
    $total_cnt_fact = 0;
    foreach ($ad_images as $key => $img) {
//        бесплатный баннер не показывать
        if ($img['width'] !== '240' || $img['free_banner']) continue;
        $keys_in_show[] = array( 'key' => $key , 'weight' => $img['weight']);
        $total_weight += $img['weight'];
        $total_cnt_fact++;
    }

    $total_cnt_fact++; // с учетом Яндекс
    $yandex_weight = 20;
    $keys_in_show['yandex']=['weight'=>$yandex_weight,'key'=>'yandex'];
    $total_weight += $yandex_weight;

    foreach ($keys_in_show as &$key) {
        $key['probability'] = mt_rand(0,$total_weight) * $key['weight'];
    }

    usort ($keys_in_show,function ($a,$b) {
        if ($a['probability'] == $b['probability']) {
            return 0;
        }
        return ($a['probability'] < $b['probability']) ? 1 : -1;
    });



    $cntMax = (int) $modSettings['ad_images_right_240_count_page'];
    $cntMax = $cntMax < $total_cnt_fact ? $cntMax : $total_cnt_fact;

    for ($i = 0; $i < 1; $i++) {
        $j = $keys_in_show[$i]['key'];
        if ( $j === 'yandex') {
            YandexScriptAD('mobile'); // выводим Яндекс
        } else {
            $img = $ad_images[$j];
            echo PHP_EOL . '<a href="', $img['url'], '" target="_blank" rel="nofollow">' . PHP_EOL . '<img src="', $img['name'], '"></a>';
        }
    }
    echo '</div>';
}

function check_no_ads()
{
    global $user_info;
    $ret_val = false;
    if (!empty($user_info) && !empty($user_info['groups'])) {
        if (in_array(255, $user_info['groups'])) {
            $ret_val = true;
        }
    } ;

    return $ret_val;
}

// код для вставки наверх в ad -тестирование без коментариев
function list_ad_img_240_right($place = 'right') {
   global $ad_images,$modSettings,$context;

   if ( check_no_ads () ) return;

//    на мобильных рекламу справа не показывать
   if ($place === 'right' && $context['is_mobile'] ) return;
//    заглушка - на мобиле рекламу отключить
   if ( $context['is_mobile'] ) return;
   if ($place != 'right' ) return;

   echo '<div class="ad_images_240">';
   $cnt=1;
   $keys_in_show = [];
   $total_weight=0;
   $total_cnt_fact = 0;
   foreach ($ad_images as $key => $img) {
       if ($img['width'] !== '240') continue;
       $keys_in_show[] = array( 'key' => $key , 'weight' => $img['weight']);
       $total_weight += $img['weight'];
       $total_cnt_fact++;
   }

   $total_cnt_fact++; // с учетом Яндекс
   $yandex_weight = 20;
   $keys_in_show['yandex']=['weight'=>$yandex_weight,'key'=>'yandex'];
   $total_weight += $yandex_weight;

   foreach ($keys_in_show as &$key) {
       $key['probability'] = mt_rand(0,$total_weight) * $key['weight'];
   }

   usort ($keys_in_show,function ($a,$b) {
       if ($a['probability'] == $b['probability']) {
           return 0;
       }
       return ($a['probability'] < $b['probability']) ? 1 : -1;
   });



   $cntMax = (int) $modSettings['ad_images_right_240_count_page'];
   $cntMax = $cntMax < $total_cnt_fact ? $cntMax : $total_cnt_fact;
   for ($i = 0; $i < $cntMax; $i++) {
       $j = $keys_in_show[$i]['key'];
       if ( $j === 'yandex') {
           YandexScriptAD(); // выводим Яндекс
       } else {
           $img = $ad_images[$j];
           echo PHP_EOL . '<a href="', $img['url'], '" target="_blank" rel="nofollow">' . PHP_EOL . '<img src="', $img['name'], '"></a>';
       }
   }
   echo '</div>';

}