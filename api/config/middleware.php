<?php

declare(strict_types=1);

use Middlewares\ContentLanguage;
use Slim\App;
use Slim\Middleware\ErrorMiddleware;
use App\Http\Middleware;

return static function (App $app): void {
    //Свой собственный посредник, который блоком try catch оборачивает наш собственный action
    $app->add(Middleware\DomainExceptionHandler::class);
    //Свой собственный посредник, который отлавливает ошибки валидации,
    // чтобы не копипастить код по валидации в наших actions
    $app->add(Middleware\ValidationExceptionHandler::class);
    //Свой собственный посредник для очистки строк от пробелов и от не загруженных файлов
    $app->add(Middleware\ClearEmptyInput::class);
    //Свой собственный посредники, которые устанавливают язык переводчика
    // исходя из заголовка Accept-Language у HTTP запроса
    $app->add(Middleware\TranslatorLocale::class);
    $app->add(ContentLanguage::class);
    //Посредник, которые парсит наш body запроса из разных форматов (таких как xml, json...)
    //В зависимости от того, какой запрос прилетел, производится декодирование запроса
    $app->addBodyParsingMiddleware();
    // Данный посредник отлавливает все ошибки (блок try catch),
    // какие могут возникнуть, и какие нами не перехвачены в коде
    $app->add(ErrorMiddleware::class);
};
