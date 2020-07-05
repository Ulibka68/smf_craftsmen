<?php
// Version: 2.0; Modifications


// Say Thanks Mod
// Russian translation by Sergey (http://www.za40.org)
$txt['saythanks_text'] = 'Сказать Спасибо';
$txt['saythanks_thanked'] = 'Пользователи, которые поблагодарили этот пост: ';
$txt['saythanks_thankscount'] = 'Поблагодарили';
$txt['saythanks_times'] = 'раз(а)'; 
$txt['saythanks_withdraw_thanks'] = 'Отменить Спасибо';
$txt['saythanks_settings'] = 'Сказать Спасибо';
$txt['saythanks_withdraw_thanks_settings_desc'] = 'Включить возможность отмены благодарности';
$txt['saythanks_hide_thanks_count_on_post_settings_desc'] = 'Скрыть счётчик благодарностей рядом с сообщением';
$txt['saythanks_hide_thanks_count_in_profile_settings_desc'] = 'Скрыть благодарности в профилях пользователей';
$txt['saythanks_hide_thanks_stats_settings_desc'] = 'Скрыть рейтинг благодарностей';
$txt['saythanks_show_thanked_posts'] = 'Сообщения, которые понравились пользователям';
$txt['saythanks_show_thank_by_user_posts'] = 'Сообщения, за которые поблагодарил пользователь';
$txt['saythanks_most_thanked'] = 'Топ 10 пользователей, получивших Спасибо';
$txt['saythanks_most_thanks_given'] = 'Топ 10 Спасибо';
$txt['saythanks_most_thanked_posts'] = 'Топ 10 сообщений, отмеченных Спасибо';
$txt['saythanks_disable_on_boards'] = 'Отключить в разделах';
$txt['saythanks_title'] = 'Скрыть подсчёт поблагодаривших';
$txt['saythanks_hide_text'] = 'Вам это нужно {thanks_needed}, спасибо. Мы нуждаемся в вас {thanks_left}, спасибо.';
$txt['saythanks_desc'] = 'Сокрытие содержимого сообщения в зависимости от числа поблагодаривших.';
$txt['saythanks_thanked_title'] = 'Скрыть от поблагодаривших';
$txt['saythanks_hide_thanked_text'] = 'Чтобы увидеть содержимое, вы должны вначале нажать Спасибо.';
$txt['saythanks_thanked_desc'] = 'Сокрытие содержимого сообщения в зависимости от числа поблагодаривших.';
$txt['saythanks_input_title'] = 'Подсчёт минимума поблагодаривших';
$txt['saythanks_ajax_success'] = 'Спасибо';
$txt['saythanks_ajax_loading'] = 'Спасибо...';
$txt['saythanks_ajax_error'] = 'Ошибка во время нажатия Спасибо';
$txt['saythanks_ajax_guest'] = '<a href="' . $scripturl . '?action=login">Войти, чтобы поблагодарить это сообщение</a>';
// Say Thanks Mod

// TrimUrl
$txt['urlLength'] = 'Trim url longer than (x) characters.';


// Bookmarks
$txt['bookmarks'] = 'Закладки';
$txt['bookmark_list'] = 'Закладки';
$txt['bookmark_list_empty'] = 'У Вас ещё нет закладок. Добавить закладки можно нажав на ссылку "Добавить закладку" внутри топиков.';
$txt['bookmark_open_window'] = 'Открыть в новом окне';
$txt['bookmark_delete'] = 'Удалить выделенные';
$txt['bookmark_delete_success'] = 'Было удалено закладок: %1$s!';
$txt['bookmark_delete_failure'] = 'Не все закладки удалились.';
$txt['bookmark_add'] = 'Добавить закладку';
$txt['bookmark_add_success'] = 'Топик был успешно добавлен в Ваши закладки!';
$txt['bookmark_add_exists'] = 'Этот топик уже есть в Ваших закладках!';
$txt['bookmark_add_failed'] = 'При добавлении закладки произошла ошибка.';
$txt['permissionname_make_bookmarks'] = 'Создавать закладки для топиков';
$txt['cannot_make_bookmarks'] = 'Извините, у Вас нет разрешения добавлять закладки.';
// CleanTalk messages begin
$txt['cleantalk_name'] = 'Антиспам от CleanTalk';
$txt['cleantalk_settings'] = 'Антиспам CleanTalk Mod настройки';
$txt['cleantalk_api_key'] = 'Ключ доступа';
$txt['cleantalk_check_registrations'] = 'Включить проверку регистраций на спам';
$txt['cleantalk_get_access_manually'] = 'Получить ключ';
$txt['cleantalk_get_access_key_or'] = 'или';
$txt['cleantalk_get_access_automatically'] = 'Получить ключ автоматически';
$txt['cleantalk_license_agreement'] = 'Лицензионное соглашение';
$txt['cleantalk_key_valid'] = 'Ключ верен';
$txt['cleantalk_account_name_ob'] = 'Аккаунт на cleantalk.org';
$txt['cleantalk_moderate_ip'] = 'Антиспам сервис оплачен вашим хостин провайдером. Лицензия #';
$txt['cleantalk_key_not_valid'] = 'Ключ неверен!';
$txt['cleantalk_get_statistics'] = 'Просмотр статистики работы антиспама';
$txt['cleantalk_admin_email_will_be_used'] = 'Для регистрации будет использован E-mail администратора (%s)';
$txt['cleantalk_first_post_checking'] = 'Включить проверку новых пользователей на спам';
$txt['cleantalk_first_post_checking_postinput'] = 'Все сообщения от пользователей группы <a href="index.php?action=moderate;area=viewgroups;sa=members;group=4">%GROUP%</a> будут проверены на спам';
$txt['cleantalk_automod'] = 'Включить автомодерацию сообщений';
$txt['cleantalk_automod_postinput'] = 'Автоматически помещать подозрительные комментарии, которые могут не являться 100% спамом на ручную модерацию и блокировать очевидный спам. Убедитесь, что у вас включена опция <b>Пост модерации</b>';
$txt['cleantalk_api_key_description'] = 'Домашняя страница расширения: <a target="_blank" href="https://cleantalk.org/">http://cleantalk.org</a>';
$txt['cleantalk_logging'] = 'Включить логирование ответов сервиса';
$txt['cleantalk_logging_postinput'] = 'Все действия плагина будут записываться в лог форума (<a target="_blank" href="%s/index.php?action=admin;area=logs;sa=errorlog;desc">Админка -> Логи ошибок</a>)';
$txt['cleantalk_tell_others'] = 'Рассказать другим про CleanTalk';
$txt['cleantalk_email_notifications'] = 'Включить e-mail уведомления для администраторов';
$txt['cleantalk_email_notifications_postinput'] = 'Отправлять e-mail уведомления администраторам о пропуске/блокировке сообщения или регистрации';
$txt['cleantalk_check_personal_messages'] = 'Проверять личные сообщение';
$txt['cleantalk_check_personal_messages_postinput'] = 'Проверять все личные сообщения пользователей на спам';
$txt['cleantalk_ccf_checking'] = 'Проверять кастомные формы';
$txt['cleantalk_ccf_checking_postinput'] = 'Может вызвать конфликты!';
$txt['cleantalk_tell_others_postinput'] = 'При выставлении этого флажка добавляется маленькая ссылка в футере, чтобы другие узнали об антиспаме, которым защищен ваш сайт.';
$txt['cleantalk_tell_others_footer_message'] = '<a href="https://cleantalk.org/smf-anti-spam-mod">Защита SMF от спама</a> от CleanTalk';
$txt['cleantalk_sfw'] = 'Включить Spam FireWall';
$txt['cleantalk_sfw_postinput'] = 'Включение этой опции позволит Вам фильтровать ботов до того, как они зайдут на сайт. Также это снизит нагрузку на сервер.';
$txt['cleantalk_check_users'] = '%CLEANTALK_CHECK_USERS%';
$txt['cleantalk_check_users_button'] = 'Проверить пользователей на спам';
$txt['cleantalk_check_users_button_after'] = 'Антиспам от CleanTalk проверит всех пользователей по черным спискам и покажет Вам отправителей, которые проявляли спам-активность на других сайтах.';
$txt['cleantalk_check_users_key_is_bad'] = 'Нет доступа к облачной базе данных. Ключ доступа не действителен.';
$txt['cleantalk_check_users_nofound'] = 'Спамеров не найдено';
$txt['cleantalk_check_users_tbl_select'] = 'Выбрать';
$txt['cleantalk_check_users_tbl_username'] = 'Пользователь';
$txt['cleantalk_check_users_tbl_username_details'] = 'Детали';
$txt['cleantalk_check_users_tbl_joined'] = 'Регистрация';
$txt['cleantalk_check_users_tbl_lastvisit'] = 'Посл. визит';
$txt['cleantalk_check_users_tbl_posts'] = 'Сообщений';
$txt['cleantalk_check_users_tbl_posts_show'] = 'Показать';
$txt['cleantalk_check_users_pages'] = 'Страницы';
$txt['cleantalk_check_users_done'] = 'Готово. Все пользователи проверены по черному списку, результаты в таблице ниже.';
$txt['cleantalk_check_users_tbl_delselect'] = 'Удалить выбранных';
$txt['cleantalk_check_users_tbl_delall'] = 'Удалить всех';
$txt['cleantalk_check_users_tbl_delnotice'] = 'Все темы и сообщения выбранных пользователей будут удалены';
$txt['cleantalk_check_users_confirm'] = 'Удалить пользователей со всеми темами и сообщениями?';
$txt['cleantalk_sfw_die_notice_ip'] = 'SpamFireWall включен для вашего IP ';
$txt['cleantalk_sfw_die_make_sure_js_enabled'] = 'Чтобы продолжить работу с сайтом, убедитесь что у вас включен JavaScript.';
$txt['cleantalk_sfw_die_click_to_pass'] = 'Нажмите ниже чтобы снять блокировку,';
$txt['cleantalk_sfw_die_you_will_be_redirected'] = 'Вы будете автоматически переадресованы на запрошенную вами страницу через 3 секунды.';
$txt['cleantalk_sfw_die_no_file'] = 'Ваш IP похож на спамерский.';
$txt['cleantalk_banner_renew_1'] = 'Пожалуйста, обновите вашу анти-спам лицензию на %s.';
$txt['cleantalk_banner_renew_2'] = 'следующий год';
$txt['cleantalk_banner_trial_1'] = 'Пробная подписка анти-спама Cleantalk заканчивается, пожалуйста обновитесь до %s!';
$txt['cleantalk_banner_trial_2'] = 'премиум версии';
$txt['cleantalk_banner_bad_key_1'] = 'Пожалуйста, введите ключ доступа в настройках %s чтобы включить анти-спам защиту!';
$txt['cleantalk_banner_bad_key_2'] = 'мода CleanTalk';
// CleanTalk messages end
