# whmcs-TeamSpeak3-backaup-addon (FTP)
Файлы разместить в папке \billing.dev.service-voice.com\modules\addons 
billing.dev.service-voice.com - корень whmcs
## Крон
  0  */6  *  *  * root php -q /var/www/billing.dev.service-voice.com/modules/addons/TeamSpeakBackaup/cron.php <br>
  0  0  *  *  *   root php -q /var/www/billing.dev.service-voice.com/modules/addons/TeamSpeakBackaup/cron.php --icon

Первая строка говорит о том что нужно бекапить только "снапшоты" <br/>
Вторая строка говорит о том что нужно бекапить снапшоты+иконки
## Доп информация
Резервное копирование файлов реализовано НЕ будет в виду высокой нагрузки при выполнении этого процесса.
