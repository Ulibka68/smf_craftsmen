<?php
if (mail("test-uzdcgqfqe@srv1.mail-tester.com", "заголовок", "текст сообщения")) {
    echo 'Отправлено';
}
else {
    echo 'Не отправлено';
}