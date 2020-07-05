<?php
if (mail("gena68@inbox.ru", "заголовок", "текст")) {
    echo 'Отправлено';
}
else {
    echo 'Не отправлено';
}