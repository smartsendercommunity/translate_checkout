# translate_checkout
Теперь модификация корзины (перевод) возможна )))


Стандартный вид корзины пользователя https://image.mufiksoft.com/Telegram_1vLdLu6vtB.jpg доступен только на нескольких языках и определение языка устанавливается настройками аккаунта владельца проекта

Модифицированый вид корзины может быть на любом языке и с любым текстом, в зависимости от цели Вашего проекта https://image.mufiksoft.com/Telegram_hp3Pgg7Log.jpg

Для отправки этого сообщения с корзиной используется "внешний запрос" на файл скрипта со следующей структурой: https://image.mufiksoft.com/chrome_0KCfEHzKwK.jpg


{

"userId":"{{ userId }}",

"token":"{{ sstoken }}",

"text":"Привет. В Вашей корзинке имеется:\n\n{checkout}\nНа общую сумму {sum} {currency}",

"null":"А корзинка то пустая"

}
